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

        $query = Document::with('childrenRecursive');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
        }

        $documents = Document::with('childrenRecursive')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('childrenRecursive', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            })
            ->whereNull('parent_id')
            ->get();

        // Ambil semua dokumen bertipe review untuk jadi opsi parent
        $parents = Document::where('type', 'review')->get();

        return view('contents.master.hierarchy.index', compact('documents', 'parents'));
    }

    public function show(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $search = $request->input('search');

        $childrenQuery = Document::where('parent_id', $id);

        if ($search) {
            $childrenQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $children = $childrenQuery->paginate(10)->appends($request->query());

        return view('contents.master.hierarchy.show', compact('document', 'children'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:documents,id',
        ]);

        $validated['type'] = 'review';

        Document::create($validated);

        return redirect()->back()->with('success', 'Document created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:documents,id',
        ]);

        $validated['type'] = 'review';

        $document = Document::where('id', $id)
            ->where('type', 'review')
            ->firstOrFail();

        $document->update($validated);

        return redirect()->back()->with('success', 'Document updated successfully.');
    }


    public function destroy($id)
    {
        $document = Document::where('id', $id)
            ->where('type', 'review')
            ->firstOrFail();

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
