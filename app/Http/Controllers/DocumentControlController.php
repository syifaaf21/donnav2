<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentMapping;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentActionNotification;
use App\Notifications\DocumentStatusNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;


class DocumentControlController extends Controller
{

    public function index(Request $request)
    {
        // Ambil base query untuk document mapping
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereNull('marked_for_deletion_at')
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

            $departmentName = $mapping->department?->name ?? 'Unknown';
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
                        route('document-control.department', $departmentName)
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

        // Tambahkan dokumen yang department_id-nya null ke grouping 'Unknown'
        $unknownDocs = $documentsMapping->whereNull('department_id');
        if ($unknownDocs->count() > 0) {
            $groupedDocuments = $groupedDocuments->merge(['Unknown' => $unknownDocs]);
        }

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
        if ($department === 'Unknown') {
            // Ambil dokumen dengan department_id null
            $query = DocumentMapping::with(['document', 'user', 'status', 'files'])
                ->whereNull('department_id')
                ->whereNull('marked_for_deletion_at')
                ->whereHas('document', fn($q) => $q->where('type', 'control'));
            $dept = null;
        } else {
            // Ambil department normal, jika tidak ada redirect ke index
            $dept = Department::where('name', $department)->first();
            if (!$dept) {
                // Jika department sudah dihapus, redirect ke index
                return redirect()->route('document-control.index');
            } else {
                $query = DocumentMapping::with(['document', 'user', 'status', 'files'])
                    ->where('department_id', $dept->id)
                    ->whereNull('marked_for_deletion_at')
                    ->whereHas('document', fn($q) => $q->where('type', 'control'));
            }
        }

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

        // Statuses untuk filter
        $statuses = [
            'active' => 'Active',
            'need_review' => 'Need Review',
            'rejected' => 'Rejected',
            'obsolete' => 'Obsolete',
            'uncomplete' => 'Uncomplete',
        ];

        // Status yang dipilih
        $selectedStatuses = $request->input('status', []);

        // Filter query jika status dipilih dan bukan 'all'
        if (!empty($selectedStatuses) && !in_array('all', $selectedStatuses)) {
            $selectedLabels = array_map(fn($k) => $statuses[$k] ?? null, $selectedStatuses);
            $selectedLabels = array_filter($selectedLabels); // buang null
            if (!empty($selectedLabels)) {
                $query->whereHas('status', function ($q) use ($selectedLabels) {
                    $q->whereIn('name', $selectedLabels);
                });
            }
        }

        // Ambil hasil dengan paginate
        $mappings = $query->paginate(10)->appends($request->query());

        // Load virtual attributes untuk modal
        $mappings->each(function ($mapping) {
            $mapping->files_for_modal;
            $mapping->files_for_modal_all;
        });

        // Counting per status
        $statusCounts = [];
        foreach ($statuses as $key => $label) {
            $statusCounts[$key] = DocumentMapping::where(function ($q) use ($dept, $department) {
                if ($department === 'Unknown') {
                    $q->whereNull('department_id');
                } else if ($dept) {
                    $q->where('department_id', $dept->id);
                }
            })
                ->whereHas('status', fn($q) => $q->where('name', $label))
                ->whereNull('marked_for_deletion_at')
                ->whereHas('document', fn($q) => $q->where('type', 'control'))
                ->count();
        }

        return view('contents.document-control.partials.department-details', [
            'department' => $dept,
            'mappings' => $mappings,
            'statuses' => $statuses,
            'statusCounts' => $statusCounts,
            'selectedStatuses' => $selectedStatuses,
        ]);
    }

    public function approvalIndex(Request $request)
    {
        if (!in_array(
            strtolower(Auth::user()->roles->pluck('name')->first() ?? ''),
            ['admin', 'super admin']
        )) {
            abort(403);
        }

        $query = DocumentMapping::with(['document', 'department', 'user', 'status', 'files'])
            ->whereNull('marked_for_deletion_at')
            ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // Search global (document, department, user)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $mappings = $query->paginate(10)->appends($request->query());
        $departments = Department::orderBy('name')->get();

        $mappings->each(function ($mapping) {
            $mapping->files_for_modal;
            $mapping->files_for_modal_all;
        });

        return view('contents.document-control.approval.index', [
            'mappings' => $mappings,
            'departments' => $departments,
            'approvalMode' => true,
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

        // ================= TOTAL SIZE LIMIT =================
        $maxTotalSize = 20 * 1024 * 1024; // 20 MB
        $totalSize = 0;

        foreach ($uploadedFiles as $file) {
            if ($file) {
                $totalSize += $file->getSize();
            }
        }

        if ($totalSize > $maxTotalSize) {
            return back()->withErrors([
                'revision_files' => 'Total ukuran semua file tidak boleh lebih dari 10 MB.'
            ])->withInput();
        }

        $request->validate([
            'revision_files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480', // 20MB per file
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
                        'marked_for_deletion_at' => now()->addYear(),
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
                ->where('original_name', 'like', $baseName . '%')
                ->count();
            $revisionNumber = $existingRevisions + 1;

            $filename = $baseName . '_rev' . $revisionNumber . '_' . $timestamp . '.' . $extension;
            $newPath  = $uploadedFile->storeAs($folder, $filename, 'public');

            $newFile = $mapping->files()->create([
                'file_path'     => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type'     => $uploadedFile->getClientMimeType(),
                'uploaded_by'   => Auth::id(),
                'is_active'     => 1,
                'pending_approval' => 1,
            ]);

            // ==================== AUTO-COMPRESS ====================
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if ($extension === 'pdf') {
                $this->compressPdf($newPath);
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $this->compressImage($newPath);
            }

            if ($oldFileId) {
                $oldFile = $mapping->files()->find($oldFileId);
                if (!$oldFile) continue;
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

                // Jika file lama rejected (pending_approval = 2): archive the rejected file immediately
                if ($oldFile->pending_approval == 2) {
                    // Previously rejected file being replaced: archive it immediately
                    $oldFile->update([
                        'replaced_by_id' => $newFile->id,
                        'pending_approval' => 0,
                        'is_active' => 0,
                        'marked_for_deletion_at' => now(),
                    ]);
                    continue;
                }

                if ($currentStatus === 'Need Review') {
                    // Correction while still in review: do not archive replaced file.
                    // Keep it hidden from preview and outside archive queries.
                    $oldFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 0,
                        'is_active'              => 0,
                        'marked_for_deletion_at' => null,
                    ]);
                    continue;
                }

                if ($currentStatus === 'Rejected') {
                    // When mapping is Rejected, replace old file by archiving it (1 year)
                    $oldFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 0,
                        'is_active'              => 0,
                        'marked_for_deletion_at' => now()->addYear(),
                    ]);
                } else {
                    // For Active and other statuses: keep old file visible but mark pending
                    // so it will be archived when the replacement is approved
                    $oldFile->update([
                        'replaced_by_id'         => $newFile->id,
                        'pending_approval'       => 1,
                        'is_active'              => 1,
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
            $departmentName = $mapping->department?->name ?? 'Unknown';
            foreach ($admins as $admin) {
                $admin->notify(new DocumentActionNotification(
                    'revised',
                    $uploader->name,
                    null,
                    $mapping->document->name,
                    route('document-control.department', $departmentName),
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

        // Snapshot pending files before they are finalized, to determine approval scenario.
        $pendingFiles = $mapping->files()
            ->where('pending_approval', 1)
            ->get();

        $hasPendingUploadedFile = $pendingFiles->contains(function ($f) {
            return (int) $f->is_active === 1 && empty($f->replaced_by_id);
        });

        $hasPendingDeletedFile = $pendingFiles->contains(function ($f) {
            return (int) $f->is_active === 0 && empty($f->replaced_by_id);
        });

        $isDeleteOnlyApproval = $hasPendingDeletedFile && !$hasPendingUploadedFile;

        $newObsolete = $isDeleteOnlyApproval
            ? \Carbon\Carbon::parse($lastObsolete)
            : \Carbon\Carbon::parse($lastObsolete)->addYears($periodYears);

        $newReminder = $isDeleteOnlyApproval
            ? ($mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date) : $newObsolete->copy()->subMonth())
            : $newObsolete->copy()->subMonth();

        // Restore notes from initial_notes when approving
        $notesToRestore = $mapping->initial_notes ?? $mapping->notes;
        
        $mapping->update([
            'status_id'     => $statusActive->id,
            'obsolete_date' => $newObsolete,
            'reminder_date' => $newReminder,
            'notes'         => $notesToRestore,  // Restore from initial_notes
        ]);

        // ===== FINALIZE PENDING FILES =====
        // Rule:
        // - pending file WITH replaced_by_id => superseded old version, move to archive window
        // - pending file WITHOUT replaced_by_id => approved latest version, keep active
        $approvedFileIds = [];

        foreach ($pendingFiles as $pendingFile) {
            if (!empty($pendingFile->replaced_by_id)) {
                $pendingFile->update([
                    'pending_approval'       => 0,
                    'is_active'              => 0,
                    'marked_for_deletion_at' => now()->addYear(),
                ]);
                continue;
            }

            // Active-file deletion request from Active status.
            // Keep hidden and archive it instead of re-activating.
            if ((int) $pendingFile->is_active === 0) {
                $pendingFile->update([
                    'pending_approval'       => 0,
                    'is_active'              => 0,
                    'marked_for_deletion_at' => now()->addYear(),
                ]);
                continue;
            }

            $pendingFile->update([
                'pending_approval'       => 0,
                'is_active'              => 1,
                'marked_for_deletion_at' => null,
            ]);

            $approvedFileIds[] = $pendingFile->id;
        }

        // If there are hidden files replaced during Need Review correction,
        // mark them for immediate archive timestamp (not archive window).
        if (!empty($approvedFileIds)) {
            $mapping->files()
                ->whereIn('replaced_by_id', $approvedFileIds)
                ->where('is_active', 0)
                ->where('pending_approval', 0)
                ->whereNull('marked_for_deletion_at')
                ->update([
                    'marked_for_deletion_at' => now(),
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
            ->where('pending_approval', 0)
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

        $departmentName = $mapping->department?->name ?? 'Unknown';
        Notification::send($departmentUsers, new DocumentActionNotification(
            'approved',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.department', $departmentName)
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
            'reject_file_ids' => 'required|array|min:1',
            'reject_file_ids.*' => 'integer|exists:tt_document_files,id',
        ], [
            'reject_file_ids.required' => 'Please select at least one file to reject.',
            'reject_file_ids.min' => 'Please select at least one file to reject.',
        ]);

        $statusRejected = Status::firstOrCreate(
            ['name' => 'Rejected'],
            ['description' => 'Document has been rejected']
        );

        // If admin selected specific files to reject, mark them as rejected (pending_approval = 2)
        $selectedIds = $request->input('reject_file_ids', []);
        if (!empty($selectedIds)) {
            foreach ($selectedIds as $fileId) {
                $file = $mapping->files()->where('id', $fileId)->first();
                if (!$file) continue;
                // Mark as rejected: keep visible (is_active = 1), set pending_approval = 2
                $file->update([
                    'pending_approval' => 2,
                    'is_active' => 1,
                    'marked_for_deletion_at' => null,
                ]);
            }
        }

        // Ensure originals (files replaced by the rejected ones) remain shown as
        // 'Replaced' (pending) so users can still see and act on them.
        if (!empty($selectedIds)) {
            $originals = $mapping->files()
                ->whereIn('replaced_by_id', $selectedIds)
                ->get();

            foreach ($originals as $orig) {
                $orig->update([
                    'pending_approval' => 1,
                    'is_active' => 1,
                    'marked_for_deletion_at' => null,
                ]);
            }
        }

        // Hard delete file yang di-mark (non-pending)
        $markedFiles = $mapping->files()
            ->where('is_active', 0)
            ->whereNotNull('marked_for_deletion_at')
            ->where('pending_approval', 0)
            ->whereDate('marked_for_deletion_at', '<=', now()->today())
            ->get();

        foreach ($markedFiles as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }

        // ===== ARSIPKAN FILE YANG DIHAPUS SAAT STATUS SEBELUMNYA ACTIVE =====
        // File dengan pending_approval = 1 dan is_active = 0 berarti user menghapusnya di modal revise.
        // Saat direject, file ini tidak boleh muncul lagi dan langsung diarahkan ke archive (is_active tetap 0).
        $deletedPendingFiles = $mapping->files()
            ->where('pending_approval', 1)
            ->where('is_active', 0)
            ->get();

        foreach ($deletedPendingFiles as $file) {
            $file->update([
                'pending_approval'       => 0,      // selesaikan proses pending
                'is_active'              => 0,      // tetap nonaktif → tidak muncul lagi
                'marked_for_deletion_at' => now()->addYear(),  // langsung masuk archive window
            ]);
        }

        // ===== UBAH HANYA FILE TERPILIH MENJADI REJECTED (pending_approval = 2) =====
        // Jangan reject semua file pending; hanya yang dipilih admin di modal reject.
        $pendingFiles = $mapping->files()
            ->whereIn('id', $selectedIds)
            ->where('pending_approval', 1)
            ->where('is_active', 1)
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

        // ===== FILE YANG DIHAPUS SUDAH DIARSIPKAN DI BLOK ATAS =====
        // Tidak perlu dihapus fisik di tahap reject; cukup diarsipkan supaya tidak muncul lagi.

        $mapping->update([
            'status_id' => $statusRejected->id,
            'notes'     => $request->input('notes'),
        ]);

        $departmentUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->where('id', '!=', Auth::id())
            ->get();

        $departmentName = $mapping->department?->name ?? 'Unknown';
        Notification::send($departmentUsers, new DocumentActionNotification(
            'rejected',
            Auth::user()->name,
            null,
            $mapping->document->name,
            route('document-control.department', $departmentName)
        ));

        return redirect()->back()->with('success', 'Document rejected successfully');
    }

    // COMPRESS PDF
    private function compressPdf($filePath)
    {
        $fullPath = storage_path("app/public/" . $filePath);
        $tempPath = $fullPath . "_compressed.pdf";
        $gsTempDir = storage_path('app/gs-temp');
        $originalSize = is_file($fullPath) ? filesize($fullPath) : 0;
        $profile = trim((string) env('PDF_GS_PROFILE', '/ebook'));
        $dpi = (int) env('PDF_GS_IMAGE_DPI', 150);

        if ($profile === '' || $profile[0] !== '/') {
            $profile = '/ebook';
        }

        if ($dpi < 72 || $dpi > 300) {
            $dpi = 150;
        }

        if (!is_file($fullPath)) {
            return false;
        }

        if (!is_dir($gsTempDir) && !@mkdir($gsTempDir, 0775, true) && !is_dir($gsTempDir)) {
            Log::warning('PDF compression skipped: unable to create Ghostscript temp directory.', [
                'file_path' => $filePath,
                'temp_dir' => $gsTempDir,
            ]);
            return false;
        }

        $gs = $this->findGhostscriptBinary();
        if (!$gs) {
            Log::warning('PDF compression skipped: Ghostscript binary not found.', [
                'file_path' => $filePath,
            ]);
            return false;
        }

        $firstPass = $this->runGhostscriptCompressionPass(
            $gs,
            $fullPath,
            $tempPath,
            $gsTempDir,
            $profile,
            $dpi,
            false,
            0
        );

        if (!$firstPass['success']) {
            Log::warning('PDF compression process failed.', [
                'file_path' => $filePath,
                'binary' => $gs,
                'error_output' => $firstPass['error_output'] ?? '',
                'output' => $firstPass['output'] ?? '',
                'message' => $firstPass['message'] ?? null,
            ]);
            return false;
        }

        // Jika compress sukses → ganti file asli
        if (file_exists($tempPath)) {
            $compressedSize = filesize($tempPath);

            if ($compressedSize !== false && $originalSize > 0 && $compressedSize >= $originalSize) {
                @unlink($tempPath);

                // Second pass: more aggressive settings for scanned/image-heavy PDFs.
                $fallbackDpi = min($dpi, 110);
                $fallbackQuality = (int) env('PDF_GS_JPEG_QUALITY', 55);
                if ($fallbackQuality < 20 || $fallbackQuality > 95) {
                    $fallbackQuality = 55;
                }

                $tempPathFallback = $fullPath . "_compressed_fallback.pdf";
                $secondPass = $this->runGhostscriptCompressionPass(
                    $gs,
                    $fullPath,
                    $tempPathFallback,
                    $gsTempDir,
                    '/screen',
                    $fallbackDpi,
                    true,
                    $fallbackQuality
                );

                if ($secondPass['success'] && file_exists($tempPathFallback)) {
                    $fallbackSize = filesize($tempPathFallback);

                    if ($fallbackSize !== false && $fallbackSize > 0 && $fallbackSize < $originalSize) {
                        @rename($tempPathFallback, $fullPath);

                        $finalSize = is_file($fullPath) ? filesize($fullPath) : null;
                        Log::info('PDF compression success (fallback pass).', [
                            'file_path' => $filePath,
                            'binary' => $gs,
                            'profile' => '/screen',
                            'dpi' => $fallbackDpi,
                            'jpeg_quality' => $fallbackQuality,
                            'original_size' => $originalSize,
                            'final_size' => $finalSize,
                        ]);

                        return true;
                    }

                    @unlink($tempPathFallback);
                }

                Log::info('PDF compression skipped replacement: output is not smaller.', [
                    'file_path' => $filePath,
                    'binary' => $gs,
                    'profile' => $profile,
                    'dpi' => $dpi,
                    'original_size' => $originalSize,
                    'compressed_size' => $compressedSize,
                    'fallback_attempted' => true,
                    'fallback_success' => $secondPass['success'] ?? false,
                ]);
                return false;
            }

            @rename($tempPath, $fullPath);

            $finalSize = is_file($fullPath) ? filesize($fullPath) : null;
            Log::info('PDF compression success.', [
                'file_path' => $filePath,
                'binary' => $gs,
                'profile' => $profile,
                'dpi' => $dpi,
                'original_size' => $originalSize,
                'final_size' => $finalSize,
            ]);

            return true;
        }

        Log::warning('PDF compression finished without temp file output.', [
            'file_path' => $filePath,
            'binary' => $gs,
        ]);

        return false;
    }

    private function runGhostscriptCompressionPass(
        string $gs,
        string $inputPath,
        string $outputPath,
        string $tempDir,
        string $profile,
        int $dpi,
        bool $forceJpeg,
        int $jpegQuality
    ): array {
        $args = [
            $gs,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dSAFER',
            '-dPDFSETTINGS=' . $profile,
            '-dDetectDuplicateImages=true',
            '-dCompressFonts=true',
            '-dSubsetFonts=true',
            '-dDownsampleColorImages=true',
            '-dDownsampleGrayImages=true',
            '-dDownsampleMonoImages=true',
            '-dColorImageResolution=' . $dpi,
            '-dGrayImageResolution=' . $dpi,
            '-dMonoImageResolution=' . $dpi,
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sTMPDIR=' . $tempDir,
        ];

        if ($forceJpeg) {
            $args[] = '-dAutoFilterColorImages=false';
            $args[] = '-dAutoFilterGrayImages=false';
            $args[] = '-dColorImageFilter=/DCTEncode';
            $args[] = '-dGrayImageFilter=/DCTEncode';
            $args[] = '-dJPEGQ=' . $jpegQuality;
        }

        $args[] = '-sOutputFile=' . $outputPath;
        $args[] = $inputPath;

        $process = new Process($args);
        $process->setTimeout(45);
        $process->setIdleTimeout(45);
        $process->setEnv([
            'TMP' => $tempDir,
            'TEMP' => $tempDir,
            'TMPDIR' => $tempDir,
        ]);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_output' => '',
                'output' => '',
            ];
        } catch (\Throwable $e) {
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_output' => '',
                'output' => '',
            ];
        }

        if (!$process->isSuccessful()) {
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return [
                'success' => false,
                'message' => null,
                'error_output' => trim($process->getErrorOutput()),
                'output' => trim($process->getOutput()),
            ];
        }

        return [
            'success' => true,
            'message' => null,
            'error_output' => '',
            'output' => '',
        ];
    }

    private function findGhostscriptBinary(): ?string
    {
        $configuredBinary = trim((string) env('GHOSTSCRIPT_BINARY', ''));
        if ($configuredBinary !== '' && is_file($configuredBinary)) {
            return $configuredBinary;
        }

        $commands = PHP_OS_FAMILY === 'Windows'
            ? [
                ['where', 'gswin64c'],
                ['where', 'gswin32c'],
                ['where', 'gs'],
            ]
            : [
                ['which', 'gs'],
            ];

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->setTimeout(3);

            try {
                $process->run();
            } catch (ProcessTimedOutException $e) {
                continue;
            }

            if (!$process->isSuccessful()) {
                continue;
            }

            $firstLine = trim((string) strtok($process->getOutput(), PHP_EOL));
            if ($firstLine !== '' && is_file($firstLine)) {
                return $firstLine;
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = array_merge(
                glob('C:\\Program Files\\gs\\gs*\\bin\\gswin64c.exe') ?: [],
                glob('C:\\Program Files\\gs\\gs*\\bin\\gswin32c.exe') ?: [],
                glob('C:\\Program Files (x86)\\gs\\gs*\\bin\\gswin32c.exe') ?: []
            );

            rsort($candidates);
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }


    // COMPRESS IMAGE
    private function compressImage($filePath)
    {
        $fullPath = storage_path("app/public/" . $filePath);

        // Buat manager baru
        $manager = new ImageManager(new Driver());

        // Proses image
        $image = $manager->read($fullPath);

        // Resize
        $image->scaleDown(1920);

        // Simpan kualitas 70%
        $image->save($fullPath, quality: 70);

        return true;
    }
}
