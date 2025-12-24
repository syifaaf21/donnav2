<?php

namespace App\Http\Controllers;

use App\Models\{Department, Document, DocumentMapping, PartNumber, Process, Product, ProductModel, Status, User};
use App\Notifications\{DocumentRevisedNotification, DocumentStatusNotification, DocumentActionNotification};
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Storage};

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $plants = $this->getEnumValues('tm_part_numbers', 'plant');
        $plants = array_filter($plants, fn($p) => in_array($p, ['Body', 'Unit', 'Electric']));
        // AMBIL SEMUA DOKUMEN DENGAN ANAK-CUCU
        $documents = Document::with('childrenRecursive')->where('type', 'review')->get();

        // ROOT = dokumen yang parent_id nya NULL
        $roots = $documents->where('parent_id', null);

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

        $groupedByPlant = $this->groupDocumentsByPlantAndCode($plants, $documents, $documentMappings);

        return view('contents.document-review.index', compact(
            'plants',
            'documents',
            'groupedByPlant',
            'roots',
        ));
    }

    public function showFolder($plant, $docCode, Request $request)
    {
        $docCode = base64_decode($docCode);

        // Group documents
        $documentsByCode = $this->getDocumentsGroupedByPlantAndCode();
        $plantGroup = $documentsByCode->get($plant) ?? collect();

        $normalizedDocCode = trim(strtolower($docCode));
        $matchedCode = $plantGroup->keys()->first(fn($code) => trim(strtolower($code)) === $normalizedDocCode);

        if (!$matchedCode)
            abort(404);

        $documents = $plantGroup->get($matchedCode, collect());

        // 1. Semua dropdown berdasarkan plant
        $allPartNumbers = PartNumber::where('plant', $plant)->pluck('part_number')->unique()->values();
        $allModels = ProductModel::where('plant', $plant)->pluck('name')->unique()->values();
        $allProcesses = Process::where('plant', $plant)->pluck('name')->unique()->values();
        $allProducts = Product::where('plant', $plant)->pluck('name')->unique()->sort()->values();

        // 2. Filter berdasarkan dropdown (part / model / process / product)
        $documents = $documents->filter(function ($doc) use ($request) {

            // Jika tidak pilih part number → filter bebas
            if (!$request->part_number) {

                $matchProduct = !$request->product || (
                    $doc->product->contains(fn($p) => strtolower(trim($p->name ?? '')) === strtolower(trim($request->product))) ||
                    $doc->partNumber->contains(fn($pn) => strtolower(trim($pn->product?->name ?? '')) === strtolower(trim($request->product)))
                );

                $matchModel = !$request->model || (
                    $doc->productModel->contains(fn($m) => strtolower(trim($m->name ?? '')) === strtolower(trim($request->model))) ||
                    $doc->partNumber->contains(fn($pn) => strtolower(trim($pn->productModel?->name ?? '')) === strtolower(trim($request->model)))
                );

                $matchProcess = !$request->process || (
                    $doc->process->contains(fn($p) => strtolower(trim($p->name ?? '')) === strtolower(trim($request->process))) ||
                    $doc->partNumber->contains(fn($pn) => strtolower(trim($pn->process?->name ?? '')) === strtolower(trim($request->process)))
                );

                return $matchProduct && $matchModel && $matchProcess;
            }

            // Jika part number dipilih → filter ketat
            $matchPartNumber = $doc->partNumber->contains(fn($pn) => $pn->part_number === $request->part_number);
            $matchProduct = !$request->product || $doc->partNumber->contains(fn($pn) => $pn->product?->name === $request->product);
            $matchModel = !$request->model || $doc->partNumber->contains(fn($pn) => $pn->productModel?->name === $request->model);
            $matchProcess = !$request->process || $doc->partNumber->contains(fn($pn) => $pn->process?->name === $request->process);

            return $matchPartNumber && $matchProduct && $matchModel && $matchProcess;
        });


        // 3. Search bar (q)
        // 3. Search bar (q)
        if ($request->filled('q')) {

            $search = strtolower($request->q);

            $documents = $documents->filter(function ($doc) use ($search) {

                // Helper function untuk cek collection
                $containsInCollection = fn($collection, $property) =>
                $collection->contains(
                    fn($item) =>
                    str_contains(strtolower($item?->$property ?? ''), $search)
                );

                return
                    // Dokumen langsung
                    str_contains(strtolower($doc->document_number ?? ''), $search)
                    || str_contains(strtolower($doc->notes ?? ''), $search)
                    || str_contains(strtolower($doc->document?->name ?? ''), $search)
                    || str_contains(strtolower($doc->document?->code ?? ''), $search)
                    || str_contains(strtolower($doc->user?->name ?? ''), $search)
                    || str_contains(strtolower($doc->status?->name ?? ''), $search)

                    // PartNumber
                    || $containsInCollection($doc->partNumber, 'part_number')

                    // Product
                    || $containsInCollection($doc->partNumber->pluck('product'), 'name')
                    || $containsInCollection($doc->product, 'name')
                    || $containsInCollection($doc->product, 'code')

                    // Model
                    || $containsInCollection($doc->partNumber->pluck('productModel'), 'name')
                    || $containsInCollection($doc->productModel, 'name')

                    // Process
                    || $containsInCollection($doc->partNumber->pluck('process'), 'name')
                    || $containsInCollection($doc->partNumber->pluck('process'), 'code')
                    || $containsInCollection($doc->process, 'name');
            });
        }


        // 4. Set dropdown dependent (harus setelah search)
        if ($request->part_number) {

            $related = PartNumber::with(['productModel', 'process', 'product'])
                ->where('part_number', $request->part_number)
                ->where('plant', $plant)
                ->first();

            $models = collect([$related?->productModel?->name])->filter()->values();
            $processes = collect([$related?->process?->name])->filter()->values();
            $products = collect([$related?->product?->name])->filter()->values();
        } else {

            $models = $allModels;
            $processes = $allProcesses;
            $products = $allProducts;
        }

        // 5. Pagination manual (karena Collection)
        $documents = $documents->map(function ($doc) {
            $doc->setRelation(
                'files',
                $doc->files
                    ? $doc->files
                    ->where('is_active', 1)
                    ->where('pending_approval', 0)
                    : collect()
            );
            return $doc;
        });

        // Pagination manual
        $page     = $request->input('page', 1);
        $perPage  = 10;

        $documentsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $documents->forPage($page, $perPage),
            $documents->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        return view('contents.document-review.partials.folder', [
            'plant' => $plant,
            'docCode' => $matchedCode,
            'documents' => $documentsPaginated,
            'partNumbers' => $allPartNumbers,
            'models' => $models,
            'processes' => $processes,
            'products' => $products,
        ]);
    }

    public function getFilters(Request $request)
    {
        $plant = $request->plant;

        // Jika part number tidak dipilih → return full list
        if (!$request->part_number) {
            return response()->json([
                'models' => ProductModel::where('plant', $plant)->pluck('name')->unique()->values(),
                'processes' => Process::where('plant', $plant)->pluck('name')->unique()->values(),
                'products' => Product::where('plant', $plant)->pluck('name')->unique()->values(),
            ]);
        }

        // Ambil detail berdasarkan part number
        $part = PartNumber::with(['productModel', 'process', 'product'])
            ->where('part_number', $request->part_number)
            ->where('plant', $plant)
            ->first();

        if (!$part) {
            return response()->json([
                'models' => [],
                'processes' => [],
                'products' => []
            ]);
        }

        return response()->json([
            'models' => [$part->productModel?->name],
            'processes' => [$part->process?->name],
            'products' => [$part->product?->name],
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

            $plantLower = strtolower($plant);

            // Filter mappings sesuai plant
            $mappingsByPlant = $documentMappings->filter(function ($item) use ($plantLower) {

                $hasPartPlant = $item->partNumber->contains(fn($pn) => strtolower($pn->plant) === $plantLower);
                $hasModelPlant = $item->productModel->contains(fn($model) => strtolower($model->plant) === $plantLower);

                return $hasPartPlant || $hasModelPlant;
            });

            // Ambil semua kode dokumen dari master + mapping (safe)
            $codesFromMaster = $documentsMaster->pluck('code');
            $codesFromMapping = $mappingsByPlant->map(fn($m) => $m->document?->code)->filter();

            $allCodes = $codesFromMaster->merge($codesFromMapping)->unique();

            // Group mapping berdasarkan kode dokumen
            $groupedDocs = $mappingsByPlant->groupBy(fn($item) => $item->document?->code ?? 'No Code');

            // Buat ordered collection agar semua kode tetap muncul
            $orderedGrouped = collect();
            foreach ($allCodes as $code) {
                $orderedGrouped->put($code, $groupedDocs->get($code, collect()));
            }

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
        $mapping = DocumentMapping::with('files', 'document', 'status')->findOrFail($id);

        if (!Auth::check())
            abort(403);

        $request->validate([
            'revision_files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'revision_file_ids.*' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        // Hitung total size semua file baru
        $totalSize = 0;

        if ($request->hasFile('revision_files')) {
            foreach ($request->file('revision_files') as $file) {
                $totalSize += $file->getSize(); // dalam bytes
            }
        }

        // Maksimal total 10 MB = 10 * 1024 * 1024
        if ($totalSize > 10 * 1024 * 1024) {
            return back()
                ->withErrors(['revision_files' => 'Total file upload tidak boleh lebih dari 10 MB'])
                ->withInput();
        }

        $uploadedFiles = $request->file('revision_files', []);
        $oldFileIds = $request->input('revision_file_ids', []);

        $folder = 'document-reviews';

        foreach ($uploadedFiles as $index => $uploadedFile) {

            $oldFileId = $oldFileIds[$index] ?? null;

            // Generate filename revision
            $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();
            $timestamp = now()->format('Ymd_His');

            $existingRevisions = $mapping->files()
                ->where('original_name', 'like', "{$baseName}_rev%")
                ->count();
            $revisionNumber = $existingRevisions + 1;

            $filename = "{$baseName}_rev{$revisionNumber}_{$timestamp}.{$extension}";
            $newPath = $uploadedFile->storeAs($folder, $filename, 'public');

            // Buat file baru
            $newFile = $mapping->files()->create([
                'file_path' => $newPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_type' => $uploadedFile->getClientMimeType(),
                'uploaded_by' => Auth::id(),
                'is_active' => true,
            ]);

            // === PERILAKU SESUAI DocumentControlController ===
            if ($oldFileId) {
                $oldFile = $mapping->files()->find($oldFileId);
                if ($oldFile) {
                    $currentStatus = optional($mapping->status)->name;

                    if ($currentStatus === 'Rejected') {
                        // Dokumen direject → tandai file lama sebagai soft deleted
                        $oldFile->update([
                            'replaced_by_id' => $newFile->id,
                            'pending_approval' => false,
                            'is_active' => false, // supaya tidak muncul di modal
                            'marked_for_deletion_at' => now(),
                        ]);
                    } else {
                        // Revisi normal → file lama masuk archive setelah approve
                        $oldFile->update([
                            'replaced_by_id' => $newFile->id,
                            'pending_approval' => true,
                        ]);
                    }
                }
            }
        }

        // Update mapping
        $needReviewStatus = Status::where('name', 'Need Review')->firstOrFail();

        // --- SEND NOTIFICATION TO ADMINS ---
        $uploader = Auth::user();
        $userRole = strtolower($uploader->roles->pluck('name')->first() ?? '');

        if (!in_array($userRole, ['admin', 'super admin'])) {

            $admins = User::whereHas(
                'roles',
                fn($q) =>
                $q->whereIn('name', ['Admin', 'Super Admin'])
            )->get();

            foreach ($admins as $admin) {

                $admin->notify(new DocumentActionNotification(
                    action: 'revised',
                    byUser: $uploader->name,
                    documentNumber: $mapping->document_number, // ← PAKAI DOCUMENT NUMBER 👍
                    documentName: null,                        // ← DI REVIEW TIDAK DIPAKAI
                    url: route('document-review.showFolder', [
                        'plant' => $this->getPlantFromMapping($mapping),
                        'docCode' => base64_encode($mapping->document->code ?? ''),
                    ]),
                    departmentName: $mapping->department?->name
                ));
            }
        }

        $mapping->update([
            'status_id' => $needReviewStatus->id,
            'notes' => $request->input('notes'),
            'user_id' => Auth::id(),
            'review_notified_at' => null, // reset supaya bisa dikirim lagi setelah approve berikutnya
        ]);

        return back()->with('success', 'Document revised successfully!');
    }

    public function getFiles($id)
    {
        $mapping = DocumentMapping::with(['files' => function ($q) {
            $q->where('is_active', 1)
                ->where('pending_approval', 0)
                ->orderBy('created_at', 'asc'); // opsional: urutkan
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'files' => $mapping->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'file_path' => $file->file_path,
                ];
            }),
        ]);
    }


    public function approveWithDates(Request $request, $id)
    {
        // $validated = $request->validate([
        //     'reminder_date' => 'required|date|after_or_equal:today',
        //     'deadline' => 'required|date|after_or_equal:reminder_date',
        // ]);


        $mapping = DocumentMapping::with(['department', 'files'])->findOrFail($id);
        $approvedStatus = Status::where('name', 'Approved')->firstOrFail();

        // === Update Mapping (Status + Dates) ===
        $mapping->timestamps = false;
        $mapping->updateQuietly([
            'status_id' => $approvedStatus->id,
            'reminder_date' => null,
            'deadline' => null,
            'last_approved_at' => now(),
            'review_notified_at' => null,
        ]);
        $mapping->timestamps = true;

        // Ambil semua file lama yang menunggu approval
        $oldFiles = $mapping->files()->where('pending_approval', true)->get();

        foreach ($oldFiles as $oldFile) {
            $oldFile->update([
                'is_active' => false,                     // nonaktifkan → masuk archive
                'pending_approval' => false,
                'marked_for_deletion_at' => now()->addYear(), // disimpan 1 tahun
            ]);
        }

        // File dengan pending_approval = false → tetap aktif
        // (contoh: revisi setelah status Rejected)

        // ===== Kirim Notifikasi seperti sebelumnya =====
        $targetUsers = User::whereHas(
            'departments',
            fn($q) =>
            $q->where('tm_departments.id', $mapping->department_id)
        )->whereDoesntHave(
            'roles',
            fn($q) =>
            $q->whereIn('name', ['Admin', 'Super Admin'])
        )->get();

        $plant = $this->getPlantFromMapping($mapping);

        $url = route('document-review.showFolder', [
            'plant' => $plant,
            'docCode' => base64_encode($mapping->document->code ?? ''),
        ]);

        Notification::send($targetUsers, new DocumentActionNotification(
            action: 'approved',
            byUser: auth()->user()->name,
            documentNumber: $mapping->document_number,
            url: $url,
            departmentName: $mapping->department?->name,
        ));

        return redirect($url)
            ->with('success', "Document '{$mapping->document_number}' approved successfully!");
    }


    private function getPlantFromMapping($mapping)
    {
        // Ambil plant dari partNumber pertama yang ada, kalau nggak ada ambil dari productModel
        return $mapping->partNumber->first()?->plant
            ?? $mapping->productModel->first()?->plant
            ?? 'unknown'; // fallback kalau nggak ada
    }

    /**
     * Kirim pesan WhatsApp setelah dokumen review di-approve
     */
    // private function sendWhatsAppReviewNotification(DocumentMapping $mapping)
    // {
    //     // Hanya kirim jika ada notes
    //     if (empty($mapping->notes)) {
    //         return;
    //     }

    //     $wa = app(WhatsAppService::class);
    //     $groupId = config('services.whatsapp.group_id');

    //     $deptName = $mapping->department?->name ?? 'N/A';
    //     $modelNames = $mapping->productModel->isNotEmpty()
    //         ? $mapping->productModel->pluck('name')->join(', ')
    //         : 'N/A';

    //     $notes = $this->convertHtmlToWhatsApp($mapping->notes);

    //     $message = "📌 *DOCUMENT REMINDER ALERT*\n";
    //     $message .= "_(Automated Whatsapp Notification)_\n\n";
    //     $message .= "1. *Department:* {$deptName}\n";
    //     $message .= "   *Document Number:* {$mapping->document_number}\n";
    //     $message .= "   *Model:* {$modelNames}\n";
    //     $message .= "   *Notes:* {$notes}\n\n";
    //     $message .= "------ *BY AISIN BISA* ------";

    //     $wa->sendGroupMessage($groupId, $message);
    // }

    /**
     * Convert HTML from Quill editor to WhatsApp markdown format
     */
    private function convertHtmlToWhatsApp($html)
    {
        if (empty($html)) {
            return '';
        }

        $text = $html;

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/p>\s*<p>/i', "\n\n", $text);
        $text = preg_replace('/<p[^>]*>/i', '', $text);
        $text = preg_replace('/<\/p>/i', "\n", $text);

        $text = preg_replace('/<li[^>]*>/i', '• ', $text);
        $text = preg_replace('/<\/li>/i', "\n", $text);
        $text = preg_replace('/<\/?[uo]l[^>]*>/i', '', $text);

        $text = preg_replace('/<(strong|b)[^>]*>\s*<(em|i)[^>]*>(.*?)<\/(em|i)>\s*<\/(strong|b)>/is', '*_$3_*', $text);
        $text = preg_replace('/<(em|i)[^>]*>\s*<(strong|b)[^>]*>(.*?)<\/(strong|b)>\s*<\/(em|i)>/is', '_*$3*_', $text);

        $text = preg_replace('/<(strong|b)[^>]*>(.*?)<\/(strong|b)>/is', '*$2*', $text);

        $text = preg_replace('/<(em|i)[^>]*>(.*?)<\/(em|i)>/is', '_$2_', $text);

        $text = preg_replace('/<(s|del|strike)[^>]*>(.*?)<\/(s|del|strike)>/is', '~$2~', $text);

        $text = preg_replace('/<u[^>]*>(.*?)<\/u>/is', '$1', $text);

        $text = strip_tags($text);

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s+/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }


    public function reject(Request $request, $id)
    {
        $mapping = DocumentMapping::with('department')->findOrFail($id);
        $rejectedStatus = Status::where('name', 'Rejected')->first();

        $request->validate([
            'notes' => 'required|string'
        ]);
        $mapping->timestamps = false;
        $mapping->updateQuietly([
            'status_id' => $rejectedStatus->id ?? $mapping->status_id,
            'notes' => $request->notes,
            'review_notified_at' => null,
        ]);
        $mapping->timestamps = true;

        //     foreach ($mapping->files as $file) {
        //     // File lama tetap aktif
        //     $file->update([
        //         'is_active' => true,
        //         'pending_approval' => false,
        //         'marked_for_deletion_at' => null,
        //     ]);
        // }

        // Hanya user departemen terkait (kecuali Admin/Super Admin)
        $targetUsers = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $mapping->department_id))
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Super Admin']))
            ->get();

        // Gunakan method private yang sama
        $plant = $this->getPlantFromMapping($mapping);

        $url = route('document-review.showFolder', [
            'plant' => $plant,
            'docCode' => base64_encode($mapping->document->code ?? ''),
        ]);

        Notification::send($targetUsers, new DocumentActionNotification(
            action: 'rejected',
            byUser: auth()->user()->name,
            documentNumber: $mapping->document_number, // Hanya document_number
            url: $url,
            departmentName: $mapping->department?->name,
        ));

        return redirect($url)
            ->with('success', "Document '{$mapping->document_number}' has been rejected.");
    }
}
