<?php

namespace App\Http\Controllers;

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
            'subKlausuls',
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

        return view('contents.ftpp2.auditee-action.create', compact('finding', 'departments', 'processes', 'products', 'auditors', 'auditTypes', 'subAudit', 'findingCategories', 'klausuls'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_finding_id' => 'required|exists:tt_audit_findings,id',
            'root_cause' => 'required|string',
            'pic' => 'nullable|string|max:100',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',

            // terima file upload (hapus rule max, akan dikompres otomatis jika image)
            'attachments.*' => 'nullable|file',
            'photos2.*' => 'nullable|file',
            'files2.*' => 'nullable|file',
        ]);

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
                    $recipients = collect();
                    $recipients = $recipients->merge($auditFinding->auditee()->get());

                    if ($auditFinding->auditor) {
                        $recipients->push($auditFinding->auditor);
                    }

                    $recipients = $recipients->unique('id')->filter()->values();

                    if ($recipients->isNotEmpty()) {
                        Notification::send(
                            $recipients,
                            new FtppActionNotification(
                                $auditFinding,
                                'assigned',   // action: assigned
                                auth()->user()?->name
                            )
                        );
                    }

                    // 2️⃣ Notify Dept Head(s) of the finding's department that review is required
                    if (!empty($auditFinding->department_id)) {
                        $deptHeads = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['dept head']))
                            ->where(function ($q) use ($auditFinding) {
                                $q->whereHas('departments', fn($qq) => $qq->where('tm_departments.id', $auditFinding->department_id))
                                    ->orWhere('department_id', $auditFinding->department_id);
                            })
                            ->get();

                        if ($deptHeads->isNotEmpty()) {
                            $customMessage = "Finding (No: {$auditFinding->registration_number}) needs your review.";

                            Notification::send(
                                $deptHeads,
                                new FtppActionNotification(
                                    $auditFinding,
                                    'dept_head_checked', // action type
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

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.whyCauses',
            'auditeeAction.file',
            'auditee',
            'department',
            'process',
            'product'
        ])->findOrFail($id);

        $subAudit = SubAudit::all();

        return view('contents.ftpp2.auditee-action.edit', compact('finding', 'subAudit'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'root_cause' => 'required|string',
            // 'pic' => 'nullable|string|max:100',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',
            'attachments.*' => 'nullable|file',
            'photos2.*' => 'nullable|file',
            'files2.*' => 'nullable|file',
            'remove_attachments.*' => 'nullable|numeric',
        ]);

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

        // Image handling (compress/resize)
        if (str_starts_with($mime, 'image/')) {
            try {
                $img = Image::make($file)->orientate();
                $img->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $encoded = (string) $img->encode('jpg', 75);
                    $newFileName = preg_replace('/\.(jpg|jpeg)$/i', '.jpg', $newFileName);
                } elseif ($extension === 'png') {
                    $encoded = (string) $img->encode('png', 8);
                } elseif ($extension === 'gif') {
                    $encoded = (string) $img->encode('gif');
                } else {
                    $encoded = (string) $img->encode('jpg', 75);
                    $newFileName = preg_replace('/\.[^.]+$/', '.jpg', $newFileName);
                }

                $path = $directory . '/' . $newFileName;
                Storage::disk('public')->put($path, $encoded);

                return ['path' => $path, 'original' => $originalName];
            } catch (\Throwable $e) {
                \Log::warning('Image compress/store failed: ' . $e->getMessage());
                // fallback: simpan file langsung
                $path = $file->storeAs($directory, $newFileName, 'public');
                return ['path' => $path, 'original' => $originalName];
            }
        }

        // PDF handling (try Ghostscript compression)
        if ($extension === 'pdf' || str_contains($mime, 'pdf')) {
            $inputPath = $file->getRealPath();
            if ($inputPath && file_exists($inputPath)) {
                $tmpName = 'gs_compressed_' . uniqid() . '.pdf';
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;

                // Choose a PDFSETTINGS level. /ebook is a decent balance (smaller than /printer, better quality than /screen)
                $pdfSettings = '/ebook';

                $cmd = sprintf(
                    'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=%s -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
                    escapeshellarg($pdfSettings),
                    escapeshellarg($tmpPath),
                    escapeshellarg($inputPath)
                );

                try {
                    @exec($cmd, $output, $returnVar);

                    if ($returnVar === 0 && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                        $path = $directory . '/' . $newFileName;
                        $contents = file_get_contents($tmpPath);
                        Storage::disk('public')->put($path, $contents);
                        @unlink($tmpPath);
                        return ['path' => $path, 'original' => $originalName];
                    } else {
                        \Log::warning('Ghostscript PDF compression failed or produced empty file. Cmd: ' . $cmd . ' Output: ' . implode("\n", (array) $output));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('PDF compression via Ghostscript failed: ' . $e->getMessage());
                }
            } else {
                \Log::warning('PDF compress: uploaded file real path missing for ' . $originalName);
            }

            // fallback: store original pdf
            $path = $file->storeAs($directory, $newFileName, 'public');
            return ['path' => $path, 'original' => $originalName];
        }

        // non-image, non-pdf files: simpan apa adanya
        $path = $file->storeAs($directory, $newFileName, 'public');
        return ['path' => $path, 'original' => $originalName];
    }
}
