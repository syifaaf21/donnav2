# ONLYOFFICE (DocSpace) - Dokumentasi Implementasi

Dokumentasi ini menjelaskan integrasi ONLYOFFICE DocSpace pada project ini: konfigurasi, alur editor, alur konversi, kebutuhan production, dan troubleshooting.

## 1. Gambaran Umum

Project menggunakan **ONLYOFFICE DocSpace API** untuk:

- Upload file dari Laravel storage ke DocSpace.
- Membuka file di editor ONLYOFFICE (`doceditor`).
- Sinkronisasi file hasil edit kembali ke Laravel storage.
- Konversi file Office (`docx/xlsx/pptx/...`) ke PDF via API `copyas`.

Komponen utama:

- `app/Services/DocSpaceService.php`
- `app/Services/DocumentConverterService.php`
- `app/Http/Controllers/EditorController.php`
- `app/Http/Controllers/DocumentConversionController.php`
- `config/onlyoffice.php`

## 2. Konfigurasi Environment

Isi variabel berikut di `.env`:

```dotenv
ONLYOFFICE_DOCSPACE_URL=https://your-docspace-domain.onlyoffice.com
ONLYOFFICE_DOCSPACE_EMAIL=admin@example.com
ONLYOFFICE_DOCSPACE_PASSWORD=your_password
ONLYOFFICE_DOCSPACE_FOLDER_ID=12345

# Optional tuning (dipakai jika diisi)
ONLYOFFICE_REQUEST_TIMEOUT=120
ONLYOFFICE_CONNECT_TIMEOUT=20
ONLYOFFICE_RETRY_TIMES=3
ONLYOFFICE_RETRY_SLEEP_MS=1000
```

Catatan:

- `ONLYOFFICE_DOCSPACE_FOLDER_ID` adalah folder tujuan upload sementara/permanen di DocSpace.
- `config/onlyoffice.php` mengambil nilai dari env di atas.

## 3. Route yang Tersedia

### 3.1 Conversion API

Prefix: `/convert`

- `POST /convert/docspace-to-pdf`
- `GET /convert/docspace-file/{fileId}/download`
- `POST /convert/local-file`
- `POST /convert/multiple`
- `GET /convert/status`

### 3.2 Document Review

- `GET /document-review/{id}/download-as-pdf`
- `GET /document-review/{id}/files`

### 3.3 Editor

Route editor berada pada area `EditorController` (menu OnlyOffice) untuk buka editor dan sinkronisasi file.

## 4. Alur Kerja Integrasi

## 4.1 Alur Edit Online (Editor)

1. User buka menu editor.
2. Jika file belum punya `docspace_file_id`, sistem upload dulu ke DocSpace.
3. Sistem membentuk URL editor:
   - `{DOCSPACE_URL}/doceditor?fileId=...`
4. User edit dokumen di DocSpace.
5. Saat sinkronisasi (`sync`), sistem download versi terbaru dari DocSpace lalu overwrite file di Laravel storage.

## 4.2 Alur Konversi ke PDF

1. Dapatkan info file DocSpace (`/api/2.0/files/file/{id}`).
2. Jika belum PDF, panggil endpoint convert:
   - `POST /api/2.0/files/file/{id}/copyas`
3. Ambil `id` file PDF hasil convert.
4. Download konten PDF.
5. (Opsional) Apply watermark.
6. Hapus file PDF sementara di DocSpace agar room tidak penuh.

## 5. Autentikasi dan Token

`DocSpaceService` menggunakan login API:

- Endpoint auth: `POST /api/2.0/authentication`
- Menyimpan token ke cache Laravel (`docspace_token`) sampai 8 jam.
- Menyimpan `asc_auth_key` untuk kebutuhan embed/session jika diperlukan.

Jika API mengembalikan `401`, service akan:

1. clear cache token,
2. login ulang,
3. retry request.

## 6. Requirement Production

## 6.1 Network dan Security

- Server app harus bisa outbound HTTPS ke domain DocSpace (`443`).
- Pastikan DNS resolve domain DocSpace stabil.
- Jangan blokir endpoint API DocSpace via firewall/proxy.
- Simpan credential ONLYOFFICE hanya di `.env` (jangan hardcode).

## 6.2 PHP Runtime

Pastikan extension berikut aktif:

- `curl`
- `openssl`
- `mbstring`
- `json`

(Umumnya sudah aktif di instalasi Laravel standard.)

## 6.3 Queue/Cache

Karena token disimpan via cache:

- pastikan cache backend stabil (`file/redis`),
- sinkronkan waktu server (NTP) untuk menghindari edge-case expiry.

## 7. Checklist Deploy ONLYOFFICE

1. Set semua env ONLYOFFICE di server.
2. Jalankan:

```bash
php artisan optimize:clear
```

3. Uji health endpoint:

```bash
GET /convert/status
```

4. Uji upload ke DocSpace dari 1 file nyata.
5. Uji convert docx -> pdf.
6. Uji open editor + sync balik ke storage.

## 8. Troubleshooting Umum

## 8.1 Timeout / cURL error 28

Gejala di log:
- `cURL error 28`

Aksi:

- cek koneksi internet/VPN/proxy dari server app ke DocSpace,
- naikkan timeout:
  - `ONLYOFFICE_REQUEST_TIMEOUT`
  - `ONLYOFFICE_CONNECT_TIMEOUT`,
- cek firewall outbound 443.

## 8.2 Konversi gagal HTTP 404/405

Gejala:
- `Konversi dokumen gagal (HTTP 404/405)`

Aksi:

- pastikan endpoint convert yang dipakai adalah `copyas` (sesuai implementasi saat ini),
- pastikan file source masih ada di folder DocSpace,
- validasi token masih valid (coba clear cache + retry).

## 8.3 Upload gagal / file tidak ditemukan

Gejala:
- `File tidak ditemukan di storage`

Aksi:

- pastikan path file benar,
- cek file berada di disk `public` atau `local` sesuai fallback logic,
- pastikan permission baca file oleh process PHP.

## 8.4 Editor tidak bisa dibuka

Aksi:

- cek `ONLYOFFICE_DOCSPACE_URL` benar,
- pastikan `docspace_file_id` ada pada record file,
- cek token login DocSpace bisa didapat dari service.

## 9. Best Practice

- Gunakan folder DocSpace khusus untuk file sementara konversi.
- Bersihkan file temporary hasil convert (sudah dilakukan di service, jangan dihapus).
- Tambahkan monitoring log untuk event berikut:
  - upload,
  - convert response,
  - delete temporary file,
  - conversion error.
- Batasi akses route conversion/editor dengan middleware auth/role sesuai kebijakan internal.

## 10. Referensi Internal Kode

- Konfigurasi: `config/onlyoffice.php`
- Service API utama: `app/Services/DocSpaceService.php`
- Service convert: `app/Services/DocumentConverterService.php`
- Integrasi editor: `app/Http/Controllers/EditorController.php`
- Integrasi download-as-pdf review: `app/Http/Controllers/DocumentReviewController.php`
