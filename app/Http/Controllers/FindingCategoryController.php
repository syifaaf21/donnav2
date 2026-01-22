<?php

namespace App\Http\Controllers;

use App\Models\FindingCategory;
use Illuminate\Http\Request;

class FindingCategoryController extends Controller
{
    public function index()
    {
        $categories = FindingCategory::orderBy('created_at', 'asc')->get();
        return view('contents.master.ftpp.finding_category.index', compact('categories'));
    }
    public function show($id)
    {
        $category = FindingCategory::findOrFail($id);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        FindingCategory::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Audit type added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $category = FindingCategory::findOrFail($id);
        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Finding Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = FindingCategory::findOrFail($id);
        $category->delete();

        return redirect()->back()->with('success', 'Finding Category deleted successfully.');
    }
}
