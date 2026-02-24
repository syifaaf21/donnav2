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
                                class="btn btn-light position-absolute top-0 end-0 m-3 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                data-bs-dismiss="modal" aria-label="Close"
                                style="width: 36px; height: 36px; border: 1px solid #ddd;">
                                <i class="bi bi-x-lg"></i>
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

            // Add caching and request deduplication
            let activeRequests = new Map();
            let requestCache = new Map();

            async function fetchJson(url) {
                // Check cache first
                if (requestCache.has(url)) {
                    return requestCache.get(url);
                }

                // If request is already in progress, wait for it
                if (activeRequests.has(url)) {
                    return await activeRequests.get(url);
                }

                const requestPromise = (async () => {
                    try {
                        const res = await fetch(url);
                        if (!res.ok) throw new Error("HTTP " + res.status);
                        const data = await res.json();

                        // Cache for 5 minutes
                        requestCache.set(url, data);
                        setTimeout(() => requestCache.delete(url), 300000);

                        return data;
                    } catch (e) {
                        console.error("fetchJson error:", url, e);
                        return null;
                    } finally {
                        activeRequests.delete(url);
                    }
                })();

                activeRequests.set(url, requestPromise);
                return await requestPromise;
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
                            ['bold', 'italic', 'strike'],
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
                // Tambahkan loading pada tombol submit saat form disubmit
                form.addEventListener('submit', function() {
                    // Sync TomSelect values to original selects before submit
                    try {
                        syncSelectFromTom(tsModel, modelEl);
                        syncSelectFromTom(tsProduct, prodEl);
                        syncSelectFromTom(tsProcess, procEl);
                        syncSelectFromTom(tsPart, partEl);
                        if (tsDept && deptEl) deptEl.value = tsDept.getValue() || '';
                        if (tsDoc && docEl) docEl.value = tsDoc.getValue() || '';
                    } catch (e) {
                        console.error('Sync on submit failed', e);
                    }

                    // Cari tombol submit di seluruh dokumen yang memiliki atribut form sesuai id form
                    const submitBtn = document.querySelector('button[type="submit"][form="' + form.id + '"]');
                    if (submitBtn) {
                        submitBtn.dataset.originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
                        submitBtn.disabled = true;
                    }
                });
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

                // --- Helper: debounce ---
                const debounce = (fn, wait = 200) => {
                    let t;
                    return (...args) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn.apply(this, args), wait);
                    };
                };

                // --- Helper: sync TomSelect -> original select element ---
                // Marks auto-added options with data-generated so we can remove them if unselected
                const syncSelectFromTom = (ts, orig) => {
                    if (!ts || !orig) return;
                    const vals = (Array.isArray(ts.getValue()) ? ts.getValue() : (ts.getValue() ? [ts.getValue()] : [])) || [];
                    // Ensure options exist for selected values
                    vals.forEach(v => {
                        if (!orig.querySelector('option[value="' + v + '"]')) {
                            const opt = document.createElement('option');
                            opt.value = v;
                            opt.text = (ts.options && ts.options[v] && (ts.options[v].text || ts.options[v].name)) || v;
                            opt.setAttribute('data-generated', '1');
                            orig.appendChild(opt);
                        }
                    });
                    // Remove previously generated options that are no longer selected
                    Array.from(orig.querySelectorAll('option[data-generated="1"]')).forEach(opt => {
                        if (!vals.includes(opt.value)) opt.remove();
                    });
                    // Mark selected flags
                    Array.from(orig.options).forEach(opt => {
                        opt.selected = vals.includes(opt.value);
                    });
                };

                // Track whether user manually modified a field to avoid auto-fill overwrites
                const userModified = {
                    model: false,
                    product: false,
                    process: false,
                    part: false
                };

                // When user changes TomSelect values, sync immediately and mark as modified
                if (tsModel) tsModel.on('change', val => {
                    if (isInitializing || isAutoFillingFromPart) return;
                    userModified.model = true;
                    syncSelectFromTom(tsModel, modelEl);
                    generateDocumentNumberEdit();
                });

                if (tsProduct) tsProduct.on('change', val => {
                    if (isInitializing || isAutoFillingFromPart) return;
                    userModified.product = true;
                    syncSelectFromTom(tsProduct, prodEl);
                    generateDocumentNumberEdit();
                });

                if (tsProcess) tsProcess.on('change', val => {
                    if (isInitializing || isAutoFillingFromPart) return;
                    userModified.process = true;
                    syncSelectFromTom(tsProcess, procEl);
                    generateDocumentNumberEdit();
                });

                if (tsPart) tsPart.on('change', val => {
                    if (isInitializing) return;
                    userModified.part = true;
                    syncSelectFromTom(tsPart, partEl);
                    // existing part handler below will run as well; keep behavior
                });

                // Flag to prevent triggering cascade during initialization
                let isInitializing = false;

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
                    if (isInitializing) return; // Skip during init

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
                    if (isInitializing) return;

                    if (!val || val.length === 0) {
                        const currentPlant = tsPlant.getValue();
                        if (currentPlant) {
                            await loadByPlant(currentPlant);
                        }
                        return;
                    }

                    if (!Array.isArray(val)) val = [val];

                    isAutoFillingFromPart = true; // 🔥 start lock

                    let allProducts = [],
                        allModels = [],
                        allProcesses = [];

                    const detailPromises = val.map(partId =>
                        fetchJson(`/api/part-number-details/${encodeURIComponent(partId)}`)
                    );

                    const details = await Promise.all(detailPromises);

                    details.forEach(detail => {
                        if (detail && !detail.error) {
                            if (detail.product) allProducts.push(detail.product);
                            if (detail.model) allModels.push(detail.model);
                            if (detail.process) allProcesses.push(detail.process);
                        }
                    });

                    const uniqueById = arr => [...new Map(arr.map(i => [i.id, i])).values()];
                    const formatTS = arr => arr.map(i => ({
                        value: i.id,
                        text: i.text ?? i.name ?? i.part_number ?? String(i.id),
                    }));

                        // Only auto-fill fields that the user hasn't manually modified
                        if (!userModified.product) {
                            tsProduct.clear(true);
                            tsProduct.clearOptions();
                            tsProduct.addOptions(formatTS(uniqueById(allProducts)));
                            tsProduct.setValue(uniqueById(allProducts).map(p => p.id), true);
                            // sync to original select
                            syncSelectFromTom(tsProduct, prodEl);
                        }

                        if (!userModified.model) {
                            tsModel.clear(true);
                            tsModel.clearOptions();
                            tsModel.addOptions(formatTS(uniqueById(allModels)));
                            tsModel.setValue(uniqueById(allModels).map(m => m.id), true);
                            // sync to original select
                            syncSelectFromTom(tsModel, modelEl);
                        }

                        if (!userModified.process) {
                            tsProcess.clear(true);
                            tsProcess.clearOptions();
                            tsProcess.addOptions(formatTS(uniqueById(allProcesses)));
                            tsProcess.setValue(uniqueById(allProcesses).map(p => p.id), true);
                            // sync to original select
                            syncSelectFromTom(tsProcess, procEl);
                        }

                    isAutoFillingFromPart = false; // 🔥 release lock

                    generateDocumentNumberEdit();
                });


                const modalEl = document.getElementById(`editDocumentModal-${id}`);
                modalEl.addEventListener("shown.bs.modal", async () => {
                    isInitializing = true; // Set flag to prevent cascade

                    const currentPlant = tsPlant.getValue();

                    // 1️⃣ FILTER ULANG semua options berdasarkan plant saat ini
                    if (currentPlant) {
                        await loadByPlant(currentPlant);
                    }

                    // 2️⃣ Inisialisasi Quill hanya sekali
                    if (modalEl.dataset.quillInitialized !== "1") {
                        initQuill(`quill_editor_edit${id}`, `notes_input_edit${id}`);
                        modalEl.dataset.quillInitialized = "1";
                    }

                    // 3️⃣ Set ulang value untuk model/product/process/part
                    const modelIds = JSON.parse(modalEl.dataset.modelIds || "[]");
                    const productIds = JSON.parse(modalEl.dataset.productIds || "[]");
                    const processIds = JSON.parse(modalEl.dataset.processIds || "[]");
                    const partIds = JSON.parse(modalEl.dataset.partIds || "[]");

                    // Set values without triggering change events
                    tsModel.setValue(modelIds, true);
                    tsProduct.setValue(productIds, true);
                    tsProcess.setValue(processIds, true);

                    const deptId = form.querySelector('[name="department_id"]').value;
                    if (deptId) tsDept.setValue(deptId, true);

                    // Set part number last and allow it to trigger after init
                    setTimeout(() => {
                        isInitializing = false; // Release flag
                        tsPart.setValue(partIds, true);
                    }, 100);
                });

                const documentNumberInput = form.querySelector('[name="document_number"]');

                async function generateDocumentNumberEditImmediate() {
                    if (isInitializing || isAutoFillingFromPart) return;

                    const documentId = tsDoc.getValue();
                    const departmentId = tsDept.getValue();
                    const productIds = tsProduct.getValue() || [];
                    const processIds = tsProcess.getValue() || [];
                    const modelIds = tsModel.getValue() || [];

                    // Minimal required
                    if (!documentId || !departmentId) {
                        if (documentNumberInput) documentNumberInput.value = '';
                        return;
                    }

                    const payload = {
                        document_id: Number(documentId),
                        department_id: Number(departmentId),
                        product_id: productIds.length ? Number(productIds[0]) : null,
                        process_id: processIds.length ? Number(processIds[0]) : null,
                        model_id: modelIds.length ? Number(modelIds[0]) : null,
                        format: 3,
                    };

                    // Immediate feedback
                    if (documentNumberInput) documentNumberInput.value = 'Generating...';

                    try {
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

                        if (json?.success && documentNumberInput) {
                            documentNumberInput.value = json.document_number;
                        }

                    } catch (err) {
                        console.error('Generate edit error:', err);
                        if (documentNumberInput) documentNumberInput.value = '';
                    }
                }

                const generateDocumentNumberEdit = debounce(generateDocumentNumberEditImmediate, 180);

                tsDoc.on('change', () => generateDocumentNumberEdit());
                tsDept.on('change', () => generateDocumentNumberEdit());
                tsProduct.on('change', () => {
                    syncSelectFromTom(tsProduct, prodEl);
                    generateDocumentNumberEdit();
                });
                tsProcess.on('change', () => {
                    syncSelectFromTom(tsProcess, procEl);
                    generateDocumentNumberEdit();
                });
                tsModel.on('change', () => {
                    syncSelectFromTom(tsModel, modelEl);
                    generateDocumentNumberEdit();
                });

                let isAutoFillingFromPart = false;
            });
        });
    </script>
@endpush
