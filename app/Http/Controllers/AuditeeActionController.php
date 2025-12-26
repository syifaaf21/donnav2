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
use App\Models\User;
use App\Models\WhyCauses;
use App\Notifications\AuditeeAssignedNotification;
use App\Notifications\FtppActionNotification;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        // ✅ VALIDASI FILE SIZE
        $validated = $request->validate([
            'audit_finding_id' => 'required|exists:tt_audit_findings,id',
            'root_cause' => 'required|string',
            'pic' => 'nullable|string|max:100',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',

            // Attachments: gabung semua file
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
        ]);

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
                    'root_cause' => $validated['root_cause'],
                    'yokoten' => $validated['yokoten'],
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

                if (DocumentFile::where('auditee_action_id', $aid)->exists()) {
                    DocumentFile::where('auditee_action_id', $aid)->delete();
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
                        'pic' => $pic ?: null,
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
                        'pic' => $pic ?: null,
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
                $auditFinding->update(['status_id' => 8]);
            }

            if ($request->has('approve_ldr_spv') && $request->approve_ldr_spv == 1) {
                $auditeeAction->update([
                    'ldr_spv_signature' => 1,
                    'ldr_spv_id' => auth()->user()->id
                ]);
            }

            DB::commit();

            // notify auditee(s) and auditor that auditee action / assignment exists
            try {
                if (!empty($auditFinding) && $auditFinding instanceof AuditFinding) {

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
                            })
                            ->get();

                        if ($deptHeads->isNotEmpty()) {
                            $regNum = $auditFinding?->registration_number ?? 'N/A';
                            $customMessage = "Finding (No: {$regNum}) needs your review.";

                            Notification::send(
                                $deptHeads->unique('id')->values(),
                                new FtppActionNotification(
                                    $auditFinding,
                                    'assigned', // action type
                                    null,                // byUser optional
                                    $customMessage       // custom message
                                )
                            );
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('FindingActionNotification failed: ' . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auditee Action submitted successfully.',
                    'id' => $auditeeAction->id
                ]);
            }

            return back()->with('success', 'Auditee Action submitted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // log error agar lebih mudah debug
            \Log::error('update_auditee_action error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
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

            // Image: max 3MB per file
            'photos2.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',

            // Files: max 3MB per file (images)
            'files2.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',

            // Attachments: max 10MB per PDF file
            // Attachments: allow images (jpg, jpeg, png) and PDF files
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',

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
                    'pic' => $validated['pic'] ?? '-',
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
                if ($activity) {
                    CorrectiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $request->corrective_pic[$i] ?? null,
                        'planning_date' => $request->corrective_planning[$i] ?? null,
                        'actual_date' => $request->corrective_actual[$i] ?? null,
                    ]);
                }
            }

            /* =====================================================
             * 3️⃣ UPDATE Preventive Action
             * ===================================================== */
            PreventiveAction::where('auditee_action_id', $aid)->delete();

            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input("preventive_{$i}_activity");
                if ($activity) {
                    PreventiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $request->preventive_pic[$i] ?? null,
                        'planning_date' => $request->preventive_planning[$i] ?? null,
                        'actual_date' => $request->preventive_actual[$i] ?? null,
                    ]);
                }
            }

            /* =====================================================
             * 4️⃣ Handle removed attachments first (from edit UI)
             * ===================================================== */
            if ($request->has('remove_attachments')) {
                $removeIds = (array) $request->input('remove_attachments');
                foreach ($removeIds as $rid) {
                    $df = DocumentFile::find($rid);
                    if ($df && $df->auditee_action_id == $auditeeAction->id) {
                        try {
                            $original = $df->file_path ?? '';
                            $candidates = [];

                            if ($original !== '') {
                                $candidates[] = $original;
                                $candidates[] = ltrim($original, '/');
                                // if stored as full URL like https://.../storage/..., extract path after '/storage/'
                                if (preg_match('#/storage/(.*)$#', $original, $m)) {
                                    $candidates[] = $m[1];
                                }
                                // if stored as public/storage/..., normalize
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
                                    // try direct filesystem path: storage/app/public/{p}
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
                                // final fallback: attempt delete using original value
                                try {
                                    Storage::disk('public')->delete($original);
                                } catch (\Throwable $inner) {
                                    \Log::warning("Final delete attempt failed for {$original}: " . $inner->getMessage());
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::warning("Failed to delete file for DocumentFile id={$rid}: " . $e->getMessage());
                        }

                        try {
                            $df->delete();
                        } catch (\Throwable $e) {
                            \Log::warning("Failed to delete DocumentFile record id={$rid}: " . $e->getMessage());
                        }
                    }
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
                        })
                        ->get();

                    if ($deptHeads->isNotEmpty()) {
                        $customMessage = "Finding (No: {$regNum}) needs your review.";
                        Notification::send(
                            $deptHeads->unique('id')->values(),
                            new FtppActionNotification(
                                $auditFinding,
                                'auditee_revised', // custom action type
                                null,
                                $customMessage
                            )
                        );
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

        // Image handling (compress/resize with Imagick)
        if (str_starts_with($mime, 'image/')) {
            try {
                // Store file temporarily first to get a real path
                $tempPath = $file->storeAs('temp', $newFileName, 'public');
                $fullPath = storage_path('app/public/' . $tempPath);

                // Use Imagick for compression
                $this->compressImageWithImagick($fullPath, $extension);

                // Move to final destination
                $path = $directory . '/' . $newFileName;
                Storage::disk('public')->move($tempPath, $path);

                return ['path' => $path, 'original' => $originalName];

            } catch (\Throwable $e) {
                \Log::warning('Image compress/store failed: ' . $e->getMessage());
                // fallback: simpan file langsung tanpa compress
                $path = $file->storeAs($directory, $newFileName, 'public');
                return ['path' => $path, 'original' => $originalName];
            }
        }

        // PDF handling (try FPDI compression)
        if ($extension === 'pdf' || str_contains($mime, 'pdf')) {
            try {
                // Store file temporarily first
                $tempPath = $file->storeAs('temp', $newFileName, 'public');
                $fullPath = storage_path('app/public/' . $tempPath);

                \Log::info("📄 PDF upload detected: {$originalName}");
                \Log::info("   Temp path: {$tempPath}");
                \Log::info("   Full path: {$fullPath}");
                \Log::info("   Original size: " . number_format(filesize($fullPath)) . " bytes");

                // ✅ COMPRESS FIRST (modifies file in place)
                $this->compressPdf($fullPath);

                // ✅ THEN MOVE to final destination
                $path = $directory . '/' . $newFileName;
                Storage::disk('public')->move($tempPath, $path);

                \Log::info("   Final size: " . number_format(filesize(storage_path('app/public/' . $path))) . " bytes");
                \Log::info("✅ PDF stored at: {$path}");

                return ['path' => $path, 'original' => $originalName];

            } catch (\Throwable $e) {
                \Log::warning('PDF compress/store failed: ' . $e->getMessage());
                // fallback: simpan file langsung tanpa compress
                $path = $file->storeAs($directory, $newFileName, 'public');
                return ['path' => $path, 'original' => $originalName];
            }
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

    /**
     * Compress PDF using FPDI (pure PHP, no Ghostscript needed)
     * ✅ Modifies file in-place at $filePath
     */
    private function compressPdf($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                \Log::warning("❌ PDF file not found: {$filePath}");
                return;
            }

            $originalSize = filesize($filePath);
            \Log::info("🔍 Checking PDF: " . basename($filePath) . " ({$originalSize} bytes / " . round($originalSize / 1024 / 1024, 2) . "MB)");

            if ($originalSize < 100000) { // < 100KB
                \Log::info("⏭️  PDF too small to compress ({$originalSize} bytes), skipping");
                return;
            }

            $outputPath = $filePath . '.compressed';

            try {
                \Log::info("🔄 Starting FPDI compression...");

                // Create FPDI instance
                $pdf = new Fpdi();

                // Get page count
                $pageCount = $pdf->setSourceFile($filePath);
                \Log::info("   Pages detected: {$pageCount}");

                // Loop through pages and import them
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    // Add page dengan compressed settings
                    $pdf->addPage(
                        $size['orientation'] == 'L' ? 'L' : 'P',
                        [$size['width'], $size['height']]
                    );
                    $pdf->useTemplate($templateId);
                }

                // Enable compression
                $pdf->setCompression(true);

                // Save compressed PDF
                $pdf->Output($outputPath, 'F');
                \Log::info("   Compressed file created: {$outputPath}");

                // Verify result
                if (file_exists($outputPath)) {
                    $compressedSize = filesize($outputPath);

                    if ($compressedSize === 0 || $compressedSize === false) {
                        \Log::warning('❌ Compressed PDF is empty, keeping original');
                        @unlink($outputPath);
                        return;
                    }

                    // Verify PDF header
                    $handle = fopen($outputPath, 'r');
                    $header = fread($handle, 5);
                    fclose($handle);

                    if ($header !== '%PDF-') {
                        \Log::warning('❌ Compressed PDF has invalid header (corrupted)');
                        @unlink($outputPath);
                        return;
                    }

                    $reduction = round((1 - $compressedSize / $originalSize) * 100, 1);

                    \Log::info("✅ PDF Compression Success:");
                    \Log::info("   Original: " . number_format($originalSize) . " bytes (" . round($originalSize / 1024 / 1024, 2) . "MB)");
                    \Log::info("   Compressed: " . number_format($compressedSize) . " bytes (" . round($compressedSize / 1024 / 1024, 2) . "MB)");
                    \Log::info("   Reduction: {$reduction}%");

                    // ✅ REPLACE jika gain > 5%
                    if ($compressedSize < $originalSize && $reduction > 5) {
                        @unlink($filePath);  // Delete original
                        rename($outputPath, $filePath);  // Rename compressed to original path
                        \Log::info("✅ Original file replaced with compressed version (saved {$reduction}%)");
                    } else {
                        @unlink($outputPath);  // Delete compressed jika gain kecil
                        \Log::info("⚠️  Compression gain < 5%, keeping original");
                    }
                } else {
                    \Log::warning("❌ Failed to create compressed PDF");
                }

            } catch (\Exception $e) {
                \Log::error("❌ FPDI PDF compression error: " . $e->getMessage());
                if (isset($outputPath) && file_exists($outputPath)) {
                    @unlink($outputPath);
                }
            }

        } catch (\Exception $e) {
            \Log::error("❌ PDF compression exception: " . $e->getMessage());
        }
    }
}
