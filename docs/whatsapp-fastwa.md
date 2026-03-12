# WhatsApp Notification via FastWA - Dokumentasi Implementasi

Dokumentasi ini menjelaskan cara kerja pengiriman notifikasi WhatsApp menggunakan FastWA pada project ini, termasuk konfigurasi, command scheduler, testing, dan deployment production.

## 1. Ringkasan

Project menggunakan `WhatsAppService` untuk mengirim pesan ke group WhatsApp melalui endpoint FastWA.

Sumber implementasi utama:

- `app/Services/WhatsAppService.php`
- `config/services.php` (`services.whatsapp`)
- `.env` (`WHATSAPP_API_URL`, `WHATSAPP_TOKEN`, `WHATSAPP_GROUP_ID`)

Command yang saat ini mengirim notifikasi WA:

- `document:send-reminder` (Document Control reminder)
- `document:send-review-reminder` (Document Review reminder)
- `notify:findings-due` (FTPP/Finding due notification)

## 2. Konfigurasi Environment

Set variabel berikut di `.env`:

```dotenv
WHATSAPP_API_URL=https://app.fastwa.com/api/v1/xxxxxxxxxxxxxxxx/send_text
WHATSAPP_TOKEN=your_fastwa_api_key
WHATSAPP_GROUP_ID=120xxxxxxxxxxxx@g.us
```

Penjelasan:

- `WHATSAPP_API_URL`: endpoint FastWA untuk kirim text.
- `WHATSAPP_TOKEN`: API key FastWA.
- `WHATSAPP_GROUP_ID`: nomor/group target default.

Mapping konfigurasi di kode:

- `config/services.php`:
  - `services.whatsapp.url`
  - `services.whatsapp.token`
  - `services.whatsapp.group_id`

## 3. Cara Kerja Service

Method utama:

- `WhatsAppService::sendGroupMessage(?string $groupId, string $message): bool`

Perilaku:

1. Gunakan `groupId` parameter jika ada, jika kosong pakai `services.whatsapp.group_id`.
2. Kirim request `POST` form-data ke FastWA dengan payload:
   - `api_key`
   - `phone`
   - `message`
3. Jika response gagal (`!successful()`), tulis log error.
4. Jika sukses, tulis log info.
5. Return `true/false` sesuai status HTTP.

Catatan payload saat ini:

- Key penerima yang dipakai adalah `phone`.
- Jika provider FastWA instance Anda butuh key lain (mis. `group_id`), sesuaikan di `WhatsAppService.php`.

## 4. Alur Notifikasi yang Sudah Ada

## 4.1 Document Control Reminder

- Command: `document:send-reminder`
- File: `app/Console/Commands/SendDocumentControlReminder.php`
- Schedule: Senin 08:00 (weekly)
- Sumber jadwal: `app/Console/Kernel.php`

Fungsi utama:

- Kategorikan dokumen berdasarkan status/kondisi (Uncomplete, Rejected, Need Review, Active nearing obsolete, Obsolete today, Overdue).
- Compose pesan ringkasan per departemen.
- Kirim 1 pesan ke group WA.

## 4.2 Document Review Reminder

- Command: `document:send-review-reminder`
- File: `app/Console/Commands/SendDocumentReviewReminder.php`
- Schedule: Kamis 08:00 (weekly)

Fungsi utama:

- Ambil dokumen review yang `Approved` dan punya `notes`.
- Convert notes HTML (dari editor) ke format teks WA.
- Kirim ringkasan revisi per departemen ke group.
- Set `review_notified_at` setelah sukses kirim.

## 4.3 Findings Due Notification

- Command: `notify:findings-due`
- File: `app/Console/Commands/SendFindingDueNotifications.php`
- Schedule: tiap hari 12:00

Fungsi utama:

- Cek finding due date (H-3, H-1, H, H+1) untuk status `Need Assign`.
- Kirim notifikasi aplikasi ke user terkait.
- Kirim ringkasan ke group WA jika ada item.

## 5. Scheduler di Production

Konfigurasi jadwal Laravel saat ini (`app/Console/Kernel.php`):

- `document:send-reminder` -> `weeklyOn(1, '08:00')`
- `document:send-review-reminder` -> `weeklyOn(4, '08:00')`
- `notify:findings-due` -> `dailyAt('12:00')`

Agar schedule jalan otomatis di production:

## 5.1 Linux (recommended cron)

Tambahkan cron:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 5.2 Windows Server (Task Scheduler)

- Buat task per menit menjalankan:
  - `php artisan schedule:run`
- Pastikan task dijalankan oleh user yang punya akses ke folder project dan PHP binary.

## 6. Testing Manual

## 6.1 Test command langsung

Jalankan command berikut untuk memaksa proses notifikasi:

```bash
php artisan document:send-reminder
php artisan document:send-review-reminder
php artisan notify:findings-due
```

## 6.2 Verifikasi log

Cek `storage/logs/laravel.log`:

- sukses:
  - `WhatsApp message to group sent successfully`
- gagal:
  - `WhatsApp send to group failed`

## 6.3 Test service via Tinker (opsional)

```bash
php artisan tinker
```

Lalu:

```php
app(\App\Services\WhatsAppService::class)
    ->sendGroupMessage(null, 'Test FastWA from Laravel');
```

## 7. Deployment Checklist (FastWA)

1. Isi env WA di server production.
2. Pastikan endpoint FastWA bisa diakses dari server app.
3. Jalankan `php artisan optimize:clear` setelah update env.
4. Pastikan scheduler aktif (`schedule:run` via cron/task scheduler).
5. Uji satu command manual dan cek log.
6. Uji penerimaan pesan di group target.

## 8. Troubleshooting

## 8.1 Pesan tidak terkirim

Cek:

- `WHATSAPP_API_URL` valid.
- `WHATSAPP_TOKEN` valid.
- `WHATSAPP_GROUP_ID` benar.
- format payload sesuai requirement endpoint FastWA Anda (`phone` vs `group_id`).

## 8.2 HTTP gagal (401/403/422/500)

Lihat body response di log error `WhatsApp send to group failed` untuk detail penyebab.

## 8.3 Schedule tidak jalan otomatis

- Cek cron/Task Scheduler sudah aktif.
- Cek timezone server sesuai ekspektasi jadwal.
- Jalankan manual `php artisan schedule:run` untuk verifikasi.

## 8.4 SSL/TLS issue saat call FastWA

Saat ini service memakai:

```php
'"verify" => false'
```

di request HTTP.

Artinya verifikasi sertifikat TLS dimatikan. Ini membantu saat environment bermasalah SSL, tetapi kurang ideal untuk keamanan production.

Rekomendasi production:

- Aktifkan verifikasi TLS (`verify => true`) bila memungkinkan.
- Jika perlu custom CA bundle, set path CA yang valid di server.

## 9. Contoh Format Pesan

Semua command menggunakan plain text + markdown WhatsApp sederhana:

- `*bold*`
- `_italic_`
- line break `\n`

Hindari:

- pesan terlalu panjang (potensi dipotong provider),
- karakter HTML mentah (khusus Document Review sudah ada converter HTML -> teks WA).

## 10. Referensi Kode

- `app/Services/WhatsAppService.php`
- `app/Console/Commands/SendDocumentControlReminder.php`
- `app/Console/Commands/SendDocumentReviewReminder.php`
- `app/Console/Commands/SendFindingDueNotifications.php`
- `app/Console/Kernel.php`
- `config/services.php`
- `.env` / `.env.example`
