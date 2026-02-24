<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentMapping;
use App\Services\PdfWatermarker;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentFile;

class DocumentControlWatermarkController extends Controller
{
    public function downloadWatermarked(Request $request, $mappingId, PdfWatermarker $watermarker)
    {
        $mapping = DocumentMapping::find($mappingId);
        if (!$mapping) {
            abort(404);
        }

        // Try several ways to locate the actual file path or URL
        $full = null;

        // 1) direct known model field(s)
        $candidates = [];
        if (!empty($mapping->file_path)) $candidates[] = $mapping->file_path;
        if (!empty($mapping->path)) $candidates[] = $mapping->path;
        if (!empty($mapping->file?->path)) $candidates[] = $mapping->file->path;

        // 2) files_for_modal_all accessor used by the view (pick first active)
        try {
            if (empty($candidates) && !empty($mapping->files_for_modal_all)) {
                foreach ($mapping->files_for_modal_all as $f) {
                    if (($f['is_active'] ?? 0) == 1 || ($f['pending_approval'] ?? 0) == 2) {
                        $candidates[] = $f['url'] ?? ($f['path'] ?? null);
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore accessor errors
        }

        // Evaluate candidates
        foreach ($candidates as $candidate) {
            if (!$candidate) continue;

            // If it's a URL (http/https), redirect to it
            if (preg_match('#^https?://#i', $candidate)) {
                return redirect()->away($candidate);
            }

            // If it looks like /storage/..., map to public path
            if (strpos($candidate, '/storage/') === 0) {
                $possible = public_path(ltrim($candidate, '/'));
                if (file_exists($possible)) { $full = $possible; break; }
            }

            // If Storage local has it
            if (Storage::disk('local')->exists($candidate)) {
                $full = Storage::disk('local')->path($candidate);
                break;
            }

            // Also check the public disk (common for user-uploaded files)
            if (Storage::disk('public')->exists($candidate)) {
                $full = Storage::disk('public')->path($candidate);
                break;
            }

            // If path as given exists on filesystem
            if (file_exists($candidate)) { $full = $candidate; break; }
        }

        if (!$full) {
            // nothing found — give clearer 404 message
            abort(404, 'File not found for mapping ' . $mappingId);
        }

        // Only allow PDF
        $mime = mime_content_type($full);
        if (stripos($mime, 'pdf') === false) {
            // fallback: stream original file
            return response()->file($full);
        }

        // Prefer image watermark if available in public/images
        $publicImage = public_path('images/madonna-icon.png');
        if (file_exists($publicImage)) {
            // smaller logo with 12% opacity as requested
            $intermediatePdf = $watermarker->stampImage($full, $publicImage, 14.0, 8.0, 90);

            // write intermediate to temp file, then stamp centered text "CONFIDENTIAL"
            $tmpPdf = tempnam(sys_get_temp_dir(), 'wm_pdf_') . '.pdf';
            file_put_contents($tmpPdf, $intermediatePdf);

            // stamp centered text (simple CONFIDENTIAL label)
            $content = $watermarker->stampText($tmpPdf, 'CONFIDENTIAL');

            // cleanup temp
            @unlink($tmpPdf);
        } else {
            // build watermark text — include user + date
            $user = $request->user();
            $who = $user ? ($user->name ?? $user->email) : 'Anonymous';
            $watermark = strtoupper("CONFIDENTIAL - {$who} - " . now()->format('Y-m-d'));
            $content = $watermarker->stampText($full, $watermark);
        }

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . pathinfo($full, PATHINFO_FILENAME) . '-watermarked.pdf"');
    }

    /**
     * Download a watermarked PDF by DocumentFile id (used in Document Review).
     */
    public function downloadWatermarkedFile(Request $request, $fileId, PdfWatermarker $watermarker)
    {
        $file = DocumentFile::find($fileId);
        if (!$file) {
            abort(404);
        }

        $candidates = [];
        if (!empty($file->file_path)) $candidates[] = $file->file_path;

        $full = null;
        foreach ($candidates as $candidate) {
            if (!$candidate) continue;

            if (preg_match('#^https?://#i', $candidate)) {
                return redirect()->away($candidate);
            }

            if (strpos($candidate, '/storage/') === 0) {
                $possible = public_path(ltrim($candidate, '/'));
                if (file_exists($possible)) { $full = $possible; break; }
            }

            if (Storage::disk('local')->exists($candidate)) {
                $full = Storage::disk('local')->path($candidate);
                break;
            }

            if (Storage::disk('public')->exists($candidate)) {
                $full = Storage::disk('public')->path($candidate);
                break;
            }

            if (file_exists($candidate)) { $full = $candidate; break; }
        }

        if (!$full) {
            abort(404, 'File not found for id ' . $fileId);
        }

        $mime = mime_content_type($full);
        if (stripos($mime, 'pdf') === false) {
            return response()->file($full);
        }

        $publicImage = public_path('images/madonna-icon.png');
        if (file_exists($publicImage)) {
            $intermediatePdf = $watermarker->stampImage($full, $publicImage, 14.0, 8.0, 90);
            $tmpPdf = tempnam(sys_get_temp_dir(), 'wm_pdf_') . '.pdf';
            file_put_contents($tmpPdf, $intermediatePdf);
            $content = $watermarker->stampText($tmpPdf, 'CONFIDENTIAL');
            @unlink($tmpPdf);
        } else {
            $user = $request->user();
            $who = $user ? ($user->name ?? $user->email) : 'Anonymous';
            $watermark = strtoupper("CONFIDENTIAL - {$who} - " . now()->format('Y-m-d'));
            $content = $watermarker->stampText($full, $watermark);
        }

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . pathinfo($full, PATHINFO_FILENAME) . '-watermarked.pdf"');
    }
}
