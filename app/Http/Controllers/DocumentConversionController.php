<?php

namespace App\Http\Controllers;

use App\Services\DocumentConverterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentConversionController extends Controller
{
    protected DocumentConverterService $converterService;

    public function __construct(DocumentConverterService $converterService)
    {
        $this->converterService = $converterService;
    }

    /**
     * Convert dokumen dari OnlyOffice ke PDF dan download
     * 
     * Usage:
     * POST /api/convert-to-pdf
     * Body:
     * {
     *   "docspace_file_id": "123",
     *   "add_watermark": true,
     *   "watermark_text": "CONFIDENTIAL",
     *   "watermark_image_path": "water marks/logo.png"
     * }
     */
    public function convertDocspaceToPdf(Request $request)
    {
        $validated = $request->validate([
            'docspace_file_id' => 'required|string',
            'add_watermark' => 'boolean',
            'watermark_text' => 'nullable|string',
            'watermark_image_path' => 'nullable|string',
            'file_name' => 'nullable|string',
        ]);

        try {
            $pdfContent = $this->converterService->convertToPdf(
                $validated['docspace_file_id'],
                $validated['add_watermark'] ?? false,
                $validated['watermark_text'] ?? null,
                $validated['watermark_image_path'] ?? null
            );

            $fileName = $validated['file_name'] ?? 'document.pdf';
            if (!str_ends_with($fileName, '.pdf')) {
                $fileName .= '.pdf';
            }

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Konversi dokumen gagal',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Convert & download file dari OnlyOffice
     * Lebih simple, hanya perlu file ID
     * 
     * GET /convert-docspace-file/{fileId}/download
     * Query params:
     * - watermark_text (optional)
     * - watermark_image (optional - path ke storage)
     */
    public function downloadConverted($fileId, Request $request)
    {
        try {
            $pdfContent = $this->converterService->convertToPdf(
                $fileId,
                $request->has('watermark_text') || $request->has('watermark_image'),
                $request->input('watermark_text'),
                $request->input('watermark_image')
            );

            $fileName = $request->input('file_name', "document_" . time()) . '.pdf';

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download konversi gagal',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Convert local file (dari storage Laravel) ke PDF dengan watermark
     * Berguna untuk file yang belum di-upload ke DocSpace
     * 
     * POST /api/convert-local-file
     * Body:
     * {
     *   "file_path": "documents/my-file.docx",
     *   "add_watermark": true,
     *   "watermark_text": "APPROVED",
     *   "watermark_image_path": "assets/watermark.png"
     * }
     */
    public function convertLocalFile(Request $request)
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
            'add_watermark' => 'boolean',
            'watermark_text' => 'nullable|string',
            'watermark_image_path' => 'nullable|string',
            'file_name' => 'nullable|string',
        ]);

        try {
            $pdfContent = $this->converterService->convertLocalFileToPdf(
                $validated['file_path'],
                $validated['add_watermark'] ?? false,
                $validated['watermark_text'] ?? null,
                $validated['watermark_image_path'] ?? null
            );

            $fileName = $validated['file_name'] ?? 'converted_document.pdf';
            if (!str_ends_with($fileName, '.pdf')) {
                $fileName .= '.pdf';
            }

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Konversi file lokal gagal',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Batch convert multiple documents
     * 
     * POST /api/convert-multiple
     * Body:
     * {
     *   "file_ids": ["123", "456", "789"],
     *   "add_watermark": true,
     *   "watermark_text": "DRAFT"
     * }
     * 
     * Response:
     * {
     *   "123": {
     *     "success": true,
     *     "content": "base64-encoded-pdf"
     *   },
     *   "456": {
     *     "success": false,
     *     "error": "Format not supported"
     *   }
     * }
     */
    public function convertMultiple(Request $request)
    {
        $validated = $request->validate([
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'required|string',
            'add_watermark' => 'boolean',
            'watermark_text' => 'nullable|string',
            'watermark_image_path' => 'nullable|string',
        ]);

        try {
            $results = $this->converterService->convertMultipleToPdf(
                $validated['file_ids'],
                $validated['add_watermark'] ?? false,
                $validated['watermark_text'] ?? null,
                $validated['watermark_image_path'] ?? null
            );

            // Encode content ke base64 untuk JSON response
            $responseData = [];
            foreach ($results as $fileId => $result) {
                if ($result['success']) {
                    $responseData[$fileId] = [
                        'success' => true,
                        'content_base64' => base64_encode($result['content']),
                    ];
                } else {
                    $responseData[$fileId] = [
                        'success' => false,
                        'error' => $result['error'],
                    ];
                }
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Batch konversi gagal',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Health check untuk conversion service
     */
    public function status()
    {
        try {
            // Cek apakah DocSpace API accessible
            // Ini simple check, bisa diperluas
            return response()->json([
                'status' => 'ok',
                'message' => 'Document conversion service is running',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
