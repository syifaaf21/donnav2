<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::whereNull('parent_id')->with('department')->get();
        $departments = Department::all();
        return view('contents.master.document.index', compact('documents', 'departments'));
    }

    public function show($id)
    {
        $document = Document::with('department')->findOrFail($id);
        $children = Document::where('parent_id', $id)->with('department')->get();
        $departments = Department::all();
        return view('contents.master.document.show', compact('document', 'children', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'parent_id' => 'nullable|exists:documents,id',
        ]);

        Document::create($validated);

        return redirect()->back()->with('success', 'Document created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $document = Document::findOrFail($id);
        $document->update($validated);

        return redirect()->back()->with('success', 'Document updated successfully.');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
