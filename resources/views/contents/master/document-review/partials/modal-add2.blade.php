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
                    <div class="d-flex align-items-center px-3 py-1 mb-3"
                        style="background-color: #fff8dc; border: 1px solid #ffeeba; border-left: 4px solid #ffc107; border-radius: 3px; font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #856404;">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-warning" style="font-size: 1rem;"></i>
                        <div>
                            <strong>Notes:</strong> Please create a <b>Parent Document</b> first.
                            <div class="text-danger ms-1" style="font-size: 0.8rem;">
                                Child Document cannot be created without Parent Document
                            </div>
                        </div>
                    </div>

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
                                <label class="form-label fw-medium">Model</label>
                                <select id="model_select" name="model_id" class="form-select border-1 shadow-sm">
                                    <option value="">-- Select Model --</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Product</label>
                                <select id="product_select" name="product_id" class="form-select border-1 shadow-sm">
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Process --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Process</label>
                                <select id="process_select" name="process_id"
                                    class="form-select border-1 shadow-sm text-capitalize">
                                    <option value="">-- Select Process --</option>
                                    @foreach ($processes as $process)
                                        <option value="({{ $process->id }})">{{ $process->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- Part Number (Optional) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Part Number (Optional)</label>
                                <select id="partNumber_select" name="part_number_id"
                                    class="form-select border-1 shadow-sm">
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
                                    required>
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Department is required.</div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 mb-3">
                                <label class="form-label fw-medium">Notes</label>
                                <textarea name="notes" class="form-control border-1 shadow-sm" rows="3" placeholder="Enter notes (optional)">{{ old('notes') }}</textarea>
                            </div>

                            {{-- File Upload --}}
                            <div class="col-12 mt-4">
                                <label class="form-label fw-medium">Upload File <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="files[]"
                                    class="form-control border-1 shadow-sm @error('files') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx" multiple required>
                                <small class="text-muted d-block mt-1">Allowed Format: PDF, DOCX, EXCEL</small>
                                <div class="invalid-feedback">At least one file is required.</div>
                            </div>
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
            // --- Inisialisasi TomSelect ---
            const plantTS = new TomSelect("#plant_select", {
                create: false
            });
            const documentTS = new TomSelect("#document_select", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
            const modelTS = new TomSelect("#model_select", {
                create: false
            });
            const productTS = new TomSelect("#product_select", {
                create: false
            });
            const processTS = new TomSelect("#process_select", {
                create: false
            });
            const partNumberTS = new TomSelect("#partNumber_select", {
                create: false
            });
            const departmentTS = new TomSelect("#department_select", {
                create: false
            });

            // --- Data dari backend ---
            const partNumbers =
            @json($partNumbers); // {id, part_number, plant, model_id, product_id, process_id}
            const models = @json($models); // {id, name, plant}
            const products = @json($products); // {id, name, plant}
            const processes = @json($processes); // {id, name, plant}

            // --- Fungsi reset semua dropdown ---
            function resetDropdowns() {
                [modelTS, productTS, processTS, partNumberTS].forEach(ts => {
                    ts.clear();
                    ts.clearOptions();
                    ts.enable();
                });
            }

            // --- Fungsi menampilkan semua data tanpa filter (saat plant kosong) ---
            function showAllOptions() {
                models.forEach(m => modelTS.addOption({
                    value: m.id,
                    text: m.name
                }));
                products.forEach(p => productTS.addOption({
                    value: p.id,
                    text: p.name
                }));
                processes.forEach(pr => processTS.addOption({
                    value: pr.id,
                    text: pr.name
                }));
                partNumbers.forEach(pn => partNumberTS.addOption({
                    value: pn.id,
                    text: pn.part_number
                }));
            }

            // --- Fungsi filter berdasarkan plant ---
            function filterByPlant(plant) {
                resetDropdowns();

                if (!plant) {
                    // Kalau plant kosong, tampilkan semua data
                    showAllOptions();
                    return;
                }

                const filteredModels = models.filter(m => m.plant === plant);
                const filteredProducts = products.filter(p => p.plant === plant);
                const filteredProcesses = processes.filter(pr => pr.plant === plant);
                const filteredParts = partNumbers.filter(pn => pn.plant === plant);

                filteredModels.forEach(m => modelTS.addOption({
                    value: m.id,
                    text: m.name
                }));
                filteredProducts.forEach(p => productTS.addOption({
                    value: p.id,
                    text: p.name
                }));
                filteredProcesses.forEach(pr => processTS.addOption({
                    value: pr.id,
                    text: pr.name
                }));
                filteredParts.forEach(pn => partNumberTS.addOption({
                    value: pn.id,
                    text: pn.part_number
                }));
            }

            // --- Event saat plant berubah ---
            plantTS.on('change', function(plant) {
                filterByPlant(plant);
            });

            // --- Event saat part number dipilih ---
            partNumberTS.on('change', function(value) {
                const selectedId = parseInt(value);
                if (!selectedId) {
                    // Jika part number dihapus → enable semua
                    modelTS.enable();
                    productTS.enable();
                    processTS.enable();
                    return;
                }

                const selectedPart = partNumbers.find(p => p.id === selectedId);
                if (selectedPart) {
                    // Set otomatis model, product, dan process berdasarkan part
                    if (selectedPart.model_id) modelTS.setValue(selectedPart.model_id, true);
                    if (selectedPart.product_id) productTS.setValue(selectedPart.product_id, true);
                    if (selectedPart.process_id) processTS.setValue(selectedPart.process_id, true);

                    // Disable ketiga field agar tidak diubah manual
                    modelTS.disable();
                    productTS.disable();
                    processTS.disable();
                }
            });

            // --- Saat pertama kali modal dibuka (default: tampilkan semua) ---
            showAllOptions();
        });
    </script>
@endpush
