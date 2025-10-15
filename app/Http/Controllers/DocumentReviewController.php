<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use App\Models\ProductModel;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentReviewController extends Controller
{
    public function index(Request $request)
{
    $plants = $this->getEnumValues('part_numbers', 'plant');
    $processes = $this->getEnumValues('part_numbers', 'process');

    $documentsMaster = Document::with('childrenRecursive')
        ->where('type', 'review')
        ->get();

    $departments = Department::all();
    $partNumbers = PartNumber::all();
    $models = ProductModel::all();

    $documentMappings = DocumentMapping::with([
        'document',
        'files',
        'partNumber.product',
        'partNumber.productModel',
        'user',
        'status',
        'department',
    ])
    ->whereHas('document', fn($q) => $q->where('type', 'review'))
    ->get();

    // Tambahkan properti url ke setiap file
    $documentMappings->each(function ($mapping) {
        $mapping->files->transform(function ($file) {
            $file->url = asset('storage/' . $file->file_path);
            return $file;
        });
    });

    $groupedByPlant = $documentMappings
        ->groupBy(fn($item) => $item->partNumber?->plant ?? 'Unknown')
        ->map(function ($items) {
            return $items->groupBy(function ($item) {
                $partNumber = $item->partNumber?->part_number ?? 'unknown';
                $model = $item->partNumber?->productModel?->name ?? 'unknown';
                $process = $item->partNumber?->process ?? 'unknown';
                return "{$partNumber}-{$model}-{$process}";
            });
        });

    return view('contents.document-review.index', compact(
        'plants',
        'processes',
        'departments',
        'partNumbers',
        'models',
        'documentsMaster',
        'groupedByPlant'
    ));
}

    public function liveSearch(Request $request)
    {
        $keyword = $request->keyword;

        $documentMappings = DocumentMapping::with([
            'document',
            'partNumber.product',
            'partNumber.productModel',
            'user',
            'status',
            'files'
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review')) // pastikan hanya review
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('partNumber', function ($q2) use ($keyword) {
                        $q2->where('part_number', 'like', "%{$keyword}%")
                            ->orWhere('plant', 'like', "%{$keyword}%")
                            ->orWhere('process', 'like', "%{$keyword}%");
                    })
                        ->orWhereHas('partNumber.productModel', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('document', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('status', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('user', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhere('notes', 'like', "%{$keyword}%")
                        ->orWhere('document_number', 'like', "%{$keyword}%");
                });
            })
            ->get();

        // group hasil by part-model-process (sama format seperti index partial expects)
        $groupedData = $documentMappings->groupBy(function ($item) {
            $partNumber = $item->partNumber?->part_number ?? 'unknown';
            $model = $item->partNumber?->productModel?->name ?? 'unknown';
            $process = $item->partNumber?->process ?? 'unknown';
            return "{$partNumber}-{$model}-{$process}";
        });

        // render partial yang hanya menerima $groupedData
        return view('contents.document-review.partials.table', compact('groupedData'))->render();
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

        $partNumbers = PartNumber::where('plant', $plant)
            ->orderBy('part_number')
            ->get(['id', 'part_number']);

        Log::info('Plant selected: ' . $plant);

        $processes = PartNumber::where('plant', $plant)
            ->select('process')
            ->distinct()
            ->pluck('process');

        return response()->json([
            'part_numbers' => $partNumbers,
            'processes' => $processes,
        ]);
    }

    public function show($id)
    {
        $document = Document::with('childrenRecursive')->findOrFail($id);

        return view('contents.document-review.show', compact('document'));
    }

    public function revise(Request $request, DocumentMapping $mapping)
{
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

    // Kalau user bukan admin dan statusnya bukan approved, ubah jadi Need Review
    if (Auth::user()->role->name !== 'Admin') {
        $approvedStatus = Status::where('name', 'Approved')->first();
        $needReviewStatus = Status::where('name', 'Need Review')->first();

        if ($mapping->status_id != $approvedStatus->id) {
            $mapping->status_id = $needReviewStatus->id;
        }
    }

    $mapping->user_id = Auth::id();
    $mapping->save();

    return redirect()->back()->with('success', 'Document revised successfully!');
}

    public function approveWithDates(Request $request, $id)
    {
        // ✅ Validasi input
        $validated = $request->validate([
            'reminder_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:reminder_date',
        ]);

        // ✅ Ambil data mapping berdasarkan ID
        $mapping = DocumentMapping::findOrFail($id);

        // ✅ Update status dan tanggal
        $mapping->update([
            'status_id'     => Status::where('name', 'Approved')->first()->id ?? $mapping->status_id,
            'reminder_date' => $validated['reminder_date'],
            'deadline'      => $validated['deadline'],
            'updated_at'    => now(),
            'user_id'       => auth()->id(), // siapa yang approve
        ]);

        // ✅ (Opsional) Catat log activity
        // ActivityLog::create([
        //     'user_id' => auth()->id(),
        //     'action' => 'Approved document',
        //     'document_mapping_id' => $mapping->id,
        //     'details' => json_encode($validated),
        // ]);

        // ✅ Redirect dengan pesan sukses
        return redirect()->route('document-review.index')
            ->with('success', "Document '{$mapping->document_number}' approved successfully!");
    }

    public function reject(Request $request, $id)
{
    $mapping = DocumentMapping::findOrFail($id);

    $mapping->update([
        'status_id' => Status::where('name', 'Rejected')->first()->id ?? $mapping->status_id,
        'updated_at' => now(),
        'user_id' => auth()->id(),
    ]);

    return redirect()->route('document-review.index')
        ->with('success', "Document '{$mapping->document_number}' has been rejected.");
}

}
