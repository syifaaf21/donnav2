<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DocumentConverterService
{
    protected DocSpaceService $docSpaceService;
    protected PdfWatermarker $pdfWatermarker;

    protected string $baseUrl;
    protected string $token;

    public function __construct(DocSpaceService $docSpaceService, PdfWatermarker $pdfWatermarker)
    {
        $this->docSpaceService = $docSpaceService;
        $this->pdfWatermarker = $pdfWatermarker;

        $this->baseUrl = rtrim(config('onlyoffice.docspace_url'), '/');
        $this->token = $this->docSpaceService->getToken();
    }

    /**
     * Convert document (DOCX, XLSX, etc) to PDF via OnlyOffice Cloud API
     *
     * @param string $docspaceFileId - ID file di OnlyOffice DocSpace
     * @param bool $addWatermark - Apakah perlu watermark setelah konversi
     * @param string|null $watermarkText - Text watermark (jika $addWatermark = true)
     * @param string|null $watermarkImagePath - Path ke image watermark di storage
     * @return string - PDF content (bisa langsung di-return ke user atau disimpan)
     */
    public function convertToPdf(
        string $docspaceFileId,
        bool $addWatermark = false,
        ?string $watermarkText = null,
        ?string $watermarkImagePath = null
    ): string {
        // 1. Get file info dari DocSpace
        $fileInfo = $this->getFileInfo($docspaceFileId);
        $fileName = $fileInfo['title'] ?? 'document';
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // 2. Check apakah file sudah PDF
        if ($fileExtension === 'pdf') {
            // Kalau sudah PDF, langsung download & watermark
            return $this->downloadAndWatermarkFile($docspaceFileId, $addWatermark, $watermarkText, $watermarkImagePath);
        }

        // 3. Convert file ke PDF via OnlyOffice API
        $pdfContent = $this->performConversion($docspaceFileId, $fileExtension);

        // 4. Tambah watermark jika diperlukan
        if ($addWatermark) {
            $pdfContent = $this->applyWatermarkSafely(
                $pdfContent,
                $watermarkText,
                $watermarkImagePath,
                context: 'convertToPdf'
            );
        }

        return $pdfContent;
    }

    /**
     * Convert local/uploaded file to PDF
     * Useful when file sudah di-upload ke Laravel storage tapi belum ke DocSpace
     */
    public function convertLocalFileToPdf(
        string $localFilePath,
        bool $addWatermark = false,
        ?string $watermarkText = null,
        ?string $watermarkImagePath = null
    ): string {
        $fullPath = Storage::path($localFilePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File tidak ditemukan: {$localFilePath}");
        }

        $fileExtension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Kalau sudah PDF, watermark aja
        if ($fileExtension === 'pdf') {
            $pdfContent = file_get_contents($fullPath);
        } else {
            // Buat temporary file di DocSpace, convert, download
            $fileName = basename($fullPath);
            $uploadedFileInfo = $this->docSpaceService->uploadFile($localFilePath, $fileName);
            $docspaceFileId = $uploadedFileInfo['file_id'];

            try {
                $pdfContent = $this->performConversion($docspaceFileId, $fileExtension);
            } finally {
                // Hapus file temporary di DocSpace
                $this->docSpaceService->deleteFile($docspaceFileId);
            }
        }

        // Tambah watermark
        if ($addWatermark) {
            $pdfContent = $this->applyWatermarkSafely(
                $pdfContent,
                $watermarkText,
                $watermarkImagePath,
                context: 'convertLocalFileToPdf'
            );
        }

        return $pdfContent;
    }

    /**
     * Perform actual conversion using OnlyOffice API
     * Download hasil konversi dan return PDF content
     */
    protected function performConversion(string $docspaceFileId, string $fileExtension): string
    {
        // OnlyOffice supports conversion untuk most common formats
        $supportedFormats = ['docx', 'doc', 'xlsx', 'xls', 'xlsm', 'pptx', 'ppt', 'odt', 'ods', 'odp'];

        if (!in_array($fileExtension, $supportedFormats)) {
            throw new \Exception("Format {$fileExtension} tidak didukung untuk konversi. Format yang didukung: " . implode(', ', $supportedFormats));
        }

        $sourceFile = $this->getFileInfo($docspaceFileId);
        $sourceFolderId = $sourceFile['folderId'] ?? config('onlyoffice.docspace_folder_id');
        $sourceTitle = $sourceFile['title'] ?? ('document_' . $docspaceFileId . '.' . $fileExtension);
        $pdfTitle = pathinfo($sourceTitle, PATHINFO_FILENAME) . '.pdf';

        // DocSpace cloud conversion endpoint yang valid: copyas
        // POST /api/2.0/files/file/{id}/copyas
        $convertUrl = "{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}/copyas";

        $convertResponse = Http::withToken($this->token)
            ->timeout(120)
            ->post($convertUrl, [
                'destTitle' => $pdfTitle,
                'destFolderId' => (int) $sourceFolderId,
            ]);

        if ($convertResponse->status() === 401) {
            Cache::forget('docspace_token');
            Cache::forget('docspace_asc_auth_key');
            $this->token = $this->docSpaceService->getToken();

            $convertResponse = Http::withToken($this->token)
                ->timeout(120)
                ->post($convertUrl, [
                    'destTitle' => $pdfTitle,
                    'destFolderId' => (int) $sourceFolderId,
                ]);
        }

        \Log::info('OnlyOffice copyas convert response', [
            'source_file_id' => $docspaceFileId,
            'status' => $convertResponse->status(),
            'url' => $convertUrl,
            'body' => substr((string) $convertResponse->body(), 0, 700),
        ]);

        if (!$convertResponse->successful()) {
            throw new \Exception(
                "Konversi dokumen gagal (HTTP {$convertResponse->status()}): " . $convertResponse->body()
            );
        }

        $conversionResponse = $convertResponse->json('response') ?? $convertResponse->json();

        if (empty($conversionResponse)) {
            throw new \Exception('Konversi gagal: response kosong dari OnlyOffice');
        }

        // Tahap 2: Get converted file info
        // Response biasanya berisi file_id dari PDF hasil konversi
        $pdfFileId = $conversionResponse['id']
            ?? $conversionResponse['file_id']
            ?? data_get($conversionResponse, 'file.id')
            ?? data_get($conversionResponse, 'file.file_id')
            ?? null;

        if (!$pdfFileId) {
            throw new \Exception('Tidak bisa mendapatkan ID file PDF hasil konversi');
        }

        // Tahap 3: Download PDF dari DocSpace
        $pdfContent = $this->downloadFileContent((string) $pdfFileId);

        // Tahap 4: Hapus file PDF hasil convert agar room DocSpace tidak penuh
        try {
            $this->docSpaceService->deleteFile((string) $pdfFileId);
        } catch (\Throwable $e) {
            \Log::warning('Failed to delete converted temporary PDF in DocSpace', [
                'pdf_file_id' => $pdfFileId,
                'error' => $e->getMessage(),
            ]);
        }

        return $pdfContent;
    }

    /**
     * Download file content dari OnlyOffice Server
     */
    protected function downloadFileContent(string $docspaceFileId): string
    {
        $viewUrl = $this->docSpaceService->getFileViewUrl($docspaceFileId);

        if (!$viewUrl) {
            throw new \Exception('Tidak bisa mendapatkan download URL untuk file PDF');
        }

        $response = Http::withToken($this->token)
            ->timeout(120)
            ->get($viewUrl);

        if (!$response->successful()) {
            throw new \Exception("Download PDF gagal (HTTP {$response->status()})");
        }

        return $response->body();
    }

    /**
     * Download dan watermark file tanpa konversi (untuk file yang sudah PDF)
     */
    protected function downloadAndWatermarkFile(
        string $docspaceFileId,
        bool $addWatermark = false,
        ?string $watermarkText = null,
        ?string $watermarkImagePath = null
    ): string {
        $pdfContent = $this->downloadFileContent($docspaceFileId);

        if ($addWatermark) {
            $pdfContent = $this->applyWatermarkSafely(
                $pdfContent,
                $watermarkText,
                $watermarkImagePath,
                context: 'downloadAndWatermarkFile'
            );
        }

        return $pdfContent;
    }

    /**
     * Apply watermark with graceful fallback when FPDI cannot parse compressed PDFs.
     */
    protected function applyWatermarkSafely(
        string $pdfContent,
        ?string $watermarkText,
        ?string $watermarkImagePath,
        string $context
    ): string {
        $tempPath = tempnam(sys_get_temp_dir(), 'doc_wm_');

        if ($tempPath === false) {
            return $pdfContent;
        }

        file_put_contents($tempPath, $pdfContent);

        try {
            if ($watermarkImagePath) {
                $imagePath = null;

                // Accept absolute filesystem path (e.g. public_path('images/ORIGINAL.png')).
                if (is_file($watermarkImagePath)) {
                    $imagePath = $watermarkImagePath;
                } elseif (Storage::exists($watermarkImagePath)) {
                    $imagePath = Storage::path($watermarkImagePath);
                } else {
                    $publicCandidate = public_path(ltrim($watermarkImagePath, '/\\'));
                    if (is_file($publicCandidate)) {
                        $imagePath = $publicCandidate;
                    }
                }

                if ($imagePath !== null) {
                    return $this->pdfWatermarker->stampImage($tempPath, $imagePath, 30.0, 10.0, 100);
                }
            }

            if ($watermarkText) {
                return $this->pdfWatermarker->stampText($tempPath, $watermarkText);
            }

            return $pdfContent;
        } catch (\Throwable $e) {
            $message = strtolower($e->getMessage());
            $isUnsupportedCompression = str_contains($message, 'fpdi-pdf-parser')
                || str_contains($message, 'compression technique which is not supported');

            if ($isUnsupportedCompression) {
                \Log::warning('Watermark skipped: FPDI parser unsupported compression', [
                    'context' => $context,
                    'error' => $e->getMessage(),
                ]);

                // Fallback: return original PDF without watermark
                return $pdfContent;
            }

            throw $e;
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Get file info dari DocSpace
     */
    protected function getFileInfo(string $docspaceFileId): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}");

        if (!$response->successful()) {
            throw new \Exception('Gagal ambil info file dari DocSpace');
        }

        return $response->json('response') ?? [];
    }

    /**
     * Batch convert multiple files
     * Useful untuk bulk conversion
     */
    public function convertMultipleToPdf(
        array $docspaceFileIds,
        bool $addWatermark = false,
        ?string $watermarkText = null,
        ?string $watermarkImagePath = null
    ): array {
        $results = [];

        foreach ($docspaceFileIds as $fileId) {
            try {
                $pdfContent = $this->convertToPdf($fileId, $addWatermark, $watermarkText, $watermarkImagePath);
                $results[$fileId] = [
                    'success' => true,
                    'content' => $pdfContent,
                ];
            } catch (\Exception $e) {
                $results[$fileId] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
