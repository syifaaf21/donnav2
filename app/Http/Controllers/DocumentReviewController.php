<?php

namespace App\Http\Controllers;

use App\Models\{Department, Document, DocumentMapping, PartNumber, Process, Product, ProductModel, Status, User};
use App\Notifications\{DocumentRevisedNotification, DocumentStatusNotification, DocumentActionNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Storage};

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $plants = $this->getEnumValues('tm_part_numbers', 'plant');
        $plants = array_filter($plants, fn($p) => in_array($p, ['Body', 'Unit', 'Electric']));
        $documentsMaster = Document::where('type', 'review')->get();

        $documentMappings = DocumentMapping::with([
            'document',
            'files',
            'partNumber.product',
            'partNumber.productModel',
            'partNumber.process',
            'productModel',
            'process',
            'product',
            'user',
            'status',
            'department'
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->get();

        // Tambahkan URL file dengan aman
        $documentMappings->each(function ($mapping) {
            $mapping->files->transform(function ($file) {
                if (is_object($file) && isset($file->file_path)) {
                    $file->url = asset('storage/' . $file->file_path);
                } else {
                    $file->url = null;
                }
                return $file;
            });
        });

        $groupedByPlant = $this->groupDocumentsByPlantAndCode($plants, $documentsMaster, $documentMappings);

        return view('contents.document-review.index', compact(
            'plants',
            'documentsMaster',
            'groupedByPlant'
        ));
    }

    public function showFolder($plant, $docCode, Request $request)
    {
        $docCode = base64_decode($docCode);

        // Group documents
        $documentsByCode = $this->getDocumentsGroupedByPlantAndCode();
        $plantGroup      = $documentsByCode->get($plant) ?? collect();

        $normalizedDocCode = trim(strtolower($docCode));
        $matchedCode       = $plantGroup->keys()->first(fn($code) => trim(strtolower($code)) === $normalizedDocCode);

        if (!$matchedCode) abort(404);

        $documents = $plantGroup->get($matchedCode, collect());

        // 1. Semua dropdown berdasarkan plant
        $allPartNumbers = PartNumber::where('plant', $plant)->pluck('part_number')->unique()->values();
        $allModels      = ProductModel::where('plant', $plant)->pluck('name')->unique()->values();
        $allProcesses   = Process::where('plant', $plant)->pluck('name')->unique()->values();
        $allProducts    = Product::where('plant', $plant)->pluck('name')->unique()->sort()->values();

        // 2. Filter berdasarkan dropdown (part / model / process / product)
        $documents = $documents->filter(function ($doc) use ($request) {

            // jika tidak pilih part number → filter bebas
            if (!$request->part_number) {

                $matchModel   = !$request->model   || ($doc->partNumber?->productModel?->name === $request->model);
                $matchProcess = !$request->process || ($doc->partNumber?->process?->name === $request->process);
                $matchProduct = !$request->product || ($doc->partNumber?->product?->name === $request->product);

                return $matchModel && $matchProcess && $matchProduct;
            }

            // jika part number dipilih → filter ketat
            $matchPartNumber = ($doc->partNumber?->part_number === $request->part_number);

            $matchModel   = !$request->model   || ($doc->partNumber?->productModel?->name === $request->model);
            $matchProcess = !$request->process || ($doc->partNumber?->process?->name === $request->process);
            $matchProduct = !$request->product || ($doc->partNumber?->product?->name === $request->product);

            return $matchPartNumber && $matchModel && $matchProcess && $matchProduct;
        });

        // 3. Search bar (q)
        if ($request->filled('q')) {

            $search = strtolower($request->q);

            $documents = $documents->filter(function ($doc) use ($search) {

                return
                    str_contains(strtolower($doc->document_number ?? ''), $search)
                    || str_contains(strtolower($doc->notes ?? ''), $search)
                    || str_contains(strtolower($doc->document?->name ?? ''), $search)
                    || str_contains(strtolower($doc->document?->code ?? ''), $search)
                    || str_contains(strtolower($doc->user?->name ?? ''), $search)
                    || str_contains(strtolower($doc->status?->name ?? ''), $search)

                    // Part number
                    || str_contains(strtolower($doc->partNumber?->part_number ?? ''), $search)

                    // Product
                    || str_contains(strtolower($doc->partNumber?->product?->name ?? ''), $search)
                    || str_contains(strtolower($doc->product?->name ?? ''), $search)
                    || str_contains(strtolower($doc->product?->code ?? ''), $search)

                    // Model
                    || str_contains(strtolower($doc->partNumber?->productModel?->name ?? ''), $search)
                    || str_contains(strtolower($doc->productModel?->name ?? ''), $search)

                    // Process
                    || str_contains(strtolower($doc->partNumber?->process?->name ?? ''), $search)
                    || str_contains(strtolower($doc->partNumber?->process?->code ?? ''), $search)
                    || str_contains(strtolower($doc->process?->name ?? ''), $search);
            });
        }

        // 4. Set dropdown dependent (harus setelah search)
        if ($request->part_number) {

            $related = PartNumber::with(['productModel', 'process', 'product'])
                ->where('part_number', $request->part_number)
                ->where('plant', $plant)
                ->first();

            $models    = collect([$related?->productModel?->name])->filter()->values();
            $processes = collect([$related?->process?->name])->filter()->values();
            $products  = collect([$related?->product?->name])->filter()->values();
        } else {

            $models    = $allModels;
            $processes = $allProcesses;
            $products  = $allProducts;
        }

        return view('contents.document-review.partials.folder', [
            'plant'       => $plant,
            'docCode'     => $matchedCode,
            'documents'   => $documents,

            'partNumbers' => $allPartNumbers,
            'models'      => $models,
            'processes'   => $processes,
            'products'    => $products,
        ]);
    }

    public function getFilters(Request $request)
    {
        if (!$request->part_number) {
            return response()->json([
                'models' => [],
                'processes' => [],
                'products' => []
            ]);
        }

        $related = PartNumber::with(['productModel', 'process', 'product'])
            ->where('part_number', $request->part_number)
            ->first();

        return response()->json([
            'models'    => $related ? [$related->productModel?->name] : [],
            'processes' => $related ? [$related->process?->name] : [],
            'products'  => $related ? [$related->product?->name] : [],
        ]);
    }

    // ========================= Helper Functions =========================
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

    private function groupDocumentsByPlantAndCode($plants, $documentsMaster, $documentMappings)
    {
        return collect($plants)->mapWithKeys(function ($plant) use ($documentMappings, $documentsMaster) {

            $mappingsByPlant = $documentMappings->filter(function ($item) use ($plant) {

                // 1️⃣ Jika ada Part Number → pakai plant dari Part Number
                if ($item->partNumber) {
                    return strtolower($item->partNumber->plant ?? '') === strtolower($plant);
                }

                // 2️⃣ Jika tidak ada Part Number → pakai plant dari Model
                if ($item->productModel) {
                    return strtolower($item->productModel->plant ?? '') === strtolower($plant);
                }

                return false;
            });

            // Group berdasarkan document code
            $groupedDocs = $mappingsByPlant->groupBy(fn($item) => $item->document?->code ?? 'No Code');

            // Pastikan semua kode dokumen master tetap ada (meskipun kosong)
            $orderedGrouped = collect();
            $documentsMaster->each(
                fn($doc) =>
                $orderedGrouped->put($doc->code, $groupedDocs->get($doc->code, collect()))
            );

            return [$plant => $orderedGrouped];
        });
    }



    private function getDocumentsGroupedByPlantAndCode()
    {
        $plants = $this->getEnumValues('tm_part_numbers', 'plant');
        $documentsMaster = Document::where('type', 'review')->get();
        $documentMappings = DocumentMapping::with([
            'document',
            'files',
            'partNumber.product',
            'partNumber.productModel',
            'partNumber.process',
            'user',
            'status',
            'department'
        ])->whereHas('document', fn($q) => $q->where('type', 'review'))->get();

        // Tambahkan URL file
        $documentMappings->each(function ($mapping) {
            $mapping->files->transform(function ($file) {
                if (is_object($file) && isset($file->file_path)) {
                    $file->url = asset('storage/' . $file->file_path);
                } else {
                    $file->url = null;
                }
                return $file;
            });
        });

        return $this->groupDocumentsByPlantAndCode($plants, $documentsMaster, $documentMappings);
    }

    public function revise(Request $request, $id)
    {
        auth()->user()->department_id;
        $mapping = DocumentMapping::with('document')->findOrFail($id);
        if (!in_array(Auth::user()->role->name, ['User', 'Admin'])) {
            abort(403);
        }

        $request->validate([
            'files.*' => 'nullable|file|mimes:pdf,docx|max:10240',
            'notes' => 'required|string|max:500',
        ]);

        $mapping->load('document');

        // Jika file ada yg diupload, proses simpan file baru dan hapus file lama
        $files = $request->file('files', []);

        foreach ($files as $fileId => $uploadedFile) {
            if (!$uploadedFile) continue;

            $oldFile = $mapping->files()->where('id', $fileId)->first();
            if (!$oldFile) continue;

            // Hapus file lama jika ada
            if ($oldFile->file_path && Storage::disk('public')->exists($oldFile->file_path)) {
                Storage::disk('public')->delete($oldFile->file_path);
            }

            // Simpan file baru
            $folder = $mapping->document && $mapping->document->type === 'control'
                ? 'document-controls'
                : 'document-reviews';

            $filename = $mapping->document_number . '_rev_' . time() . "_{$fileId}." . $uploadedFile->getClientOriginalExtension();
            $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

            $oldFile->update([
                'file_path' => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type' => $uploadedFile->getClientMimeType(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        // Update notes
        $mapping->notes = $request->notes;

        // Setelah update file dan notes
        // $needReviewStatus = Status::where('name', 'Need Review')->first();
        // $mapping->status_id = $needReviewStatus->id;
        $needReviewStatus = Status::where('name', 'Need Review')->first();
        if (!$needReviewStatus) {
            throw new \Exception("Status 'Need Review' tidak ditemukan.");
        }
        $mapping->status_id = $needReviewStatus->id;


        $mapping->user_id = Auth::id();
        if (!$mapping->document_id) {
            throw new \Exception("Missing document_id on this document mapping.");
        }

        $mapping->save();

        $allUsers = \App\Models\User::all();
        Notification::send($allUsers, new DocumentRevisedNotification($mapping->document_number, auth()->user()->name));


        return redirect()->back()->with('success', 'Document revised successfully!');
    }

    public function approveWithDates(Request $request, $id)
    {
        $validated = $request->validate([
            'reminder_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:reminder_date',
        ]);

        $mapping = DocumentMapping::with('department')->findOrFail($id);
        $approvedStatus = Status::where('name', 'Approved')->first();

        $mapping->timestamps = false;
        $mapping->updateQuietly([
            'status_id'     => $approvedStatus->id ?? $mapping->status_id,
            'reminder_date' => $validated['reminder_date'],
            'deadline'      => $validated['deadline'],
            'user_id'       => auth()->id(),
        ]);
        $mapping->timestamps = true;

        // Hanya user departemen terkait (kecuali Admin/Super Admin)
        $targetUsers = User::where('department_id', $mapping->department_id)
            ->whereNotIn('role_id', function ($query) {
                $query->select('id')->from('tm_roles')->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->get();

        $url = route('document-review.showFolder', [
            'plant' => $mapping->partNumber->plant ?? 'unknown',
            'docCode' => base64_encode($mapping->document->code ?? ''),
        ]);

        Notification::send($targetUsers, new DocumentActionNotification(
            action: 'approved',
            byUser: auth()->user()->name,
            documentNumber: $mapping->document_number, // Hanya document_number
            url: $url,
            departmentName: $mapping->department?->name,
        ));

        return redirect()->route('document-review.index')
            ->with('success', "Document '{$mapping->document_number}' approved successfully!");
    }

    public function reject(Request $request, $id)
    {
        $mapping = DocumentMapping::with('department')->findOrFail($id);
        $rejectedStatus = Status::where('name', 'Rejected')->first();

        $mapping->timestamps = false;
        $mapping->updateQuietly([
            'status_id' => $rejectedStatus->id ?? $mapping->status_id,
            'user_id' => auth()->id(),
        ]);
        $mapping->timestamps = true;

        $targetUsers = User::where('department_id', $mapping->department_id)
            ->whereNotIn('role_id', function ($query) {
                $query->select('id')->from('tm_roles')->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->get();

        $url = route('document-review.showFolder', [
            'plant' => $mapping->partNumber->plant ?? 'unknown',
            'docCode' => base64_encode($mapping->document->code ?? ''),
        ]);

        Notification::send($targetUsers, new DocumentActionNotification(
            action: 'rejected',
            byUser: auth()->user()->name,
            documentNumber: $mapping->document_number, // Hanya document_number
            url: $url,
            departmentName: $mapping->department?->name,
        ));

        return redirect()->route('document-review.index')
            ->with('success', "Document '{$mapping->document_number}' has been rejected.");
    }
}
