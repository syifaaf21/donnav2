<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $plants = $this->getEnumValues('part_numbers', 'plant');
        $processes = $this->getEnumValues('part_numbers', 'process');

        $documentsMaster = Document::with('childrenRecursive')->where('type', 'review')->get();
        $departments = Department::all();
        $partNumbers = PartNumber::all();
        $models = ProductModel::all();

        // ✅ Ambil semua document mappings dengan relasi yang dibutuhkan
        $documentMappings = DocumentMapping::with([
            'document',
            'partNumber.productModel',
            'user',
            'status'
        ])
            ->when($request->plant, fn($q) => $q->whereHas('partNumber', fn($q2) => $q2->where('plant', $request->plant)))
            ->when($request->department, fn($q) => $q->where('department_id', $request->department))
            ->when($request->document_id, fn($q) => $q->where('document_id', $request->document_id))
            ->when($request->part_number, fn($q) => $q->where('part_number_id', $request->part_number))
            ->when($request->model, function ($q) use ($request) {
                $q->whereHas('partNumber.model', function ($q2) use ($request) {
                    $q2->where('id', $request->model);
                });
            })
            ->when($request->process, function ($q) use ($request) {
                $q->whereHas('partNumber', function ($q2) use ($request) {
                    $q2->where('process', $request->process);
                });
            })

            ->get();

        // ✅ Kelompokkan berdasarkan kombinasi unik Part Number + Model + Process
        $groupedData = $documentMappings->groupBy(function ($item) {
            $partNumber = $item->partNumber?->part_number ?? 'unknown';
            $model = $item->partNumber?->productModel?->name ?? 'unknown';
            $process = $item->document?->process ?? 'unknown';
            return "{$partNumber}-{$model}-{$process}";
        });

        return view('contents.document-review.index', compact(
            'plants',
            'processes',
            'departments',
            'partNumbers',
            'models',
            'documentsMaster',
            'groupedData',
        ));
    }

    private function getEnumValues($table, $column)
    {
        $type = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'")[0]->Type;
        preg_match('/enum\((.*)\)/', $type, $matches);
        $enum = [];

        if (!empty($matches)) {
            foreach (explode(',', $matches[1]) as $value) {
                $enum[] = trim($value, "'");
            }
        }

        return $enum;
    }


    public function getDataByPlant(Request $request)
    {
        $plant = $request->plant;

        // Ambil part number berdasarkan plant
        $partNumbers = PartNumber::where('plant', $plant)
            ->orderBy('part_number')
            ->get(['id', 'part_number']);

        Log::info('Plant selected: ' . $plant);
        // Ambil unique process dari part numbers dengan plant tsb
        $processes = PartNumber::where('plant', $plant)
            ->select('process')
            ->distinct()
            ->pluck('process');

        return response()->json([
            'part_numbers' => $partNumbers,
            'processes' => $processes,
        ]);
    }

    // Tambahkan di DocumentReviewController
public function show($id)
{
    $document = Document::with('childrenRecursive')->findOrFail($id);

    return view('contents.document-review.show', compact('document'));
}

}
