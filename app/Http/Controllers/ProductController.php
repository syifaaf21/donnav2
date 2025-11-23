<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%");
            });
        })->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('contents.master.product', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tm_products,name',
            'code' => 'required|string|max:50|unique:tm_products,code',
            'plant' => 'required|in:Body,Unit,Electric',
        ]);

        Product::create($validated);

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:100|unique:tm_products,name,' . $product->id,
        'code' => 'required|string|max:50|unique:tm_products,code,' . $product->id,
        'plant' => 'required|in:Body,Unit,Electric',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('edit_modal', $product->id); // <<< penting
    }

    $product->update($validator->validated());
    return redirect()->back()->with('success', 'Product updated successfully.');
}

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}