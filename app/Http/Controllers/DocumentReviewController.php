<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = DocumentMapping::with([
            'document.parent',
            'document.children',
            'department',
            'status',
            'user',
            'partNumber.product',
            'partNumber.productModel',
            'files'
        ])->whereHas('document', fn($q) => $q->where('type', 'review'));

        $documentMappings = $query->get();

        // Grouping berdasarkan plant → part_number
        $groupedByPlant = $documentMappings->groupBy([
            fn($item) => $item->partNumber->plant ?? 'Unknown',
            fn($item) => $item->partNumber->part_number ?? 'Unknown',
        ]);

        $masterDocuments = Document::all()->keyBy('id');

        return view('contents.document-review.index', compact('groupedByPlant', 'masterDocuments'));
    }
    // public function approveOrReject(Request $request, DocumentMapping $mapping)
    // {
    //     $request->validate([
    //         'notes' => 'nullable|string|max:500',
    //         'action' => 'required|in:approve,reject',
    //     ]);

    //     $statusName = $request->action === 'approve' ? 'Approved' : 'Rejected';
    //     $status = Status::where('name', $statusName)->first();

    //     if (!$status) {
    //         return back()->with('error', 'Status not found.');
    //     }

    //     $mapping->update([
    //         'status_id' => $status->id,
    //         'notes' => $request->notes,
    //     ]);

    //     return back()->with('success', 'Document has been ' . strtolower($statusName));
    // }

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
}
