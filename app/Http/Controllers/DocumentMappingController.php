<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class DocumentMappingController extends Controller
{
    // ================= Document Review Index =================
    public function reviewIndex()
    {
        $documentMappings = DocumentMapping::with(['document.department', 'partNumber', 'status', 'user'])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->get();

        // Group per plant
        $partNumbers = PartNumber::all();
        $allPlants = PartNumber::pluck('plant')->unique();
        $groupedByPlant = [];
        foreach ($allPlants as $plant) {
            $groupedByPlant[$plant] = $documentMappings->filter(fn($m) => $m->partNumber->plant == $plant);
        }

        $documentsMaster = Document::where('type', 'review')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();

        return view('contents.document-review.index', compact(
            'groupedByPlant',
            'documentsMaster',
            'partNumbers',
            'statuses'
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
            'file' => 'required|file|mimes:pdf,docx',
            'reminder_date' => 'required|date',
            'deadline' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $filePath = $request->file('file')->store('documents', 'public');

        DocumentMapping::create([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'file_path' => $filePath,
            'department_id' => Document::find($request->document_id)->department_id,
            'reminder_date' => $request->reminder_date,
            'deadline' => $request->deadline,
            'status_id' => Status::where('name', 'need review')->first()->id,
            'notes' => $request->notes ?? '',
            'user_id' => Auth::id(),
            'version' => 1,
        ]);

        return redirect()->back()->with('success', 'Document review created!');
    }

    // ================= Update Review (Admin) =================
    public function updateReview(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') abort(403);

        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'document_number' => 'required|string|max:255',
            'part_number_id' => 'required|exists:part_numbers,id',
            'reminder_date' => 'required|date',
            'deadline' => 'required|date',
        ]);

        $mapping->update([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'department_id' => Document::find($request->document_id)->department_id,
            'reminder_date' => $request->reminder_date,
            'deadline' => $request->deadline,
        ]);

        return redirect()->back()->with('success', 'Document metadata updated!');
    }

    // ================= Revisi Review (User) =================
    public function reviseReview(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'User' && Auth::user()->role->name != 'Admin') abort(403);

        $request->validate([
            'file' => 'required|file|mimes:pdf,docx',
            'notes' => 'required|string|max:500',
        ]);

        $filePath = $request->file('file')->store('documents', 'public');

        $mapping->update([
            'file_path' => $filePath,
            'notes' => $request->notes,
            'status_id' => Status::where('name', 'need review')->first()->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document review revised!');
    }

    // ================= Delete Review (Admin) =================
    public function destroyReview(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') abort(403);
        $mapping->delete();
        return redirect()->back()->with('success', 'Document review deleted!');
    }

    // ================= Approve / Reject Review (Admin) =================
    public function approve(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $statusApproved = Status::where('name', 'approved')->first();

        if (!$statusApproved) {
            return redirect()->back()->with('error', 'Status "approved" not found!');
        }

        // ✅ Naikkan version ketika dokumen disetujui
        $mapping->update([
            'status_id' => $statusApproved->id,
            'version' => $mapping->version + 1,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document approved successfully!');
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
}
