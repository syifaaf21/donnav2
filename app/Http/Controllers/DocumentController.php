<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentPlant;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Document::with(['childrenRecursive', 'plants']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
        }

        $documents = Document::with(['childrenRecursive','plants'])
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
            ->where('type', 'review')
            ->get();

        // Ambil semua dokumen bertipe review untuk jadi opsi parent
        $parents = Document::where('type', 'review')->with('plants')->get();

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
            'parent_id' => 'nullable|exists:tm_documents,id',
            'plants' => 'required|array|min:1',
            'plants.*' => 'in:body,unit,electric,all',
        ]);

        $validated['type'] = 'review';

        $doc = Document::create($validated);

        // Sync document_plant entries
        if ($request->filled('plants')) {
            // normalize to lowercase
            $plants = collect($request->input('plants'))->map(fn($p) => strtolower($p))->unique();
            foreach ($plants as $p) {
                DocumentPlant::create(['document_id' => $doc->id, 'plant' => $p]);
            }
        }

        return redirect()->back()->with('success', 'Document created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:tm_documents,id',
            'plants' => 'nullable|array',
            'plants.*' => 'in:body,unit,electric,all',
        ]);

        $validated['type'] = 'review';

        $document = Document::where('id', $id)
            ->where('type', 'review')
            ->firstOrFail();

        $document->update($validated);

        // Sync plants: delete existing relations and insert new
        if ($request->has('plants')) {
            DB::table('document_plant')->where('document_id', $document->id)->delete();
            $plants = collect($request->input('plants'))->map(fn($p) => strtolower($p))->unique();
            foreach ($plants as $p) {
                DB::table('document_plant')->insert(['document_id' => $document->id, 'plant' => $p, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

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
