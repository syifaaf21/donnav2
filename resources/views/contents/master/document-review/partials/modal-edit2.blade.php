@if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
    @foreach ($groupedByPlant as $plant => $documents)
        @foreach ($documents as $mapping)
            <div class="modal fade" id="editDocumentModal-{{ $mapping->id }}" tabindex="-1"
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
                                        <label class="form-label fw-medium">Document Number</label>
                                        <input type="text" name="document_number" class="form-control"
                                            value="{{ $mapping->document_number }}">
                                    </div>

                                    {{-- Plant --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Plant <span
                                                class="text-danger">*</span></label>
                                        <select name="plant" class="form-select tom-select" required>
                                            <option value="body"
                                                {{ strtolower($mapping->partNumber->plant ?? '') == 'body' ? 'selected' : '' }}>
                                                Body</option>
                                            <option value="unit"
                                                {{ strtolower($mapping->partNumber->plant ?? '') == 'unit' ? 'selected' : '' }}>
                                                Unit</option>
                                            <option value="electric"
                                                {{ strtolower($mapping->partNumber->plant ?? '') == 'electric' ? 'selected' : '' }}>
                                                Electric</option>
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
                                        <label class="form-label fw-medium">Model</label>
                                        <select name="model_id" class="form-select tom-select">
                                            <option value="">-- Select Model --</option>
                                            @foreach ($models as $model)
                                                <option value="{{ $model->id }}"
                                                    {{ $mapping->model_id == $model->id ? 'selected' : '' }}>
                                                    {{ $model->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Product --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Product</label>
                                        <select name="product_id" class="form-select tom-select">
                                            <option value="">-- Select Product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ $mapping->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Process --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Process</label>
                                        <select name="process_id" class="form-select tom-select text-capitalize">
                                            <option value="">-- Select Process --</option>
                                            @foreach ($processes as $process)
                                                <option value="{{ $process->id }}"
                                                    {{ $mapping->process_id == $process->id ? 'selected' : '' }}>
                                                    {{ $process->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Part Number --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Part Number</label>
                                        <select name="part_number_id" class="form-select tom-select">
                                            <option value="">-- Select Part Number --</option>
                                            @foreach ($partNumbers as $part)
                                                <option value="{{ $part->id }}"
                                                    {{ $mapping->part_number_id == $part->id ? 'selected' : '' }}>
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
                                        <small class="text-muted">You can format your notes with bold, italic, colors,
                                            and more.</small>
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

            /* ----------- Helpers ----------- */
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

                // Prevent double initialization
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
                (items || []).forEach(i => {
                    ts.addOption({
                        value: i.id,
                        text: i.text ?? i.name ?? i.part_number ?? String(i.id),
                    });
                });
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
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ["bold", "italic", "underline", "strike"],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            [{
                                list: "ordered"
                            }, {
                                list: "bullet"
                            }],
                            [{
                                align: []
                            }],
                            ["clean"]
                        ]
                    }
                });

                quill.root.innerHTML = hidden.value ?? "";

                const form = hidden.closest("form");
                if (form) {
                    form.addEventListener("submit", () => {
                        hidden.value = quill.root.innerHTML;
                    });
                }
            }

            /* INIT FOR ALL EDIT FORMS
             */
            document.querySelectorAll('[id^="editForm-"]').forEach(form => {

                const id = form.id.split("-").pop();

                // GET ALL FIELDS
                const plantEl = form.querySelector('[name="plant"]');
                const modelEl = form.querySelector('[name="model_id"]');
                const prodEl = form.querySelector('[name="product_id"]');
                const procEl = form.querySelector('[name="process_id"]');
                const partEl = form.querySelector('[name="part_number_id"]');
                const deptEl = form.querySelector('[name="department_id"]');
                const docEl = form.querySelector('[name="document_id"]');

                // INIT TOMSELECT
                const tsPlant = createTS(plantEl);
                const tsModel = createTS(modelEl);
                const tsProduct = createTS(prodEl);
                const tsProcess = createTS(procEl);
                const tsPart = createTS(partEl);
                const tsDept = createTS(deptEl);
                const tsDoc = createTS(docEl);

                // LOAD ALL BASED ON PLANT
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

                /*
                   ON PLANT CHANGE (MAIN LOGIC)
                - */
                tsPlant.on("change", async val => {

                    // Hard reset dulu
                    disableAndClear(tsModel);
                    disableAndClear(tsProduct);
                    disableAndClear(tsProcess);
                    disableAndClear(tsPart);

                    tsDept.clear(true);
                    tsDept.disable();

                    if (!val) return;

                    await loadByPlant(val);
                });

                /*
                   PART NUMBER CHANGE
                - */
                tsPart.on("change", async val => {

                    if (!val) {
                        disableAndClear(tsModel);
                        disableAndClear(tsProduct);
                        disableAndClear(tsProcess);

                        tsDept.clear(true);
                        tsDept.enable();
                        return;
                    }

                    const detail = await fetchJson(
                        `/api/part-number-details/${encodeURIComponent(val)}`);
                    if (!detail) return;

                    // Auto-fill model
                    if (detail.model?.id) {
                        tsModel.addOption({
                            value: detail.model.id,
                            text: detail.model.name
                        });
                        tsModel.setValue(detail.model.id, true);
                        tsModel.enable();
                    }

                    // Auto-fill product
                    if (detail.product?.id) {
                        tsProduct.addOption({
                            value: detail.product.id,
                            text: detail.product.name
                        });
                        tsProduct.setValue(detail.product.id, true);
                        tsProduct.enable();
                    }

                    // Auto-fill process
                    if (detail.process?.id) {
                        tsProcess.addOption({
                            value: detail.process.id,
                            text: detail.process.name
                        });
                        tsProcess.setValue(detail.process.id, true);
                        tsProcess.enable();
                    }
                });

                /*INIT QUILL EDITOR */
                const modalEl = document.getElementById(`editDocumentModal-${id}`);

                modalEl.addEventListener("shown.bs.modal", () => {

                    // Cegah double-init
                    if (modalEl.dataset.quillInitialized === "1") return;

                    initQuill(`quill_editor_edit${id}`, `notes_input_edit${id}`);

                    modalEl.dataset.quillInitialized = "1";
                });
            }); // END LOOP FORM
        });
    </script>
@endpush
