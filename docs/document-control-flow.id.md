# Document Control Flow (Implementasi Saat Ini)

Dokumen ini menjelaskan perilaku aktual fitur Document Control berdasarkan kode saat ini, termasuk:
- Kapan tombol aksi aktif/tampil
- Alur upload/revise/approve/reject
- Kapan file masuk archive atau tidak

## 1. Aktor dan Halaman

- User biasa (non Admin/Super Admin)
  - Halaman department: `document-control.department`
- Admin / Super Admin
  - Halaman department: `document-control.department`
  - Halaman approval queue: `document-control.approval`

Catatan:
- Halaman approval queue hanya memuat dokumen status `Need Review`.

## 2. Status Utama Dokumen

Status mapping yang dipakai di Document Control:
- `Active`
- `Need Review`
- `Rejected`
- `Uncomplete`
- `Obsolete`

## 3. Aturan Tombol di Halaman Department

Di tabel department, tombol utama:
- `View File`
- `Upload/Revise`

### 3.1 View File

File akan tampil di preview/list jika:
- `is_active = 1`, atau
- `pending_approval = 2` (rejected file tetap terlihat untuk tindak lanjut)

### 3.2 Upload/Revise (ringkasan rule)

`Upload/Revise` hanya muncul di `approvalMode = false` (halaman department biasa).

Rule efektif (gabungan Blade + JS):
- Untuk `Need Review`:
  - Tombol tampil hanya jika ada minimal satu file pending yang memenuhi:
  - `is_active = 1`, `pending_approval = 1`
- Untuk user non-admin:
  - `Rejected`, `Obsolete`, `Uncomplete` -> tampil
  - `Active` -> tampil hanya jika `reminder_date` = hari ini
- Untuk admin:
  - Secara JS, admin diperbolehkan revise
  - Tapi jika status `Need Review` tanpa file pending, tombol tidak dirender dari Blade

## 4. Aturan Tombol di Halaman Approval

Di `document-control.approval` (`approvalMode = true`):
- Tombol `Approve` dan `Reject` hanya untuk Admin/Super Admin
- Aksi ini dijalankan pada dokumen status `Need Review`

## 5. Flow Revise (Upload/Replace/Delete)

Endpoint: `POST document-control/{mapping}/revise`

Setelah revise diproses, status mapping selalu di-set ke `Need Review`.

### 5.1 Upload file baru

File baru dibuat dengan:
- `is_active = 1`
- `pending_approval = 1`

Artinya file baru menunggu approval admin.

### 5.2 Replace file lama

Kasus per status saat ini:

- Jika file lama `pending_approval = 2` (rejected):
  - file lama langsung diselesaikan (`pending_approval = 0`, `is_active = 0`, `marked_for_deletion_at = now()`)

- Jika status mapping saat replace = `Need Review`:
  - file lama yang direplace dibuat hidden dan tidak masuk archive window:
  - `is_active = 0`, `pending_approval = 0`, `marked_for_deletion_at = null`
  - file ini tidak muncul di preview

- Jika status mapping saat replace = `Rejected`:
  - file lama masuk archive window:
  - `is_active = 0`, `pending_approval = 0`, `marked_for_deletion_at = now() + 1 year`

- Status lain (contoh `Active`):
  - file lama ditandai pending agar diproses saat approve:
  - `is_active = 1`, `pending_approval = 1`, `replaced_by_id` diisi

### 5.3 Delete file dari modal revise

- Jika status saat ini `Active`:
  - file ditandai pending delete:
  - `is_active = 0`, `pending_approval = 1`, `marked_for_deletion_at = null`
  - butuh approval admin

- Jika status saat ini `Rejected`:
  - file dihapus fisik langsung

- Status lain:
  - file diset `is_active = 0`, `marked_for_deletion_at = now() + 1 year`

## 6. Flow Approve

Endpoint: `POST document-control/{mapping}/approve`

Saat approve:
- status mapping -> `Active`
- notes dipulihkan dari `initial_notes` (jika ada)

### 6.1 Pengaruh ke obsolete date

Ada dua mode:
- Approval normal (ada upload pending):
  - `obsolete_date` ditambah `period_years`
  - `reminder_date` di-set ke `obsolete_date - 1 bulan`

- Delete-only approval dari status Active:
  - kondisi: ada pending delete (`is_active=0, pending=1`) dan tidak ada pending upload aktif
  - `obsolete_date` tidak berubah
  - `reminder_date` dipertahankan

### 6.2 Finalisasi file pending saat approve

Untuk file `pending_approval = 1`:
- Jika `replaced_by_id` terisi:
  - dianggap versi lama tersupersede -> archive window
  - `is_active = 0`, `pending_approval = 0`, `marked_for_deletion_at = now() + 1 year`

- Jika `is_active = 0` (delete request):
  - masuk archive window
  - `is_active = 0`, `pending_approval = 0`, `marked_for_deletion_at = now() + 1 year`

- Selain itu (file pending terbaru yang valid):
  - menjadi file aktif final
  - `is_active = 1`, `pending_approval = 0`, `marked_for_deletion_at = null`

Tambahan:
- File lama hasil koreksi saat `Need Review` yang masih hidden (`is_active=0`, `pending=0`, `marked_for_deletion_at=null`) dan terganti oleh file yang diapprove:
  - ditandai `marked_for_deletion_at = now()`
  - tidak muncul di preview

## 7. Flow Reject

Endpoint: `POST document-control/{mapping}/reject`

Validasi:
- Wajib pilih minimal 1 file (`reject_file_ids[]`)
- Wajib isi notes

### 7.1 File yang boleh dipilih di modal reject

Frontend hanya menampilkan file dengan syarat:
- `is_active = 1`
- `pending_approval = 1`
- `replaced_by_id = null`
- `marked_for_deletion_at = null`

### 7.2 Efek reject

- Hanya file yang dipilih admin yang di-set rejected:
  - `pending_approval = 2`, `is_active = 1`, `marked_for_deletion_at = null`

- File pending lain yang tidak dipilih tidak ikut menjadi rejected.

- File delete-pending (`pending_approval=1`, `is_active=0`) diarahkan ke archive window:
  - `pending_approval = 0`, `is_active = 0`, `marked_for_deletion_at = now() + 1 year`

- Status mapping -> `Rejected`

## 8. Kapan File Masuk Archive

Secara praktis, file dianggap masuk archive ketika:
- `is_active = 0`
- `pending_approval = 0`
- `marked_for_deletion_at` terisi dengan waktu archive window

### Masuk archive window (umum)

Contoh kondisi:
- versi lama tersupersede saat approve
- delete-pending yang disetujui/rejected
- replace pada status `Rejected`

Biasanya memakai:
- `marked_for_deletion_at = now() + 1 year`

### Tidak masuk archive (tetap aktif)

Contoh:
- file pending terbaru yang diapprove menjadi final
- hasil akhirnya:
- `is_active = 1`, `pending_approval = 0`, `marked_for_deletion_at = null`

### Tidak tampil preview dan tidak jadi file aktif

Contoh:
- file salah yang diganti saat status `Need Review`
- hasil interim:
- `is_active = 0`, `pending_approval = 0`, biasanya `marked_for_deletion_at = now()` setelah approve file pengganti

## 9. Rekomendasi Uji Cepat (QA)

1. Active -> upload salah -> Need Review -> replace -> Approve
- Pastikan file terbaru aktif (`is_active=1, pending=0`)
- File salah tidak muncul preview

2. Active -> delete file -> Need Review -> Approve
- Pastikan `obsolete_date` tidak bertambah
- File yang dihapus masuk archive window

3. Need Review -> Reject pilih 1 file dari 3 file pending
- Hanya file terpilih jadi `pending_approval=2`
- File pending lain tetap `pending_approval=1`

4. Uncomplete -> upload -> Need Review
- Tombol Upload/Revise tetap muncul jika masih ada file pending
