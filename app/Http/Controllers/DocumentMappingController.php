<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentUpdatedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class DocumentMappingController extends Controller
{
    // ================= Document Review Index =================
    public function reviewIndex(Request $request)
    {
        $documentsMaster = Document::with('childrenRecursive')->where('type', 'review')->get();
        $partNumbers = PartNumber::all();
        $statuses = Status::all();
        $departments = Department::all();

        $plants = PartNumber::pluck('plant')->map(fn($p) => ucfirst(strtolower($p)))->unique();

        $groupedByPlant = [];

        foreach ($plants as $plant) {
            // di dalam foreach ($plants as $plant) { ... }
            $query = DocumentMapping::with([
                'document.parent',
                'document.children',
                'department',
                'partNumber',
                'status',
                'user',
                'files',
                'parent',
            ])
                ->whereHas('document', function ($q) {
                    $q->where('type', 'review');
                })
                ->whereHas('partNumber', function ($q) use ($plant) {
                    $q->whereRaw('LOWER(plant) = ?', [strtolower($plant)]);
                });

            // Search by part number (tetap)
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_number', 'like', "%{$search}%")
                        ->orWhereHas('partNumber', function ($q2) use ($search) {
                            $q2->where('part_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('document', function ($q2) use ($search) {
                            $q2->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"]);
                        })
                        ->orWhereHas('department', function ($q2) use ($search) {
                            $q2->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"]);
                        });
                });
            }

            // ----- NEW: filter by document mapping (document_id) -----
            if ($documentId = $request->get('document_id')) {
                $query->where('document_id', $documentId);
            }

            // status filter (existing)
            if ($status = $request->get('status')) {
                $query->whereHas(
                    'status',
                    fn($q) =>
                    $q->whereRaw('LOWER(name) = ?', [strtolower($status)])
                );
            }

            // department filter (existing - applied on mapping)
            if ($department = $request->get('department')) {
                $query->where('department_id', $department);
            }

            if ($deadline = $request->get('deadline')) {
                $query->whereDate('deadline', $deadline);
            }

            $groupedByPlant[$plant] = $query
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $documentMappings = collect();

        foreach ($groupedByPlant as $plantMappings) {
            $documentMappings = $documentMappings->merge($plantMappings);
        }

        $existingDocuments = DocumentMapping::with([
            'document',
            'parent', // 🔹 ini juga perlu
            'partNumber.product',
            'partNumber.process',
            'partNumber.productModel',
        ])
            ->whereHas('partNumber', function ($q) use ($plant) {
                $q->whereRaw('LOWER(plant) = ?', [strtolower($plant)]);
            })
            ->get();

        return view('contents.master.document-review.index', compact(
            'groupedByPlant',
            'documentsMaster',
            'partNumbers',
            'statuses',
            'departments',
            'documentMappings',
            'existingDocuments',
        ));
    }

    // ================= Store Review (Admin) =================
    public function storeReview(Request $request)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'document_id' => 'required|exists:tm_documents,id',
            'document_number' => 'required|string|max:255',
            'part_number_id' => 'required|exists:tm_part_numbers,id',
            'department_id' => 'required|exists:tm_departments,id',
            'notes' => 'nullable|string|max:500',
            'files' => 'required',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx',
        ], [
            'files.*.mimes' => 'Only PDF, Word, or Excel files are allowed.',
        ]);

        // Cek existing document_number
        $existing = DocumentMapping::where('document_number', $request->document_number)->exists();
        if ($existing) {
            return redirect()->back()->withErrors([
                'document_number' => 'Document number already exists.'
            ])->withInput();
        }

        // 3. Validasi kecocokan parent document dengan part number
        if ($request->filled('parent_id')) {
            $parent = DocumentMapping::find($request->parent_id);

            if (!$parent) {
                return redirect()->back()->withErrors([
                    'parent_id' => 'Parent Document tidak ditemukan.'
                ])->withInput();
            }

            if ($parent->part_number_id != $request->part_number_id) {
                return redirect()->back()->withErrors([
                    'parent_id' => 'Parent Document tidak cocok dengan Part Number yang dipilih.'
                ])->withInput();
            }
        }

        // Simpan DocumentMapping dulu
        $mapping = DocumentMapping::create([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'parent_id' => $request->parent_id,
            'part_number_id' => $request->part_number_id,
            'department_id' => $request->department_id,
            'reminder_date' => null,
            'deadline' => null,
            'obsolete_date' => null,
            'status_id' => Status::where('name', 'Need Review')->first()->id,
            'notes' => $request->notes ?? '',
            'user_id' => Auth::id(),
        ]);

        // Simpan file seperti di storeControl
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $request->document_number . '_v' . $mapping->version . '_' . time() . '_' . $index . '.' . $extension;

                $path = $file->storeAs('document-reviews', $filename, 'public');

                $mapping->files()->create([
                    'document_id' => $mapping->document_id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Document review created!');
    }

    public function generateDocumentNumber(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:tm_documents,id',
            'department_id' => 'required|exists:tm_departments,id',
            'part_number_id' => 'required|exists:tm_part_numbers,id',
        ]);

        $document = Document::findOrFail($request->document_id);
        $department = Department::findOrFail($request->department_id);
        $partNumber = PartNumber::with(['product', 'productModel', 'process'])->findOrFail($request->part_number_id);

        $docCode = $document->code;
        $deptCode = $department->code;
        $productCode = $partNumber->product->code ?? '';
        $processCode = $partNumber->process->code ?? '';
        $modelName = $partNumber->productModel->name ?? '';

        // ========================
        // Nomor urut berdasarkan kombinasi
        // ========================
        $existingCount = DocumentMapping::where('document_id', $document->id)
            ->where('department_id', $department->id)
            ->whereHas('partNumber', function ($q) use ($partNumber) {
                $q->where('product_id', $partNumber->product_id)
                    ->where('process_id', $partNumber->process_id)
                    ->where('model_id', $partNumber->model_id);
            })
            ->count();

        $noUrut = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
        $noRevisi = '01';

        $generatedNumber = "{$docCode}-{$deptCode}-{$productCode}_{$processCode}_{$modelName}-{$noUrut}-{$noRevisi}";

        return response()->json([
            'document_number' => $generatedNumber
        ]);
    }

    public function generateChildDocumentNumber(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:tt_document_mappings,id',
            'document_id' => 'required|exists:tm_documents,id',
            'department_id' => 'required|exists:tm_departments,id',
            'part_number_id' => 'required|exists:tm_part_numbers,id',
        ]);

        $parent = DocumentMapping::with('document')->findOrFail($request->parent_id);
        $document = Document::findOrFail($request->document_id);
        $department = Department::findOrFail($request->department_id);

        $parentParts = explode('-', $parent->document_number);

        if (count($parentParts) < 5) {
            return response()->json(['error' => 'Invalid parent document number format'], 400);
        }

        // Ambil bagian belakang setelah kode departemen → HDL_AS_660A-001-01
        $tail = implode('-', array_slice($parentParts, 2));

        // Ganti kode dokumen dan departemen
        $newNumber = $document->code . '-' . $department->code . '-' . $tail;

        $exists = DocumentMapping::where('parent_id', $request->parent_id)
            ->where('document_id', $request->document_id)
            ->where('part_number_id', $request->part_number_id)
            ->where('department_id', $request->department_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'document_number' => null,
                'message' => 'Document child sudah pernah dibuat untuk parent ini.'
            ], 409); // atau 200 kalau kamu cuma mau disable tombol simpan
        }

        return response()->json([
            'document_number' => $newNumber
        ]);
    }

    public function searchParentDocuments(Request $request)
    {
        $query = DocumentMapping::with('partNumber.product', 'partNumber.process', 'partNumber.productModel', 'document', 'department');

        if ($request->filled('q')) {
            $q = strtolower($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->whereRaw('LOWER(document_number) LIKE ?', ["%{$q}%"]);
            });
        }

        // Jika ada filter plant dari TomSelect
        if ($request->filled('plant')) {
            $query->whereHas('partNumber', function ($q2) use ($request) {
                $q2->whereRaw('LOWER(plant) = ?', [strtolower($request->plant)]);
            });
        }

        $parents = $query->limit(20)->get();

        return response()->json($parents->map(function ($doc) {
            return [
                'value' => $doc->id,
                'text' => $doc->document_number,
            ];
        }));
    }

    public function getDepartmentsByDocumentAndPlant(Request $request)
    {
        $documentId = $request->input('document_id');
        $plant = $request->input('plant');

        if (!$documentId || !$plant) {
            return response()->json(['departments' => []]);
        }

        $document = Document::find($documentId);
        if (!$document) {
            return response()->json(['departments' => []]);
        }

        // Mapping document name ke kode department
        $documentToDeptCode = [
            'FMEA' => 'ENG',
            'QCPC' => 'ENG',
            'Q-COMPO' => 'ENG',
            'C/S PARAMETER' => 'ENG',

            'QCWIS' => 'QAS',
            'C/S QCWIS' => 'QAS',
            'PIS' => 'QAS',

            'C/S PRD' => 'PRD',
            'WIS' => 'PRD',
        ];

        // Normalisasi nama document dari DB
        $docCodeRaw = $document->code;
        $docCode = strtoupper(trim($docCodeRaw));

        // Ubah keys mapping juga jadi uppercase agar case-insensitive
        $documentToDeptCodeUpper = [];
        foreach ($documentToDeptCode as $key => $value) {
            $documentToDeptCodeUpper[strtoupper($key)] = $value;
        }

        $deptCode = $documentToDeptCodeUpper[$docCode] ?? null;

        if (!$deptCode) {
            return response()->json(['departments' => []]);
        }

        // Filter department berdasarkan kode dan plant (pastikan kolom plant ada di tabel department)
        $departments = Department::where('code', $deptCode)
            ->where('plant', $plant)
            ->get();

        return response()->json(['departments' => $departments]);
    }

    // ================= Update Review (Admin) =================
    public function updateReview(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $request->validate([
            'document_id' => 'required|exists:tm_documents,id',
            'document_number' => 'required|string|max:255',
            'part_number_id' => 'required|exists:tm_part_numbers,id',
            'department_id' => 'required|exists:tm_departments,id',
            'notes' => 'nullable|string|max:500',
            'reminder_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx',
        ], [
            'files.*.mimes' => 'Only PDF, Word, or Excel files are allowed.',
        ]);

        // Update metadata
        $mapping->timestamps = false;
        $mapping->update([
            'document_id' => $request->document_id,
            'document_number' => $request->document_number,
            'part_number_id' => $request->part_number_id,
            'parent_id' => $request->parent_id,
            'department_id' => $request->department_id,
            'notes' => $request->notes ?? $mapping->notes,
            'reminder_date' => optional($mapping->status)->name === 'approved'
                ? $request->reminder_date
                : $mapping->reminder_date,
            'deadline' => optional($mapping->status)->name === 'approved'
                ? $request->deadline
                : $mapping->deadline,
        ]);
        $mapping->timestamps = true;

        // Upload files baru jika ada
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $request->document_number . '_v' . $mapping->version . '_' . time() . '_' . $index . '.' . $extension;

                $path = $file->storeAs('document-reviews', $filename, 'public');

                $mapping->files()->create([
                    'document_id' => $mapping->document_id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        $mapping = DocumentMapping::with([
            'document',
            'department',
            'partNumber',
            'parent',
            'files'
        ])->find($mapping->id);


        // Notify users
        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\DocumentUpdatedNotification(
                $mapping->document_number,
                Auth::user()->name,
                'Review'
            ));
        }

        return redirect()->back()->with('success', 'Document updated successfully!');
    }

    // ================= Delete Review (Admin) =================
    public function destroy(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin')
            abort(403);

        // Hapus semua file yang berelasi
        foreach ($mapping->files as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete(); // Hapus record dari tabel document_files
        }

        // Hapus file utama (jika ada)
        if ($mapping->file_path && Storage::disk('public')->exists($mapping->file_path)) {
            Storage::disk('public')->delete($mapping->file_path);
        }

        $mapping->delete();

        return redirect()->back()->with('success', 'Document and associated files deleted successfully!');
    }

    // ================= Approve / Reject Review (Admin) =================
    public function approveWithDates(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin')
            abort(403);

        $request->validate([
            'reminder_date' => 'required|date|before_or_equal:deadline',
            'deadline' => 'required|date|after_or_equal:reminder_date',
        ]);

        $statusApproved = Status::where('name', 'approved')->first();
        if (!$statusApproved) {
            return redirect()->back()->with('error', 'Status "approved" not found!');
        }

        $mapping->update([
            'status_id' => $statusApproved->id,
            'reminder_date' => $request->reminder_date,
            'deadline' => $request->deadline,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document approved and dates set successfully!');
    }

    public function reject(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $statusRejected = Status::where('name', 'rejected')->first();

        if (!$statusRejected) {
            return redirect()->back()->with('error', 'Status "rejected" not found!');
        }

        $mapping->update([
            'status_id' => $statusRejected->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document rejected!');
    }

    // Document Controll
    // ================= Document Control Index =================
    public function controlIndex(Request $request)
    {
        $query = DocumentMapping::with(['document', 'department', 'status', 'files'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'));

        // 🔍 Filter by search (document name, number, or department)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', fn($d) => $d->where('name', 'like', "%$search%"))
                    ->orWhereHas('department', fn($d) => $d->where('name', 'like', "%$search%"));
            });
        }

        $documentMappings = $query->get();

        // 🔁 Update status Obsolete otomatis
        $statusObsolete = Status::where('name', 'Obsolete')->first();
        $now = now();

        foreach ($documentMappings as $mapping) {
            if ($mapping->obsolete_date && $mapping->obsolete_date < $now) {
                if ($mapping->status_id !== $statusObsolete?->id) {
                    $mapping->status_id = $statusObsolete?->id;
                    $mapping->save();
                }
            }
        }

        $documents = Document::where('type', 'control')->get();
        $statuses = Status::all();
        $departments = Department::all();
        $files = DocumentFile::all();

        return view('contents.master.document-control.index', compact(
            'documentMappings',
            'documents',
            'statuses',
            'departments',
            'files'
        ));
    }

    // ================= Store Control (Admin) =================
    public function storeControl(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'department' => 'required|exists:tm_departments,id',
            'obsolete_date' => 'required|date',
            'reminder_date' => 'required|date|before_or_equal:obsolete_date',
            'notes' => 'nullable|string',
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx',
        ], [
            'reminder_date.before_or_equal' => 'Reminder Date must be earlier than or equal to Obsolete Date.',
            'files.required' => 'File must be uploaded.',
            'files.array' => 'Invalid file format.',
            'files.*.mimes' => 'Only PDF, Word, or Excel files are allowed.',
        ]);


        $status = Status::where('name', 'Need Review')->first();
        if (!$status) {
            return redirect()->back()->with('error', 'Status "Need Review" not found!');
        }

        // ✅ Bersihkan notes dari <p><br></p>
        $cleanNotes = trim($validated['notes'] ?? '');
        if ($cleanNotes === '<p><br></p>' || $cleanNotes === '') {
            $cleanNotes = null;
        }

        // ✅ Buat record Document baru
        $newDocument = Document::create([
            'name' => $validated['document_name'],
            'parent_id' => null,
            'type' => 'control',
        ]);

        // ✅ Buat DocumentMapping dengan notes yang sudah dibersihkan
        $mapping = DocumentMapping::create([
            'document_id' => $newDocument->id,
            'status_id' => $status->id,
            'obsolete_date' => $validated['obsolete_date'],
            'reminder_date' => $validated['reminder_date'],
            'deadline' => null,
            'user_id' => Auth::id(),
            'department_id' => $validated['department'],
            'version' => 0,
            'notes' => $cleanNotes,
        ]);

        // ✅ Simpan file
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $extension = $file->getClientOriginalExtension();

                $safeName = Str::slug($validated['document_name'], '_');
                $filename = $safeName . '_v' . $mapping->version . '_' . time() . '_' . $index . '.' . $extension;

                $path = $file->storeAs('document-controls', $filename, 'public');

                $mapping->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('master.document-control.index')
            ->with('success', 'Document Control berhasil ditambahkan!');
    }

    // Update Document Control
    public function updateControl(Request $request, DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'document_id' => 'required|exists:tm_documents,id',
            'department_id' => 'required|exists:tm_departments,id',
            'obsolete_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        $mapping->update($validated);

        return redirect()->route('master.document-control.index')->with('success', 'Document Control berhasil diupdate!');
    }

    // Approve Document Control
    public function approveControl(DocumentMapping $mapping)
    {
        if (Auth::user()->role->name != 'Admin') {
            abort(403);
        }

        $statusActive = Status::where('name', 'active')->first();
        if (!$statusActive) {
            return redirect()->back()->with('error', 'Status "active" not found!');
        }

        $mapping->update([
            'status_id' => $statusActive->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('master.document-control.index')->with('success', 'Document Control approved and status set to active!');
    }

    public function bulkDestroy(Request $request)
    {
        // validasi
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tt_document_mappings,id',
        ]);

        $ids = $data['ids'];

        $docs = DocumentMapping::whereIn('id', $ids)->get();
        foreach ($docs as $doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
        }

        // hapus records
        DocumentMapping::whereIn('id', $ids)->delete();

        return redirect()->route('master.document-control.index')
            ->with('success', count($ids) . ' document(s) deleted successfully.');
    }
}
