@if (in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']))
    @foreach ($groupedByPlant as $plant => $documents)
        @foreach ($documents as $mapping)
            @php
                $model_ids = $mapping->productModel->pluck('id')->toArray();
                $product_ids = $mapping->product->pluck('id')->toArray();
                $process_ids = $mapping->process->pluck('id')->toArray();
                $part_ids = $mapping->partNumber->pluck('id')->toArray();
            @endphp

            <div class="modal fade" id="editDocumentModal-{{ $mapping->id }}"
                data-model-ids='@json($model_ids)' data-product-ids='@json($product_ids)'
                data-process-ids='@json($process_ids)' data-part-ids='@json($part_ids)'
                aria-labelledby="editDocumentModalLabel-{{ $mapping->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                        {{-- Header --}}
                        <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                            style="background-color: #f5f5f7;">
                            <h5 class="modal-title fw-semibold text-dark"
                                id="editDocumentModalLabel-{{ $mapping->id }}"
                                style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                                <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Document Review
                            </h5>
                            <button type="button"
                                class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                                data-bs-dismiss="modal" aria-label="Close"
                                style="width: 36px; height: 36px; border: 1px solid #ddd;">
                                <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body p-5 bg-gray-50"
                            style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                            <form id="editForm-{{ $mapping->id }}"
                                action="{{ route('master.document-review.update2', $mapping->id) }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row g-4">

                                    {{-- Document Name --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Document Name <span
                                                class="text-danger">*</span></label>
                                        <select name="document_id"
                                            class="form-select border-0 shadow-sm rounded-3 tom-select" required>
                                            @foreach ($documentsMaster as $doc)
                                                <option value="{{ $doc->id }}"
                                                    @if ($mapping->document_id == $doc->id) selected @endif>
                                                    {{ $doc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Document Number --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Document Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="document_number"
                                            class="form-control border-0 shadow-sm rounded-3"
                                            value="{{ $mapping->document_number }}">
                                    </div>

                                    {{-- Plant --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Plant <span
                                                class="text-danger">*</span></label>
                                        <select name="plant"
                                            class="form-select border-0 shadow-sm rounded-3 tom-select" required>
                                            <option value="body" {{ $mapping->plant == 'body' ? 'selected' : '' }}>
                                                Body</option>
                                            <option value="unit" {{ $mapping->plant == 'unit' ? 'selected' : '' }}>
                                                Unit</option>
                                            <option value="electric"
                                                {{ $mapping->plant == 'electric' ? 'selected' : '' }}>
                                                Electric</option>
                                        </select>
                                    </div>

                                    {{-- Department --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Department <span
                                                class="text-danger">*</span></label>
                                        <select name="department_id"
                                            class="form-select border-0 shadow-sm rounded-3 tom-select" required>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}"
                                                    {{ $mapping->department_id == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Model --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Model <span
                                                class="text-danger">*</span></label>
                                        <select name="model_id[]" multiple
                                            class="form-select border-0 shadow-sm rounded-3 tom-select">
                                            @foreach ($models as $model)
                                                <option value="{{ $model->id }}"
                                                    {{ in_array($model->id, $model_ids) ? 'selected' : '' }}>
                                                    {{ $model->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Product --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Product</label>
                                        <select name="product_id[]" multiple
                                            class="form-select border-0 shadow-sm rounded-3 tom-select">
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ in_array($product->id, $product_ids) ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Process --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Process</label>
                                        <select name="process_id[]" multiple
                                            class="form-select border-0 shadow-sm rounded-3 tom-select text-capitalize">
                                            @foreach ($processes as $process)
                                                <option value="{{ $process->id }}"
                                                    {{ in_array($process->id, $process_ids) ? 'selected' : '' }}>
                                                    {{ $process->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Part Number --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Part Number</label>
                                        <select name="part_number_id[]" multiple
                                            class="form-select border-0 shadow-sm rounded-3 tom-select">
                                            @foreach ($partNumbers as $part)
                                                <option value="{{ $part->id }}"
                                                    {{ in_array($part->id, $part_ids) ? 'selected' : '' }}>
                                                    {{ $part->part_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="col-12 mb-4">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="hidden" name="notes" id="notes_input_edit{{ $mapping->id }}"
                                            value="{{ old('notes', $mapping->notes) }}">
                                        <div id="quill_editor_edit{{ $mapping->id }}"
                                            class="bg-white rounded-3 shadow-sm p-2"
                                            style="min-height: 100px; max-height: 130px; overflow-y: auto; border: 1px solid #e2e8f0;">
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                            <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                                data-bs-dismiss="modal"
                                style="text-decoration: none; transition: background-color 0.3s ease;">
                                Cancel
                            </button>
                            <button type="submit"
                                class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
                                form="editForm-{{ $mapping->id }}">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endif

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            async function fetchJson(url) {
                try {
                    const res = await fetch(url);
                    if (!res.ok) throw new Error("HTTP " + res.status);
                    return await res.json();
                } catch (e) {
                    console.error("fetchJson error:", url, e);
                    return null;
                }
            }

            function createTS(el, opts = {}) {
                if (!el) return null;
                if (el.tomselect) return el.tomselect;
                return new TomSelect(el, Object.assign({
                    allowEmptyOption: true,
                    maxOptions: 100,
                    placeholder: "-- Select --",
                    plugins: ['remove_button'],
                }, opts));
            }

            function setOptions(ts, items = []) {
                if (!ts) return;
                ts.clearOptions();
                (items || []).forEach(i => ts.addOption({
                    value: i.id,
                    text: i.text ?? i.name ?? i.part_number ?? String(i.id)
                }));
                ts.refreshOptions(false);
            }

            function disableAndClear(ts) {
                if (!ts) return;
                ts.clear(true);
                ts.clearOptions();
                ts.disable();
            }

            function enableTS(ts) {
                if (!ts) return;
                ts.enable();
            }

            function initQuill(idEditor, idInput) {
                const el = document.getElementById(idEditor);
                const hidden = document.getElementById(idInput);
                if (!el || !hidden) return;
                const quill = new Quill(el, {
                    theme: "snow",
                    placeholder: 'Write your notes here...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            ['clean']
                        ]
                    }
                });
                quill.root.innerHTML = hidden.value ?? "";
                const form = hidden.closest("form");
                if (form) form.addEventListener("submit", () => {
                    hidden.value = quill.root.innerHTML;
                });
            }

            document.querySelectorAll('[id^="editForm-"]').forEach(form => {
                const id = form.id.split("-").pop();
                const plantEl = form.querySelector('[name="plant"]');
                const modelEl = form.querySelector('[name="model_id[]"]');
                const prodEl = form.querySelector('[name="product_id[]"]');
                const procEl = form.querySelector('[name="process_id[]"]');
                const partEl = form.querySelector('[name="part_number_id[]"]');
                const deptEl = form.querySelector('[name="department_id"]');
                const docEl = form.querySelector('[name="document_id"]');

                const tsPlant = createTS(plantEl);
                const tsModel = createTS(modelEl, {
                    maxItems: null
                });
                const tsProduct = createTS(prodEl, {
                    maxItems: null
                });
                const tsProcess = createTS(procEl, {
                    maxItems: null
                });
                const tsPart = createTS(partEl, {
                    maxItems: null
                });
                const tsDept = createTS(deptEl);
                const tsDoc = createTS(docEl);

                async function loadByPlant(plant) {
                    if (!plant) {
                        disableAndClear(tsModel);
                        disableAndClear(tsProduct);
                        disableAndClear(tsProcess);
                        disableAndClear(tsPart);
                        tsDept.clear(true);
                        tsDept.disable();
                        return;
                    }
                    const p = encodeURIComponent(plant);
                    const [models, products, processes, departments, parts] = await Promise.all([
                        fetchJson(`/api/models?plant=${p}`),
                        fetchJson(`/api/products?plant=${p}`),
                        fetchJson(`/api/processes?plant=${p}`),
                        fetchJson(`/api/departments?plant=${p}`),
                        fetchJson(`/api/part-numbers?plant=${p}`)
                    ]);
                    setOptions(tsModel, models || []);
                    setOptions(tsProduct, products || []);
                    setOptions(tsProcess, processes || []);
                    setOptions(tsDept, departments || []);
                    setOptions(tsPart, parts || []);
                    enableTS(tsModel);
                    enableTS(tsProduct);
                    enableTS(tsProcess);
                    enableTS(tsPart);
                    enableTS(tsDept);
                }

                tsPlant.on("change", async val => {
                    disableAndClear(tsModel);
                    disableAndClear(tsProduct);
                    disableAndClear(tsProcess);
                    disableAndClear(tsPart);
                    tsDept.clear(true);
                    tsDept.disable();
                    if (!val) return;
                    await loadByPlant(val);
                });

                tsPart.on("change", async val => {
                    if (!val || val.length === 0) {
                        disableAndClear(tsModel);
                        disableAndClear(tsProduct);
                        disableAndClear(tsProcess);
                        tsDept.enable();
                        return;
                    }
                    if (!Array.isArray(val)) val = [val];
                    let allProducts = [],
                        allModels = [],
                        allProcesses = [];
                    for (const partId of val) {
                        const detail = await fetchJson(
                            `/api/part-number-details/${encodeURIComponent(partId)}`);
                        if (detail && !detail.error) {
                            if (detail.product) allProducts.push(detail.product);
                            if (detail.model) allModels.push(detail.model);
                            if (detail.process) allProcesses.push(detail.process);
                        }
                    }
                    const uniqueById = arr => [...new Map(arr.map(i => [i.id, i])).values()];
                    const formatTS = arr => arr.map(i => ({
                        value: i.id,
                        text: i.name ?? i.part_number ?? String(i.id)
                    }));

                    tsProduct.clearOptions();
                    tsProduct.addOptions(formatTS(uniqueById(allProducts)));
                    tsProduct.setValue(uniqueById(allProducts).map(p => p.id));
                    tsProduct.enable();

                    tsModel.clearOptions();
                    tsModel.addOptions(formatTS(uniqueById(allModels)));
                    tsModel.setValue(uniqueById(allModels).map(m => m.id));
                    tsModel.enable();

                    tsProcess.clearOptions();
                    tsProcess.addOptions(formatTS(uniqueById(allProcesses)));
                    tsProcess.setValue(uniqueById(allProcesses).map(p => p.id));
                    tsProcess.enable();
                });

                const modalEl = document.getElementById(`editDocumentModal-${id}`);
                modalEl.addEventListener("shown.bs.modal", async () => {
                    // Inisialisasi Quill hanya 1x
                    if (modalEl.dataset.quillInitialized === "1") return;
                    initQuill(`quill_editor_edit${id}`, `notes_input_edit${id}`);

                    // Set multi-select default value
                    const modelIds = JSON.parse(modalEl.dataset.modelIds || "[]");
                    const productIds = JSON.parse(modalEl.dataset.productIds || "[]");
                    const processIds = JSON.parse(modalEl.dataset.processIds || "[]");
                    const partIds = JSON.parse(modalEl.dataset.partIds || "[]");

                    // Set value hanya untuk modal ini
                    tsModel.setValue(modelIds);
                    tsProduct.setValue(productIds);
                    tsProcess.setValue(processIds);
                    tsPart.setValue(partIds);

                    modalEl.dataset.quillInitialized = "1";
                });

            });
        });
    </script>
@endpush
