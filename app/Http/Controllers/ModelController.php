<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductModel;

class ModelController extends Controller
{
    public function index(Request $request)
    {
        $models = ProductModel::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('contents.master.model', compact('models'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:models,name',
            'plant' => 'required|in:Body,Unit,Electric',
        ]);

        ProductModel::create($validated);

        return redirect()->back()->with('success', 'Model created successfully.');
    }

    public function update(Request $request, ProductModel $model)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:models,name,' . $model->id,
            'plant' => 'required|in:Body,Unit,Electric',
        ]);

        $model->update($validated);

        return redirect()->back()->with('success', 'Model updated successfully.');
    }

    public function destroy(ProductModel $model)
    {
        $model->delete();

        return redirect()->back()->with('success', 'Model deleted successfully.');
    }
}
