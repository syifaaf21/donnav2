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
        // Ambil semua data dengan relasi yang dibutuhkan
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // Filter department kalau bukan Admin atau Super Admin
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();
            // qualify column to avoid ambiguity in generated SQL
            $query->whereHas('department', fn($q) => $q->whereIn('tm_departments.id', $userDeptIds));
        }

        // Filter department kalau Admin atau Super Admin pilih
        if (in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin']) && $request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Search global
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

        // Ambil atau buat status "Obsolete"
        $obsoleteStatus = Status::firstOrCreate(['name' => 'Obsolete']);

        // Ambil semua dokumen aktif yang tanggal obsolete-nya sudah sampai atau lewat hari ini
        $toBeObsoleted = DocumentMapping::whereDate('obsolete_date', '<=', now()->today())
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->get();
        foreach ($toBeObsoleted as $mapping) {

            // Ambil user department terkait
            // qualify the column name to avoid ambiguity in the EXISTS subquery
            $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))->get();

            // Ambil semua admin
            $adminUsers = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->get();

            // Gabungkan keduanya dan hapus duplikat
            $notifiableUsers = $departmentUsers->merge($adminUsers)->unique('id');

            foreach ($notifiableUsers as $user) {

                // Cek dulu apakah notif untuk dokumen ini sudah dikirim hari ini
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

            // Update status menjadi Obsolete
            $mapping->update(['status_id' => $obsoleteStatus->id]);
        }

        // ✅ Ambil data untuk tampilan
        $documentsMapping = $query->with(['files'])->get();

        // kalau butuh count aktif, dipakai di Blade:
        $activeCount = $documentsMapping->map(function ($m) {
            return $m->files->where('is_active', true)->count();
        });


        // Hitung statistik
        $totalDocuments = $documentsMapping->count();
        $activeDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Active')->count();
        $obsoleteDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Obsolete')->count();

        // Dropdown filter department
        $departments = Department::all();

        // Jika admin memilih department → tampilkan hanya department tersebut
        if ($request->filled('department_id')) {
            $allDepartments = Department::where('id', $request->department_id)->get();
        } else {
            $allDepartments = Department::orderBy('name')->get();
        }

        // Gabungkan department + dokumen
        $groupedDocuments = $allDepartments->mapWithKeys(function ($dept) use ($documentsMapping) {
            $docs = $documentsMapping->where('department_id', $dept->id);
            return [$dept->name => $docs];
        });


        return view('contents.document-control.index', compact(
            'documentsMapping',
            'groupedDocuments',
            'departments',
            'totalDocuments',
            'activeDocuments',
            'obsoleteDocuments'
        ));
    }

    public function showByDepartment($department, Request $request)
    {
        // Ambil department
        $dept = Department::where('name', $department)->firstOrFail();

        // Base query
        $query = DocumentMapping::with(['document', 'user', 'status', 'files'])
            ->where('department_id', $dept->id)
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', fn($qq) => $qq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('status', fn($qq) => $qq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn($qq) => $qq->where('name', 'like', "%{$search}%"))
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Ambil hasil dengan paginate
        $mappings = $query->paginate(10)->appends($request->query());

        // Load virtual attributes untuk modal
        $mappings->each(function ($mapping) {
            $mapping->files_for_modal;
            $mapping->files_for_modal_all;
        });

        return view('contents.document-control.partials.department-details', [
            'department' => $dept,
            'mappings' => $mappings
        ]);
    }

    public function revise(Request $request, DocumentMapping $mapping)
    {
        $uploadedFiles = $request->file('revision_files', []);
        $oldFileIds = $request->input('revision_file_ids', []); // index-parsed

        if (empty($uploadedFiles)) {
            return redirect()->back()->with('info', 'No files uploaded, document unchanged.');
        }

        $request->validate([
            'revision_files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);

        $mapping->load('document');
        $folder = $mapping->document->type === 'control' ? 'document-controls' : 'document-reviews';

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $oldFileId = $oldFileIds[$index] ?? null;

            $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();
            $timestamp = now()->format('Ymd_His');

            // Calculate revision number (optional)
            $existingRevisions = $mapping->files()
                ->where('original_name', 'like', $baseName . '_rev%')
                ->count();
            $revisionNumber = $existingRevisions + 1;

            $filename = $baseName . '_rev' . $revisionNumber . '_' . $timestamp . '.' . $extension;
            $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

            // Create new file record (active)
            $newFile = $mapping->files()->create([
                'file_path' => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type' => $uploadedFile->getClientMimeType(),
                'uploaded_by' => Auth::id(),
                'is_active' => true,
            ]);

            // Mark old file as inactive and link to replacer
            if ($oldFileId) {
                $oldFile = $mapping->files()->find($oldFileId);
                if ($oldFile) {
                    $isRejected = optional($mapping->status)->name === 'Rejected';

                    if ($isRejected) {
                        // KASUS 1: DOKUMEN REJECTED
                        // Langsung hapus (now), jangan masukkan ke Archive
                        $deletionDate = now();
                    } else {
                        // KASUS 2: REVISI NORMAL (Active / Need Review)
                        // Masukkan ke Archive selama 1 tahun
                        $deletionDate = now()->addYears(1);
                    }

                    $oldFile->update([
                        'replaced_by_id' => $newFile->id,
                        'is_active' => false,
                        'marked_for_deletion_at' => $deletionDate, // Gunakan variabel ini
                    ]);
                }
            }
        }

        // Update mapping status, notify, etc (sama seperti code kamu sekarang)
        $needReviewStatus = Status::firstOrCreate(['name' => 'Need Review']);
        $mapping->update([
            'status_id' => $needReviewStatus->id,
            'user_id' => Auth::id(),
        ]);

        //Notif ke admin
        $uploader = Auth::user();
        if (!in_array(strtolower($uploader->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'Super Admin']))->get();
            foreach ($admins as $admin) {
                $admin->notify(new DocumentActionNotification(
                    'revised',
                    $uploader->name,
                    null,
                    $mapping->document->name,
                    route('document-control.department', $mapping->department->name),
                    $uploader->department?->name
                ));
            }
        }

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }

    public function approve(Request $request, DocumentMapping $mapping)
    {
        // Hanya admin atau super admin yang bisa approve
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            abort(403, 'Only admin or super admin can approve documents.');
        }

        // Ambil status "Active" atau buat kalau belum ada
        $statusActive = Status::firstOrCreate(
            ['name' => 'Active'],
            ['description' => 'Document is active']
        );

        // Ambil periode tahun dari mapping lama, default 1 tahun kalau null
        $periodYears = $mapping->period_years ?? 1;

        // Hitung tanggal obsolete baru otomatis
        $lastObsoleteDate = $mapping->obsolete_date ?? now();
        $newObsoleteDate = \Carbon\Carbon::parse($lastObsoleteDate)->addYears($periodYears);

        // Hitung reminder otomatis, misal 1 bulan sebelum obsolete
        $newReminderDate = $newObsoleteDate->copy()->subMonth();

        // Update mapping
        $mapping->update([
            'status_id' => $statusActive->id,
            'obsolete_date' => $newObsoleteDate,
            'reminder_date' => $newReminderDate,
        ]);

        // Ambil semua user di department terkait, kecuali user yang approve
        $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->where('id', '!=', Auth::id())
            ->get();

        // Kirim notifikasi ke user department
        Notification::send($departmentUsers, new DocumentActionNotification(
            'approved',                       // aksi
            Auth::user()->name,                // admin yang approve
            null,                              // bisa dipakai untuk optional info
            $mapping->document->name,          // nama document
            route('document-control.department', $mapping->department->name) // url
        ));

        return back()->with('success', 'Document approved successfully!');
    }


    public function reject(Request $request, DocumentMapping $mapping)
    {
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            abort(403, 'Only admin can reject documents.');
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $statusRejected = Status::firstOrCreate(
            ['name' => 'Rejected'],
            ['description' => 'Document has been rejected']
        );

        $mapping->update([
            'status_id' => $statusRejected->id,
            'notes' => $request->input('notes'), // <-- simpan notes
        ]);

        // Notifikasi ke semua user bahwa dokumen di-reject
        $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->where('id', '!=', Auth::id()) // kecuali user yang approve
            ->get();
        // qualify for ambiguity
        $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->where('id', '!=', Auth::id())
            ->get();
        Notification::send($departmentUsers, new DocumentActionNotification(
            'rejected',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.department', $mapping->department->name)
        ));

        return redirect()->back()->with('success', 'Document rejected successfully');
    }
}