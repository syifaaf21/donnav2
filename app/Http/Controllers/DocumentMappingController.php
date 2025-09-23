<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documentMappings = DocumentMapping::with(['document', 'department'])->get();
        $departments = Department::all();
        $documents = Document::all();

        return view('contents.master.document', compact('documentMappings', 'departments', 'documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Data untuk tabel documents
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',

            // Data untuk tabel document_mappings
            // 'part_number_id' => 'required|exists:part_numbers,id',
            // 'status_id' => 'required|exists:statuses,id',
            'document_number' => 'required|string|max:255',
            'type' => 'required|in:control,review',
            'version' => 'required|string|max:50',
            // 'file_path' => 'required|string|max:255', // atau bisa diganti jadi file upload
            // 'notes' => 'nullable|string',
            // 'obsolete_date' => 'required|date',
            // 'reminder_date' => 'required|date',
            // 'deadline' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $document = Document::create([
                'name' => $validated['name'],
                'department_id' => $validated['department_id'],
            ]);

            DocumentMapping::create([
                'document_id' => $document->id,
                // 'part_number_id' => $validated['part_number_id'],
                // 'status_id' => $validated['status_id'],
                'document_number' => $validated['document_number'],
                'type' => $validated['type'],
                'version' => $validated['version'],
                // 'file_path' => $validated['file_path'],
                // 'notes' => $validated['notes'] ?? '',
                // 'obsolete_date' => $validated['obsolete_date'],
                // 'reminder_date' => $validated['reminder_date'],
                // 'deadline' => $validated['deadline'],
                // 'user_id' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('documents.index')->with('success', 'Document and mapping created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:255|unique:documents,document_number,' . $document->id,
            'type' => 'required|in:review,control',
            'version' => 'required|string|max:50',
            'part_number' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $document->update($validated);

        return redirect()->route('documents.index')->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
    }
}
