<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use App\Models\Audit;
use App\Models\AuditeeAction;
use App\Models\AuditFinding;
use App\Models\CorrectiveAction;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\PreventiveAction;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\Status;
use App\Models\User;
use App\Models\WhyCauses;
use App\Notifications\AuditeeAssignedNotification;
use App\Notifications\FtppActionNotification;
use App\Notifications\DeptHeadNeedCheckNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuditeeActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $id)
    {
        $finding = AuditFinding::with([
            'audit',
            'subAudit',
            'findingCategory',
            'auditor',
            'auditee',
            'department',
            'process',
            'product',
            'subKlausuls',  // pastikan relasi ini ada
            'file',
            'status',
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file',
        ])->findOrFail($id);

        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();
        $subAudit = SubAudit::all();
        $findingCategories = FindingCategory::all();
        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();

        // Debug: cek apakah sub_klausuls terload
        // dd($finding->subKlausuls); // uncomment untuk debug

        return view('contents.ftpp2.auditee-action.create', compact('finding', 'departments', 'processes', 'products', 'auditors', 'auditTypes', 'subAudit', 'findingCategories', 'klausuls'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isDraft = $request->boolean('is_draft', false);

        try {
            // ✅ VALIDASI FILE SIZE
            $validated = $request->validate([
                'audit_finding_id' => 'required|exists:tt_audit_findings,id',
                'root_cause' => [$isDraft ? 'nullable' : 'required', 'string'],
                'pic' => 'nullable|string|max:255',
                'yokoten' => [$isDraft ? 'nullable' : 'required', Rule::in([0, 1, '0', '1'])],
                'yokoten_area' => [
                    'nullable',
                    'string',
                    Rule::requiredIf(function () use ($isDraft, $request) {
                        return !$isDraft && (string) $request->input('yokoten') === '1';
                    }),
                ],
                'ldr_spv_signature' => 'nullable|boolean',

                // Why and Cause validation
                'why_*_mengapa' => 'nullable|string',
                'cause_*_karena' => 'nullable|string',

                // Corrective and Preventive validation
                'corrective_*_activity' => 'nullable|string',
                'corrective_*_pic' => 'nullable|string',
                'corrective_*_planning' => 'nullable|date',
                'corrective_*_actual' => 'nullable|date',

                'preventive_*_activity' => 'nullable|string',
                'preventive_*_pic' => 'nullable|string',
                'preventive_*_planning' => 'nullable|date',
                'preventive_*_actual' => 'nullable|date',

                // Attachments: gabung semua file
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',

                'is_draft' => 'nullable|boolean',
            ]);

            $draftStatusId = Status::whereRaw('LOWER(name) = ?', ['draft'])->value('id');
            $needCheckStatusId = Status::whereRaw('LOWER(name) = ?', ['need check'])->value('id') ?? 8;
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in auditee action store: ' . json_encode($e->errors()));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(' ', array_map(fn($err) => implode(' ', $err), $e->errors())),
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        }

        // ✅ VALIDASI TOTAL FILE SIZE (SERVER-SIDE BACKUP)
        $totalSize = $this->calculateTotalFileSize($request);
        if ($totalSize > 20 * 1024 * 1024) { // 20MB
            \Log::warning('Total file size validation bypassed on client-side!');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total file size exceeds 20MB. Please compress your PDF files using <a href="https://smallpdf.com/compress-pdf" target="_blank">this tool</a>.'
                ], 422);
            }

            return back()->withErrors([
                'attachments' => 'Total file size exceeds 20MB. Please compress your PDF files. <a href="https://smallpdf.com/compress-pdf" target="_blank" class="text-blue-600 underline">Use this tool to compress</a>'
            ])->withInput();
        }


        DB::beginTransaction();
        try {
            // 1️⃣ Simpan tt_auditee_actions
            $auditeeAction = AuditeeAction::updateOrCreate(
                ['audit_finding_id' => $validated['audit_finding_id']],
                [
                    'pic' => $validated['pic'] ?? '-',
                    'root_cause' => $validated['root_cause'] ?? '',
                    'yokoten' => $validated['yokoten'] ?? ($isDraft ? null : 0),
                    'yokoten_area' => $validated['yokoten_area'] ?? null,
                    'ldr_spv_id' => auth()->user()->id,
                ]
            );

            // Hapus child lama agar sync (opsional tapi direkomendasikan saat update)
            if ($auditeeAction && $auditeeAction->id) {
                $aid = $auditeeAction->id;

                if (WhyCauses::where('auditee_action_id', $aid)->exists()) {
                    WhyCauses::where('auditee_action_id', $aid)->delete();
                }

                if (CorrectiveAction::where('auditee_action_id', $aid)->exists()) {
                    CorrectiveAction::where('auditee_action_id', $aid)->delete();
                }

                if (PreventiveAction::where('auditee_action_id', $aid)->exists()) {
                    PreventiveAction::where('auditee_action_id', $aid)->delete();
                }

                // PATCH: Only delete DocumentFile not in existing_evidence_ids[]
                $keepIds = $request->input('existing_evidence_ids', []);
                $keepIds = array_map('intval', (array)$keepIds);
                $allFiles = DocumentFile::where('auditee_action_id', $aid)->get();
                foreach ($allFiles as $file) {
                    if (!in_array($file->id, $keepIds)) {
                        // Validasi: hanya hapus jika file milik auditee_action_id ini
                        if ($file->auditee_action_id != $aid) {
                            // Bisa log error atau return response error jika ajax
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Tidak dapat menghapus file yang bukan milik auditee action ini.'
                                ], 403);
                            } else {
                                return back()->withErrors(['attachments' => 'Tidak dapat menghapus file yang bukan milik auditee action ini.'])->withInput();
                            }
                        }
                        try {
                            $original = $file->file_path ?? '';
                            $candidates = [];
                            if ($original !== '') {
                                $candidates[] = $original;
                                $candidates[] = ltrim($original, '/');
                                if (preg_match('#/storage/(.*)$#', $original, $m)) {
                                    $candidates[] = $m[1];
                                }
                                $candidates[] = preg_replace('#^public/storage/#', '', $original);
                                $candidates[] = basename($original);
                            }
                            foreach (array_filter(array_unique($candidates)) as $p) {
                                if ($p === '') continue;
                                if (Storage::disk('public')->exists($p)) {
                                    Storage::disk('public')->delete($p);
                                    break;
                                }
                                $fsPath = storage_path('app/public/' . $p);
                                if (file_exists($fsPath)) {
                                    @unlink($fsPath);
                                    break;
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::warning("Failed to delete file for DocumentFile id={$file->id}: " . $e->getMessage());
                        }
                        $file->delete();
                    }
                }
            }

            // 3️⃣ Simpan Why (5 Why)
            for ($i = 1; $i <= 5; $i++) {
                $why = $request->input('why_' . $i . '_mengapa');
                $cause = $request->input('cause_' . $i . '_karena');
                if ($why || $cause) {
                    WhyCauses::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'why_description' => $why ?? '',
                        'cause_description' => $cause ?? '',
                    ]);
                }
            }

            // 4️⃣ Simpan Corrective Action
            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input('corrective_' . $i . '_activity');
                $pic = $request->input('corrective_' . $i . '_pic');
                $plan = $request->input('corrective_' . $i . '_planning');
                $actual = $request->input('corrective_' . $i . '_actual');

                if ($activity) {
                    CorrectiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'pic' => $pic ?: '-',
                        'activity' => $activity,
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
                    ]);
                }
            }

            // 5️⃣ Simpan Preventive Action
            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input('preventive_' . $i . '_activity');
                $pic = $request->input('preventive_' . $i . '_pic');
                $plan = $request->input('preventive_' . $i . '_planning');
                $actual = $request->input('preventive_' . $i . '_actual');

                if ($activity) {
                    PreventiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'pic' => $pic ?: '-',
                        'activity' => $activity,
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
                    ]);
                }
            }

            // 6️⃣ Upload Attachments (pastikan form mengirim 'attachments[]')
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $stored = $this->compressAndStore($file, 'ftpp/auditee_action_attachments');
                    DocumentFile::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'file_path' => $stored['path'],
                        'original_name' => $stored['original'],
                    ]);
                }
            }

            // update status finding
            $auditFinding = AuditFinding::find($validated['audit_finding_id']);
            if ($auditFinding) {
                if ($isDraft && $draftStatusId) {
                    $auditFinding->update(['status_id' => $draftStatusId]);
                } elseif (!$isDraft) {
                    $auditFinding->update(['status_id' => $needCheckStatusId]);
                }
            }

            if (!$isDraft && $request->has('approve_ldr_spv') && $request->approve_ldr_spv == 1) {
                $auditeeAction->update([
                    'ldr_spv_signature' => 1,
                    'ldr_spv_id' => auth()->user()->id
                ]);
            }

            DB::commit();

            // notify auditee(s) and auditor that auditee action / assignment exists (skip draft)
            try {
                if (!$isDraft && !empty($auditFinding) && $auditFinding instanceof AuditFinding) {

                    // 1️⃣ Notify auditees + auditor that auditee action / assignment exists
                    if ($auditFinding->auditor) {
                        Notification::send(
                            $auditFinding->auditor,
                            new FtppActionNotification(
                                $auditFinding,
                                'assigned',
                                auth()->user()?->name
                            )
                        );
                    }

                    // 2️⃣ Notify Dept Head(s) of the finding's department that review is required
                    $deptId = $auditFinding?->department_id;
                    if (!empty($deptId)) {
                        $deptHeads = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['dept head']))
                            ->where(function ($q) use ($deptId) {
                                $q->whereExists(function ($sub) use ($deptId) {
                                    $sub->select(\DB::raw(1))
                                        ->from('tt_user_department')
                                        ->whereColumn('tt_user_department.user_id', 'users.id')
                                        ->where('tt_user_department.department_id', $deptId);
                                });

                                if (Schema::hasColumn('users', 'department_id')) {
                                    $q->orWhere('department_id', $deptId);
                                }
                            })
                            ->get();

                        if ($deptHeads->isNotEmpty()) {
                            $regNum = $auditFinding?->registration_number ?? 'N/A';
                            $customMessage = "Finding (No: {$regNum}) needs your review.";

                            // Separate mail recipients (have email) from all dept heads
                            $allDeptHeads = $deptHeads->unique('id')->values();
                            $mailRecipients = $allDeptHeads->filter(fn($u) => !empty($u->email))->values();

                            try {
                                $emailsAll = $allDeptHeads->pluck('email')->toArray();
                                $emailsMail = $mailRecipients->pluck('email')->toArray();
                                \Log::info('DeptHeadNeedCheckNotification: recipients', ['all' => $emailsAll, 'mail' => $emailsMail, 'reply_to' => auth()->user()?->email]);
                            } catch (\Throwable $e) {
                                \Log::warning('Failed to log dept head recipients: ' . $e->getMessage());
                            }

                            // 1) database notification for all dept heads (so they see it in-app)
                            Notification::send(
                                $allDeptHeads,
                                new FtppActionNotification(
                                    $auditFinding,
                                    'assigned',
                                    null,
                                    $customMessage
                                )
                            );

                            // 2) send email-only notification to users that have email addresses
                            if ($mailRecipients->isNotEmpty()) {
                                Notification::send(
                                    $mailRecipients,
                                    new DeptHeadNeedCheckNotification(
                                        $auditFinding,
                                        $auditeeAction,
                                        auth()->user()?->name,
                                        auth()->user()?->email
                                    )
                                );
                            } else {
                                \Log::warning('DeptHeadNeedCheckNotification: no dept head email addresses found for department_id ' . ($deptId ?? 'N/A'));
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('FindingActionNotification failed: ' . $e->getMessage());
            }

            $successMessage = $isDraft ? 'Draft saved successfully.' : 'Auditee Action submitted successfully.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'id' => $auditeeAction->id
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            \Log::error('Database error in auditee action store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database error occurred. Please try again or contact administrator.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Database error occurred. Please try again.'])
                ->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            // log error agar lebih mudah debug
            \Log::error('update_auditee_action error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $finding = AuditFinding::with([
            'audit',
            'subAudit',
            'findingCategory',
            'auditor',
            'auditee',
            'department',
            'process',
            'product',
            'subKlausuls',  // pastikan relasi ini ada
            'file',
            'status',
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file',
        ])->findOrFail($id);

        $auditTypes = Audit::with('subAudit')->get();
        $subAudit = SubAudit::all();

        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $findingCategories = FindingCategory::all();
        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();

        return view('contents.ftpp2.auditee-action.edit', compact('finding', 'subAudit', 'auditTypes', 'departments', 'processes', 'products', 'auditors', 'findingCategories', 'klausuls'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // ✅ VALIDASI FILE SIZE
        $validated = $request->validate([
            'root_cause' => 'required|string',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',

            // Images: allow common image types (no individual size limit)
            'photos2.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',

            // Files: allow common image types (no individual size limit)
            'files2.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',

            // Attachments: allow images and PDF (no individual size limit — total enforced separately)
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf',

            'remove_attachments.*' => 'nullable|numeric',
        ]);

        // ✅ VALIDASI TOTAL FILE SIZE
        $totalSize = $this->calculateTotalFileSize($request);
        if ($totalSize > 20971520) { // 20MB
            return back()->withErrors([
                'total_file_size' => 'Total file size exceeds 20MB. Please compress your PDF files. <a href="https://smallpdf.com/compress-pdf" target="_blank" class="text-blue-600 underline">Use this tool to compress</a>'
            ])->withInput();
        }


        DB::beginTransaction();
        try {
            // Find or create AuditeeAction by audit_finding_id (route param is the finding id)
            $auditeeAction = AuditeeAction::updateOrCreate(
                ['audit_finding_id' => $id],
                [
                    'pic' => $request->input('pic', '-'),
                    'root_cause' => $validated['root_cause'],
                    'yokoten' => $validated['yokoten'],
                    'yokoten_area' => $validated['yokoten_area'] ?? null,
                    'ldr_spv_id' => auth()->user()->id,
                ]
            );

            /* =====================================================
             * 1️⃣ UPDATE WHY (5 WHY)
             * ===================================================== */
            // use the actual auditee action id when deleting children
            $aid = $auditeeAction->id;
            WhyCauses::where('auditee_action_id', $aid)->delete();

            for ($i = 1; $i <= 5; $i++) {
                $why = $request->input("why_{$i}_mengapa");
                $cause = $request->input("cause_{$i}_karena");

                if ($why || $cause) {
                    WhyCauses::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'why_description' => $why ?? '',
                        'cause_description' => $cause ?? '',
                    ]);
                }
            }

            /* =====================================================
             * 2️⃣ UPDATE Corrective Action (hapus & replace)
             * ===================================================== */
            CorrectiveAction::where('auditee_action_id', $aid)->delete();

            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input("corrective_{$i}_activity");
                $pic = $request->input("corrective_{$i}_pic");
                $plan = $request->input("corrective_{$i}_planning");
                $actual = $request->input("corrective_{$i}_actual");

                if ($activity) {
                    CorrectiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $pic ?: '-',
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
                    ]);
                }
            }

            /* =====================================================
             * 3️⃣ UPDATE Preventive Action
             * ===================================================== */
            PreventiveAction::where('auditee_action_id', $aid)->delete();

            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input("preventive_{$i}_activity");
                $pic = $request->input("preventive_{$i}_pic");
                $plan = $request->input("preventive_{$i}_planning");
                $actual = $request->input("preventive_{$i}_actual");

                if ($activity) {
                    PreventiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $pic ?: '-',
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
                    ]);
                }
            }

            /* =====================================================
             * 4️⃣ PATCH: Only delete DocumentFile not in existing_evidence_ids[]
             * ===================================================== */
            $keepIds = $request->input('existing_evidence_ids', []);
            $keepIds = array_map('intval', (array)$keepIds);
            $allFiles = DocumentFile::where('auditee_action_id', $aid)->get();
            foreach ($allFiles as $file) {
                if (!in_array($file->id, $keepIds)) {
                    // Validasi: hanya hapus jika file milik auditee_action_id ini
                    if ($file->auditee_action_id != $aid) {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Tidak dapat menghapus file yang bukan milik auditee action ini.'
                            ], 403);
                        } else {
                            return back()->withErrors(['attachments' => 'Tidak dapat menghapus file yang bukan milik auditee action ini.'])->withInput();
                        }
                    }
                    try {
                        $original = $file->file_path ?? '';
                        $candidates = [];
                        if ($original !== '') {
                            $candidates[] = $original;
                            $candidates[] = ltrim($original, '/');
                            if (preg_match('#/storage/(.*)$#', $original, $m)) {
                                $candidates[] = $m[1];
                            }
                            $candidates[] = preg_replace('#^public/storage/#', '', $original);
                            $candidates[] = basename($original);
                        }
                        foreach (array_filter(array_unique($candidates)) as $p) {
                            if ($p === '') continue;
                            if (Storage::disk('public')->exists($p)) {
                                Storage::disk('public')->delete($p);
                                break;
                            }
                            $fsPath = storage_path('app/public/' . $p);
                            if (file_exists($fsPath)) {
                                @unlink($fsPath);
                                break;
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning("Failed to delete file for DocumentFile id={$file->id}: " . $e->getMessage());
                    }
                    $file->delete();
                }
            }

            /* =====================================================
             * 5️⃣ Handle Upload Attachments Baru
             * ===================================================== */
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $stored = $this->compressAndStore($file, 'ftpp/auditee_action_attachments');

                    DocumentFile::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'file_path' => $stored['path'],
                        'original_name' => $stored['original'],
                    ]);
                }
            }

            /* =====================================================
             * 5️⃣ UPDATE Status Finding
             * ===================================================== */
            $auditFinding = AuditFinding::find($auditeeAction->audit_finding_id);
            if ($auditFinding) {
                $auditFinding->update(['status_id' => 8]);
            }

            /* =====================================================
             * 6️⃣ Approve Ldr/SPV
             * ===================================================== */
            if ($request->approve_ldr_spv == 1) {
                $auditeeAction->update([
                    'ldr_spv_signature' => 1,
                    'ldr_spv_id' => auth()->id(),
                ]);
            }


            DB::commit();

            // Kirim notifikasi kepada Dept Head bahwa perlu review setelah update auditee action
            try {
                if (!empty($auditFinding) && $auditFinding instanceof AuditFinding) {
                    $deptId = (int) $auditFinding->department_id;
                    $regNum = (string) $auditFinding->registration_number;

                        $deptHeads = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['dept head']))
                            ->where(function ($q) use ($deptId) {
                                // avoid ambiguous `id` by checking existence in pivot table explicitly
                                $q->whereExists(function ($sub) use ($deptId) {
                                    $sub->select(\DB::raw(1))
                                        ->from('tt_user_department')
                                        ->whereColumn('tt_user_department.user_id', 'users.id')
                                        ->where('tt_user_department.department_id', $deptId);
                                });

                                // also include users who have department_id set on users table,
                                // but only if that column exists in the current schema
                                if (Schema::hasColumn('users', 'department_id')) {
                                    $q->orWhere('department_id', $deptId);
                                }
                            })
                            ->get();

                    if ($deptHeads->isNotEmpty()) {
                        $customMessage = "Finding (No: {$regNum}) needs your review.";

                        // Separate mail recipients (have email) from all dept heads
                        $allDeptHeads = $deptHeads->unique('id')->values();
                        $mailRecipients = $allDeptHeads->filter(fn($u) => !empty($u->email))->values();

                        try {
                            $emailsAll = $allDeptHeads->pluck('email')->toArray();
                            $emailsMail = $mailRecipients->pluck('email')->toArray();
                            \Log::info('DeptHeadNeedCheckNotification (update): recipients', ['all' => $emailsAll, 'mail' => $emailsMail, 'reply_to' => auth()->user()?->email]);
                        } catch (\Throwable $e) {
                            \Log::warning('Failed to log dept head recipients (update): ' . $e->getMessage());
                        }

                        // database notification for all dept heads
                        Notification::send(
                            $allDeptHeads,
                            new FtppActionNotification(
                                $auditFinding,
                                'auditee_revised', // custom action type
                                null,
                                $customMessage
                            )
                        );

                        // send email-only notification to users that have email addresses
                        if ($mailRecipients->isNotEmpty()) {
                            Notification::send(
                                $mailRecipients,
                                new DeptHeadNeedCheckNotification(
                                    $auditFinding,
                                    $auditeeAction,
                                    auth()->user()?->name,
                                    auth()->user()?->email
                                )
                            );
                        } else {
                            \Log::warning('DeptHeadNeedCheckNotification (update): no dept head email addresses found for department_id ' . ($deptId ?? 'N/A'));
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('FtppActionNotification (update -> auditee_revised) failed: ' . $e->getMessage());
            }

            return redirect()->route('ftpp.index')->with('success', 'Auditee Action updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("update_auditee_action: " . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Delete a single attachment (AJAX)
     */
    public function destroyAttachment(Request $request, $id)
    {
        try {
            $df = DocumentFile::findOrFail($id);

            // Optional: ensure the user can delete (skip complex auth here)
            $original = $df->file_path ?? '';
            $candidates = [];

            if ($original !== '') {
                $candidates[] = $original;
                $candidates[] = ltrim($original, '/');
                if (preg_match('#/storage/(.*)$#', $original, $m)) {
                    $candidates[] = $m[1];
                }
                $candidates[] = preg_replace('#^public/storage/#', '', $original);
                $candidates[] = basename($original);
            }

            $deleted = false;
            foreach (array_filter(array_unique($candidates)) as $p) {
                try {
                    if ($p === '')
                        continue;
                    if (Storage::disk('public')->exists($p)) {
                        Storage::disk('public')->delete($p);
                        $deleted = true;
                        break;
                    }
                    $fsPath = storage_path('app/public/' . $p);
                    if (file_exists($fsPath)) {
                        @unlink($fsPath);
                        $deleted = true;
                        break;
                    }
                } catch (\Throwable $inner) {
                    \Log::debug("Attempt to delete file candidate failed for {$p}: " . $inner->getMessage());
                }
            }

            if (!$deleted && $original) {
                try {
                    Storage::disk('public')->delete($original);
                } catch (\Throwable $inner) {
                    \Log::warning("Final delete attempt failed for {$original}: " . $inner->getMessage());
                }
            }

            $df->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('destroyAttachment error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Compress image files and store to disk. Returns ['path'=>..., 'original'=>...]
     */
    private function compressAndStore($file, $directory)
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $date = now()->format('Y-m-d_His');
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = Str::slug($base);
        $newFileName = $safeBase . '_' . $date . '.' . $extension;

        $mime = $file->getMimeType() ?? '';

        // Image handling: store then compress with Intervention Image
        if (str_starts_with($mime, 'image/')) {
            try {
                // store final file first
                $path = $file->storeAs($directory, $newFileName, 'public');
                $fullPath = storage_path('app/public/' . $path);

                // compress/resize using Intervention
                try {
                    $img = Image::make($fullPath);
                    $maxWidth = 1920;
                    if ($img->width() > $maxWidth) {
                        $img->resize($maxWidth, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }
                    $img->save($fullPath, 75);
                } catch (\Throwable $e) {
                    \Log::warning('Intervention compress failed: ' . $e->getMessage());
                }

                return ['path' => $path, 'original' => $originalName];
            } catch (\Throwable $e) {
                \Log::warning('Image compress/store failed: ' . $e->getMessage());
                $path = $file->storeAs($directory, $newFileName, 'public');
                return ['path' => $path, 'original' => $originalName];
            }
        }

        // PDF handling: compression disabled — store as-is
        if ($extension === 'pdf' || str_contains($mime, 'pdf')) {
            $path = $file->storeAs($directory, $newFileName, 'public');
            return ['path' => $path, 'original' => $originalName];
        }

        // non-image, non-pdf files: simpan apa adanya
        $path = $file->storeAs($directory, $newFileName, 'public');
        return ['path' => $path, 'original' => $originalName];
    }

    /**
     * Calculate total file size from request
     */
    private function calculateTotalFileSize(Request $request): int
    {
        $totalSize = 0;

        // Hitung semua file di 'attachments'
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $totalSize += $file->getSize();
            }
        }

        \Log::info("📊 Total attachment size: " . number_format($totalSize) . " bytes (" . round($totalSize / 1024 / 1024, 2) . "MB)");

        return $totalSize;
    }

    // PDF compression removed — files are stored as-is
}
