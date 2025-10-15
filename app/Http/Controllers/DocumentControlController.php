<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentMapping;
use App\Models\Status;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentControlController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Ambil data utama
        $documentsMapping = $query->get();

        // Hitung statistik berdasarkan relasi status
        $totalDocuments = $documentsMapping->count();
        $activeDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Active')->count();
        $obsoleteDocuments = $documentsMapping->filter(fn($d) => $d->status?->name === 'Obsolete')->count();

        // Group untuk accordion per department
        $groupedDocuments = $documentsMapping->groupBy(fn($d) => $d->department->name ?? 'Unknown Department');

        // Dropdown filter department
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
        // Hanya admin atau user yang boleh revise
        if (!in_array(Auth::user()->role->name, ['User', 'Admin'])) {
            abort(403, 'You do not have permission to revise this document.');
        }

        // Validasi input
        $request->validate([
            'files.*' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'notes' => 'required|string|max:500',
        ]);

        // Tentukan folder berdasarkan tipe dokumen
        $mapping->load('document');
        $folder = $mapping->document && $mapping->document->type === 'control'
            ? 'document-controls'
            : 'document-reviews';

        // Replace file lama jika ada file baru diupload
        $files = $request->file('files', []);
        foreach ($files as $fileId => $uploadedFile) {
            if (!$uploadedFile)
                continue;

            $oldFile = $mapping->files()->where('id', $fileId)->first();
            if (!$oldFile)
                continue;

            // Hapus file lama
            if ($oldFile->file_path && Storage::disk('public')->exists($oldFile->file_path)) {
                Storage::disk('public')->delete($oldFile->file_path);
            }

            // Upload file baru
            $filename = $mapping->document_number . '_rev_' . time() . "_{$fileId}." . $uploadedFile->getClientOriginalExtension();
            $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

            // Update record di database
            $oldFile->update([
                'file_path' => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type' => $uploadedFile->getClientMimeType(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        // Update mapping
        $status = Status::firstOrCreate(
            ['name' => 'Need Review'],
            ['description' => 'Document waiting for review']
        );

        $mapping->update([
            'notes' => $request->notes,
            'status_id' => $status->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document revised successfully!');
    }


    public function approve(DocumentMapping $mapping)
    {
        // hanya admin yang bisa approve
        if (Auth::user()->role->name != 'Admin') {
            abort(403, 'Only admin can approve documents.');
        }

        $statusActive = Status::firstOrCreate(['name' => 'Active'], ['description' => 'Document is active']);

        $mapping->update([
            'status_id' => $statusActive->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Document approved successfully.');
    }

    public function reject(DocumentMapping $mapping)
    {
        // hanya admin yang bisa reject
        if (Auth::user()->role->name != 'Admin') {
            abort(403, 'Only admin can reject documents.');
        }

        $statusRejected = Status::firstOrCreate(['name' => 'Rejected'], ['description' => 'Document has been rejected']);

        $mapping->update([
            'status_id' => $statusRejected->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Document rejected successfully.');
    }
}
