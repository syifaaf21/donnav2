# Document Conversion Service - Implementation Guide

Project Anda sekarang mendukung **konversi dokumen (DOCX/XLSX) ke PDF via OnlyOffice Cloud dengan watermark otomatis**.

## 📋 Overview

Sistem ini menggunakan **OnlyOffice Cloud API** untuk melakukan konversi dokumen tanpa perlu install LibreOffice di server.

### Komponen yang ditambahkan:

1. **`DocumentConverterService`** - Service untuk handle konversi
2. **`DocumentConversionController`** - Controller dengan endpoint untuk konversi
3. **Routes** - API endpoints yang siap digunakan
4. **Integration** - Terintegrasi dengan `DocSpaceService` & `PdfWatermarker` yang sudah ada

---

## 🚀 Quick Start

### **1. Convert File dari OnlyOffice ke PDF dengan Watermark**

**Request:**
```bash
POST /convert/docspace-to-pdf
Content-Type: application/json

{
  "docspace_file_id": "12345",
  "add_watermark": true,
  "watermark_text": "CONFIDENTIAL",
  "file_name": "my-document"
}
```

**Response:**
```
PDF file download (attachment)
```

---

### **2. Download & Convert Docspace File (Simple)**

**Request:**
```bash
GET /convert/docspace-file/12345/download?watermark_text=APPROVED
```

**Response:**
```
PDF file (APPROVED watermark)
```

---

### **3. Convert Local File (Storage Laravel)**

**Request:**
```bash
POST /convert/local-file
Content-Type: application/json

{
  "file_path": "documents/report.docx",
  "add_watermark": true,
  "watermark_text": "DRAFT",
  "file_name": "report-draft"
}
```

---

### **4. Batch Convert Multiple Files**

**Request:**
```bash
POST /convert/multiple
Content-Type: application/json

{
  "file_ids": ["123", "456", "789"],
  "add_watermark": true,
  "watermark_text": "CONFIDENTIAL"
}
```

**Response:**
```json
{
  "123": {
    "success": true,
    "content_base64": "JVBERi0xLjQK..."
  },
  "456": {
    "success": false,
    "error": "Format not supported"
  },
  "789": {
    "success": true,
    "content_base64": "JVBERi0xLjQK..."
  }
}
```

---

## 📝 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/convert/docspace-to-pdf` | Convert OnlyOffice file to PDF |
| GET | `/convert/docspace-file/{fileId}/download` | Download & convert single file |
| POST | `/convert/local-file` | Convert file dari storage Laravel |
| POST | `/convert/multiple` | Batch convert multiple files |
| GET | `/convert/status` | Health check service |

---

## 🔧 Usage di Controller/Code

### **Dari Controller:**

```php
<?php

namespace App\Http\Controllers;

use App\Services\DocumentConverterService;

class MyController extends Controller
{
    public function __construct(private DocumentConverterService $converter)
    {}

    public function convertDocument($fileId)
    {
        // Convert + Watermark
        $pdfContent = $this->converter->convertToPdf(
            $fileId,
            addWatermark: true,
            watermarkText: 'APPROVED'
        );

        // Return langsung ke user
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="document.pdf"');
    }

    public function convertAndStore($fileId)
    {
        // Convert tanpa watermark
        $pdfContent = $this->converter->convertToPdf($fileId);

        // Simpan ke storage
        Storage::put('pdfs/converted-' . time() . '.pdf', $pdfContent);

        return "File berhasil dikonversi dan disimpan";
    }

    public function convertWithImageWatermark($fileId)
    {
        // Convert + Image watermark (misal: logo perusahaan)
        $pdfContent = $this->converter->convertToPdf(
            $fileId,
            addWatermark: true,
            watermarkImagePath: 'assets/logo.png'
        );

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf');
    }
}
```

### **Dari Command/Job:**

```php
<?php

namespace App\Console\Commands;

use App\Services\DocumentConverterService;
use Illuminate\Console\Command;

class ConvertPendingDocuments extends Command
{
    protected $signature = 'documents:convert-pending';

    public function handle(DocumentConverterService $converter)
    {
        $files = \App\Models\Document::where('status', 'pending')->get();

        foreach ($files as $file) {
            $pdfContent = $converter->convertToPdf(
                $file->docspace_id,
                addWatermark: true,
                watermarkText: "Converted at " . now()
            );

            Storage::put(
                "converted/{$file->id}.pdf",
                $pdfContent
            );

            $file->update(['status' => 'converted']);
        }
    }
}
```

---

## 🎨 Watermark Customization

### **Text Watermark:**

```php
$pdf = $converter->convertToPdf(
    '12345',
    addWatermark: true,
    watermarkText: 'CONFIDENTIAL' // Simple text
);
```

Output: "CONFIDENTIAL" ditampilkan besar di tengah halaman dengan gaya bold & warna abu-abu terang.

### **Image Watermark:**

```php
$pdf = $converter->convertToPdf(
    '12345',
    addWatermark: true,
    watermarkImagePath: 'assets/watermarks/logo.png' // Path relative ke storage
);
```

Output: Logo ditampilkan di bottom-right setiap halaman.

### **Customize PdfWatermarker:**

Edit [app/Services/PdfWatermarker.php](../../app/Services/PdfWatermarker.php) untuk ubah:
- Font size
- Color (RGB)
- Position
- Opacity

---

## 📦 Supported Formats

**Input (untuk konversi):**
- DOCX (Word)
- DOC (Word)
- XLSX (Excel)
- XLS (Excel)
- XLSM (Excel Macro)
- PPTX (PowerPoint)
- PPT (PowerPoint)
- ODT (OpenDocument Text)
- ODS (OpenDocument Spreadsheet)
- ODP (OpenDocument Presentation)
- PDF (no conversion needed, langsung watermark)

**Output:**
- PDF (selalu)

---

## ⚠️ Error Handling

### **Contoh Error Responses:**

```json
{
  "error": "Konversi dokumen gagal",
  "message": "Format xlsx tidak didukung untuk konversi"
}
```

```json
{
  "error": "Download konversi gagal",
  "message": "Tidak bisa mendapatkan download URL untuk file PDF"
}
```

---

## ⚙️ Configuration

Pastikan `config/onlyoffice.php` sudah benar:

```php
return [
    'docspace_url' => env('ONLYOFFICE_DOCSPACE_URL'),
    'docspace_email' => env('ONLYOFFICE_DOCSPACE_EMAIL'),
    'docspace_password' => env('ONLYOFFICE_DOCSPACE_PASSWORD'),
    'docspace_folder_id' => env('ONLYOFFICE_DOCSPACE_FOLDER_ID'),
];
```

**Environment variables (.env):**
```
ONLYOFFICE_DOCSPACE_URL=https://your-docspace.onlyoffice.com
ONLYOFFICE_DOCSPACE_EMAIL=admin@example.com
ONLYOFFICE_DOCSPACE_PASSWORD=your-password
ONLYOFFICE_DOCSPACE_FOLDER_ID=123
```

---

## 🔒 Security

- Semua endpoints protected dengan **`auth` middleware** (login required)
- Token OnlyOffice di-cache 8 jam untuk performance
- File temporary dihapus otomatis setelah proses selesai
- PDF content langsung di-stream (tidak disimpan ke disk)

---

## 📊 Performance Tips

1. **Batch Processing** - Gunakan `/convert/multiple` untuk banyak file sekaligus
2. **Caching** - Token OnlyOffice di-cache, tidak perlu re-authenticate setiap request
3. **Async Processing** - Untuk file besar, gunakan jobs/queues:

```php
// app/Jobs/ConvertDocumentJob.php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConvertDocumentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $docspaceId) {}

    public function handle(DocumentConverterService $converter)
    {
        $pdf = $converter->convertToPdf($this->docspaceId, addWatermark: true);
        Storage::put("pdfs/{$this->docspaceId}.pdf", $pdf);
    }
}

// Di Controller:
ConvertDocumentJob::dispatch($fileId);
```

---

## 🐛 Troubleshooting

**Problem:** Konversi timeout
**Solution:** Increase timeout atau use async jobs untuk file besar

**Problem:** "Tidak bisa mendapatkan ID file PDF"
**Solution:** Pastikan OnlyOffice account punya permission untuk convert files

**Problem:** Watermark tidak muncul
**Solution:** Check `app/Services/PdfWatermarker.php` dan verify image path

---

## 📞 Support

Jika ada error, check:
1. Logs: `storage/logs/laravel.log`
2. OnlyOffice credentials di `.env`
3. File permissions di `storage/` folder
4. Network access ke OnlyOffice Cloud

---

**Dokumentasi dibuat:** March 5, 2026
