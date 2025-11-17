<?php

namespace App\Http\Controllers;

use App\Models\{Department, Document, DocumentMapping, PartNumber, Process, ProductModel, Status, User};
use App\Notifications\{DocumentRevisedNotification, DocumentStatusNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Storage};

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $plants = $this->getEnumValues('tm_part_numbers', 'plant');
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

    //     public function showFolder($plant, $docCode, Request $request)
    // {
    //     // 1) decode base64 (karena di blade kamu pakai base64_encode)
    //     $docCode = base64_decode($docCode);

    //     // 2) ambil grouped data (Collection)
    //     $documentsByCode = $this->getDocumentsGroupedByPlantAndCode();

    //     // 3) ambil group untuk plant (bisa Collection atau null)
    //     $plantGroup = $documentsByCode->get($plant) ?? collect();

    //     // debug keys (pilih uncomment kalau butuh)
    //     // dd($plantGroup->keys()->toArray());

    //     // 4) normalisasi docCode untuk pencocokan (case-insensitive + trim)
    //     $normalizedDocCode = trim(strtolower($docCode));

    //     // 5) cari kode yang match pada keys dari plantGroup
    //     $matchedCode = $plantGroup->keys()->first(function ($code) use ($normalizedDocCode) {
    //         return trim(strtolower($code)) === $normalizedDocCode;
    //     });

    //     if (!$matchedCode) {
    //         abort(404);
    //     }

    //     // 6) ambil documents berdasarkan matchedCode
    //     $documents = $plantGroup->get($matchedCode, collect());

    //     // 7) search/filter (sama seperti sebelumnya)
    //     if ($search = strtolower($request->query('q'))) {
    //         $documents = $documents->filter(function ($doc) use ($search) {
    //             return str_contains(strtolower($doc->document_number ?? ''), $search)
    //                 || str_contains(strtolower($doc->notes ?? ''), $search)
    //                 || str_contains(strtolower($doc->document?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->document?->code ?? ''), $search)
    //                 || str_contains(strtolower($doc->user?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->status?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->partNumber?->part_number ?? ''), $search)
    //                 || str_contains(strtolower($doc->partNumber?->product?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->product?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->product?->code ?? ''), $search)
    //                 || str_contains(strtolower($doc->partNumber?->productModel?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->productModel?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->partNumber?->process?->name ?? ''), $search)
    //                 || str_contains(strtolower($doc->partNumber?->process?->code ?? ''), $search)
    //                 || str_contains(strtolower($doc->process?->name ?? ''), $search);
    //         });
    //     }

    //     $documents = $documents->filter(function ($doc) use ($request) {
    //         if ($request->filter_mode === 'independent') {
    //             // ✅ Independen: masing-masing filter bebas
    //             $matchPartNumber = !$request->part_number || ($doc->partNumber?->part_number === $request->part_number);
    //             $matchModel      = !$request->model || ($doc->partNumber?->productModel?->name === $request->model || $doc->productModel?->name === $request->model);
    //             $matchProcess    = !$request->process || ($doc->partNumber?->process?->name === $request->process || $doc->process?->name === $request->process);
    //             $matchProduct    = !$request->product || ($doc->partNumber?->product?->name === $request->product || $doc->product?->name === $request->product);

    //             return $matchPartNumber && $matchModel && $matchProcess && $matchProduct;
    //         } else {
    //             // ✅ Terikat / dependent: filter lain hanya relevan jika part number dipilih
    //             $matchPartNumber = !$request->part_number || ($doc->partNumber?->part_number === $request->part_number);
    //             $matchModel      = !$request->part_number || !$request->model || ($doc->partNumber?->productModel?->name === $request->model);
    //             $matchProcess    = !$request->part_number || !$request->process || ($doc->partNumber?->process?->name === $request->process);
    //             $matchProduct    = !$request->part_number || !$request->product || ($doc->partNumber?->product?->name === $request->product);

    //             return $matchPartNumber && $matchModel && $matchProcess && $matchProduct;
    //         }
    //     });

    //     // Pass matchedCode instead of original decoded docCode (lebih akurat untuk view)
    //     return view('contents.document-review.partials.folder', [
    //         'plant' => $plant,
    //         'docCode' => $matchedCode,
    //         'documents' => $documents,
    //     ]);
    // }

public function showFolder($plant, $docCode, Request $request)
{
    $docCode = base64_decode($docCode);

    // Ambil grouped documents
    $documentsByCode = $this->getDocumentsGroupedByPlantAndCode();
    $plantGroup = $documentsByCode->get($plant) ?? collect();

    $normalizedDocCode = trim(strtolower($docCode));
    $matchedCode = $plantGroup->keys()->first(fn($code) => trim(strtolower($code)) === $normalizedDocCode);

    if (!$matchedCode) abort(404);

    $documents = $plantGroup->get($matchedCode, collect());

    // --- Filtering sesuai request (independent/dependent) ---
    $documents = $documents->filter(function ($doc) use ($request) {
        if ($request->filter_mode === 'independent') {
            $matchPartNumber = !$request->part_number || ($doc->partNumber?->part_number === $request->part_number);
            $matchModel      = !$request->model || ($doc->partNumber?->productModel?->name === $request->model || $doc->productModel?->name === $request->model);
            $matchProcess    = !$request->process || ($doc->partNumber?->process?->name === $request->process || $doc->process?->name === $request->process);
            $matchProduct    = !$request->product || ($doc->partNumber?->product?->name === $request->product || $doc->product?->name === $request->product);
            return $matchPartNumber && $matchModel && $matchProcess && $matchProduct;
        } else {
            $matchPartNumber = !$request->part_number || ($doc->partNumber?->part_number === $request->part_number);
            $matchModel      = !$request->part_number || !$request->model || ($doc->partNumber?->productModel?->name === $request->model);
            $matchProcess    = !$request->part_number || !$request->process || ($doc->partNumber?->process?->name === $request->process);
            $matchProduct    = !$request->part_number || !$request->product || ($doc->partNumber?->product?->name === $request->product);
            return $matchPartNumber && $matchModel && $matchProcess && $matchProduct;
        }
    });

    // --- Ambil list untuk filter dropdown ---
    $partNumbers = $documents->pluck('partNumber.part_number')->unique()->filter()->values();
    $models      = $documents->pluck('partNumber.productModel.name')->unique()->filter()->values();
    $processes   = $documents->pluck('partNumber.process.name')->unique()->filter()->values();
    $products    = $documents->pluck('partNumber.product.name')->unique()->filter()->values();

    return view('contents.document-review.partials.folder', [
        'plant'       => $plant,
        'docCode'     => $matchedCode,
        'documents'   => $documents,
        'partNumbers' => $partNumbers,
        'models'      => $models,
        'processes'   => $processes,
        'products'    => $products,
    ]);
}

    public function liveSearch(Request $request)
    {
        $keyword = $request->keyword;

        $documentMappings = DocumentMapping::with([
            'document',
            'partNumber.product',
            'partNumber.productModel',
            'partNumber.process',
            'user',
            'status',
            'files'
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->when($keyword, fn($q) => $q->where(function ($q) use ($keyword) {
                $q->whereHas(
                    'partNumber',
                    fn($q2) =>
                    $q2->where('part_number', 'like', "%{$keyword}%")
                        ->orWhere('plant', 'like', "%{$keyword}%")
                        ->orWhereHas(
                            'process',
                            fn($q3) =>
                            $q3->where('name', 'like', "%{$keyword}%")
                                ->orWhere('code', 'like', "%{$keyword}%")
                        )
                )
                    ->orWhereHas(
                        'partNumber.productModel',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$keyword}%")
                    )
                    ->orWhereHas(
                        'document',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$keyword}%")
                    )
                    ->orWhereHas(
                        'status',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$keyword}%")
                    )
                    ->orWhereHas(
                        'user',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$keyword}%")
                    )
                    ->orWhere('notes', 'like', "%{$keyword}%")
                    ->orWhere('document_number', 'like', "%{$keyword}%");
            }))
            ->get();

        $groupedData = $documentMappings->groupBy(
            fn($item) => ($item->partNumber?->part_number ?? 'unknown') . '-' .
                ($item->partNumber?->productModel?->name ?? 'unknown') . '-' .
                ($item->partNumber?->process?->code ?? 'unknown')
        );

        return view('contents.document-review.partials.table', compact('groupedData'))->render();
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
        // Validasi input
        $validated = $request->validate([
            'reminder_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:reminder_date',
        ]);

        $mapping = DocumentMapping::findOrFail($id);

        $approvedStatus = Status::where('name', 'Approved')->first();

        $mapping->update([
            'status_id'     => $approvedStatus->id ?? $mapping->status_id,
            'reminder_date' => $validated['reminder_date'],
            'deadline'      => $validated['deadline'],
            'updated_at'    => now(),
            'user_id'       => auth()->id(),
        ]);

        // Kirim notifikasi ke semua user
        $allUsers = User::all();
        Notification::send($allUsers, new DocumentStatusNotification(
            $mapping->document_number,
            'Approved',
            auth()->user()->name
        ));

        // ✅ (Opsional) Catat log activity
        // ActivityLog::create([
        //     'user_id' => auth()->id(),
        //     'action' => 'Approved document',
        //     'document_mapping_id' => $mapping->id,
        //     'details' => json_encode($validated),
        // ]);

        return redirect()->route('document-review.index')
            ->with('success', "Document '{$mapping->document_number}' approved successfully!");
    }

    public function reject(Request $request, $id)
    {
        $mapping = DocumentMapping::findOrFail($id);

        $rejectedStatus = Status::where('name', 'Rejected')->first();

        $mapping->update([
            'status_id' => $rejectedStatus->id ?? $mapping->status_id,
            'updated_at' => now(),
            'user_id' => auth()->id(),
        ]);

        // Kirim notifikasi ke semua user
        $allUsers = User::all();
        Notification::send($allUsers, new DocumentStatusNotification(
            $mapping->document_number,
            'Rejected',
            auth()->user()->name
        ));

        return redirect()->route('document-review.index')
            ->with('success', "Document '{$mapping->document_number}' has been rejected.");
    }
}
