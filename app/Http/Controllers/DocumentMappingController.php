<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentMappingController extends Controller
{
    // ================= Document Review Index =================
    public function reviewIndex(Request $request)
    {
        $documentsMaster = Document::with('childrenRecursive')->where('type', 'review')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();
        $departments = Department::all();

        $plants = PartNumber::pluck('plant')->map(fn($p) => ucfirst(strtolower($p)))->unique();

        $groupedByPlant = [];

        foreach ($plants as $plant) {
            $query = DocumentMapping::with([
                'document.parent', // ambil parent dokumen
                'document.children', // ambil child dari document
                'department',
                'partNumber',
                'status',
                'user',
                'files',
            ])
                ->whereHas('document', function ($q) {
                    $q->where('type', 'review');
                })
                ->whereHas('partNumber', function ($q) use ($plant) {
                    $q->whereRaw('LOWER(plant) = ?', [strtolower($plant)]);
                });


            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('document', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhereHas('partNumber', fn($q3) => $q3->where('part_number', 'like', "%{$search}%"));
                });
            }

            // Filter by Status
            if ($status = request('status')) {
                $query->whereHas(
                    'status',
                    fn($q) =>
                    $q->whereRaw('LOWER(name) = ?', [strtolower($status)])
                );
            }


            // Filter by Department
            if ($department = request('department')) {
                $query->where('department_id', $department);
            }

            // Filter by Deadline (tanggal exact)
            if ($deadline = request('deadline')) {
                $query->whereDate('deadline', $deadline);
            }

            $groupedByPlant[$plant] = $query->orderBy('created_at', 'asc')->get();
            // pakai page_plant supaya paginator tiap tab independen
        }

        return view('contents.document-review.index', compact(
            'groupedByPlant',
            'documentsMaster',
            'partNumbers',
            'statuses',
            'departments'
        ));
    }

    // ================= Store Review (Admin) =================
    public function storeReview(Request $request)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'document_number' => 'required|string|max:255',
            'part_number_id' => 'required|exists:part_numbers,id',
            'files' => 'required',
            'files.*' => 'file|mimes:pdf,docx',
            'department_id' => 'required|exists:departments,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Simpan DocumentMapping dulu
        $mapping = DocumentMapping::create([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'department_id' => $request->department_id,
            'reminder_date' => null,
            'deadline' => null,
            'obsolete_date' => null,
            'status_id' => Status::where('name', 'Need Review')->first()->id,
            'notes' => $request->notes ?? '',
            'user_id' => Auth::id(),
            'version' => 0,
        ]);

        // Upload file dan simpan ke DocumentFile
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploadedFile) {
                $extension = $uploadedFile->getClientOriginalExtension();
                $filename = $request->document_number . '_v' . $mapping->version . '_' . time() . '.' . $extension;

                $path = $uploadedFile->storeAs(
                    'document-reviews',
                    $filename,
                    'public'
                );

                DocumentFile::create([
                    'document_mapping_id' => $mapping->id,
                    'file_path' => $path,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Document review created!');
    }

    // ================= Update Review (Admin) =================
    public function updateReview(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin')
            abort(403);

        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'document_number' => 'required|string|max:255',
            'part_number_id' => 'required|exists:part_numbers,id',
            'department_id' => 'required|exists:departments,id',
            'reminder_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        $mapping->timestamps = false;
        $mapping->update([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'department_id' => $request->department_id,
            'reminder_date' => optional($mapping->status)->name === 'approved'
                ? $request->reminder_date
                : $mapping->reminder_date,
            'deadline' => optional($mapping->status)->name === 'approved'
                ? $request->deadline
                : $mapping->deadline,
        ]);
        $mapping->timestamps = true;

        return redirect()->back()->with('success', 'Document metadata updated!');
    }

    // ================= Revisi Review (User) =================
    public function revise(Request $request, DocumentMapping $mapping)
{
    if (!in_array(Auth::user()->role->name, ['User', 'Admin'])) {
        abort(403);
    }

    // Validasi
    $request->validate([
        'files.*' => 'nullable|file|mimes:pdf,docx|max:10240',
        'notes'   => 'required|string|max:500',
    ]);

    // Tentukan folder berdasarkan tipe dokumen
    $mapping->load('document');
    $folder = $mapping->document && $mapping->document->type === 'control'
        ? 'document-controls'
        : 'document-reviews';

    $files = $request->file('files', []);
    foreach ($files as $fileId => $uploadedFile) {
        if (!$uploadedFile) continue;

        $oldFile = $mapping->files()->where('id', $fileId)->first();
        if (!$oldFile) continue;

        // Hapus file lama
        if ($oldFile->file_path && Storage::disk('public')->exists($oldFile->file_path)) {
            Storage::disk('public')->delete($oldFile->file_path);
        }

        // Upload baru
        $filename = $mapping->document_number . '_rev_' . time() . "_{$fileId}." . $uploadedFile->getClientOriginalExtension();
        $newPath  = $uploadedFile->storeAs($folder, $filename, 'public');

        // Update ke DB
        $oldFile->update([
            'file_path'     => $newPath,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'file_type'     => $uploadedFile->getClientMimeType(),
            'uploaded_by'   => Auth::id(),
        ]);
    }

    // Update mapping
    $mapping->update([
        'notes'     => $request->notes,
        'status_id' => Status::where('name', 'Need Review')->first()->id,
        'user_id'   => Auth::id(),
    ]);

    return redirect()->back()->with('success', 'Document revised successfully!');
}


    // ================= Delete Review (Admin) =================
    public function destroy(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin')
            abort(403);

        if ($mapping->file_path) {
            Storage::disk('public')->delete($mapping->file_path);
        }

        $mapping->delete();
        return redirect()->back()->with('success', 'Document deleted successfully!');
    }

    // ================= Approve / Reject Review (Admin) =================
    public function approveWithDates(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin')
            abort(403);

        $request->validate([
            'reminder_date' => 'required|date|before_or_equal:deadline',
            'deadline' => 'required|date|after_or_equal:reminder_date',
        ]);

        $statusApproved = Status::where('name', 'approved')->first();
        if (!$statusApproved) {
            return redirect()->back()->with('error', 'Status "approved" not found!');
        }

        $mapping->update([
            'status_id' => $statusApproved->id,
            'reminder_date' => $request->reminder_date,
            'deadline' => $request->deadline,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document approved and dates set successfully!');
    }



    public function reject(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $statusRejected = Status::where('name', 'rejected')->first();

        if (!$statusRejected) {
            return redirect()->back()->with('error', 'Status "rejected" not found!');
        }

        $mapping->update([
            'status_id' => $statusRejected->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document rejected!');
    }

    // Document Controll
    // ================= Document Control Index =================
    public function controlIndex(Request $request)
    {
        // Ambil semua mapping dokumen control beserta relasi
        $documentMappings = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->get();

        // Update status menjadi 'Obsolete' jika obsolete_date < now
        $statusObsolete = Status::where('name', 'Obsolete')->first();
        $now = now();

        foreach ($documentMappings as $mapping) {
            if ($mapping->obsolete_date && $mapping->obsolete_date < $now) {
                if ($mapping->status_id !== $statusObsolete?->id) {
                    $mapping->status_id = $statusObsolete?->id;
                    $mapping->save();
                }
            }
        }

        $documents = Document::where('type', 'control')->get();
        $statuses = Status::all();
        $departments = Department::all();
        $files = DocumentFile::all();

        return view('contents.document-control.index', compact(
            'documentMappings',
            'documents',
            'statuses',
            'departments',
            'files',
        ));
    }

    // ================= Store Control (Admin) =================
    public function storeControl(Request $request)
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
            'department' => 'required|exists:departments,id',
            'document_number' => 'required|string|max:100',
            'files' => 'required',
            'files.*' => 'file|mimes:pdf,docx',
            'obsolete_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
        ]);

        $status = Status::where('name', 'Need Review')->first();
        if (!$status) {
            return redirect()->back()->with('error', 'Status "Need Review" not found!');
        }

        // buat record document_mapping dulu
        $mapping = DocumentMapping::create([
            'document_id' => $validated['document_id'],
            'document_number' => $validated['document_number'],
            'status_id' => $status->id,
            'obsolete_date' => $validated['obsolete_date'] ?? null,
            'reminder_date' => $validated['reminder_date'] ?? null,
            'deadline' => null,
            'user_id' => Auth::id(),
            'department_id' => $validated['department'],
            'version' => 0,
            'notes' => null,
        ]);

        // simpan file ke tabel document_files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $validated['document_number'] . '_v' . $mapping->version . '_' . time() . '_' . $index . '.' . $extension;

                $path = $file->storeAs('document-controls', $filename, 'public');

                $mapping->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('document-control.index')
            ->with('success', 'Document Control berhasil ditambahkan!');
    }

    // Update Document Control
    public function updateControl(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
            'department_id' => 'required|exists:departments,id',
            'document_number' => 'required|string|max:100',
            'obsolete_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        $mapping->update($validated);

        return redirect()->route('document-control.index')->with('success', 'Document Control berhasil diupdate!');
    }

    // Approve Document Control
    public function approveControl(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $statusActive = Status::where('name', 'active')->first();
        if (!$statusActive) {
            return redirect()->back()->with('error', 'Status "active" not found!');
        }

        $mapping->update([
            'status_id' => $statusActive->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('document-control.index')->with('success', 'Document Control approved and status set to active!');
    }

    public function bulkDestroy(Request $request)
    {
        // validasi
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:document_mappings,id',
        ]);

        $ids = $data['ids'];

        $docs = DocumentMapping::whereIn('id', $ids)->get();
        foreach ($docs as $doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
        }

        // hapus records
        DocumentMapping::whereIn('id', $ids)->delete();

        return redirect()->route('document-control.index')
            ->with('success', count($ids) . ' document(s) deleted successfully.');
    }
}
