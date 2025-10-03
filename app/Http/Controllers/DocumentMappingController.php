<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class DocumentMappingController extends Controller
{
    // ================= Document Review Index =================
    public function reviewIndex(Request $request)
    {
        $documentsMaster = Document::where('type', 'review')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();
        $departments = Department::all();

        $plants = PartNumber::pluck('plant')->map(fn($p) => ucfirst(strtolower($p)))->unique();

        $groupedByPlant = [];

        foreach ($plants as $plant) {
            $query = DocumentMapping::with(['document', 'department', 'partNumber', 'status', 'user'])
                ->whereHas('document', fn($q) => $q->where('type', 'review'))
                ->whereHas('partNumber', fn($q) => $q->whereRaw('LOWER(plant) = ?', [strtolower($plant)]));

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
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }

            // Filter by Department
            if ($department = request('department')) {
                $query->where('department_id', $department);
            }

            // Filter by Deadline (tanggal exact)
            if ($deadline = request('deadline')) {
                $query->whereDate('deadline', $deadline);
            }

            // Ambil versi terakhir per document berdasarkan updated_at (tanggal)
            $mappings = $query->orderBy('updated_at', 'desc')->get()->groupBy('document_id')->map(function ($group) {
                return $group->first(); // ambil mapping terakhir per document
            });

            // Ubah updated_at jadi hanya tanggal
            $mappings->transform(function ($item) {
                $item->updated_at = $item->updated_at->format('Y-m-d'); // format tanggal saja
                return $item;
            });

            $groupedByPlant[$plant] = $query->orderBy('created_at', 'desc')->paginate(5, ['*'], 'page_' . $plant);
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
            'file' => 'required|file|mimes:pdf,docx',
            'department_id' => 'required|exists:departments,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $filePath = $request->file('file')->store('documents', 'public');

        DocumentMapping::create([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'file_path' => $filePath,
            'department_id' => $request->department_id,
            'reminder_date' => null,
            'deadline' => null,
            'obsolete_date' => null,
            'status_id' => Status::where('name', 'need review')->first()->id,
            'notes' => $request->notes ?? '',
            'user_id' => Auth::id(),
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
            'department_id' => 'required|exists:departments,id',
            'reminder_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        $mapping->update([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'department_id' => $request->department_id,
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

        // update tanpa mengubah updated_at
        $mapping->updateQuietly([
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
    public function approveWithDates(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') abort(403);

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
        if (Auth::user()->role->name != 'Admin') abort(403);

        $statusRejected = Status::where('name', 'rejected')->first();
        if (!$statusRejected) {
            return redirect()->back()->with('error', 'Status "rejected" not found!');
        }

        // Update tanpa mengubah updated_at
        DocumentMapping::withoutTouching(function () use ($mapping, $statusRejected) {
            $mapping->updateQuietly([
                'status_id' => $statusRejected->id,
                'user_id' => Auth::id(),
            ]);
        });


        return redirect()->back()->with('success', 'Document rejected!');
    }
}
