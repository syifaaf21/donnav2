<?php

namespace App\Http\Controllers;

use App\Services\DocumentConverterService;
use App\Services\PdfWatermarker;
use Illuminate\Support\Facades\Storage;

class DebugConversionController extends Controller
{
    /**
     * Test endpoint untuk debug PDF generation
     * GET /debug/test-pdf-generation
     */
    public function testPdfGeneration(DocumentConverterService $converter, PdfWatermarker $watermarker)
    {
        try {
            // Test 1: Create simple PDF dengan FPDI
            $this->logger("Test 1: Creating simple PDF with FPDI");
            
            // Buat PDF test sederhana
            $testPdfPath = storage_path('app/test_document.pdf');
            
            // Create minimal PDF if doesn't exist
            if (!file_exists($testPdfPath)) {
                $pdf = new \setasign\Fpdi\Fpdi();
                $pdf->AddPage();
                $pdf->SetFont('Helvetica', '', 12);
                $pdf->Cell(0, 10, 'Test Document for Watermarking', 0, 1);
                file_put_contents($testPdfPath, $pdf->Output('S'));
            }

            if (!file_exists($testPdfPath)) {
                throw new \Exception("Test PDF file not created");
            }

            $fileSize = filesize($testPdfPath);
            $this->logger("Test PDF created: {$fileSize} bytes");

            // Test 2: Apply watermark
            $this->logger("Test 2: Applying watermark to PDF");
            
            $watermarkedContent = $watermarker->stampText($testPdfPath, 'TEST WATERMARK');
            
            if (empty($watermarkedContent)) {
                throw new \Exception("Watermarked PDF is empty");
            }

            $watermarkedSize = strlen($watermarkedContent);
            $this->logger("Watermarked PDF: {$watermarkedSize} bytes");

            // Verify PDF header
            $header = substr($watermarkedContent, 0, 10);
            if (strpos($watermarkedContent, '%PDF') !== 0) {
                throw new \Exception("Invalid PDF header: {$header}");
            }
            
            $this->logger("PDF header is valid: %PDF");

            return response($watermarkedContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Length', strlen($watermarkedContent))
                ->header('Content-Disposition', 'attachment; filename="test_watermarked.pdf"')
                ->header('X-Debug', 'Test PDF - if you can open this, generation works correctly');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Test failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Cari semua file di storage untuk debug
     * GET /debug/list-storage-files?path=document-reviews
     */
    public function listStorageFiles()
    {
        try {
            $basePath = request('path', 'document-reviews');
            
            $this->logger("Searching files in: {$basePath}");

            // List all files dalam path
            $files = \Illuminate\Support\Facades\Storage::allFiles($basePath);
            
            if (empty($files)) {
                return response()->json([
                    'base_path' => $basePath,
                    'storage_path' => Storage::path($basePath),
                    'files_found' => 0,
                    'files' => [],
                    'note' => 'No files found. Check if path exists.'
                ]);
            }

            $fileDetails = array_map(function ($file) {
                return [
                    'path' => $file,
                    'size' => Storage::size($file),
                    'last_modified' => Storage::lastModified($file),
                    'modified_date' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                    'exists' => Storage::exists($file)
                ];
            }, $files);

            return response()->json([
                'base_path' => $basePath,
                'storage_root' => Storage::path(''),
                'files_found' => count($fileDetails),
                'files' => $fileDetails
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test file path validation
     * POST /debug/test-file-path
     */
    public function testFilePath()
    {
        try {
            $filePath = request('file_path');
            
            if (!$filePath) {
                return response()->json(['error' => 'file_path parameter required'], 400);
            }

            $this->logger("Checking file: {$filePath}");

            // Check if file exists in storage
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'exists' => false,
                    'file_path' => $filePath,
                    'full_path' => Storage::path($filePath),
                    'message' => 'File not found in storage'
                ], 404);
            }

            // Get file info
            $size = Storage::size($filePath);
            $content = Storage::get($filePath);
            $header = substr($content, 0, 20);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            return response()->json([
                'exists' => true,
                'file_path' => $filePath,
                'full_path' => Storage::path($filePath),
                'size_bytes' => $size,
                'extension' => $extension,
                'header' => bin2hex($header),
                'is_pdf' => strpos($content, '%PDF') === 0,
                'content_length' => strlen($content)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek document mapping dengan file yang sebenarnya ada
     * GET /debug/check-mapping-files/{id}
     */
    public function checkMappingFiles($id)
    {
        try {
            $mapping = \App\Models\DocumentMapping::with(['files'])->findOrFail($id);

            $fileResults = [];

            foreach ($mapping->files as $file) {
                $filePath = $file->file_path;
                $exists = Storage::exists($filePath);
                $size = $exists ? Storage::size($filePath) : 0;

                // Jika file tidak ada, cari di storage dengan nama yang sama
                $alternativePaths = [];
                if (!$exists) {
                    $fileName = basename($filePath);
                    $allFiles = Storage::allFiles();
                    
                    foreach ($allFiles as $storagedFile) {
                        if (basename($storagedFile) === $fileName) {
                            $alternativePaths[] = [
                                'path' => $storagedFile,
                                'size' => Storage::size($storagedFile),
                                'exists' => true
                            ];
                        }
                    }
                }

                $fileResults[] = [
                    'file_id' => $file->id,
                    'original_name' => $file->original_name,
                    'stored_path' => $filePath,
                    'exists' => $exists,
                    'size' => $size,
                    'full_disk_path' => $exists ? Storage::path($filePath) : 'N/A',
                    'alternative_paths' => $alternativePaths
                ];
            }

            return response()->json([
                'mapping_id' => $id,
                'document' => $mapping->document?->name,
                'total_files' => count($mapping->files),
                'files' => $fileResults,
                'note' => 'If alternative_paths not empty, file exists but path in DB is wrong'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find file by name across entire storage
     * GET /debug/find-file?filename=FTPP_Summary
     */
    public function findFileByName()
    {
        try {
            $filename =request('filename');
            
            if (!$filename) {
                return response()->json(['error' => 'filename parameter required'], 400);
            }

            $this->logger("Searching for file containing: {$filename}");

            $allFiles = Storage::allFiles();
            $matches = [];

            foreach ($allFiles as $file) {
                if (stripos($file, $filename) !== false) {
                    $matches[] = [
                        'path' => $file,
                        'size' => Storage::size($file),
                        'modified' => date('Y-m-d H:i:s', Storage::lastModified($file))
                    ];
                }
            }

            return response()->json([
                'search_term' => $filename,
                'total_matches' => count($matches),
                'matches' => $matches
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test DocumentMapping record
     * GET /debug/test-mapping/{id}
     */
    public function testMapping($id)
    {
        try {
            $mapping = \App\Models\DocumentMapping::with(['files'])->findOrFail($id);

            $file = $mapping->files()
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$file) {
                return response()->json([
                    'mapping_id' => $id,
                    'has_files' => false,
                    'message' => 'No active files found'
                ]);
            }

            $filePath = $file->file_path;
            $exists = \Illuminate\Support\Facades\Storage::exists($filePath);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            return response()->json([
                'mapping_id' => $id,
                'file_id' => $file->id,
                'file_path' => $filePath,
                'original_name' => $file->original_name,
                'file_exists' => $exists,
                'extension' => $extension,
                'full_path' => $exists ? \Illuminate\Support\Facades\Storage::path($filePath) : 'N/A',
                'size_bytes' => $exists ? \Illuminate\Support\Facades\Storage::size($filePath) : 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mapping details
     * GET /debug/mapping-info/{id}
     */
    public function getMappingInfo($id)
    {
        try {
            $mapping = \App\Models\DocumentMapping::with('files')->find($id);
            
            if (!$mapping) {
                return response()->json(['error' => 'Mapping not found'], 404);
            }

            $fileData = [];
            foreach ($mapping->files as $file) {
                $fileData[] = [
                    'file_id' => $file->id,
                    'original_name' => $file->original_name,
                    'stored_path' => $file->file_path,
                    'exists' => Storage::exists($file->file_path)
                ];
            }

            return response()->json([
                'mapping_id' => $id,
                'document_name' => $mapping->document?->name,
                'files_count' => count($mapping->files),
                'files' => $fileData
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show latest uploaded files and their paths
     * GET /debug/latest-files
     */
    public function latestFiles()
    {
        try {
            $files = \App\Models\DocumentFile::with('mapping.document')
                ->latest('id')
                ->limit(10)
                ->get(['id', 'original_name', 'file_path', 'mapping_id', 'created_at']);

            $fileData = $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'stored_path' => $file->file_path,
                    'mapping_id' => $file->mapping_id,
                    'document' => $file->mapping?->document?->name,
                    'exists_in_storage' => Storage::exists($file->file_path),
                    'full_disk_path' => Storage::path($file->file_path),
                    'created_at' => $file->created_at
                ];
            });

            return response()->json([
                'total' => count($fileData),
                'files' => $fileData
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function logger($message)
    {
        \Log::info("[DEBUG-CONVERSION] {$message}");
    }
}
