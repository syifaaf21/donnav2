@if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('master.document-review.store2') }}" method="POST" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-content border-0 rounded-4 shadow-lg">

                    {{-- Modal Header --}}
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="addDocumentModalLabel">
                            <i class="bi bi-plus-circle me-2"></i> Add Document Review
                        </h5>
                    </div>

                    {{-- Notes --}}
                    {{-- <div class="d-flex align-items-center px-3 py-1 mb-3"
                        style="background-color: #fff8dc; border: 1px solid #ffeeba; border-left: 4px solid #ffc107; border-radius: 3px; font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #856404;">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-warning" style="font-size: 1rem;"></i>
                        <div>
                            <strong>Notes:</strong> Please create a <b>Parent Document</b> first.
                            <div class="text-danger ms-1" style="font-size: 0.8rem;">
                                Child Document cannot be created without Parent Document
                            </div>
                        </div>
                    </div> --}}

                    {{-- Modal Body --}}
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            {{-- Document Name --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Name <span
                                        class="text-danger">*</span></label>
                                <select id="document_select" name="document_id"
                                    class="form-select border-1 shadow-sm @error('document_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Select Document --</option>
                                    @foreach ($documentsMaster as $doc)
                                        <option value="{{ $doc->id }}" data-code="{{ strtoupper($doc->code) }}">
                                            {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Document Name is required.</div>
                                @enderror
                            </div>

                            {{-- Document Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="document_number" id="document_number"
                                    class="form-control border-1 shadow-sm @error('document_number') is-invalid @enderror"
                                    placeholder="Enter document number manually" required>
                                <div class="invalid-feedback">Document Number is required.</div>
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-4">
                                <label for="plant_select" class="form-label fw-medium">Plant <span
                                        class="text-danger">*</span></label>
                                <select name="plant" id="plant_select"
                                    class="form-select border-1 shadow-sm @error('plant') is-invalid @enderror"
                                    required>
                                    <option value="">-- Select Plant --</option>
                                    <option value="body">Body</option>
                                    <option value="unit">Unit</option>
                                    <option value="electric">Electric</option>
                                </select>
                                @error('plant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Plant is required.</div>
                                @enderror
                            </div>

                            {{-- Parent Document --}}
                            {{-- <div class="col-md-4">
                                <label class="form-label fw-medium">Parent Document (Optional)</label>
                                <select id="parent_document_select" name="parent_id" class="form-select border-1 shadow-sm @error('parent_id') is-invalid @enderror">
                                    <option value="">-- Select Parent Document --</option>
                                    @foreach ($existingDocuments as $docMap)
                                        <option value="{{ $docMap->id }}">{{ $docMap->document_number }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted fst-italic">Only required for child documents.</small>
                            </div> --}}

                            {{-- Model --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Model <span class="text-danger">*</span></label>
                                <select id="model_select" name="model_id[]" multiple
                                    class="form-select border-1 shadow-sm" disabled required>
                                    <option value="">-- Select Model --</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Product</label>
                                <select id="product_select" name="product_id[]" multiple
                                    class="form-select border-1 shadow-sm" disabled>
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Process --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Process</label>
                                <select id="process_select" name="process_id[]" multiple
                                    class="form-select border-1 shadow-sm text-capitalize" disabled>
                                    <option value="">-- Select Process --</option>
                                    @foreach ($processes as $process)
                                        <option value="({{ $process->id }})">{{ $process->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Part Number (Optional) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Part Number (Optional)</label>
                                <select id="partNumber_select" name="part_number_id[]" multiple
                                    class="form-select border-1 shadow-sm" disabled>
                                    <option value="">-- Select Part Number --</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part->id }}">{{ $part->part_number }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Department <span
                                        class="text-danger">*</span></label>
                                <select id="department_select" name="department_id"
                                    class="form-select border-1 shadow-sm @error('department_id') is-invalid @enderror"
                                    disabled required>
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Department is required.</div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 mb-10">
                                <label class="form-label fw-medium">Notes</label>
                                <input type="hidden" name="notes" id="notes_input_add" value="{{ old('notes') }}">
                                <div id="quill_editor" class="bg-white border-1 shadow-sm rounded"
                                    style="min-height: 100px; max-height: 150px; width: 100%; overflow-y: auto; word-wrap: break-word; white-space: pre-wrap; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background-color: #fff;">
                                </div>
                                <small class="text-muted">You can format your notes with bold, italic, underline,
                                    colors, and more.</small>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- File Upload --}}
                            {{-- <div class="col-12 mt-10">
                                <label class="form-label fw-medium">Upload File</label>
                                <div id="file-upload-container">
                                    <div class="input-group mb-2">
                                        <input type="file" name="files[]" class="form-control border-1 shadow-sm"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    </div>
                                    <button class="btn btn-outline-success btn-sm mt-2 add-file-btn" type="button">+
                                        Add
                                        File</button>
                                </div>
                                <small class="text-muted d-block mt-1">Allowed Format: PDF, DOCX, EXCEL</small>
                                <div class="invalid-feedback">At least one file is required.</div>
                            </div> --}}
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
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

            // --- Initialize TomSelects ---
            const tsDocument = new TomSelect('#document_select', {
                create: false,
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


            tsDocument.setValue(@json(old('document_id')));
            tsPlant.setValue(@json(old('plant')));
            tsPart.setValue(@json(old('part_number_id')));
            tsProduct.setValue(@json(old('product_id')));
            tsModel.setValue(@json(old('model_id')));
            tsProcess.setValue(@json(old('process_id')));
            tsDept.setValue(@json(old('department_id')));

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
                    disableAllDetailControls();
                    tsPart.clear();
                    tsProduct.clear();
                    tsModel.clear();
                    tsProcess.clear();
                    tsDept.clear();
                    return;
                }

                // Enable all fields once a plant is chosen
                tsPart.enable();
                tsProduct.enable();
                tsModel.enable();
                tsProcess.enable();
                tsDept.enable();

                // Fetch data filtered by plant
                const [parts, products, models, processes, departments] = await Promise.all([
                    safeFetchJson(`/api/part-numbers?plant=${encodeURIComponent(plant)}`),
                    safeFetchJson(`/api/products?plant=${encodeURIComponent(plant)}`),
                    safeFetchJson(`/api/models?plant=${encodeURIComponent(plant)}`),
                    safeFetchJson(`/api/processes?plant=${encodeURIComponent(plant)}`),
                    safeFetchJson(`/api/departments?plant=${encodeURIComponent(plant)}`)
                ]);

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
                tsDept.addOptions(departments || []);
                tsDept.refreshOptions(false);
            });

            // --- When Part Number selected ---
            tsPart.on('change', async function(partIds) {
                if (!partIds || partIds.length === 0) {
                    tsProduct.clear(true);
                    tsModel.clear(true);
                    tsProcess.clear(true);
                    tsProduct.disable();
                    tsModel.disable();
                    tsProcess.disable();
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



            });


            const quill = new Quill('#quill_editor', {
                theme: 'snow',
                placeholder: 'Write your notes here...',
                modules: {
                    toolbar: [
                        [{
                            font: []
                        }, {
                            size: []
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            color: []
                        }, {
                            background: []
                        }],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }],
                        [{
                            align: []
                        }],
                        ['clean']
                    ]
                }
            });

            // Pilih form dan hidden input
            const form = document.querySelector('#addDocumentModal form');
            const hiddenInput = document.querySelector('#notes_input_add');

            // Set old value jika ada
            quill.root.innerHTML = hiddenInput.value || '';

            // Saat submit form, isi hidden input dari Quill
            form.addEventListener('submit', function() {
                hiddenInput.value = quill.root.innerHTML;
            });


            const container = document.getElementById('file-upload-container');

            container.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('add-file-btn')) {
                    // Buat input file baru
                    const newInputGroup = document.createElement('div');
                    newInputGroup.className = 'input-group mb-2';
                    newInputGroup.innerHTML = `
                <input type="file" name="files[]" class="form-control border-1 shadow-sm" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                <button class="btn btn-outline-danger remove-file-btn" type="button">Remove</button>
            `;
                    container.prepend(newInputGroup);

                }

                if (e.target && e.target.classList.contains('remove-file-btn')) {
                    e.target.parentElement.remove();
                }
            });

            @if ($errors->any())
                var addDocModal = new bootstrap.Modal(document.getElementById('addDocumentModal'));
                addDocModal.show();
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
