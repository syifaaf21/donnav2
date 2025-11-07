<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use App\Models\Process;
use App\Models\ProductModel;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentRevisedNotification;
use App\Notifications\DocumentStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $plants = $this->getEnumValues('tm_part_numbers', 'plant');
        $processes = \App\Models\Process::pluck('name', 'id');

        $documentsMaster = Document::with('childrenRecursive')
            ->where('type', 'review')
            ->get();

        $departments = Department::whereIn('plant', ['body', 'unit', 'electric'])->get();
        $partNumbers = PartNumber::all();
        $models = ProductModel::all();

        $documentMappings = DocumentMapping::with([
            'document',
            'files',
            'partNumber.product',
            'partNumber.productModel',
            'partNumber.process',
            'user',
            'status',
            'department',
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->when($request->plant, fn($q, $plant) => $q->whereHas('partNumber', fn($q2) => $q2->where('plant', $plant)))
            ->when($request->department, fn($q, $dept) => $q->where('department_id', $dept))
            ->when($request->document_id, fn($q, $docId) => $q->where('document_id', $docId))
            ->when($request->part_number, fn($q, $partId) => $q->where('part_number_id', $partId))
            ->when(
                $request->process,
                fn($q, $procId) =>
                $q->whereHas('partNumber.process', fn($q2) => $q2->where('id', $procId))
            )

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
                    $process = $item->partNumber?->process?->name ?? 'unknown';
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
    public function getFiltersByPlant(Request $request)
    {
        $plant = $request->get('plant');

        // Departments
        $departments = $plant
            ? Department::where('plant', $plant)->orderBy('name')->get(['id', 'name'])
            : Department::whereIn('plant', ['body', 'unit', 'electric'])->orderBy('name')->get(['id', 'name']);

        // Part Numbers
        $partNumbers = $plant
            ? PartNumber::where('plant', $plant)->orderBy('part_number')->get(['id', 'part_number'])
            : PartNumber::whereIn('plant', ['body', 'unit', 'electric'])->orderBy('part_number')->get(['id', 'part_number']);

        // Processes → tampilkan berdasarkan plant, tanpa peduli PartNumber
        $processes = $plant
            ? Process::where('plant', $plant)->orderBy('name')->get(['id', 'name'])
            : Process::orderBy('name')->get(['id', 'name']);

        $processes = $processes->map(fn($p) => ['id' => $p->id, 'name' => ucwords($p->name)]);

        return response()->json([
            'departments' => $departments,
            'part_numbers' => $partNumbers,
            'processes' => $processes,
        ]);
    }




    public function liveSearch(Request $request)
    {
        $keyword = $request->keyword;

        $documentMappings = DocumentMapping::with([
            'document',
            'partNumber.product',
            'partNumber.productModel',
            'partNumber.process', // pastikan relasi ini ikut di-load
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
                            ->orWhereHas('process', function ($q3) use ($keyword) {
                                $q3->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('code', 'like', "%{$keyword}%");
                            });
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
            $process = $item->partNumber?->process?->code ?? 'unknown';
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

    public function show($id)
    {
        $document = Document::with('childrenRecursive')->findOrFail($id);

        return view('contents.document-review.show', compact('document'));
    }

    public function revise(Request $request, $id)
    {
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
