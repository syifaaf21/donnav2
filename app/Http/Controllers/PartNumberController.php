<?php

namespace App\Http\Controllers;

use App\Models\PartNumber;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PartNumberController extends Controller
{
    // jangan lupa import model Process di atas

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = PartNumber::with(['product', 'productModel', 'process']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('productModel', fn($m) => $m->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('process', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('plant', 'like', "%{$search}%");
            });
        }

        $partNumbers = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
        $products = Product::all();
        $models = ProductModel::all();
        $processes = Process::all(); // <--- tambah ini

        return view('contents.master.part-number', compact('partNumbers', 'products', 'models', 'processes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_number' => 'required|string|max:255|unique:tm_part_numbers,part_number',
            'product_id' => 'required|exists:tm_products,id',
            'model_id' => 'required|exists:tm_models,id',
            'process_id' => 'required|exists:tm_processes,id',
            'plant' => 'required|in:body,unit,electric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('_form', 'add');
        }

        // ⬇️ pastikan ini pakai findOrFail, bukan get()
        $product = Product::findOrFail($request->product_id);
        $model = ProductModel::findOrFail($request->model_id);

        PartNumber::create([
            'part_number' => $request->part_number,
            'product_id' => $product->id,
            'model_id' => $model->id,
            'process_id' => $request->process_id,
            'plant' => $request->plant
        ]);

        return redirect()->back()->with('success', 'Part Number added successfully.');
    }


    public function update(Request $request, PartNumber $partNumber)
    {
        $validator = Validator::make($request->all(), [
            'part_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tm_part_numbers', 'part_number')->ignore($partNumber->id),
            ],
            'product_id' => 'required|exists:tm_products,id',
            'model_id' => 'required|exists:tm_models,id',
            'process_id' => 'required|exists:tm_processes,id',
            'plant' => 'required|in:body,unit,electric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit_modal', $partNumber->id); // flag modal edit yang mau dibuka
        }

        $partNumber->update($request->only('part_number', 'product_id', 'model_id', 'process_id', 'plant'));

        return redirect()->back()->with('success', 'Part Number updated successfully.');
    }

    public function getDetails($id)
    {
        $part = PartNumber::with(['product', 'productModel', 'process'])->find($id);
        if (!$part) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $part->id,
            'part_number' => $part->part_number,
            'plant' => $part->plant,
            'product' => $part->product ? ['id' => $part->product->id, 'text' => $part->product->name] : null,
            'model' => $part->productModel ? ['id' => $part->productModel->id, 'text' => $part->productModel->name] : null,
            'process' => $part->process ? ['id' => $part->process->id, 'text' => $part->process->name] : null,
        ]);
    }

    public function getOptionsByPlant(Request $request)
    {
        $plant = ucfirst($request->query('plant'));

        $processes = Process::where('plant', $plant)->get(['id', 'name'])->map(function ($item) {
            $item->name = ucwords($item->name);
            return $item;
        });

        $products = Product::where('plant', $plant)->get(['id', 'name']);
        $models = ProductModel::where('plant', $plant)->get(['id', 'name']);

        return response()->json([
            'processes' => $processes,
            'products' => $products,
            'models' => $models,
        ]);
    }

    public function destroy(PartNumber $partNumber)
    {
        $partNumber->delete();

        return redirect()->back()->with('success', 'Part Number deleted successfully.');
    }
}
