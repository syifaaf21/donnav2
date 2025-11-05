<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentMapping;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentActionNotification;
use App\Notifications\DocumentStatusNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;


class DocumentControlController extends Controller
{

    public function index(Request $request)
    {
        // 🔹 Ambil semua data dengan relasi yang dibutuhkan
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // 🔹 Filter department kalau bukan Admin atau Super Admin
        if (!in_array(Auth::user()->role->name, ['Admin', 'Super Admin'])) {
            $query->where('department_id', Auth::user()->department_id);
        }

        // 🔹 Filter department kalau Admin atau Super Admin pilih
        if (in_array(Auth::user()->role->name, ['Admin', 'Super Admin']) && $request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 🔹 Filter department kalau ada
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 🔹 Search global
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('status', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('department', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhere('notes', 'like', "%$search%");
            });
        }

        // 🔹 Ambil atau buat status "Obsolete"
        $obsoleteStatus = Status::firstOrCreate(['name' => 'Obsolete']);

        // 🔹 Ambil semua dokumen aktif yang tanggal obsolete-nya sudah sampai atau lewat hari ini
        $toBeObsoleted = DocumentMapping::whereDate('obsolete_date', '<=', now()->today())
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->get();
        foreach ($toBeObsoleted as $mapping) {

            // 🔹 Ambil user department terkait
            $departmentUsers = User::where('department_id', $mapping->department_id)->get();

            // 🔹 Ambil semua admin
            $adminUsers = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->get();

            // 🔹 Gabungkan keduanya dan hapus duplikat
            $notifiableUsers = $departmentUsers->merge($adminUsers)->unique('id');

            foreach ($notifiableUsers as $user) {

                // 🔹 Cek dulu apakah notif untuk dokumen ini sudah dikirim hari ini
                $alreadyNotified = $user->notifications()
                    ->where('type', DocumentStatusNotification::class)
                    ->whereDate('created_at', now()->today())
                    ->whereJsonContains('data->message', $mapping->document->name)
                    ->exists();

                if (!$alreadyNotified) {
                    $user->notify(new DocumentStatusNotification(
                        $mapping->document->name,
                        'obsolete',
                        Auth::user()->name ?? 'System',
                        route('document-control.index')
                    ));
                }
            }

            // 🔹 Update status menjadi Obsolete
            $mapping->update(['status_id' => $obsoleteStatus->id]);
        }

        // ✅ Ambil data untuk tampilan
        $documentsMapping = $query->get();

        // 🔹 Hitung statistik
        $totalDocuments = $documentsMapping->count();
        $activeDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Active')->count();
        $obsoleteDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Obsolete')->count();

        // 🔹 Group by department untuk accordion
        $groupedDocuments = $documentsMapping->groupBy(fn($d) => $d->department->name ?? 'Unknown Department');

        // 🔹 Dropdown filter department
        $departments = Department::all();

        return view('contents.document-control.index', compact(
            'documentsMapping',
            'groupedDocuments',
            'departments',
            'totalDocuments',
            'activeDocuments',
            'obsoleteDocuments'
        ));
    }


    public function revise(Request $request, DocumentMapping $mapping)
    {
        $request->validate([
            'revision_files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ]);


        $mapping->load('document');
        $folder = $mapping->document->type === 'control' ? 'document-controls' : 'document-reviews';

        $uploadedFiles = $request->file('revision_files', []);
        $revisionFileIds = $request->input('revision_file_ids', []);

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $replaceId = $revisionFileIds[$index] ?? null;

            // Ambil base name dan extension
            $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();

            // Generate timestamp string, misal format: 20251031_093000
            $timestamp = now()->format('Ymd_His');

            if ($replaceId) {
                // REPLACE file lama
                $oldFile = $mapping->files()->find($replaceId);
                if ($oldFile) {
                    // Hapus file lama
                    if (Storage::disk('public')->exists($oldFile->file_path)) {
                        Storage::disk('public')->delete($oldFile->file_path);
                    }

                    // Gunakan nama revisi dengan tambahan timestamp dan _revX
                    $existingRevisions = $mapping->files()
                        ->where('original_name', 'like', $baseName . '_rev%')
                        ->count();
                    $revisionNumber = $existingRevisions + 1;

                    $filename = $baseName . '_rev' . $revisionNumber . '_' . $timestamp . '.' . $extension;

                    $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

                    // Update file lama
                    $oldFile->update([
                        'file_path' => $newPath,
                        'original_name' => $filename,
                        'file_type' => $uploadedFile->getClientMimeType(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            } else {
                // ADD file baru
                $existingRevisions = $mapping->files()
                    ->where('original_name', 'like', $baseName . '_rev%')
                    ->count();
                $revisionNumber = $existingRevisions + 1;

                $filename = $baseName . '_rev' . $revisionNumber . '_' . $timestamp . '.' . $extension;

                $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

                $mapping->files()->create([
                    'file_path' => $newPath,
                    'original_name' => $filename,
                    'file_type' => $uploadedFile->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Update status dan info revisi
        $needReviewStatus = Status::firstOrCreate(['name' => 'Need Review']);
        $mapping->update([
            'status_id' => $needReviewStatus->id,
            'user_id' => Auth::id(),
        ]);

        // Ambil role pengupload
        $uploader = Auth::user();

        // Jika pengupload bukan admin dan super admin, kirim notifikasi ke admin
        if (!in_array($uploader->role->name, ['Admin', 'Super Admin'])) {
            $admins = User::whereHas('role', fn($q) => $q->whereIn('name', ['Admin', 'Super Admin']))->get();

            foreach ($admins as $admin) {
                $admin->notify(new DocumentActionNotification(
                    'revised',                   // action
                    $uploader->name,             // siapa yang upload
                    null,                        // documentNumber, untuk review bisa null
                    $mapping->document->name,    // documentName, untuk document control
                    route('document-control.index') // url
                ));
            }
        }

        return redirect()->back()->with('success', 'Document Uploaded successfully!');
    }

    public function approve(Request $request, DocumentMapping $mapping)
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'Super Admin'])) {
            abort(403, 'Only admin or super admin can approve documents.');
        }


        $request->validate([
            'obsolete_date' => 'required|date',
            'reminder_date' => 'required|date|before_or_equal:obsolete_date',
        ], [
            'reminder_date.before_or_equal' => 'Reminder Date must be earlier than or equal to Obsolete Date.',
        ]);

        $statusActive = Status::firstOrCreate(
            ['name' => 'Active'],
            ['description' => 'Document is active']
        );

        $mapping->update([
            'status_id' => $statusActive->id,
            'user_id' => Auth::id(),
            'obsolete_date' => $request->obsolete_date,
            'reminder_date' => $request->reminder_date,
        ]);

        // Ambil semua user di department terkait, kecuali user yang melakukan approve
        $departmentUsers = User::where('department_id', $mapping->department_id)
            ->whereNotIn('id', [Auth::id()]) // kecuali user yang approve
            ->get();

        // Kirim notifikasi
        Notification::send($departmentUsers, new DocumentActionNotification(
            'approved',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.index') // url
        ));


        return back()->with('success', 'Document approved successfully.');
    }

    public function reject(Request $request, DocumentMapping $mapping)
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'Super Admin'])) {
            abort(403, 'Only admin can reject documents.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $statusRejected = Status::firstOrCreate(
            ['name' => 'Rejected'],
            ['description' => 'Document has been rejected']
        );

        $mapping->update([
            'status_id' => $statusRejected->id,
            'user_id' => Auth::id(),
            'notes' => $request->input('notes'), // <-- simpan notes
        ]);

        // Notifikasi ke semua user bahwa dokumen di-reject
        $departmentUsers = User::where('department_id', $mapping->department_id)
            ->whereNotIn('id', [Auth::id()]) // kecuali user yang approve
            ->get();
        Notification::send($departmentUsers, new DocumentActionNotification(
            'rejected',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.index')
        ));

        return redirect()->back()->with('success', 'Document rejected successfully');
    }
}
