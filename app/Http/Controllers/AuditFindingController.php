<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
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
use App\Notifications\FindingCreatedNotification;
use App\Notifications\FtppActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Smalot\PdfParser\Parser;

class AuditFindingController extends Controller
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
        // Validate request (TANPA individual file size check)
        $validated = $request->validate([
            'audit_type_id' => 'required|exists:tm_audit_types,id',
            'sub_audit_type_id' => [
                'nullable',
                'exists:tm_sub_audit_types,id',
                Rule::requiredIf(fn() => $request->input('audit_type_id') == 2),
            ],
            'finding_category_id' => 'required|exists:tm_finding_categories,id',
            'sub_klausul_id' => 'required|array',
            'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
            'department_id' => 'required|exists:tm_departments,id',
            'process_id' => 'nullable|exists:tm_processes,id',
            'product_id' => 'nullable|exists:tm_products,id',
            'auditor_id' => 'required|exists:users,id',
            'auditee_ids' => 'required|array',
            'auditee_ids.*' => 'exists:users,id',
            'registration_number' => 'nullable|string|max:100',
            'finding_description' => 'required|string',
            'due_date' => 'required|date',

            // ✅ Hilangkan custom closure validation - biarkan client-side handle
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
        ]);

        // ✅ Server-side total size check (backup - seharusnya tidak pernah tercapai)
        $totalSize = $this->calculateTotalFileSize($request);
        if ($totalSize > 10 * 1024 * 1024) {
            \Log::warning('Total file size validation bypassed on client-side!');
            return back()->withErrors([
                'attachments' => 'Total file size must not exceed 10MB. Please compress your PDF files.'
            ])->withInput();
        }

        // Create the audit finding
        $auditFinding = AuditFinding::create([
            'audit_type_id' => $validated['audit_type_id'],
            'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
            'finding_category_id' => $validated['finding_category_id'],
            'department_id' => $validated['department_id'],
            'process_id' => $validated['process_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'auditor_id' => $validated['auditor_id'],
            'registration_number' => $validated['registration_number'] ?? null,
            'finding_description' => $validated['finding_description'],
            'status_id' => 7,
            'due_date' => $validated['due_date'],
        ]);

        // Store auditee relationship
        $auditFinding->auditee()->attach($validated['auditee_ids']);

        // ✅ ATTACH SUB KLAUSUL
        if (!empty($validated['sub_klausul_id'])) {
            $auditFinding->subKlausuls()->attach($validated['sub_klausul_id']);
        }

        // send notification to auditees
        try {
            $recipients = $auditFinding->auditee()->get();
            $recipients = $recipients->unique('id')->filter()->values();

            if ($recipients->isNotEmpty()) {
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

        // Handle file uploads
        $this->handleFileUploads($request, $auditFinding);

        // Return response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Audit Finding saved successfully!',
            ]);
        }

        return redirect('/ftpp')->with('success', 'Audit Finding saved successfully!');
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
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $this->compressImage($fullPath, $mime);
        } elseif (strtolower($extension) === 'pdf') {
            $this->compressPdf($fullPath);
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
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Try using Imagick if available
            if (extension_loaded('imagick')) {
                $image = new \Imagick($filePath);

                // Set compression quality
                $image->setImageCompressionQuality(75);

                // Strip metadata to reduce size
                $image->stripImage();

                // For PNG
                if ($extension === 'png') {
                    $image->setImageFormat('png');
                    $image->setOption('png:compression-level', '9');
                }

                // For JPEG
                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $image->setImageFormat('jpg');
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                }

                // Resize if too large (max 1920px width)
                if ($image->getImageWidth() > 1920) {
                    $image->scaleImage(1920, 0);
                }

                $image->writeImage($filePath);
                $image->clear();
                $image->destroy();

            } else {
                // Fallback: use GD library
                $this->compressImageWithGD($filePath, $extension);
            }
        } catch (\Exception $e) {
            \Log::warning("Image compression failed: " . $e->getMessage());
        }
    }

    /**
     * Compress image using GD library (fallback)
     */
    private function compressImageWithGD($filePath, $extension)
    {
        try {
            // Load image based on type
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($filePath);
                    break;
                default:
                    return;
            }

            if (!$image) {
                return;
            }

            // Get dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize if too large
            if ($width > 1920) {
                $newWidth = 1920;
                $newHeight = (int)(($height / $width) * $newWidth);

                $resized = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG
                if ($extension === 'png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                }

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            // Save compressed image
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $filePath, 75); // 75% quality
                    break;
                case 'png':
                    imagepng($image, $filePath, 9); // Max compression
                    break;
            }

            imagedestroy($image);

        } catch (\Exception $e) {
            \Log::warning("GD image compression failed: " . $e->getMessage());
        }
    }

    /**
     * Compress PDF using FPDI (pure PHP, no Ghostscript needed)
     */
    private function compressPdf($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                \Log::warning("PDF file not found: {$filePath}");
                return;
            }

            $originalSize = filesize($filePath);

            if ($originalSize < 100000) { // < 100KB
                \Log::info("PDF too small to compress ({$originalSize} bytes), skipping");
                return;
            }

            $outputPath = $filePath . '.compressed';

            try {
                // Create FPDI instance
                $pdf = new Fpdi();

                // Get page count
                $pageCount = $pdf->setSourceFile($filePath);

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

                // Verify result
                if (file_exists($outputPath)) {
                    $compressedSize = filesize($outputPath);

                    if ($compressedSize === 0 || $compressedSize === false) {
                        \Log::warning('Compressed PDF is empty, keeping original');
                        @unlink($outputPath);
                        return;
                    }

                    // Verify PDF header
                    $handle = fopen($outputPath, 'r');
                    $header = fread($handle, 5);
                    fclose($handle);

                    if ($header !== '%PDF-') {
                        \Log::warning('Compressed PDF has invalid header (corrupted)');
                        @unlink($outputPath);
                        return;
                    }

                    $reduction = round((1 - $compressedSize / $originalSize) * 100, 1);

                    \Log::info("✅ PDF Compression Result (FPDI):");
                    \Log::info("   Original: " . number_format($originalSize) . " bytes");
                    \Log::info("   Compressed: " . number_format($compressedSize) . " bytes");
                    \Log::info("   Reduction: {$reduction}%");

                    // Replace jika gain > 5%
                    if ($compressedSize < $originalSize && $reduction > 5) {
                        @unlink($filePath);
                        rename($outputPath, $filePath);
                        \Log::info("✅ PDF successfully compressed (saved {$reduction}%)");
                    } else {
                        @unlink($outputPath);
                        \Log::info("⚠️ Compression gain < 5%, keeping original");
                    }
                } else {
                    \Log::warning("❌ Failed to create compressed PDF");
                }

            } catch (\Exception $e) {
                \Log::error("FPDI PDF compression error: " . $e->getMessage());
                if (isset($outputPath) && file_exists($outputPath)) {
                    @unlink($outputPath);
                }
            }

        } catch (\Exception $e) {
            \Log::error("PDF compression exception: " . $e->getMessage());
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
    public function edit(string $id)
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
            'department',
            'status',
            'auditeeAction',
        ])
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        $finding = AuditFinding::with(['auditee', 'subKlausuls', 'file', 'department', 'process', 'product'])
            ->findOrFail($id);

        return view('contents.ftpp2.audit-finding.edit', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit', 'finding'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'audit_type_id' => 'required|exists:tm_audit_types,id',
            'sub_audit_type_id' => 'nullable|exists:tm_sub_audit_types,id',
            'finding_category_id' => 'required|exists:tm_finding_categories,id',
            'sub_klausul_id' => 'required|array',
            'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
            'department_id' => 'required|exists:tm_departments,id',
            'process_id' => 'nullable|exists:tm_processes,id',
            'product_id' => 'nullable|exists:tm_products,id',
            'auditor_id' => 'required|exists:users,id',
            'auditee_ids' => 'required|array',
            'auditee_ids.*' => 'exists:users,id',
            'registration_number' => 'nullable|string|max:100',
            'finding_description' => 'required|string',
            'due_date' => 'required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png|max:5120', // 5 MB
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf|max:10240', // 10 MB
            'attachments' => 'nullable|array',
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf',
                function ($attribute, $file, $fail) {
                    if (!$file) {
                        return;
                    }
                    $mime = $file->getClientMimeType();
                    $size = $file->getSize(); // bytes

                    $isPdf = str_contains($mime, 'pdf');
                    $isImage = str_contains($mime, 'image/');

                    if ($isPdf && $size > 10 * 1024 * 1024) {
                        return $fail('PDF must be 10MB or smaller.');
                    }
                    if ($isImage && $size > 5 * 1024 * 1024) {
                        return $fail('Image must be 5MB or smaller.');
                    }
                },
            ],
            'existing_file_delete' => 'nullable|array',
            'existing_file_delete.*' => 'integer',
        ]);

        $auditFinding = AuditFinding::findOrFail($id);

        $auditFinding->update([
            'audit_type_id' => $validated['audit_type_id'],
            'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
            'finding_category_id' => $validated['finding_category_id'],
            'department_id' => $validated['department_id'],
            'process_id' => $validated['process_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'auditor_id' => $validated['auditor_id'],
            'registration_number' => $validated['registration_number'] ?? null,
            'finding_description' => $validated['finding_description'],
            'due_date' => $validated['due_date'],
        ]);

        // sync auditee relationship
        $auditFinding->auditee()->sync($validated['auditee_ids']);

        // replace sub klausul relationships
        AuditFindingSubKlausul::where('audit_finding_id', $auditFinding->id)->delete();
        foreach ($validated['sub_klausul_id'] as $subId) {
            AuditFindingSubKlausul::create([
                'audit_finding_id' => $auditFinding->id,
                'sub_klausul_id' => $subId,
            ]);
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Audit Finding updated successfully!']);
        }

        return redirect('/ftpp')->with('success', 'Audit Finding updated successfully!');
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
