# Watermarking (PDF) — Catatan Implementasi (Bahasa Indonesia)

Dokumen ini menjelaskan fitur watermark (cap air) pada file PDF yang diimplementasikan pada aplikasi.

Ringkasan
- Tujuan: menambahkan watermark yang terlihat (logo di kanan-bawah + teks "CONFIDENTIAL" di tengah) pada PDF yang diunduh.
- Cakupan: hanya berlaku untuk berkas PDF; berkas non-PDF akan dikirim tanpa watermark.

File penting
- Service: `app/Services/PdfWatermarker.php` — fungsi untuk menempelkan gambar atau teks pada PDF.
- Controller: `app/Http/Controllers/DocumentControlWatermarkController.php` — endpoint yang mencari file dan mengembalikan PDF ber-watermark.
- Routes: `routes/web.php`
  - Document Control (berdasarkan mapping): `document-control/{mapping}/download-watermarked` (nama: `document-control.downloadWatermarked`)
  - Document Review (berdasarkan file id): `document-review/file/{file}/download-watermarked` (nama: `document-review.downloadWatermarkedFile`)
- Views: Blade untuk Document Control dan Document Review berisi link ke route ini.

Prasyarat
- Paket Composer: `setasign/fpdi`. Pasang dengan:

```bash
composer require setasign/fpdi
```

- Ekstensi PHP GD digunakan untuk preprocessing gambar (membuat PNG transparan/translusen). Periksa dengan:

```bash
php -m | grep -i gd
```

Alur kerja
- Controller akan mencoba menemukan path file dengan urutan pengecekan:
  1. Jika path adalah URL http(s) → redirect ke URL tersebut
  2. `public/storage/<file_path>` (mapping ke `public_path('storage/' . $file_path)`)
  3. `Storage::disk('local')`
  4. `Storage::disk('public')`
  5. Path filesystem mentah

- Jika file bukan PDF, controller akan mengirim file asli tanpa watermark.

- Jika ada gambar watermark di `public/images/madonna-icon.png`, controller melakukan dua langkah:
  1. `PdfWatermarker::stampImage($pdfPath, $imagePath, $widthMm, $marginMm, $opacityPercent)` — menempelkan logo pada tiap halaman di kanan-bawah. Jika `opacityPercent` < 100, service akan membuat PNG sementara dengan opacity yang disimulasikan menggunakan GD.
  2. PDF sementara ditulis ke file temp, lalu `PdfWatermarker::stampText($tmpPdf, 'CONFIDENTIAL')` menempelkan teks di tengah tiap halaman.

- Jika gambar watermark tidak ditemukan, controller menggunakan `stampText` saja (teks watermark menyertakan username + tanggal).

Pengaturan
- Mengganti gambar watermark: simpan gambar baru di `public/images/madonna-icon.png` (lebih baik gunakan latar transparan).
- Ubah opacity/ukuran/margin gambar dengan mengubah argumen pada pemanggilan `stampImage` di `DocumentControlWatermarkController`.
- Ubah tampilan teks CONFIDENTIAL di `PdfWatermarker::stampText()` (pilih font, ukuran, warna).

Keterbatasan & rekomendasi
- FPDI tidak memiliki utilitas alpha-blending tinggi atau rotasi watermark yang kompleks. Saat ini opacity disimulasikan lewat GD.
- Jika butuh watermark miring/berulang atau blending lebih baik, pertimbangkan menggunakan ekstensi `Imagick` dan mengolah gambar lewat Imagick.
- Untuk performa pada file besar atau banyak unduhan, pertimbangkan caching PDF ber-watermark (generate saat pertama kali dan simpan), tetapi pastikan akses dan invalidasi cache saat file sumber berubah.

Troubleshooting
- 404 File Not Found: pastikan `DocumentFile.file_path` benar dan file tersedia di salah satu lokasi: `public/storage/<file_path>`, `storage/app/<file_path>`, atau path absolut.

- Error 500 atau PDF kosong: cek `storage/logs/laravel.log` untuk error FPDI/GD.
  - Penyebab umum: paket `setasign/fpdi` belum terpasang, ekstensi GD tidak aktif, atau file font yang digunakan `stampText` hilang.

Contoh penggunaan
- Link di Blade (Document Review):

```blade
<a href="{{ route('document-review.downloadWatermarkedFile', $fileId, false) }}" target="_blank">Download (watermarked)</a>
```

- URL langsung (dev lokal):

```
http://127.0.0.1:8000/document-review/file/24/download-watermarked
```

Catatan untuk pengembang
- Lokasi implementasi:
  - `app/Services/PdfWatermarker.php`
  - `app/Http/Controllers/DocumentControlWatermarkController.php`
  - `routes/web.php` (nama route `document-review.downloadWatermarkedFile`)

- Saat membuat link di Blade, gunakan `route(..., false)` untuk menghasilkan URL relatif agar terhindar mismatch host di lingkungan pengembangan.

Kontak
- Jika ingin gaya watermark lain (teks diagonal, pola berulang, atau hanya gambar), beri tahu preferensi — saya bisa implementasikan dengan Imagick atau metode lain.
