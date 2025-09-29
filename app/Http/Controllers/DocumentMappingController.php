<?php

namespace App\Http\Controllers;

use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentMappingController extends Controller
{
    /**
     * Halaman Document Review (grouped by plant)
     */
    public function reviewIndex()
    {
        $documentMappings = DocumentMapping::with(['document.department', 'partNumber', 'status'])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->get();

        $groupedByPlant = $documentMappings->groupBy(fn($m) => $m->partNumber->plant ?? 'Unknown');

        $documentsMaster = Document::where('type', 'review')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();

        return view('contents.document.review', compact('groupedByPlant', 'documentsMaster', 'partNumbers', 'statuses'));
    }

    /**
     * Halaman Document Control (single table, tidak grouped)
     */
    public function controlIndex()
    {
        $documentMappings = DocumentMapping::with(['document.department', 'partNumber', 'status'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->get();

        $documentsMaster = Document::where('type', 'control')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();

        return view('contents.document.control', compact('documentMappings', 'documentsMaster', 'partNumbers', 'statuses'));
    }

    /**
     * Store new mapping (works for both review & control)
     */
    public function store(Request $request)
    {
        // get document to detect its type (ensures consistency with master)
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'part_number_id' => 'required|exists:part_numbers,id',
            // status_id optional, validated below
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
        ]);

        $document = Document::findOrFail($request->document_id);
        $type = $document->type; // 'review' atau 'control'

        // build rules depending on type
        $rules = [
            'document_id' => 'required|exists:documents,id',
            'part_number_id' => 'required|exists:part_numbers,id',
            'status_id' => 'nullable|exists:statuses,id',
            'notes' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
        ];

        if ($type === 'review') {
            $rules += [
                'document_number' => 'required|string|max:255',
                'version' => 'required|string|max:50',
                'reminder_date' => 'nullable|date',
                'deadline' => 'nullable|date',
            ];
        } else { // control
            $rules += [
                'obsolete_date' => 'nullable|date',
                'reminder_date' => 'nullable|date',
            ];
        }

        $validated = $request->validate($rules);

        // handle file upload
        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('documents', 'public'); // storage/app/public/documents
        }

        // fill mandatory fields
        $validated['user_id'] = auth()->id() ?? 1;

        // set default status if not provided
        if (empty($validated['status_id'])) {
            $defaultStatus = Status::where('name', 'Active')->first();
            $validated['status_id'] = $defaultStatus ? $defaultStatus->id : Status::first()->id ?? 1;
        }

        // ensure obsolete_date for review may be null (only control uses it)
        if ($type === 'review') {
            // document_number/version must exist (already validated)
        } else {
            // control: ensure document_number/version are not set accidentally
            unset($validated['document_number'], $validated['version'], $validated['deadline']);
        }

        // assign type? (optional) — we keep type in documents master, not in mapping
        // create record
        DocumentMapping::create($validated);

        // redirect back to the right listing page
        return redirect()->route($type === 'review' ? 'document.review' : 'document.control')
            ->with('success', 'Dokumen berhasil ditambahkan.');
    }

    /**
     * Update mapping
     */
    public function update(Request $request, DocumentMapping $documentMapping)
    {
        // allow change of document_id too (so re-evaluate type)
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'part_number_id' => 'required|exists:part_numbers,id',
            'status_id' => 'nullable|exists:statuses,id',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'notes' => 'nullable|string',
        ]);

        $document = Document::findOrFail($request->document_id);
        $type = $document->type;

        $rules = [
            'document_id' => 'required|exists:documents,id',
            'part_number_id' => 'required|exists:part_numbers,id',
            'status_id' => 'nullable|exists:statuses,id',
            'notes' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
        ];

        if ($type === 'review') {
            $rules += [
                'document_number' => 'required|string|max:255',
                'version' => 'required|string|max:50',
                'reminder_date' => 'nullable|date',
                'deadline' => 'nullable|date',
            ];
        } else {
            $rules += [
                'obsolete_date' => 'nullable|date',
                'reminder_date' => 'nullable|date',
            ];
        }

        $validated = $request->validate($rules);

        // file replace: remove old file if a new one uploaded
        if ($request->hasFile('file_path')) {
            if ($documentMapping->file_path) {
                Storage::disk('public')->delete($documentMapping->file_path);
            }
            $validated['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        // default status if not provided
        if (empty($validated['status_id'])) {
            $defaultStatus = Status::where('name', 'Active')->first();
            $validated['status_id'] = $defaultStatus ? $defaultStatus->id : Status::first()->id ?? 1;
        }

        // clean fields not relevant to this type
        if ($type === 'review') {
            unset($validated['obsolete_date']);
        } else {
            unset($validated['document_number'], $validated['version'], $validated['deadline']);
        }

        $documentMapping->update($validated);

        return redirect()->route($type === 'review' ? 'document.review' : 'document.control')
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    /**
     * Destroy mapping
     */
    public function destroy(DocumentMapping $documentMapping)
    {
        // delete file if exists
        if ($documentMapping->file_path) {
            Storage::disk('public')->delete($documentMapping->file_path);
        }

        $type = $documentMapping->document->type ?? 'review';
        $documentMapping->delete();

        return redirect()->route($type === 'review' ? 'document.review' : 'document.control')
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
