<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use App\Models\Product;
use App\Models\Process;
use App\Models\ProductModel;
use App\Models\DocumentMapping;
use Illuminate\Http\Request;

class DocumentNumberController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer',
            'department_id' => 'required|integer',
            'product_id' => 'nullable|integer',
            'process_id' => 'nullable|integer',
            'model_id' => 'nullable|integer',
            'rev' => 'nullable|string',
            'format' => 'nullable|integer',
        ]);

        $doc = Document::find($request->document_id);
        $dept = Department::find($request->department_id);
        $product = $request->product_id ? Product::find($request->product_id) : null;
        $process = $request->process_id ? Process::find($request->process_id) : null;
        $model = $request->model_id ? ProductModel::find($request->model_id) : null;

        $docCode = $doc?->code ?? strtoupper(substr($doc?->name ?? 'DOC', 0, 3));
        $deptCode = $dept?->code ?? '';
        $productCode = $product?->code ?? '';
        $processCode = $process?->code ?? '';
        $modelName = $model?->name ?? '';

        // normalize pieces: convert spaces and slashes to underscore, keep underscores and dashes
        $normalize = function ($s) {
            $s = (string) $s;
            $s = trim($s);
            // replace spaces or slashes with underscore
            $s = preg_replace('/[\s\/]+/', '_', $s);
            // remove any characters except letters, numbers, underscore, and dash
            $s = preg_replace('/[^A-Za-z0-9_\-]/', '', $s);
            return $s;
        };

        // build parts but keep each component intact (preserve internal underscores/dashes)
        $parts = array_values(array_filter([
            $normalize($docCode),
            $normalize($deptCode),
            $normalize($productCode),
            $normalize($processCode),
            $normalize($modelName),
        ]));

        $format = (int) ($request->format ?? 0);

        // For format 3 we want components joined by '-' (example: FMEA-QAS-CSH-AS-1SZ-002-00)
        $format = (int) ($request->format ?? 0);

        if ($format === 3) {

            // Normalize each part separately
            $docPart = $normalize($docCode);
            $deptPart = $normalize($deptCode);
            $productPart = $normalize($productCode);
            $processPart = $normalize($processCode);
            $modelPart = $normalize($modelName);

            // First section: Document - Department
            $mainPrefix = implode('-', array_filter([
                $docPart,
                $deptPart
            ]));

            // Second section: Product_Process_Model
            $subPrefix = implode('_', array_filter([
                $productPart,
                $processPart,
                $modelPart
            ]));

            // Combine both sections using dash
            $prefix = $subPrefix
                ? $mainPrefix . '-' . $subPrefix
                : $mainPrefix;
        } else {

            // Default behavior (optional)
            $prefix = implode('_', array_filter([
                $normalize($docCode),
                $normalize($deptCode),
                $normalize($productCode),
                $normalize($processCode),
                $normalize($modelName),
            ]));
        }

        // Find existing mappings matching the same combination and extract max seq
        $query = DocumentMapping::query()
            ->where('document_id', $request->document_id)
            ->where('department_id', $request->department_id)
            ->whereNull('marked_for_deletion_at');

        // Only consider mappings that match exactly the selected product/process/model.
        // If a field is not selected, require mappings to have no related record for that relation.
        if ($request->filled('product_id')) {
            $query->whereHas('product', fn($q) => $q->where('tm_products.id', $request->product_id));
        } else {
            $query->whereDoesntHave('product');
        }

        if ($request->filled('process_id')) {
            $query->whereHas('process', fn($q) => $q->where('tm_processes.id', $request->process_id));
        } else {
            $query->whereDoesntHave('process');
        }

        if ($request->filled('model_id')) {
            $query->whereHas('productModel', fn($q) => $q->where('tm_models.id', $request->model_id));
        } else {
            $query->whereDoesntHave('productModel');
        }

        $existing = $query->pluck('document_number')->filter()->values();

        $maxSeq = 0;
        foreach ($existing as $num) {
            // Use regex to reliably extract the sequence number from the end of the string,
            // avoiding incorrect splitting when components themselves contain dashes.
            // Match: -<seq>-<rev> at the end
            if (preg_match('/-(\d+)-([0-9A-Za-z]+)$/', $num, $m)) {
                $seq = $m[1];
                if (is_numeric($seq)) {
                    $maxSeq = max($maxSeq, (int)$seq);
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $seqPadded = str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);
        $rev = $request->rev ?? '00';
        $rev = str_pad((string)$rev, 2, '0', STR_PAD_LEFT);

        $final = $prefix . '-' . $seqPadded . '-' . $rev;

        return response()->json(['success' => true, 'document_number' => $final]);
    }
}
