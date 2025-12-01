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
        // Ambil base query untuk document mapping
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // Tentukan role user
        $userRole = strtolower(Auth::user()->roles->pluck('name')->first() ?? '');

        // ===== FILTER DEPARTMENT BERDASARKAN ROLE =====
        if (!in_array($userRole, ['admin', 'super admin'])) {
            // User biasa: hanya department mereka
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();
            $query->whereIn('department_id', $userDeptIds);
            $departments = Department::whereIn('id', $userDeptIds)->orderBy('name')->get();
        } else {
            // Admin / Super Admin
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
                $departments = Department::where('id', $request->department_id)->get();
            } else {
                $departments = Department::orderBy('name')->get();
            }
        }

        // ===== SEARCH GLOBAL =====
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

        // ===== CEK DAN UPDATE DOKUMEN OBSELETE =====
        $obsoleteStatus = Status::firstOrCreate(['name' => 'Obsolete']);
        $toBeObsoleted = DocumentMapping::whereDate('obsolete_date', '<=', now()->today())
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->get();

        foreach ($toBeObsoleted as $mapping) {
            $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))->get();
            $adminUsers = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->get();
            $notifiableUsers = $departmentUsers->merge($adminUsers)->unique('id');

            foreach ($notifiableUsers as $user) {
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

            $mapping->update(['status_id' => $obsoleteStatus->id]);
        }

        // Ambil data untuk tampilan
        $documentsMapping = $query->get();

        // Hitung statistik
        $totalDocuments = $documentsMapping->count();
        $activeDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Active')->count();
        $obsoleteDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Obsolete')->count();

        // Gabungkan department + dokumen untuk grouping
        $groupedDocuments = $departments->mapWithKeys(function ($dept) use ($documentsMapping) {
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

            // Hitung nomor revisi
            $existingRevisions = $mapping->files()
                ->where('original_name', 'like', $baseName . '_rev%')
                ->count();
            $revisionNumber = $existingRevisions + 1;

            $filename = $baseName . '_rev' . $revisionNumber . '_' . $timestamp . '.' . $extension;
            $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

            // Buat file baru (active)
            $newFile = $mapping->files()->create([
                'file_path' => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type' => $uploadedFile->getClientMimeType(),
                'uploaded_by' => Auth::id(),
                'is_active' => true,
            ]);

            // Handle file lama
            if ($oldFileId) {
    $oldFile = $mapping->files()->find($oldFileId);
    if ($oldFile) {
        $currentStatus = optional($mapping->status)->name;

        if ($currentStatus === 'Rejected') {
            // Dokumen direject → tandai file lama sebagai soft deleted
            $oldFile->update([
                'replaced_by_id' => $newFile->id,
                'pending_approval' => false,
                'is_active' => false, // supaya tidak muncul di modal
                'marked_for_deletion_at' => now(),
            ]);
        } else {
            // Revisi normal → file lama masuk archive setelah approve
            $oldFile->update([
                'replaced_by_id' => $newFile->id,
                'pending_approval' => true,
            ]);
        }
    }
}

        }

        // Update mapping status → Need Review
        $needReviewStatus = Status::firstOrCreate(['name' => 'Need Review']);
        $mapping->update([
            'status_id' => $needReviewStatus->id,
            'user_id' => Auth::id(),
        ]);

        // Notifikasi ke admin jika uploader bukan admin/super admin
        $uploader = Auth::user();
        $userRole = strtolower($uploader->roles->pluck('name')->first() ?? '');
        if (!in_array($userRole, ['admin', 'super admin'])) {
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

        // NONAKTIFKAN FILE LAMA SETELAH APPROVE
        $oldFiles = $mapping->files()
            ->where('pending_approval', true)
            ->get();

        foreach ($oldFiles as $oldFile) {

            $oldFile->update([
                'is_active' => false,
                'pending_approval' => false,
                'marked_for_deletion_at' => now()->addYear(), // Archive 1 tahun
            ]);
        }

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