<?php

namespace App\Http\Controllers;

// PDF compression removed — FPDI not used
use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditFindingSubKlausul;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\User;
use App\Models\Status;
use App\Notifications\FindingCreatedNotification;
use App\Notifications\FtppActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
// Smalot PDF parser removed (PDF compression disabled)
use Intervention\Image\Facades\Image;

class AuditFindingController extends Controller
{
    /**
     * Show the form for uploading evidence.
     */
    public function showUploadEvidenceForm(Request $request, $id)
    {
        $finding = AuditFinding::with([
            'department',
            'auditor',
            'auditee',
            'status',
            'file',
            'auditeeAction.file'
        ])->findOrFail($id);

        // ✅ HANYA BOLEH VIA AJAX (MODAL)
        if ($request->ajax()) {
            return view('contents.ftpp2.partials.evidence-upload', compact('finding'));
        }

        // ❌ JIKA DIAKSES LANGSUNG → KEMBALIKAN KE INDEX
        return redirect()
            ->route('ftpp.index')
            ->with('warning', 'Upload Evidence must be opened from the FTPP list.');
    }

    /**
     * Handle evidence upload.
     */
    public function uploadEvidence(Request $request, $id)
    {
        $finding = AuditFinding::findOrFail($id);

        $request->validate([
            'evidence' => 'required|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,pdf',
        ]);

        foreach ($request->file('evidence') as $file) {
            $path = $file->store('ftpp/audit_finding_attachments', 'public');

            $docFile = new \App\Models\DocumentFile();
            $docFile->auditee_action_id = optional($finding->auditeeAction)->id;
            $docFile->file_path = $path;
            $docFile->original_name = $file->getClientOriginalName();
            $docFile->save();
        }

        return redirect()->route('ftpp.index')->with('success', 'Evidence uploaded successfully.');
    }

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
    public function create()
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $subAudit = SubAudit::all();

        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with([
            'auditee',
            'auditor',
            'findingCategory',
            'department',   // 👈 tambahkan ini
            'status',        // 👈 dan ini
            'auditeeAction',
        ])
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        return view('contents.ftpp2.audit-finding.create', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('action') === 'draft';

        // When saving as draft, relax most required fields
        $validated = $request->validate([
            'audit_type_id' => [$isDraft ? 'nullable' : 'required', 'exists:tm_audit_types,id'],
            'sub_audit_type_id' => [
                'nullable',
                'exists:tm_sub_audit_types,id',
                Rule::requiredIf(fn() => !$isDraft && $request->input('audit_type_id') == 2),
            ],
            'finding_category_id' => [$isDraft ? 'nullable' : 'required', 'exists:tm_finding_categories,id'],
            'sub_klausul_id' => [$isDraft ? 'nullable' : 'required', 'array'],
            'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
            'department_id' => [$isDraft ? 'nullable' : 'required', 'exists:tm_departments,id'],
            'process_id' => 'nullable|exists:tm_processes,id',
            'product_id' => 'nullable|exists:tm_products,id',
            'auditor_id' => [$isDraft ? 'nullable' : 'required', 'exists:users,id'],
            'auditee_ids' => [$isDraft ? 'nullable' : 'required', 'array'],
            'auditee_ids.*' => 'exists:users,id',
            'registration_number' => 'nullable|string|max:100',
            'finding_description' => [$isDraft ? 'nullable' : 'required', 'string'],
            'due_date' => [$isDraft ? 'nullable' : 'required', 'date'],

            // ✅ Hilangkan custom closure validation - biarkan client-side handle
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
        ]);

        // ✅ Server-side total size check (backup - seharusnya tidak pernah tercapai)
        $totalSize = $this->calculateTotalFileSize($request);
        if ($totalSize > 20 * 1024 * 1024) {
            \Log::warning('Total file size validation bypassed on client-side!');
            return back()->withErrors([
                'attachments' => 'Total file size must not exceed 20MB. Please compress your PDF files.'
            ])->withInput();
        }

        // Defensive guard: ensure required fields are present after validation
        $requiredWhenNotDraft = [
            'finding_category_id',
            'department_id',
            'auditor_id',
            'finding_description',
            'due_date',
        ];

        $missing = [];
        foreach ($requiredWhenNotDraft as $field) {
            if (!$isDraft && (empty($validated[$field]) && $validated[$field] !== '0')) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            \Log::warning('AuditFinding store: missing required fields after validation', ['missing' => $missing, 'payload' => $request->all()]);

            $msg = 'Required fields missing: ' . implode(', ', $missing);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'missing' => $missing], 422);
            }

            return back()->withErrors(['missing' => $msg])->withInput();
        }

        // Create the audit finding
        // Determine status ID - use Draft Finding for draft, or default status (7) for normal save
        $statusId = 7; // default status for normal save
        if ($isDraft) {
            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft Finding'],
                ['name' => 'Draft Finding']
            );
            $statusId = $draftStatus->id;
        }

        $auditFinding = AuditFinding::create([
            'audit_type_id' => $validated['audit_type_id'] ?? null,
            'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
            'finding_category_id' => $validated['finding_category_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'process_id' => $validated['process_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'auditor_id' => $validated['auditor_id'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'finding_description' => $validated['finding_description'] ?? null,
            'status_id' => $statusId,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Store auditee relationship
        if (!empty($validated['auditee_ids'])) {
            $auditFinding->auditee()->attach($validated['auditee_ids']);
        }

        // ✅ ATTACH SUB KLAUSUL
        if (!empty($validated['sub_klausul_id'])) {
            $auditFinding->subKlausuls()->attach($validated['sub_klausul_id']);
        }

        // send notification to auditees
        try {
            $recipients = $auditFinding->auditee()->get();
            $recipients = $recipients->unique('id')->filter()->values();

            if (!$isDraft && $recipients->isNotEmpty()) {
                Notification::send(
                    $recipients,
                    new FtppActionNotification(
                        $auditFinding,
                        'created'
                    )
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('Notify auditee (finding created) failed: ' . $e->getMessage());
        }

        // Handle file uploads (defensive: don't let upload processing break the whole save)
        try {
            $this->handleFileUploads($request, $auditFinding);
        } catch (\Throwable $e) {
            \Log::error('File processing after finding save failed: ' . $e->getMessage());

            $message = $isDraft ? 'Draft saved successfully! (file processing failed)' : 'Audit Finding saved successfully! (file processing failed)';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'draft' => $isDraft,
                    'warning' => 'Some attachments could not be processed. Check server logs.'
                ]);
            }

            return redirect('/ftpp')->with('success', $message)->with('warning', 'Some attachments could not be processed. Check server logs.');
        }

        // Return response
        $message = $isDraft ? 'Draft saved successfully!' : 'Audit Finding saved successfully!';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'draft' => $isDraft,
            ]);
        }

        return redirect('/ftpp')->with('success', $message);
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
     * Handle file uploads
     */
    private function handleFileUploads(Request $request, $auditFinding)
    {
        // ✅ Hanya process 'attachments' (gabung photos + files)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeFile($file, $auditFinding);
            }
        }

        // Also accept temp uploaded files from client (uploaded earlier via AJAX)
        if ($request->filled('temp_attachments')) {
            $tempPaths = $request->input('temp_attachments', []);
            $originalNames = $request->input('temp_original_names', []);

            foreach ($tempPaths as $index => $tempPath) {
                try {
                    // Ensure path is within public disk and exists
                    if (!Storage::disk('public')->exists($tempPath)) {
                        \Log::warning("Temp attachment not found: {$tempPath}");
                        continue;
                    }

                    $fileName = basename($tempPath);
                    $finalPath = 'ftpp/audit_finding_attachments/' . $fileName;

                    // Move file
                    Storage::disk('public')->move($tempPath, $finalPath);

                    $original = $originalNames[$index] ?? $fileName;

                    // Prevent duplicate
                    $exists = DocumentFile::where('audit_finding_id', $auditFinding->id)
                        ->where('original_name', $original)
                        ->where('file_path', $finalPath)
                        ->exists();

                    if (!$exists) {
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $finalPath,
                            'original_name' => $original,
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to move temp attachment: ' . $e->getMessage());
                }
            }
        }

    }

    /**
     * Upload a single file to temporary storage, compress if needed, and return metadata.
     * This endpoint is intended for AJAX upload on file selection.
     */
    public function uploadTemp(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());

            $timestamp = now()->format('YmdHis') . '_' . uniqid();
            $safeOriginal = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $originalName);
            $fileName = $timestamp . '-' . pathinfo($safeOriginal, PATHINFO_FILENAME) . '.' . $extension;

            // ensure temp directory exists on public disk
            Storage::disk('public')->makeDirectory('temp');

            $tempPath = $file->storeAs('temp', $fileName, 'public');
            $fullPath = storage_path('app/public/' . $tempPath);

            $fileSize = @filesize($fullPath) ?: 0;

            try {
                // Image: compress if > 2MB
                if (in_array($extension, ['jpg', 'jpeg', 'png']) && $fileSize > 2 * 1024 * 1024) {
                    try {
                        $this->compressImage($fullPath, $file->getClientMimeType());
                    } catch (\Throwable $e) {
                        \Log::warning('Temp image compression failed: ' . $e->getMessage());
                    }
                }

                // PDF compression disabled — skip PDF processing here
            } catch (\Throwable $e) {
                \Log::warning('Temp compression general error: ' . $e->getMessage());
            }

            // Generate small thumb for images (200px width) if image
            $thumbUrl = null;
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                try {
                    $thumbName = 'temp/thumb_' . $fileName . '.jpg';
                    $thumbFull = storage_path('app/public/' . $thumbName);
                    @mkdir(dirname($thumbFull), 0755, true);

                    $img = Image::make($fullPath)->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode('jpg', 70);

                    file_put_contents($thumbFull, (string) $img);

                    if (Storage::disk('public')->exists($thumbName)) {
                        $thumbUrl = Storage::disk('public')->url($thumbName);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Thumb generation failed: ' . $e->getMessage());
                }
            }

            // Return metadata
            return response()->json([
                'success' => true,
                'temp_path' => $tempPath,
                'original_name' => $originalName,
                'size' => @filesize(storage_path('app/public/' . $tempPath)) ?: $fileSize,
                'thumb' => $thumbUrl,
            ]);
        } catch (\Throwable $e) {
            \Log::error('uploadTemp failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Upload failed. See server logs.'], 500);
            }
            abort(500, 'Upload failed');
        }
    }
    private function storeFile($file, $auditFinding)
    {
        // generate a more unique filename to avoid collisions
        $timestamp = now()->format('YmdHis') . '_' . uniqid();
        $safeOriginal = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
        $extension = $file->getClientOriginalExtension();
        $fileName = $timestamp . '-' . pathinfo($safeOriginal, PATHINFO_FILENAME) . '.' . $extension;

        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();

        // Temporary path
        $tempPath = $file->storeAs('temp', $fileName, 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        // Compress based on file type
        // Increase memory temporarily for compression to avoid exhausting default limit
        $previousMemoryLimit = ini_get('memory_limit');
        // Allow larger memory for image/pdf processing; adjust if needed
        @ini_set('memory_limit', '256M');

        try {
            $fileSize = @filesize($fullPath) ?: 0;
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                try {
                    $this->compressImage($fullPath, $mime);
                } catch (\Throwable $e) {
                    \Log::warning('storeFile image compression failed: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Compression failed: ' . $e->getMessage());
        } finally {
            // restore previous memory limit
            @ini_set('memory_limit', $previousMemoryLimit ?: '-1');
        }

        // Move to final destination
        $finalPath = 'ftpp/audit_finding_attachments/' . $fileName;
        Storage::disk('public')->move($tempPath, $finalPath);

        // prevent duplicate DocumentFile records
        $exists = DocumentFile::where('audit_finding_id', $auditFinding->id)
            ->where('original_name', $originalName)
            ->where('file_path', $finalPath)
            ->exists();

        if (!$exists) {
            return DocumentFile::create([
                'audit_finding_id' => $auditFinding->id,
                'file_path' => $finalPath,
                'original_name' => $originalName,
            ]);
        }

        return DocumentFile::where('audit_finding_id', $auditFinding->id)
            ->where('original_name', $originalName)
            ->where('file_path', $finalPath)
            ->first();
    }

    /**
     * Compress image files (jpg, jpeg, png)
     */
    private function compressImage($filePath, $mimeType)
    {
        try {
            $img = Image::make($filePath);
            $maxWidth = 1920;
            if ($img->width() > $maxWidth) {
                $img->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            $img->encode(null, 75)->save($filePath);
        } catch (\Exception $e) {
            \Log::warning('Image compression (Intervention) failed: ' . $e->getMessage());
        }
    }

    /**
     * Compress image using GD library (fallback)
     */
    

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
    public function edit(string $id)
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();


        // Filter auditors by audit type (if available on finding)
        $auditTypeId = null;
        $finding = AuditFinding::with(['auditee', 'subKlausuls', 'file', 'department', 'process', 'product'])
            ->findOrFail($id);
        if ($finding && $finding->audit_type_id) {
            $auditTypeId = $finding->audit_type_id;
        }
        if ($auditTypeId) {
            $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'auditor'))
                ->whereHas('auditTypes', fn($q) => $q->where('tm_audit_types.id', $auditTypeId))
                ->select('id', 'name')->get();
        } else {
            $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'auditor'))
                ->select('id', 'name')->get();
        }

        $auditTypes = Audit::with('subAudit')->get();
        $subAudit = SubAudit::all();
        $findingCategories = FindingCategory::all();
        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with([
            'auditee',
            'auditor',
            'findingCategory',
            'department',
            'status',
            'auditeeAction',
        ])
            ->orderByDesc('created_at')
            ->get();
        $user = auth()->user();
        return view('contents.ftpp2.audit-finding.edit', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit', 'finding'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $action = $request->input('action'); // 'save' or 'submit'
        $isSubmit = $action === 'submit';
        $isDraft = false; // No more draft mode in edit - only in create

        // For submit action, require all fields; for save, allow partial
        $validated = $request->validate([
            'audit_type_id' => [$isSubmit ? 'required' : 'nullable', 'exists:tm_audit_types,id'],
            'sub_audit_type_id' => 'nullable|exists:tm_sub_audit_types,id',
            'finding_category_id' => [$isSubmit ? 'required' : 'nullable', 'exists:tm_finding_categories,id'],
            'sub_klausul_id' => [$isSubmit ? 'required' : 'nullable', 'array'],
            'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
            'department_id' => [$isSubmit ? 'required' : 'nullable', 'exists:tm_departments,id'],
            'process_id' => 'nullable|exists:tm_processes,id',
            'product_id' => 'nullable|exists:tm_products,id',
            'auditor_id' => [$isSubmit ? 'required' : 'nullable', 'exists:users,id'],
            'auditee_ids' => [$isSubmit ? 'required' : 'nullable', 'array'],
            'auditee_ids.*' => 'exists:users,id',
            'registration_number' => 'nullable|string|max:100',
            'finding_description' => [$isSubmit ? 'required' : 'nullable', 'string'],
            'due_date' => [$isSubmit ? 'required' : 'nullable', 'date'],
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
            'existing_file_delete' => 'nullable|array',
            'existing_file_delete.*' => 'integer',
        ]);

        // ✅ Server-side total size check (skip for save, enforce for submit)
        if ($isSubmit) {
            $totalSize = $this->calculateTotalFileSize($request);
            if ($totalSize > 20 * 1024 * 1024) {
                \Log::warning('Total file size validation bypassed on client-side!');
                return back()->withErrors([
                    'attachments' => 'Total file size must not exceed 20MB. Please compress your PDF files.'
                ])->withInput();
            }
        }

        $auditFinding = AuditFinding::findOrFail($id);

        // Check if current status is "Draft Finding"
        $isDraftFinding = false;
        if ($auditFinding->status) {
            $isDraftFinding = strtolower($auditFinding->status->name) === 'draft finding';
        }

        // ✅ Determine status based on action
        // - submit: Change to "Need Assign"
        // - save: Keep current status
        $statusId = $auditFinding->status_id;
        if ($isSubmit) {
            $needAssignStatus = Status::firstOrCreate(
                ['name' => 'Need Assign'],
                ['name' => 'Need Assign']
            );
            $statusId = $needAssignStatus->id;
        }

        // Auto-increment registration number revision (last numeric segment),
        // BUT only if NOT Draft Finding
        $baseRegistration = $validated['registration_number'] ?? $auditFinding->registration_number;
        if ($isDraftFinding) {
            // Draft Finding: keep same registration number (no increment)
            $newRegistration = $baseRegistration;
        } else {
            // Non-Draft: increment revision
            $newRegistration = $this->incrementRegistrationRevision($baseRegistration);
        }

        $auditFinding->update([
            'audit_type_id' => $validated['audit_type_id'] ?? $auditFinding->audit_type_id,
            'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
            'finding_category_id' => $validated['finding_category_id'] ?? $auditFinding->finding_category_id,
            'department_id' => $validated['department_id'] ?? $auditFinding->department_id,
            'process_id' => $validated['process_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'auditor_id' => $validated['auditor_id'] ?? $auditFinding->auditor_id,
            'registration_number' => $newRegistration,
            'finding_description' => $validated['finding_description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status_id' => $statusId,
        ]);

        // sync auditee relationship (only if provided)
        if (!empty($validated['auditee_ids'])) {
            $auditFinding->auditee()->sync($validated['auditee_ids']);
        }

        // sync sub klausul relationships (add new ones, preserve existing ones if not explicitly deleted)
        if (!empty($validated['sub_klausul_id'])) {
            $currentSubKlausulIds = $auditFinding->subKlausuls()->pluck('sub_klausul_id')->toArray();
            $newSubKlausulIds = $validated['sub_klausul_id'];

            // Combine old and new, keeping unique values
            $mergedSubKlausulIds = array_unique(array_merge($currentSubKlausulIds, $newSubKlausulIds));

            // Use attach to add new ones without deleting existing
            $toAttach = array_diff($newSubKlausulIds, $currentSubKlausulIds);
            if (!empty($toAttach)) {
                foreach ($toAttach as $subId) {
                    AuditFindingSubKlausul::create([
                        'audit_finding_id' => $auditFinding->id,
                        'sub_klausul_id' => $subId,
                    ]);
                }
            }
        }

        // handle deletion of existing attachments if requested
        if ($request->filled('existing_file_delete')) {
            $toDelete = $request->input('existing_file_delete', []);
            foreach ($toDelete as $fileId) {
                $df = DocumentFile::where('audit_finding_id', $auditFinding->id)->where('id', $fileId)->first();
                if ($df) {
                    if (Storage::disk('public')->exists($df->file_path)) {
                        Storage::disk('public')->delete($df->file_path);
                    }
                    $df->delete();
                }
            }
        }

        // Handle new uploads
        $this->handleFileUploads($request, $auditFinding);

        $message = $isSubmit ? 'Finding submitted successfully! Status changed to Need Assign.' : 'Audit Finding saved successfully!';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'submitted' => $isSubmit]);
        }

        return redirect('/ftpp')->with('success', $message);
    }

    /**
     * Increment the revision part of a registration number.
     * Expected formats like: MS/YYYY/FTPP/001/00 or MR/YYYY/FTPP/001/00
     * This will increment the last numeric segment while preserving padding.
     */
    private function incrementRegistrationRevision(?string $registrationNumber): ?string
    {
        if (!$registrationNumber) {
            return $registrationNumber;
        }

        $parts = explode('/', $registrationNumber);
        if (!empty($parts)) {
            $last = $parts[count($parts) - 1];
            if (preg_match('/^\d+$/', $last)) {
                $width = strlen($last);
                $num = ((int) $last) + 1;
                $parts[count($parts) - 1] = str_pad((string) $num, $width, '0', STR_PAD_LEFT);
                return implode('/', $parts);
            }
        }

        // Fallback: increment the last number anywhere in the string
        if (preg_match('/^(.*?)(\d+)\s*$/', $registrationNumber, $m)) {
            $prefix = $m[1];
            $digits = $m[2];
            $width = strlen($digits);
            $num = ((int) $digits) + 1;
            return $prefix . str_pad((string) $num, $width, '0', STR_PAD_LEFT);
        }

        // If no numeric segment found, return unchanged
        return $registrationNumber;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Detach an auditee from an audit finding (AJAX)
     */
    public function destroyAuditee(string $id, string $auditee)
    {
        $finding = AuditFinding::findOrFail($id);
        // detach the auditee (if exists)
        $finding->auditee()->detach($auditee);

        return response()->json(['success' => true, 'message' => 'Auditee removed from finding']);
    }

    /**
     * Remove a sub-klausul association from an audit finding (AJAX)
     */
    public function destroySubKlausul(string $id, string $sub)
    {
        $row = AuditFindingSubKlausul::where('audit_finding_id', $id)
            ->where('sub_klausul_id', $sub)
            ->first();

        if ($row) {
            $row->delete();
            return response()->json(['success' => true, 'message' => 'Sub-klausul removed']);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    /**
     * Delete an attachment (DocumentFile) by id (AJAX)
     */
    public function destroyAttachment(string $id)
    {
        $df = DocumentFile::find($id);
        if (!$df) {
            return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
        }

        // delete file from storage if exists
        if ($df->file_path && Storage::disk('public')->exists($df->file_path)) {
            Storage::disk('public')->delete($df->file_path);
        }

        $df->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted']);
    }
}
