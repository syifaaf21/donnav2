<?php

/**
 * DOCUMENT CONVERSION SERVICE - PRACTICAL INTEGRATION EXAMPLES
 * 
 * File ini berisi contoh-contoh praktis untuk mengintegrasikan
 * Document Conversion Service ke dalam existing features project.
 */

// ============================================================================
// CONTOH 1: Add "Download as PDF" button ke DocumentReviewController
// ============================================================================

namespace App\Http\Controllers;

use App\Services\DocumentConverterService;

class DocumentReviewController extends Controller
{
    public function __construct(
        private DocumentConverterService $converter
    ) {}

    /**
     * Download dokumen result dari review sebagai PDF dengan watermark
     */
    public function downloadAsPdf($documentId)
    {
        $document = Document::findOrFail($documentId);

        // Ambil docspace_id dari model atau mapping
        $docspaceFileId = $document->docspace_file_id;

        try {
            $pdfContent = $this->converter->convertToPdf(
                $docspaceFileId,
                addWatermark: true,
                watermarkText: 'REVIEWED - ' . now()->format('Y-m-d')
            );

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$document->name}.pdf\"");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal download PDF: ' . $e->getMessage());
        }
    }
}

// Route:
// GET /document-review/{id}/download-pdf - DocumentReviewController@downloadAsPdf


// ============================================================================
// CONTOH 2: Add conversion ke DocumentMappingController (Approval)
// ============================================================================

namespace App\Http\Controllers;

class DocumentMappingController extends Controller
{
    /**
     * Approve document + convert to PDF with approval stamp
     */
    public function approveWithDates(Request $request, $mappingId)
    {
        $mapping = DocumentMapping::findOrFail($mappingId);

        // ... validate & update status ...

        // Convert dokumen ke PDF as proof of approval
        $converter = app(DocumentConverterService::class);
        
        $pdfContent = $converter->convertToPdf(
            $mapping->docspace_file_id,
            addWatermark: true,
            watermarkText: "APPROVED\n" . auth()->user()->name . "\n" . now()->format('Y-m-d H:i')
        );

        // Simpan approved PDF ke storage
        Storage::put(
            "approved-documents/{$mapping->id}-approved.pdf",
            $pdfContent
        );

        // Update mapping
        $mapping->update([
            'status' => 'approved',
            'approved_pdf_path' => "approved-documents/{$mapping->id}-approved.pdf",
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Dokumen disetujui & PDF tersimpan');
    }
}


// ============================================================================
// CONTOH 3: Add conversion ke File Upload handler
// ============================================================================

namespace App\Http\Controllers;

class AuditFindingController extends Controller
{
    /**
     * Upload evidence file dan auto-convert ke PDF jika format document
     */
    public function uploadEvidence(Request $request, $findingId)
    {
        $validated = $request->validate([
            'evidence_file' => 'required|file|mimes:pdf,docx,xlsx,doc,xls',
        ]);

        $file = $validated['evidence_file'];
        
        // Simpan original file
        $originalPath = $file->store('evidence/' . $findingId, 'public');

        $finding = AuditFinding::findOrFail($findingId);

        // Auto-convert DOCX/XLSX ke PDF
        if (in_array($file->getClientOriginalExtension(), ['docx', 'xlsx', 'doc', 'xls'])) {
            $converter = app(DocumentConverterService::class);

            try {
                $pdfContent = $converter->convertLocalFileToPdf(
                    $originalPath,
                    addWatermark: true,
                    watermarkText: "Evidence #" . $finding->id
                );

                $pdfPath = "evidence/{$findingId}/converted-" . time() . '.pdf';
                Storage::put($pdfPath, $pdfContent);

                // Create file record untuk PDF version
                $finding->files()->create([
                    'file_path' => $pdfPath,
                    'file_type' => 'evidence_pdf',
                    'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf',
                ]);

            } catch (\Exception $e) {
                // Jika konversi gagal, tetap simpan original file
                \Log::warning("Evidence conversion failed: " . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'path' => $originalPath]);
    }
}


// ============================================================================
// CONTOH 4: Background Job untuk Batch Conversion
// ============================================================================

namespace App\Jobs;

use App\Services\DocumentConverterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConvertDocumentsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $docspaceFileIds,
        public string $watermarkText,
        public ?string $watermarkImagePath = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DocumentConverterService $converter): void
    {
        foreach ($this->docspaceFileIds as $fileId) {
            try {
                $pdfContent = $converter->convertToPdf(
                    $fileId,
                    addWatermark: true,
                    watermarkText: $this->watermarkText,
                    watermarkImagePath: $this->watermarkImagePath
                );

                // Store result
                \Illuminate\Support\Facades\Storage::put(
                    "batch-conversions/{$fileId}-" . now()->timestamp . ".pdf",
                    $pdfContent
                );

                // Log success
                \Log::info("Document {$fileId} converted successfully");

            } catch (\Exception $e) {
                // Log error but continue processing
                \Log::error("Failed to convert {$fileId}: " . $e->getMessage());
            }
        }
    }
}

// Usage di Controller:
// ConvertDocumentsJob::dispatch($fileIds, "BATCH APPROVED");


// ============================================================================
// CONTOH 5: Middleware untuk auto-convert download requests
// ============================================================================

namespace App\Http\Middleware;

use Closure;

class AutoConvertToPhysicallyRelated extends Closure
{
    /**
     * Intercept download requests dan otomatis convert ke PDF jika needed
     */
    public function handle($request, Closure $next)
    {
        // Match routes yang butuh conversion
        if ($request->is('*/download-as-pdf')) {
            // Extract file info dari request
            // Convert using DocumentConverterService
            // Return PDF response
        }

        return $next($request);
    }
}


// ============================================================================
// CONTOH 6: API Endpoint untuk mobile/external integration
// ============================================================================

namespace App\Http\Controllers\Api;

use App\Services\DocumentConverterService;

class DocumentConversionApiController
{
    /**
     * API untuk external systems convert dokumen
     * 
     * POST /api/v1/documents/{id}/convert-to-pdf
     * Auth: Bearer token
     */
    public function convertDocument($docspaceId, DocumentConverterService $converter)
    {
        // Validate user has access to this document
        // ...

        $pdfContent = $converter->convertToPdf(
            $docspaceId,
            addWatermark: true,
            watermarkText: "API Request - " . auth('api')->user()->name
        );

        return response()->json([
            'success' => true,
            'file_id' => $docspaceId,
            'pdf_base64' => base64_encode($pdfContent),
            'pdf_size' => strlen($pdfContent),
            'converted_at' => now(),
        ]);
    }
}


// ============================================================================
// CONTOH 7: Model Helper Methods
// ============================================================================

namespace App\Models;

use App\Services\DocumentConverterService;

class Document extends Model
{
    /**
     * Shortcut method untuk convert document ini ke PDF
     */
    public function convertToPdf(bool $withWatermark = true): string
    {
        $converter = app(DocumentConverterService::class);
        
        return $converter->convertToPdf(
            $this->docspace_file_id,
            addWatermark: $withWatermark,
            watermarkText: "Document: {$this->name}"
        );
    }

    /**
     * Download as PDF dengan watermark
     */
    public function downloadAsAttachment()
    {
        $pdf = $this->convertToPdf();
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$this->name}.pdf\"");
    }
}

// Usage:
// Document::find(1)->downloadAsAttachment();


// ============================================================================
// CONTOH 8: Service Class Helper
// ============================================================================

namespace App\Services;

class DocumentService
{
    public function __construct(
        private DocumentConverterService $converter
    ) {}

    /**
     * Smart document download dengan auto-detect format
     */
    public function downloadDocument(string $docspaceId, array $options = [])
    {
        $format = $options['format'] ?? 'pdf';
        $withWatermark = $options['watermark'] ?? true;
        $watermarkText = $options['watermark_text'] ?? 'DOWNLOADED - ' . now();

        if ($format === 'pdf') {
            return $this->converter->convertToPdf(
                $docspaceId,
                addWatermark: $withWatermark,
                watermarkText: $watermarkText
            );
        } elseif ($format === 'original') {
            // Return original file tanpa konversi
            // ...
        }
    }

    /**
     * Generate document report dengan multiple formats
     */
    public function generateReport(array $docspaceIds, string $format = 'pdf')
    {
        // Collect PDFs dari semua documents
        $pdfs = $this->converter->convertMultipleToPdf(
            $docspaceIds,
            addWatermark: true,
            watermarkText: "Report Generated " . now()
        );

        if ($format === 'zip') {
            return $this->createZipArchive($pdfs);
        } elseif ($format === 'merged') {
            return $this->mergePdfs($pdfs);
        }
    }
}


// ============================================================================
// CONTOH 9: Scheduled Task untuk regular conversions
// ============================================================================

namespace App\Console\Commands;

use App\Services\DocumentConverterService;
use Illuminate\Console\Command;

class ConvertPendingDocuments extends Command
{
    protected $signature = 'documents:convert-pending {--watermark=Converted}';

    public function handle(DocumentConverterService $converter)
    {
        $watermark = $this->option('watermark');

        $pending = Document::where('conversion_status', 'pending')->get();

        $this->info("Converting {$pending->count()} documents...");

        foreach ($pending as $doc) {
            try {
                $pdf = $converter->convertToPdf(
                    $doc->docspace_file_id,
                    addWatermark: true,
                    watermarkText: $watermark
                );

                Storage::put("converted/{$doc->id}.pdf", $pdf);
                $doc->update(['conversion_status' => 'completed']);
                $this->line("✓ {$doc->name}");

            } catch (\Exception $e) {
                $doc->update(['conversion_status' => 'failed', 'error' => $e->getMessage()]);
                $this->error("✗ {$doc->name}: {$e->getMessage()}");
            }
        }

        $this->info("Done!");
    }
}

// In app/Console/Kernel.php:
// $schedule->command('documents:convert-pending --watermark="ARCHIVED"')->daily();


// ============================================================================
// CONTOH 10: Event Listener untuk automatic conversion
// ============================================================================

namespace App\Listeners;

use App\Events\DocumentApproved;
use App\Services\DocumentConverterService;

class ConvertApprovedDocument
{
    public function handle(DocumentApproved $event)
    {
        $converter = app(DocumentConverterService::class);

        $pdfContent = $converter->convertToPdf(
            $event->document->docspace_file_id,
            addWatermark: true,
            watermarkText: "APPROVED by {$event->approver->name}"
        );

        // Store PDF
        Storage::put(
            "approved/{$event->document->id}_approved.pdf",
            $pdfContent
        );

        // Update document status
        $event->document->update([
            'approved_pdf_path' => "approved/{$event->document->id}_approved.pdf"
        ]);
    }
}

?>
