# Download as PDF - Dokumentasi Implementasi

Dokumentasi ini menjelaskan fitur `Download as PDF` pada menu **Document Review**, termasuk alur fungsional, konfigurasi watermark, dan deployment di server production (termasuk Ghostscript).

## 1. Ringkasan Fitur

Fitur `Download as PDF` di Document Review mendukung:

- Download file yang sudah PDF dengan watermark.
- Konversi file non-PDF (`docx`, `xlsx`, `doc`, `xls`, `xlsm`, `pptx`, `ppt`, `odt`, `ods`, `odp`) ke PDF, lalu watermark.
- Jika dokumen punya lebih dari 1 file aktif, user diminta memilih file (radio button) sebelum download.
- Inline preview (`?inline=1`) untuk keperluan iframe preview.

## 2. Alur User (UI)

1. User klik tombol `Download as PDF` pada dokumen berstatus approved.
2. Frontend memanggil endpoint `GET /document-review/{id}/files`.
3. Jika file aktif > 1, tampil modal pilihan file (radio) dengan nama file aktif.
4. Frontend memanggil endpoint `GET /document-review/{id}/download-as-pdf` dengan `file_id` terpilih.
5. Backend generate PDF + watermark, lalu response file PDF attachment.

Catatan:
- Jika file aktif hanya 1, request langsung download tanpa modal pilih file.

## 3. Alur Backend

### 3.1 Endpoint utama

- Route:
  - `GET /document-review/{id}/download-as-pdf`
- Controller:
  - `DocumentReviewController::downloadAsPdf()`

### 3.2 Pemilihan file

- Jika query `file_id` ada: backend pakai file itu.
- Jika `file_id` tidak ada: backend pakai file aktif terbaru (`created_at` paling baru).

### 3.3 Proses berdasarkan extension

- Jika extension `pdf`:
  - ambil file dari storage,
  - apply watermark langsung via `PdfWatermarker`.
- Jika extension office (`docx/xlsx/...`):
  - upload sementara ke DocSpace,
  - convert ke PDF via `DocumentConverterService`,
  - apply watermark,
  - hapus file sementara di DocSpace.

### 3.4 Validasi output

Sebelum dikirim ke browser, backend memverifikasi:

- konten tidak kosong,
- header file valid PDF (`%PDF`).

## 4. Watermark yang Dipakai Saat Ini

Default flow Document Review:

- Prioritas watermark image: `public/images/ORIGINAL.png`.
- Jika image tidak ditemukan, fallback ke text watermark:
  - `Reviewed by {nama_user} - {YYYY-mm-dd HH:ii}`.

## 5. Ghostscript dan FPDI (Kenapa Dibutuhkan)

Beberapa PDF hasil converter memiliki kompresi yang tidak didukung parser FPDI free.

Untuk kasus ini, service `PdfWatermarker` melakukan fallback:

1. coba watermark dengan FPDI,
2. jika gagal karena parser/compression issue,
3. normalisasi PDF dengan Ghostscript,
4. coba watermark lagi.

Tanpa Ghostscript, sebagian file bisa terdownload tanpa watermark (fallback original PDF).

## 6. Konfigurasi Environment

Tambahkan di `.env`:

```dotenv
# Optional: path eksplisit binary Ghostscript
GS_BIN='C:/Program Files/gs/gs10.06.0/bin/gswin64c.exe'
```

Jika binary Ghostscript sudah ada di `PATH`, `GS_BIN` boleh kosong.

## 7. Setup Production Server

### 7.1 Linux (Ubuntu/Debian)

Install Ghostscript:

```bash
sudo apt update
sudo apt install -y ghostscript
```

Cek versi:

```bash
gs --version
```

Opsional `.env`:

```dotenv
GS_BIN=/usr/bin/gs
```

### 7.2 RHEL/CentOS/Alma/Rocky

```bash
sudo dnf install -y ghostscript
# atau
sudo yum install -y ghostscript
```

Cek:

```bash
gs --version
```

### 7.3 Windows Server

1. Download installer Ghostscript dari situs resmi Artifex.
2. Install (64-bit).
3. Pastikan executable tersedia, contoh:
   - `C:\Program Files\gs\gs10.06.0\bin\gswin64c.exe`
4. Set `.env`:

```dotenv
GS_BIN='C:/Program Files/gs/gs10.06.0/bin/gswin64c.exe'
```

5. Verifikasi dari user yang menjalankan PHP (IIS/PHP-FPM/service account) punya akses execute binary.

## 8. Checklist Deploy

1. Deploy code terbaru.
2. Install Ghostscript di server. melalui link https://www.ghostscript.com/releases/gsdnld.html
3. Set `.env` (`GS_BIN` bila perlu).
4. Pastikan file watermark ada:
   - `public/images/ORIGINAL.png`
5. Jalankan clear cache:

```bash
php artisan optimize:clear
```

6. Uji 2 skenario:
- file sumber PDF,
- file sumber non-PDF (docx/xlsx).

## 9. Troubleshooting

### 9.1 Watermark tidak muncul

Cek `storage/logs/laravel.log` untuk keyword:

- `Watermark skipped`
- `PDF watermark error`
- `Document conversion error`

Jika ada parser unsupported compression:

- pastikan Ghostscript terinstall,
- pastikan `GS_BIN` benar,
- pastikan process PHP punya izin execute.

### 9.2 Error dotenv: unexpected whitespace

Penyebab umum: path `.env` ber-spasi tapi tanpa quote.

Gunakan format aman:

```dotenv
GS_BIN='C:/Program Files/gs/gs10.06.0/bin/gswin64c.exe'
```

### 9.3 Modal pilih file tidak tampil benar

Pastikan frontend memuat SweetAlert (`Swal`) dengan benar.

Flow fallback saat `Swal` tidak ada:
- sistem akan pilih file terakhir secara otomatis.

## 10. Batasan Saat Ini

- Tombol `Download as PDF` menghasilkan **1 file PDF per request** (bukan gabung semua file).
- Jika dokumen punya banyak file aktif, user harus pilih 1 file dulu.
- Belum ada mode bulk: `download semua file jadi zip` atau `merge multi-file jadi satu PDF`.

## 11. Saran Pengembangan Lanjutan

- Tambah endpoint `download-all-as-zip` untuk dokumen multi-file.
- Tambah opsi `merge all active files to single PDF`.
- Tambah setting admin untuk mengatur style watermark (size, position, opacity, image/text mode).
