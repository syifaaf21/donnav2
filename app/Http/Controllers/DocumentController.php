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

        $query = \App\Models\Document::with('childrenRecursive'); // eager load recursive children

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Ambil hanya root nodes (parent_id null) untuk memulai tree
        $documents = Document::with('childrenRecursive')  // definisikan di model
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('childrenRecursive', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%");
                    });
            })
            ->whereNull('parent_id')
            ->get();

        return view('contents.master.hierarchy.index', compact('documents'));
    }

    public function show(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $search = $request->input('search');

        $childrenQuery = Document::where('parent_id', $id);

        if ($search) {
            $childrenQuery->where('name', 'like', "%{$search}%");
        }

        $children = $childrenQuery->paginate(10)->appends($request->query());

        return view('contents.master.hierarchy.show', compact('document', 'children'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
