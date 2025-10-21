<?php

use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\PartNumberController;
use App\Models\Department;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/products', function(Request $request) {
    $search = $request->input('q');
    return Product::where('name', 'like', "%$search%")
        ->select('id', 'name as text')  // ambil id asli dan name sebagai text
        ->limit(20)
        ->get();
});

Route::get('/models', function(Request $request) {
    $search = $request->input('q');
    return ProductModel::where('name', 'like', "%$search%")
        ->select('id', 'name as text')  // ambil id asli dan name sebagai text
        ->limit(20)
        ->get();
});
Route::middleware('auth')->get('/roles', function(Request $request) {
    $search = $request->query('q', '');
    $roles = Role::where('name', 'like', "%{$search}%")
        ->select('id', 'name as text')
        ->limit(20)
        ->get();

    return response()->json($roles);
});
Route::get('/departments', function(Request $request) {
    $search = $request->input('q');
    return Department::where('name', 'like', "%$search%")
        ->select('id', 'name as text')
        ->limit(20)
        ->get();
});

Route::get('/document-types', function() {
    $types = Document::getTypes(); // ['control' => 'Control', 'review' => 'Review']

    // Format jadi array untuk TomSelect [{id, text}]
    $result = collect($types)->map(function($label, $key) {
        return ['id' => $key, 'text' => $label];
    })->values();

    return response()->json($result);
});

Route::get('/documents', function(Request $request) {
    $search = $request->input('q');
    return Document::where('name', 'like', "%$search%")
        ->select('id', 'name as text')
        ->limit(20)
        ->get();
});
// Route::get('/part-numbers', function(Request $request) {
//     $search = $request->input('q');
//     return PartNumber::where('part_number', 'like', "%$search%")
//         ->select('id', 'part_number as text')
//         ->limit(20)
//         ->get();
// });
Route::get('/plants', function (Request $request) {
    $plants = ['body' => 'Body', 'unit' => 'Unit', 'electric' => 'Electric'];
    $formatted = collect($plants)->map(function ($label, $value) {
        return ['id' => $value, 'text' => $label];
    })->values();

    return response()->json($formatted);
});
Route::get('/part-numbers', function(Request $request) {
    $plant = $request->query('plant');
    $query = PartNumber::query();

    if ($plant) {
        $query->whereRaw('LOWER(plant) = ?', [strtolower($plant)]);
    }

    return $query->select('id', 'part_number as text')->limit(50)->get();
});

Route::get('/get-options-by-plant', [PartNumberController::class, 'getOptionsByPlant'])->name('master.get.options.by.plant');

// routes/api.php
Route::get('/generate-document-number', [DocumentMappingController::class, 'generateDocumentNumber']);

// Route::get('/plants', function() {
//     $plants = ['body' => 'Body', 'unit' => 'Unit', 'electric' => 'Electric'];
//     return collect($plants)->map(fn($label, $value) => ['id' => $value, 'text' => $label])->values();
// });

