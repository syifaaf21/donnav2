<?php

namespace App\Http\Controllers;

use App\Models\{Department, Document, DocumentFile, DocumentMapping, PartNumber, Process, Product, ProductModel, Status, User, DownloadReport};
use App\Notifications\{DocumentRevisedNotification, DocumentStatusNotification, DocumentActionNotification};
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Storage};

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $rawPlants = $this->getEnumValues('tm_part_numbers', 'plant');
        // Normalize values (trim + lowercase -> then ucfirst for display)
        $plants = array_map(fn($p) => ucfirst(strtolower(trim($p))), $rawPlants);
        // Ensure 'Others' tab exists even if not present in enum (we represent global docs with 'ALL')
        if (!in_array('Others', $plants)) {
            $plants[] = 'Others';
        }
        // Keep only allowed plants (case-insensitive)
        $allowed = ['body', 'unit', 'electric', 'others'];
        $plants = array_values(array_filter($plants, fn($p) => in_array(strtolower($p), $allowed)));
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
        // Accept both base64-encoded docCode (preferred) and raw docCode.
        $decoded = @base64_decode($docCode, true);
        if ($decoded === false || $decoded === '') {
            $docCode = $docCode; // raw value
        } else {
            $docCode = $decoded;
        }

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

        // 5. Filter by status (setelah search/filter lain)
        $statusMap = [
            'approved' => 'Approved',
            'need_review' => 'Need Review',
            'rejected' => 'Rejected',
            'uncomplete' => 'Uncomplete',
        ];
        $selectedStatuses = $request->input('status', []);
        if (!is_array($selectedStatuses)) {
            $selectedStatuses = [$selectedStatuses];
        }
        if (!empty($selectedStatuses) && !in_array('all', $selectedStatuses)) {
            $documents = $documents->filter(function ($doc) use ($selectedStatuses, $statusMap) {
                $docStatus = strtolower(str_replace(' ', '_', $doc->status?->name ?? ''));
                return in_array($docStatus, $selectedStatuses);
            });
        }

        // Hitung jumlah dokumen per status
        $statusCounts = [
            'approved' => 0,
            'need_review' => 0,
            'rejected' => 0,
            'uncomplete' => 0,
        ];
        foreach ($documents as $doc) {
            $docStatus = strtolower(str_replace(' ', '_', $doc->status?->name ?? ''));
            if (isset($statusCounts[$docStatus])) {
                $statusCounts[$docStatus]++;
            }
        }

        // 6. Pagination manual (karena Collection)
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
        $page = $request->input('page', 1);
        $perPage = 10;

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
            'statusCounts' => $statusCounts,
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
        // Ambil deklarasi document->plant (pivot-like)
        $docPlantRows = \DB::table('document_plant')->get();

        return collect($plants)->mapWithKeys(function ($plant) use ($documentMappings, $documentsMaster, $docPlantRows) {

            $plantLower = strtolower($plant);
            // Map tab 'Others' to stored plant value 'all'
            $docPlantKey = $plantLower === 'others' ? 'all' : $plantLower;

            // Filter mappings sesuai plant.
            // Rules:
            // - If current tab is 'others' (mapped to 'all'), include mappings where mapping->plant == 'all'
            //   or mappings that don't have partNumber (manual entries).
            // - For physical plants (body/unit/electric), exclude mappings explicitly set to 'all'
            //   and only include mappings whose related partNumber/productModel belong to the plant.
            $mappingsByPlant = $documentMappings->filter(function ($item) use ($plantLower) {
                $mappingPlant = strtolower(trim($item->plant ?? ''));

                $hasPartPlant = $item->partNumber->contains(fn($pn) => strtolower(trim($pn->plant ?? '')) === $plantLower);
                $hasModelPlant = $item->productModel->contains(fn($model) => strtolower(trim($model->plant ?? '')) === $plantLower);

                if ($plantLower === 'others') {
                    // include mappings explicitly marked 'all'
                    if ($mappingPlant === 'all') return true;

                    // include manual mappings without part numbers
                    if ($item->partNumber->isEmpty()) return true;

                    // otherwise ignore mappings tied to specific plants
                    return false;
                }

                // physical plant: ignore mappings explicitly set to 'all'
                if ($mappingPlant === 'all') return false;

                return $hasPartPlant || $hasModelPlant;
            });

            // Ambil dokumen yang eksplisit di-assign ke plant melalui document_plant
            $docIdsFromDocPlant = $docPlantRows->filter(fn($r) => strtolower(trim($r->plant ?? '')) === $docPlantKey)->pluck('document_id')->unique();
            $docsFromDocPlant = $documentsMaster->whereIn('id', $docIdsFromDocPlant->toArray());

            // Ambil semua kode dokumen khusus untuk plant ini:
            // - dari document_plant (dokumen yang secara eksplisit diset ke plant)
            // - dari documentMappings yang relevan untuk plant (berdasarkan partNumber/productModel)
            // Jangan memasukkan semua kode master karena itu menyebabkan semua dokumen
            // muncul di semua tab.
            $codesFromDocPlant = $docsFromDocPlant->pluck('code');
            $codesFromMapping = $mappingsByPlant->map(fn($m) => $m->document?->code)->filter();

            // Include master documents that have NO explicit plant assignment (legacy)
            // so they appear under all physical plant tabs (Body/Unit/Electric).
            $codesFromMaster = $documentsMaster->filter(function ($doc) use ($docPlantKey, $plantLower) {
                // If document has explicit plants -> only include when matches current plant
                $docPlants = $doc->plants->pluck('plant')->map(fn($p) => strtolower(trim($p ?? '')))->unique();

                if ($docPlants->isEmpty()) {
                    // legacy: no assignment => show on physical plants (not 'all'/Others)
                    return $plantLower !== 'others';
                }

                // If document explicitly assigned to 'all' -> include only in Others
                if ($docPlants->contains('all')) {
                    return $docPlantKey === 'all';
                }

                // Otherwise include if docPlants contains this plant
                return $docPlants->contains($plantLower);
            })->pluck('code');

            $allCodes = $codesFromDocPlant->merge($codesFromMapping)->merge($codesFromMaster)->unique();

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
        if (!in_array('Others', $plants)) {
            $plants[] = 'Others';
        }
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
        ])->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->whereNull('marked_for_deletion_at')
            ->get();

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

        // Authorization check: hanya supervisor dari department pemilik dokumen yang bisa edit
        $user = Auth::user();
        $isAdmin = in_array(strtolower($user->roles->pluck('name')->first() ?? ''), ['admin', 'super admin']);

        // Jika bukan admin, cek apakah supervisor dari department pemilik dokumen
        if (!$isAdmin && !$user->canEditDocument($mapping)) {
            abort(403, 'Unauthorized. Only Leader from the document\'s department can edit.');
        }

        $request->validate([
            'revision_files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
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

        // Maksimal total 20 MB = 20 * 1024 * 1024
        if ($totalSize > 20 * 1024 * 1024) {
            return back()
            ->withErrors(['revision_files' => 'Total file upload tidak boleh lebih dari 20 MB'])
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
        $mapping = DocumentMapping::with([
            'files' => function ($q) {
                $q->where('is_active', 1)
                    ->where('pending_approval', 0)
                    ->orderBy('created_at', 'asc'); // opsional: urutkan
            }
        ])->findOrFail($id);

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
        // Prioritize explicit mapping->plant. If it's 'all' treat as 'Others'.
        $mappingPlant = strtolower(trim($mapping->plant ?? ''));
        if ($mappingPlant === 'all') {
            return 'Others';
        }

        // Ambil plant dari partNumber pertama yang ada, kalau nggak ada ambil dari productModel
        $pnPlant = $mapping->partNumber->first()?->plant;
        $pmPlant = $mapping->productModel->first()?->plant;

        $plant = $pnPlant ?? $pmPlant;

        if (!$plant) return 'Others';

        // Normalize to ucfirst format used in tabs (Body/Unit/Electric)
        return ucfirst(strtolower(trim($plant)));
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

    public function getDownloadReport(Request $request, $id)
    {
        try {
            $document = DocumentMapping::with(['files' => function ($query) {
                $query->where('is_active', 1)->where('pending_approval', 0);
            }])->findOrFail($id);

            $requestedFileId = (int) ($request->query('document_file_id') ?? $request->query('file_id'));

            $file = $requestedFileId
                ? $document->files->firstWhere('id', $requestedFileId)
                : $document->files->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file found for this document.',
                ], 404);
            }

            $downloadLogs = DownloadReport::where('document_mapping_id', $id)
                ->where('document_file_id', $file->id)
                ->select('user_id', DB::raw('COUNT(*) as download_count'))
                ->groupBy('user_id')
                ->with('user:id,name')
                ->orderBy('download_count', 'desc')
                ->get();

            $downloads = $downloadLogs->map(function ($log) {
                return [
                    'user_name' => $log->user->name ?? 'Unknown User',
                    'download_count' => $log->download_count,
                ];
            });

            return response()->json([
                'success' => true,
                'document_number' => $document->document_number,
                'file' => [
                    'id' => $file->id,
                    'name' => $file->original_name ?? basename($file->file_path),
                ],
                'downloads' => $downloads,
                'total_downloads' => $downloads->sum('download_count'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load download report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logDownload(Request $request, $id)
    {
        try {
            $action = $request->input('action', 'view');
            $fileId = (int) ($request->input('document_file_id') ?? $request->input('file_id'));

            $document = DocumentMapping::with(['files' => function ($query) {
                $query->where('is_active', 1)->where('pending_approval', 0);
            }])->findOrFail($id);

            $file = null;
            if ($fileId) {
                $file = $document->files->firstWhere('id', $fileId);
            } else {
                $file = $document->files->first();
            }

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found for this document.',
                ], 404);
            }

            $log = DownloadReport::create([
                'document_mapping_id' => $id,
                'document_file_id' => $file->id,
                'user_id' => auth()->id(),
            ]);

            \Log::info('Download logged', [
                'doc_id' => $id,
                'file_id' => $file?->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'log_id' => $log->id
            ]);

            return response()->json([
                'success' => true,
                'action' => $action,
                'log_id' => $log->id,
                'file_id' => $file?->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log download', [
                'error' => $e->getMessage(),
                'doc_id' => $id,
                'file_id' => $fileId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkDownloadLogs($id)
    {
        $logs = DownloadReport::where('document_mapping_id', $id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'document_id' => $id,
            'total_logs' => DownloadReport::where('document_mapping_id', $id)->count(),
            'recent_logs' => $logs->map(fn($log) => [
                'user' => $log->user->name,
                'logged_at' => $log->created_at,
            ])
        ]);
    }
}
