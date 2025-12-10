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
        $uploadedFiles   = $request->file('revision_files', []);
        $oldFileIds      = $request->input('revision_file_ids', []);
        $deletedFileIds  = $request->input('deleted_file_ids', []);

        if (empty($uploadedFiles) && empty($deletedFileIds)) {
            return redirect()->back()->with('info', 'No changes made to document.');
        }

        $request->validate([
            'revision_files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);

        $mapping->load('document');
        $folder         = $mapping->document->type === 'control' ? 'document-controls' : 'document-reviews';
        $currentStatus  = optional($mapping->status)->name;

        // ========== DELETE ==========
        if (!empty($deletedFileIds)) {
            foreach ($deletedFileIds as $fileId) {
                $fileToDelete = $mapping->files()->find($fileId);
                if (!$fileToDelete) continue;

                // Jika file rejected (pending_approval = 2): hard delete
                if ($fileToDelete->pending_approval == 2) {
                    Storage::disk('public')->delete($fileToDelete->file_path);
                    $fileToDelete->delete();
                    continue;
                }

                if ($currentStatus === 'Rejected') {
                    Storage::disk('public')->delete($fileToDelete->file_path);
                    $fileToDelete->delete();
                } elseif ($currentStatus === 'Active') {
                    $fileToDelete->update([
                        'pending_approval'       => 1,
                        'is_active'              => 0,
                        'replaced_by_id'         => null,
                        'marked_for_deletion_at' => null,
                    ]);
                } else {
                    $fileToDelete->update([
                        'is_active'              => 0,
                        'marked_for_deletion_at' => now(),
                    ]);
                }
            }
        }

        // ========== REPLACE / UPLOAD ==========
        foreach ($uploadedFiles as $index => $uploadedFile) {
            if (!$uploadedFile) continue;
            $oldFileId = $oldFileIds[$index] ?? null;

            $baseName   = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension  = $uploadedFile->getClientOriginalExtension();
            $timestamp  = now()->format('Ymd_His');

            $existingRevisions = $mapping->files()
                ->where('original_name', 'like', $baseName.'%')
                ->count();
            $revisionNumber = $existingRevisions + 1;

            $filename = $baseName.'_rev'.$revisionNumber.'_'.$timestamp.'.'.$extension;
            $newPath  = $uploadedFile->storeAs($folder, $filename, 'public');

            $newFile = $mapping->files()->create([
                'file_path'     => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type'     => $uploadedFile->getClientMimeType(),
                'uploaded_by'   => Auth::id(),
                'is_active'     => 1,
            ]);

            if ($oldFileId) {
                $oldFile = $mapping->files()->find($oldFileId);
                if (!$oldFile) continue;

                // Jika file lama rejected (pending_approval = 2): hard delete
                if ($oldFile->pending_approval == 2) {
                    Storage::disk('public')->delete($oldFile->file_path);
                    $oldFile->delete();
                    continue;
                }

                // Cari file yang diganti oleh oldFile (file asli sebelum oldFile)
                $originalFile = $mapping->files()
                    ->where('replaced_by_id', $oldFileId)
                    ->first();

                // Jika oldFile diganti, update originalFile agar menunjuk ke newFile
                if ($originalFile) {
                    $originalFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 1,
                        'is_active'              => 1,
                        'marked_for_deletion_at' => null,
                    ]);
                }

                if ($currentStatus === 'Rejected') {
                    Storage::disk('public')->delete($oldFile->file_path);
                    $oldFile->delete();
                } elseif ($currentStatus === 'Active') {
                    $oldFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 1,
                        'is_active'              => 1,
                        'marked_for_deletion_at' => null,
                    ]);
                } else {
                    $oldFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 1,
                        'marked_for_deletion_at' => null,
                    ]);
                }
            }
        }

        // ========== SET STATUS NEED REVIEW ==========
        $needReviewStatus = Status::firstOrCreate(['name' => 'Need Review']);
        $mapping->update([
            'status_id' => $needReviewStatus->id,
            'user_id'   => Auth::id(),
        ]);

        // Notifikasi ke admin
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

        return redirect()->back()->with('success', 'Document revised successfully!');
    }

    public function approve(Request $request, DocumentMapping $mapping)
    {
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            abort(403, 'Only admin or super admin can approve documents.');
        }

        $statusActive = Status::firstOrCreate(
            ['name' => 'Active'],
            ['description' => 'Document is active']
        );

        $periodYears    = $mapping->period_years ?? 1;
        $lastObsolete   = $mapping->obsolete_date ?? now();
        $newObsolete    = \Carbon\Carbon::parse($lastObsolete)->addYears($periodYears);
        $newReminder    = $newObsolete->copy()->subMonth();

        $mapping->update([
            'status_id'     => $statusActive->id,
            'obsolete_date' => $newObsolete,
            'reminder_date' => $newReminder,
        ]);

        // ===== ARCHIVE FILE YANG PENDING (pending_approval = 1) =====
        $pendingFiles = $mapping->files()
            ->where('pending_approval', 1)
            ->get();

        foreach ($pendingFiles as $pendingFile) {
            $pendingFile->update([
                'pending_approval'       => 0,
                'is_active'              => 0,
                'marked_for_deletion_at' => now()->addYear(),
            ]);
        }

        // ===== HAPUS FILE REJECTED (pending_approval = 2) =====
        $rejectedFiles = $mapping->files()
            ->where('pending_approval', 2)
            ->get();

        foreach ($rejectedFiles as $rejectedFile) {
            Storage::disk('public')->delete($rejectedFile->file_path);
            $rejectedFile->delete();
        }

        // File lain yang sudah di-mark sebelumnya
        $markedFilesToArchive = $mapping->files()
            ->where('is_active', 0)
            ->whereNotNull('marked_for_deletion_at')
            ->whereNull('pending_approval')
            ->where('marked_for_deletion_at', '<=', now())
            ->get();

        foreach ($markedFilesToArchive as $file) {
            $file->update([
                'marked_for_deletion_at' => now()->addYear(),
            ]);
        }

        $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->where('id', '!=', Auth::id())
            ->get();

        Notification::send($departmentUsers, new DocumentActionNotification(
            'approved',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.department', $mapping->department->name)
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

        // Hard delete file yang di-mark (non-pending)
        $markedFiles = $mapping->files()
            ->where('is_active', 0)
            ->whereNotNull('marked_for_deletion_at')
            ->whereNull('pending_approval')
            ->whereDate('marked_for_deletion_at', '<=', now()->today())
            ->get();

        foreach ($markedFiles as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }

        // ===== UBAH SEMUA FILE PENDING MENJADI REJECTED (pending_approval = 2) =====
        // File baru yang di-pending (baik yang replace maupun yang baru ditambah)
        $pendingFiles = $mapping->files()
            ->where('pending_approval', 1)
            ->get();

        foreach ($pendingFiles as $file) {
            $file->update([
                'pending_approval'       => 2,  // Mark as rejected
                'is_active'              => 1,  // Tetap tampil
                'marked_for_deletion_at' => null,
            ]);
        }

        // ===== PULIHKAN FILE LAMA YANG DIGANTI =====
        // File lama (yang punya replaced_by_id) → tetap dengan replaced_by_id dan pending_approval = 1
        // Agar badge "Replaced" muncul dan nanti masuk archive saat approve
        $replacedFiles = $mapping->files()
            ->whereIn('replaced_by_id', $pendingFiles->pluck('id'))
            ->get();

        foreach ($replacedFiles as $file) {
            // JANGAN ubah replaced_by_id agar badge "Replaced" tetap muncul
            // JANGAN ubah pending_approval agar tetap 1 (akan masuk archive saat approve)
            $file->update([
                'is_active' => 1, // Pastikan tetap tampil
            ]);
        }

        // ===== HAPUS FILE YANG DIDELETE SAAT REVISE =====
        // File yang pending_approval = 1 dengan is_active = 0 (artinya didelete, bukan direplace)
        $deletedPendingFiles = $mapping->files()
            ->where('pending_approval', 1)
            ->where('is_active', 0)
            ->get();

        foreach ($deletedPendingFiles as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }

        $mapping->update([
            'status_id' => $statusRejected->id,
            'notes'     => $request->input('notes'),
        ]);

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
