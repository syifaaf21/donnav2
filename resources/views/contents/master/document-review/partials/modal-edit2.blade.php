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
                    <div class="modal-content border-0 rounded-4 shadow-lg">

                        {{-- Header --}}
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="editDocumentModalLabel-{{ $mapping->id }}">
                                <i class="bi bi-pencil-square me-2"></i> Edit Document Review
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body p-4">
                            <form id="editForm-{{ $mapping->id }}"
                                action="{{ route('master.document-review.update2', $mapping->id) }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row g-3">

                                    {{-- Document Name --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Document Name <span
                                                class="text-danger">*</span></label>
                                        <select name="document_id" class="form-select tom-select" required>
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
                                        <label class="form-label fw-medium">Document Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="document_number" class="form-control"
                                            value="{{ $mapping->document_number }}">
                                    </div>

                                    {{-- Plant --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Plant <span
                                                class="text-danger">*</span></label>
                                        <select name="plant" class="form-select tom-select" required>
                                            <option value="body" {{ $mapping->plant == 'body' ? 'selected' : '' }}>
                                                Body</option>
                                            <option value="unit" {{ $mapping->plant == 'unit' ? 'selected' : '' }}>
                                                Unit</option>
                                            <option value="electric"
                                                {{ $mapping->plant == 'electric' ? 'selected' : '' }}>Electric</option>
                                        </select>
                                    </div>

                                    {{-- Department --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Department <span
                                                class="text-danger">*</span></label>
                                        <select name="department_id" class="form-select tom-select" required>
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
                                        <label class="form-label fw-medium">Model <span
                                                class="text-danger">*</span></label>
                                        <select name="model_id[]" multiple class="form-select tom-select">
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
                                        <label class="form-label fw-medium">Product</label>
                                        <select name="product_id[]" multiple class="form-select tom-select">
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
                                        <label class="form-label fw-medium">Process</label>
                                        <select name="process_id[]" multiple
                                            class="form-select tom-select text-capitalize">
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
                                        <label class="form-label fw-medium">Part Number</label>
                                        <select name="part_number_id[]" multiple class="form-select tom-select">
                                            @foreach ($partNumbers as $part)
                                                <option value="{{ $part->id }}"
                                                    {{ in_array($part->id, $part_ids) ? 'selected' : '' }}>
                                                    {{ $part->part_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-medium">Notes</label>
                                        <input type="hidden" name="notes" id="notes_input_edit{{ $mapping->id }}"
                                            value="{{ old('notes', $mapping->notes) }}">
                                        <div id="quill_editor_edit{{ $mapping->id }}"
                                            class="bg-white border-1 shadow-sm rounded"
                                            style="min-height: 100px; max-height: 130px; overflow-y: auto;">
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" form="editForm-{{ $mapping->id }}">Save
                                Changes</button>
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
                    placeholder: "-- Select --"
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
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
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
