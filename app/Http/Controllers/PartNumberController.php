<?php

namespace App\Http\Controllers;

use App\Models\PartNumber;
use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Http\Request;

class PartNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = PartNumber::with(['product', 'productModel']);

        if($search){
            $query->where(function ($q) use ($search){
                $q->where('part_number', 'like', "%{$search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"))
                ->orWhereHas('productModel', fn($m) => $m->where('name', 'like', "%{$search}%"))
                ->orWhere('process', 'like', "%{$search}%");
            });
        }

        $partNumbers = $query->paginate(10)->appends($request->query());
        $products = Product::all();
        $models = ProductModel::all();

        return view('contents.master.part-number', compact('partNumbers', 'products', 'models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_number' => 'required|string|max:255|unique:part_numbers,part_number',
            'product_id' => 'required|exists:products,id',
            'model_id' => 'required|exists:models,id',
            'process' => 'required|in:injection,painting,assembling body,die casting,machining,assembling unit,electric',
        ]);

        PartNumber::create($request->only('part_number', 'product_id', 'model_id', 'process'));

        return redirect()->back()->with('success', 'Part Number added sucessfully');
    }

    // Update Part Number berdasarkan id
    public function update(Request $request, PartNumber $partNumber)
    {
        $request->validate([
            'part_number' => 'required|string|max:255|unique:part_numbers,part_number',
            'product_id' => 'required|exists:products,id',
            'model_id' => 'required|exists:models,id',
            'process' => 'required|in:injection,painting,assembling body,die casting,machining,assembling unit,electric',
        ]);

        $partNumber->update($request->only('part_number', 'product_id', 'model_id', 'process'));

        return redirect()->back()->with('success', 'Part Number updated successfully.');
    }

    // Hapus Part Number berdasarkan id
    public function destroy(PartNumber $partNumber)
    {
        $partNumber->delete();

        return redirect()->back()->with('success', 'Part Number deleted successfully.');
    }
}
