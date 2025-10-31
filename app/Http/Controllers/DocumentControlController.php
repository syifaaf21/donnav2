<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentMapping;
use App\Models\Status;
use App\Models\User;
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

        // 🔹 Filter department kalau ada
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 🔹 Update status otomatis jadi "Obsolete" kalau sudah lewat tanggal
        $obsoleteStatus = Status::firstOrCreate(['name' => 'Obsolete']);

        DocumentMapping::whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->whereDate('obsolete_date', '<', now()->today())
            ->update([
                'status_id' => $obsoleteStatus->id,
            ]);

        // ✅ Ambil data (JANGAN tambahkan with() lagi karena akan menimpa eager load di atas)
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
            'revision_files.*' => 'required|file|mimes:pdf,doc,docx|max:20480',
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

        return redirect()->back()->with('success', 'Document revised successfully!');
    }

    public function approve(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403, 'Only admin can approve documents.');
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

        // Kirim notifikasi
        $allUsers = User::all();
        Notification::send($allUsers, new DocumentStatusNotification(
            $mapping->document->name,
            'approved',
            Auth::user()->name
        ));

        return back()->with('success', 'Document approved successfully.');
    }

    public function reject(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
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
        $allUsers = User::all();
        Notification::send($allUsers, new DocumentStatusNotification(
            $mapping->document->name,
            'rejected',
            Auth::user()->name
        ));

        return response()->json(['status' => 'success']);
    }
}
