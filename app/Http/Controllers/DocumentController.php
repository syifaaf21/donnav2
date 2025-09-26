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
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Document::whereNull('parent_id')->with('department');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Pastikan di sini menggunakan paginate, bukan get()
        $documents = $query->paginate(10)->appends($request->query());

        $departments = Department::all();

        return view('contents.master.document.index', compact('documents', 'departments'));
    }

    public function show(Request $request, $id)
    {
        $document = Document::with('department')->findOrFail($id);
        $departments = Department::all();

        $search = $request->input('search');

        $childrenQuery = Document::where('parent_id', $id)->with('department');

        if ($search) {
            $childrenQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $children = $childrenQuery->paginate(10)->appends($request->query());

        return view('contents.master.document.show', compact('document', 'children', 'departments'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'parent_id' => 'nullable|exists:documents,id',
            'type' => 'required|in:control,review',
        ]);

        Document::create($validated);

        return redirect()->back()->with('success', 'Document created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:control,review',
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
