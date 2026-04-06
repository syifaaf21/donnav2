@php
    $canShowAddDocumentModal = ($allowAllUsers ?? false)
        || in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']);
    $isAdminOrSuper = in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']);
    $addDocumentFormAction = $formAction ?? route('master.document-review.store2');
    $plantLabels = [
        'all' => 'ALL',
        'body' => 'Body',
        'unit' => 'Unit',
        'electric' => 'Electric',
    ];
    $allowedPlantValues = collect($allowedPlants ?? array_keys($plantLabels))
        ->map(fn($p) => strtolower(trim((string) $p)))
        ->filter(fn($p) => array_key_exists($p, $plantLabels))
        ->unique()
        ->values();
    if ($allowedPlantValues->isEmpty()) {
        $allowedPlantValues = collect(array_keys($plantLabels));
    }

    $reviewDocumentOptions = collect($documentsMaster ?? [])
        ->map(function ($doc) {
            return [
                'value' => $doc->id,
                'text' => $doc->name,
                'code' => strtoupper((string) $doc->code),
                'plants' => collect($doc->plants ?? [])
                    ->pluck('plant')
                    ->map(fn($plant) => strtolower(trim((string) $plant)))
                    ->filter()
                    ->values()
                    ->all(),
            ];
        })
        ->values()
        ->all();
@endphp

@if ($canShowAddDocumentModal)
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 900px;">
            <form action="{{ $addDocumentFormAction }}" method="POST" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="max-width: 100%;">


                    {{-- Modal Header --}}
                    <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                        style="background-color: #f5f5f7;">
                        <h5 class="modal-title fw-semibold text-dark" id="addDocumentModalLabel"
                            style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                            <i class="bi bi-plus-circle me-2 text-primary"></i> Add Document Review
                        </h5>

                        {{-- Close button --}}
                        <button type="button"
                            class="btn btn-light position-absolute top-0 end-0 m-3 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="width: 36px; height: 36px; border: 1px solid #ddd;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body p-5 bg-gray-50"
                        style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div class="row g-4">

                            {{-- Plant --}}
                            <div class="col-md-4">
                                <label for="plant_select" class="form-label fw-semibold">Plant <span
                                        class="text-danger">*</span></label>
                                <select name="plant" id="plant_select"
                                    class="form-select border-0 shadow-sm rounded-3 @error('plant') is-invalid @enderror"
                                    required>
                                        <option value="">-- Select Plant --</option>
                                        @foreach ($allowedPlantValues as $plantValue)
                                            <option value="{{ $plantValue }}" {{ old('plant') === $plantValue ? 'selected' : '' }}>
                                                {{ $plantLabels[$plantValue] }}
                                            </option>
                                        @endforeach
                                </select>
                                @error('plant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Plant is required.</div>
                                @enderror
                            </div>

                            {{-- Document Name --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Document Name <span
                                        class="text-danger">*</span></label>
                                <select id="document_select" name="document_id"
                                    class="form-select border-0 shadow-sm rounded-3 @error('document_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Select Plant First --</option>
                                </select>
                                @error('document_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Document Name is required.</div>
                                @enderror
                            </div>
                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Department <span
                                        class="text-danger">*</span></label>
                                <select id="department_select" name="department_id"
                                    class="form-select border-0 shadow-sm rounded-3 @error('department_id') is-invalid @enderror"
                                    disabled required>
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Department is required.</div>
                            </div>

                            {{-- Model --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                                <select id="model_select" name="model_id[]" multiple
                                    class="form-select border-0 shadow-sm rounded-3" disabled required>
                                    <option value="">-- Select Model --</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Product</label>
                                <select id="product_select" name="product_id[]" multiple
                                    class="form-select border-0 shadow-sm rounded-3" disabled>
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Process --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Process</label>
                                <select id="process_select" name="process_id[]" multiple
                                    class="form-select border-0 shadow-sm rounded-3 text-capitalize" disabled>
                                    <option value="">-- Select Process --</option>
                                    @foreach ($processes as $process)
                                        <option value="({{ $process->id }})">{{ $process->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Part Number (Optional) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Part Number (Optional)</label>
                                <select id="partNumber_select" name="part_number_id[]" multiple
                                    class="form-select border-0 shadow-sm rounded-3" disabled>
                                    <option value="">-- Select Part Number --</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part->id }}">{{ $part->part_number }}</option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Document Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Document Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="document_number" id="document_number"
                                    class="form-control border-0 shadow-sm rounded-3 @error('document_number') is-invalid @enderror"
                                    placeholder="Automatically generated" required>
                                <small id="document_number_hint" class="text-muted d-block mt-1"></small>
                                <small class="text-muted">Document number will be automatically generated and editable.</small>
                                <div class="invalid-feedback">Document Number is required.</div>
                            </div>

                            @if ($isAdminOrSuper)
                                {{-- Notes --}}
                                <div class="col-12 mb-4">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <input type="hidden" name="notes" id="notes_input_add"
                                        value="{{ old('notes') }}">
                                    <div id="quill_editor" class="bg-white rounded-3 shadow-sm p-2"
                                        style="min-height: 120px; max-height: 160px; overflow-y: auto; overflow-x: hidden; border: 1px solid #e2e8f0; word-wrap: break-word; word-break: break-word;">
                                    </div>
                                    <small class="text-muted">You can format your notes with bold, italic and underline.</small>
                                    @error('notes')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            {{-- File Upload --}}
                            <div class="col-12 mt-4">
                                <label class="form-label fw-semibold">Upload File</label>
                                    <div id="file-upload-container" class="d-flex flex-column gap-2">
                                        <div class="input-group file-input-group">
                                            <input type="file" name="files[]"
                                                class="form-control border-0 shadow-sm rounded-3 @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror"
                                                accept=".pdf,.doc,.docx,.xls,.xlsx">
                                            <button class="btn btn-outline-danger remove-file-btn" type="button" style="display:none;">Remove</button>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm mt-2" type="button" id="add-file-btn">+ Add File</button>
                                    <small class="text-muted d-block mt-1">Allowed format: PDF, DOC, DOCX, XLS, XLSX. Max Total File Size: 20MB</small>
                                    @error('files')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('files.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                        <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                            data-bs-dismiss="modal"
                            style="text-decoration: none; transition: background-color 0.3s ease;">
                            Cancel
                        </button>
                        <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif


{{-- Initialize TomSelect --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Utility fetch wrapper with safe error handling
            async function safeFetchJson(url) {
                try {
                    const res = await fetch(url);
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const data = await res.json();

                    // Kalau data array → normalisasi untuk dropdown
                    if (Array.isArray(data)) {
                        return data.map(item => ({
                            value: item.id ?? item.value,
                            text: item.name ?? item.text ?? item.part_number ?? 'Unnamed'
                        }));
                    }

                    // Kalau data object → return apa adanya (untuk detail)
                    return data;
                } catch (err) {
                    console.error(`Error fetching ${url}:`, err);
                    return [];
                }
            }

            const allReviewDocuments = @json($reviewDocumentOptions);
            const allowedDepartmentIds = @json(collect($allowedDepartmentIds ?? [])->map(fn($id) => (int) $id)->values()->all());

            function normalizePlant(plant) {
                return String(plant || '').trim().toLowerCase();
            }

            function getDocumentsByPlant(plant) {
                const normalizedPlant = normalizePlant(plant);
                if (!normalizedPlant) return [];

                return allReviewDocuments.filter((doc) => {
                    const docPlants = Array.isArray(doc.plants) ? doc.plants.map(normalizePlant) : [];

                    if (normalizedPlant === 'all') {
                        return docPlants.includes('all');
                    }

                    if (docPlants.length === 0) {
                        return ['body', 'unit', 'electric'].includes(normalizedPlant);
                    }

                    if (docPlants.includes('all')) {
                        return false;
                    }

                    return docPlants.includes(normalizedPlant);
                });
            }


            // --- Initialize TomSelects ---
            const tsDocument = new TomSelect('#document_select', {
                create: false,
                placeholder: 'Select Plant First',
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            const tsPlant = new TomSelect('#plant_select', {
                create: false,
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            // Multiple selects with remove button
            const tsPart = new TomSelect('#partNumber_select', {
                create: false,
                maxItems: null,
                plugins: ['remove_button'], // <- enable remove button
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            const tsProduct = new TomSelect('#product_select', {
                create: false,
                maxItems: null,
                plugins: ['remove_button'], // <- enable remove button
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            const tsModel = new TomSelect('#model_select', {
                create: false,
                maxItems: null,
                plugins: ['remove_button'], // <- enable remove button
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            const tsProcess = new TomSelect('#process_select', {
                create: false,
                maxItems: null,
                plugins: ['remove_button'], // <- enable remove button
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            // Single select department (tidak perlu remove button)
            const tsDept = new TomSelect('#department_select', {
                create: false,
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            // Reset all fields when modal is closed
            const addModalEl = document.getElementById('addDocumentModal');
            if (addModalEl) {
                addModalEl.addEventListener('hidden.bs.modal', function() {
                    tsPlant.clear();
                    tsDocument.clear();
                    tsPart.clear();
                    tsProduct.clear();
                    tsModel.clear();
                    tsProcess.clear();
                    tsDept.clear();
                    // Reset document number
                    const docNumInput = document.getElementById('document_number');
                    if (docNumInput) docNumInput.value = '';
                    // Reset notes
                    const notesInput = document.getElementById('notes_input_add');
                    if (notesInput) notesInput.value = '';
                    // Reset Quill editor notes
                    if (window.quill) window.quill.root.innerHTML = '';
                    if (typeof quill !== 'undefined' && quill && quill.root) quill.root.innerHTML = '';

                    // Reset file upload: hapus semua kecuali satu
                    const fileContainer = document.getElementById('file-upload-container');
                    if (fileContainer) {
                        const groups = fileContainer.querySelectorAll('.file-input-group');
                        groups.forEach((group, idx) => {
                            if (idx === 0) {
                                // Reset value file input pertama
                                const input = group.querySelector('input[type="file"]');
                                if (input) input.value = '';
                            } else {
                                group.remove();
                            }
                        });
                        // Refresh remove button
                        if (typeof refreshRemoveButtons === 'function') refreshRemoveButtons();
                    }
                });
            }


            const oldDocumentId = @json(old('document_id'));
            tsPlant.setValue(@json(old('plant')));
            tsPart.setValue(@json(old('part_number_id')));
            tsProduct.setValue(@json(old('product_id')));
            tsModel.setValue(@json(old('model_id')));
            tsProcess.setValue(@json(old('process_id')));
            tsDept.setValue(@json(old('department_id')));

            function updateDocumentOptionsByPlant(plant, selectedDocumentId = null) {
                const docs = getDocumentsByPlant(plant);

                tsDocument.clear(true);
                tsDocument.clearOptions();
                tsDocument.addOptions(docs);
                tsDocument.refreshOptions(false);

                if (!plant) {
                    tsDocument.settings.placeholder = 'Select Plant First';
                    tsDocument.disable();
                    return;
                }

                tsDocument.settings.placeholder = docs.length > 0
                    ? '-- Select Document --'
                    : 'No document available for selected plant';

                if (docs.length === 0) {
                    tsDocument.disable();
                    return;
                }

                tsDocument.enable();

                if (selectedDocumentId && docs.some((doc) => String(doc.value) === String(selectedDocumentId))) {
                    tsDocument.setValue(String(selectedDocumentId), false);
                }
            }

            updateDocumentOptionsByPlant(null);

            // --- Function: disable all controls initially ---
            function disableAllDetailControls() {
                tsPart.disable();
                tsProduct.disable();
                tsModel.disable();
                tsProcess.disable();
                tsDept.disable();
            }
            disableAllDetailControls();

            // --- When Plant selected ---
            tsPlant.on('change', async function(plant) {
                if (!plant) {
                    // If plant cleared: disable all and clear values
                    updateDocumentOptionsByPlant(null);
                    disableAllDetailControls();
                    tsPart.clear();
                    tsProduct.clear();
                    tsModel.clear();
                    tsProcess.clear();
                    tsDept.clear();
                    if (documentNumberInput) {
                        documentNumberInput.value = '';
                    }
                    return;
                }

                updateDocumentOptionsByPlant(plant);

                // Enable all fields once a plant is chosen
                tsPart.enable();
                tsProduct.enable();
                tsModel.enable();
                tsProcess.enable();
                tsDept.enable();

                // Fetch data. If plant == 'all' we request unfiltered lists for model/product/process/part,
                // but departments should be the full set (i.e. not filtered) when 'all' is chosen.
                let partsUrl, productsUrl, modelsUrl, processesUrl, departmentsUrl;
                if (plant === 'all') {
                    partsUrl = `/api/part-numbers`;
                    productsUrl = `/api/products`;
                    modelsUrl = `/api/models`;
                    processesUrl = `/api/processes`;
                    // request departments for plant=all only
                    departmentsUrl = `/api/departments?plant=all`;
                } else {
                    const p = encodeURIComponent(plant);
                    partsUrl = `/api/part-numbers?plant=${p}`;
                    productsUrl = `/api/products?plant=${p}`;
                    modelsUrl = `/api/models?plant=${p}`;
                    processesUrl = `/api/processes?plant=${p}`;
                    departmentsUrl = `/api/departments?plant=${p}`;
                }

                const [parts, products, models, processes, departments] = await Promise.all([
                    safeFetchJson(partsUrl),
                    safeFetchJson(productsUrl),
                    safeFetchJson(modelsUrl),
                    safeFetchJson(processesUrl),
                    safeFetchJson(departmentsUrl)
                ]);

                const filteredDepartments = (departments || []).filter((dept) =>
                    allowedDepartmentIds.includes(Number(dept.value))
                );

                // Clear and repopulate each dropdown
                tsPart.clearOptions();
                tsPart.addOptions(parts || []);
                tsPart.refreshOptions(false);

                tsProduct.clearOptions();
                tsProduct.addOptions(products || []);
                tsProduct.refreshOptions(false);

                tsModel.clearOptions();
                tsModel.addOptions(models || []);
                tsModel.refreshOptions(false);

                tsProcess.clearOptions();
                tsProcess.addOptions(processes || []);
                tsProcess.refreshOptions(false);

                tsDept.clearOptions();
                tsDept.addOptions(filteredDepartments);
                tsDept.refreshOptions(false);
            });

            // --- When Part Number selected ---
            tsPart.on('change', async function(partIds) {
                if (!partIds || partIds.length === 0) {
                    // Clear values dan reload fresh options berdasarkan plant saat ini
                    tsProduct.clear(true);
                    tsModel.clear(true);
                    tsProcess.clear(true);
                    document.getElementById('document_number').value = '';

                    // Reload options berdasarkan plant yang dipilih saat ini
                    const currentPlant = tsPlant.getValue();
                    if (currentPlant) {
                        let partsUrl, productsUrl, modelsUrl, processesUrl;
                        if (currentPlant === 'all') {
                            partsUrl = `/api/part-numbers`;
                            productsUrl = `/api/products`;
                            modelsUrl = `/api/models`;
                            processesUrl = `/api/processes`;
                        } else {
                            const p = encodeURIComponent(currentPlant);
                            partsUrl = `/api/part-numbers?plant=${p}`;
                            productsUrl = `/api/products?plant=${p}`;
                            modelsUrl = `/api/models?plant=${p}`;
                            processesUrl = `/api/processes?plant=${p}`;
                        }

                        const [parts, products, models, processes] = await Promise.all([
                            safeFetchJson(partsUrl),
                            safeFetchJson(productsUrl),
                            safeFetchJson(modelsUrl),
                            safeFetchJson(processesUrl)
                        ]);

                        tsPart.clearOptions();
                        tsPart.addOptions(parts || []);
                        tsPart.refreshOptions(false);

                        tsProduct.clearOptions();
                        tsProduct.addOptions(products || []);
                        tsProduct.refreshOptions(false);

                        tsModel.clearOptions();
                        tsModel.addOptions(models || []);
                        tsModel.refreshOptions(false);

                        tsProcess.clearOptions();
                        tsProcess.addOptions(processes || []);
                        tsProcess.refreshOptions(false);
                    }
                    return;
                }

                // Pastikan partIds selalu array
                if (!Array.isArray(partIds)) partIds = [partIds];

                let allProducts = [];
                let allModels = [];
                let allProcesses = [];

                // Ambil detail tiap part number
                for (const partId of partIds) {
                    const detail = await safeFetchJson(
                        `/api/part-number-details/${encodeURIComponent(partId)}`);
                    if (detail && !detail.error) {
                        if (detail.product) allProducts.push(detail.product);
                        if (detail.model) allModels.push(detail.model);
                        if (detail.process) allProcesses.push(detail.process);
                    }
                }

                // Hapus duplikat berdasarkan ID
                const uniqueById = (arr, idField = 'id') => [...new Map(arr.map(item => [item[idField],
                    item
                ])).values()];
                const formatForTomSelect = (arr) => arr.map(i => ({
                    value: i.id,
                    text: i.text
                }));

                // Product
                tsProduct.clearOptions();
                tsProduct.addOptions(formatForTomSelect(uniqueById(allProducts)));
                tsProduct.setValue(uniqueById(allProducts).map(p => p.id));
                tsProduct.enable();

                // Model
                tsModel.clearOptions();
                tsModel.addOptions(formatForTomSelect(uniqueById(allModels)));
                tsModel.setValue(uniqueById(allModels).map(m => m.id));
                tsModel.enable();

                // Process
                tsProcess.clearOptions();
                tsProcess.addOptions(formatForTomSelect(uniqueById(allProcesses)));
                tsProcess.setValue(uniqueById(allProcesses).map(p => p.id));
                tsProcess.enable();

                // Trigger document number generation setelah autofill selesai
                generateDocumentNumber();
            });

            // --- Auto-generate document number when key fields change ---
            const documentNumberInput = document.getElementById('document_number');
            const documentNumberHint = document.getElementById('document_number_hint');
            let isGeneratingDocumentNumber = false;
            let generateDocumentNumberRequestId = 0;

            function setDocumentNumberHint(message, isError = false) {
                if (!documentNumberHint) return;

                if (message === 'Generating document number...') {
                    documentNumberHint.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="width:0.8rem;height:0.8rem;"></span>Generating document number...';
                } else {
                    documentNumberHint.textContent = message || '';
                }

                documentNumberHint.classList.toggle('text-danger', Boolean(isError));
                documentNumberHint.classList.toggle('text-muted', !isError);
            }

            function setDocumentNumberGeneratingState(isGenerating) {
                isGeneratingDocumentNumber = isGenerating;
                const submitBtn = document.querySelector('#addDocumentModal form button[type="submit"]');

                if (documentNumberInput) {
                    documentNumberInput.readOnly = isGenerating;
                }

                if (submitBtn && submitBtn.dataset.isSubmitting !== '1') {
                    submitBtn.disabled = isGenerating;
                }

                if (isGenerating) {
                    setDocumentNumberHint('Generating document number...');
                } else if (!documentNumberHint?.classList.contains('text-danger')) {
                    setDocumentNumberHint('');
                }
            }

            async function generateDocumentNumber() {
                const requestId = ++generateDocumentNumberRequestId;
                const documentId = tsDocument.getValue();
                const departmentId = tsDept.getValue();
                const productIds = tsProduct.getValue() || [];
                const processIds = tsProcess.getValue() || [];
                const modelIds = tsModel.getValue() || [];

                // Use first selected if multiple
                const payload = {
                    document_id: documentId ? Number(documentId) : null,
                    department_id: departmentId ? Number(departmentId) : null,
                    product_id: productIds && productIds.length ? Number(productIds[0]) : null,
                    process_id: processIds && processIds.length ? Number(processIds[0]) : null,
                    model_id: modelIds && modelIds.length ? Number(modelIds[0]) : null,
                    format: 3,
                };

                if (!payload.document_id || !payload.department_id) {
                    if (requestId === generateDocumentNumberRequestId) {
                        setDocumentNumberGeneratingState(false);
                    }
                    return; // need minimum fields
                }

                try {
                    setDocumentNumberGeneratingState(true);

                    const res = await fetch('{{ route('document-number.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });

                    if (!res.ok) throw new Error('Failed to generate');
                    const json = await res.json();
                    if (requestId !== generateDocumentNumberRequestId) {
                        return;
                    }

                    if (json?.success && json.document_number) {
                        if (documentNumberInput) {
                            documentNumberInput.value = json.document_number;
                        }
                        setDocumentNumberHint('');
                    } else {
                        setDocumentNumberHint('Failed to generate document number. Please try again.', true);
                    }
                } catch (err) {
                    if (requestId !== generateDocumentNumberRequestId) {
                        return;
                    }
                    console.error('Generate error:', err);
                    setDocumentNumberHint('Failed to generate document number. Please try again.', true);
                } finally {
                    if (requestId === generateDocumentNumberRequestId) {
                        setDocumentNumberGeneratingState(false);
                    }
                }
            }

            // Trigger generation when relevant selects change
            tsDocument.on('change', generateDocumentNumber);
            tsDept.on('change', generateDocumentNumber);
            tsProduct.on('change', generateDocumentNumber);
            tsProcess.on('change', generateDocumentNumber);
            tsModel.on('change', generateDocumentNumber);


            // Pilih form dan hidden input
            const form = document.querySelector('#addDocumentModal form');
            const hiddenInput = document.querySelector('#notes_input_add');
            const quillEditorEl = document.querySelector('#quill_editor');
            let quill = null;

            if (quillEditorEl && hiddenInput) {
                quill = new Quill('#quill_editor', {
                    theme: 'snow',
                    placeholder: 'Write your notes here...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'strike'],
                            ['clean']
                        ]
                    }
                });

                // Set old value jika ada
                quill.root.innerHTML = hiddenInput.value || '';
            }


            // Saat submit form, isi hidden input dari Quill dan tampilkan loading pada tombol submit
            form.addEventListener('submit', function(event) {
                if (isGeneratingDocumentNumber) {
                    event.preventDefault();
                    setDocumentNumberHint('Please wait until document number generation is complete.', true);
                    return;
                }

                if (quill && hiddenInput) {
                    hiddenInput.value = quill.root.innerHTML;
                }

                // Do not clear plant 'all' here — allow server to receive 'all' so it can be
                // handled (we treat 'all' as Other/Manual Entry tab).

                // Temukan tombol submit
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    // Simpan teks asli
                    submitBtn.dataset.originalText = submitBtn.innerHTML;
                    submitBtn.dataset.isSubmitting = '1';
                    // Tampilkan spinner dan teks Loading
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
                    submitBtn.disabled = true;
                }
            });


            // const container = document.getElementById('file-upload-container');

            // container.addEventListener('click', function(e) {
            //     if (e.target && e.target.classList.contains('add-file-btn')) {
            //         // Buat input file baru
            //         const newInputGroup = document.createElement('div');
            //         newInputGroup.className = 'input-group mb-2';
            //         newInputGroup.innerHTML = `
            //     <input type="file" name="files[]" class="form-control border-1 shadow-sm" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
            //     <button class="btn btn-outline-danger remove-file-btn" type="button">Remove</button>
            // `;
            //         container.prepend(newInputGroup);

            //     }

            //     if (e.target && e.target.classList.contains('remove-file-btn')) {
            //         e.target.parentElement.remove();
            //     }
            // });
                const fileContainer = document.getElementById('file-upload-container');
                const addFileBtn = document.getElementById('add-file-btn');

                function refreshRemoveButtons() {
                    if (!fileContainer) return;
                    const groups = fileContainer.querySelectorAll('.file-input-group');
                    groups.forEach((group, index) => {
                        const removeBtn = group.querySelector('.remove-file-btn');
                        if (!removeBtn) return;
                        removeBtn.style.display = groups.length > 1 ? 'inline-block' : 'none';
                        removeBtn.disabled = groups.length <= 1 && index === 0;
                    });
                }

                if (fileContainer && addFileBtn) {
                    addFileBtn.addEventListener('click', function() {
                        const newInputGroup = document.createElement('div');
                        newInputGroup.className = 'input-group file-input-group';
                        newInputGroup.innerHTML = `
                            <input type="file" name="files[]" class="form-control border-0 shadow-sm rounded-3" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            <button class="btn btn-outline-danger remove-file-btn" type="button">Remove</button>
                        `;
                        fileContainer.appendChild(newInputGroup);
                        refreshRemoveButtons();
                    });

                    fileContainer.addEventListener('click', function(e) {
                        if (e.target && e.target.classList.contains('remove-file-btn')) {
                            const group = e.target.closest('.file-input-group');
                            if (group) {
                                group.remove();
                                refreshRemoveButtons();
                            }
                        }
                    });

                    refreshRemoveButtons();
                }

            /* -------------------------------------------------------------------
             * SHOW MODAL IF VALIDATION ERRORS
             * ------------------------------------------------------------------- */
            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('addDocumentModal')).show();

                // Jika plant punya old value → aktifkan dan reload datanya
                const oldPlant = @json(old('plant'));
                if (oldPlant) {
                    // Set plant value first
                    tsPlant.setValue(oldPlant, false); // false = don't trigger change yet
                    updateDocumentOptionsByPlant(oldPlant, oldDocumentId);

                    // Trigger change event untuk load data dari API
                    setTimeout(async () => {
                        // Manually trigger the plant change to load data. If oldPlant == 'all', fetch unfiltered lists.
                        let partsUrl2, productsUrl2, modelsUrl2, processesUrl2, departmentsUrl2;
                        if (oldPlant === 'all') {
                            partsUrl2 = `/api/part-numbers`;
                            productsUrl2 = `/api/products`;
                            modelsUrl2 = `/api/models`;
                            processesUrl2 = `/api/processes`;
                            // request departments for plant=all only
                            departmentsUrl2 = `/api/departments?plant=all`;
                        } else {
                            partsUrl2 = `/api/part-numbers?plant=${encodeURIComponent(oldPlant)}`;
                            productsUrl2 = `/api/products?plant=${encodeURIComponent(oldPlant)}`;
                            modelsUrl2 = `/api/models?plant=${encodeURIComponent(oldPlant)}`;
                            processesUrl2 = `/api/processes?plant=${encodeURIComponent(oldPlant)}`;
                            departmentsUrl2 = `/api/departments?plant=${encodeURIComponent(oldPlant)}`;
                        }

                        const [parts, products, models, processes, departments] = await Promise.all([
                            safeFetchJson(partsUrl2),
                            safeFetchJson(productsUrl2),
                            safeFetchJson(modelsUrl2),
                            safeFetchJson(processesUrl2),
                            safeFetchJson(departmentsUrl2)
                        ]);

                        // Enable fields
                        tsPart.enable();
                        tsProduct.enable();
                        tsModel.enable();
                        tsProcess.enable();
                        tsDept.enable();

                        // Populate options
                        tsPart.clearOptions();
                        tsPart.addOptions(parts || []);
                        tsPart.refreshOptions(false);

                        tsProduct.clearOptions();
                        tsProduct.addOptions(products || []);
                        tsProduct.refreshOptions(false);

                        tsModel.clearOptions();
                        tsModel.addOptions(models || []);
                        tsModel.refreshOptions(false);

                        tsProcess.clearOptions();
                        tsProcess.addOptions(processes || []);
                        tsProcess.refreshOptions(false);

                        tsDept.clearOptions();
                        tsDept.addOptions(departments || []);
                        tsDept.refreshOptions(false);

                        // After options loaded, restore old values
                        setTimeout(() => {
                            const oldPartNumbers = @json(old('part_number_id', []));
                            const oldProducts = @json(old('product_id', []));
                            const oldModels = @json(old('model_id', []));
                            const oldProcesses = @json(old('process_id', []));
                            const oldDepartment = @json(old('department_id'));

                            if (oldPartNumbers && oldPartNumbers.length > 0) {
                                tsPart.setValue(oldPartNumbers, false);
                            }

                            if (oldProducts && oldProducts.length > 0) {
                                tsProduct.setValue(oldProducts, false);
                            }

                            if (oldModels && oldModels.length > 0) {
                                tsModel.setValue(oldModels, false);
                            }

                            if (oldProcesses && oldProcesses.length > 0) {
                                tsProcess.setValue(oldProcesses, false);
                            }

                            if (oldDepartment) {
                                tsDept.setValue(oldDepartment, false);
                            }
                        }, 200);
                    }, 100);
                }
            @endif
        });
    </script>
@endpush
@push('styles')
    <style>
        /* Biarkan tag TomSelect wrap ke baris baru */
        .ts-control {
            flex-wrap: wrap !important;
            min-height: 38px;
            /* optional, agar tinggi input tetap nyaman */
        }
    </style>
@endpush
