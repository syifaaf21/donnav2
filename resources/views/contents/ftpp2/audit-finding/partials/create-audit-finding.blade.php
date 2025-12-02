<form action="{{ route('ftpp.audit-finding.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- CARD WRAPPER -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
            <!-- AUDIT TYPE -->
            <div>
                <label class="font-semibold block">Audit Type: <span class="text-danger">*</span></label>
                <div class="mt-2 space-y-1">
                    @foreach ($auditTypes as $type)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="audit_type_id" value="{{ $type->id }}"
                                x-model="form.audit_type_id">
                            {{ $type->name }}
                        </label>
                    @endforeach
                </div>

                <!-- SUB AUDIT TYPE -->
                <div id="subAuditType" class="mt-2 ml-4 space-y-1">
                    @foreach ($subAudit as $sub)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="sub_audit_type_id" value="{{ $sub->id }}" disabled>
                            {{ $sub->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- DEPARTMENT / PROCESS / PRODUCT -->
            <div class="space-y-1">
                <label class="font-semibold">Department / Process / Product: <span class="text-danger">*</span></label>

                <button type="button"
                    class="px-3 py-1 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors"
                    onclick="openPlantSidebar()">
                    Choose Dept/Process/Product
                </button>

                <input type="hidden" id="selectedPlant" name="plant">
                <input type="hidden" id="selectedDepartment" name="department_id" x-model="form.department_id">
                <input type="hidden" id="selectedProcess" name="process_id" x-model="form.process_id">
                <input type="hidden" id="selectedProduct" name="product_id" x-model="form.product_id">

                <div id="plantDeptDisplay" x-text="form._plant_display ?? '-'" class="mt-1 text-gray-700">
                </div>
            </div>

            <!-- AUDITEE -->
            <div class="space-y-1">
                <label class="font-semibold">Auditee: <span class="text-danger">*</span></label>

                <button type="button" onclick="openAuditeeSidebar()"
                    class="px-3 py-1 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                    Select Auditee
                </button>

                <div id="selectedAuditees" x-html="form._auditee_html ?? ''" class="flex flex-wrap gap-2 mt-2">
                </div>

                <!-- Hidden holder for selected auditees (NOT submitted directly).
                     We avoid naming this input so only the dynamic `auditee_ids[]` inputs
                     created by `saveHeaderOnly()` are included in the POST request. -->
                <input type="hidden" id="auditee_ids" x-model="form.auditee_ids">
            </div>

            <!-- ROW: Auditor / Date / Reg Number (tidak full width -> gunakan grid kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="font-semibold block">Auditor / Inisiator: <span class="text-danger">*</span></label>
                    <select name="auditor_id" x-model="form.auditor_id"
                        class="border border-gray-300 rounded w-full p-2 focus:outline-none">
                        <option value="">-- Choose Auditor --</option>
                        @foreach ($auditors as $auditor)
                            <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold block">Date: <span class="text-danger">*</span></label>
                    <input type="date" name="created_at" x-model="form.created_at"
                        class="border border-gray-300 rounded w-full p-2 focus:outline-none"
                        value="{{ now()->toDateString() }}">
                </div>

                <div>
                    <label class="font-semibold block">Registration Number: <span class="text-danger">*</span></label>
                    <input type="text" name="registration_number" id="reg_number" x-model="form.registration_number"
                        class="border border-gray-300 rounded w-full p-2 bg-gray-100" readonly>
                </div>
            </div>
            <!-- FINDING CATEGORY -->
            <div>
                <label class="font-semibold">Finding Category: <span class="text-danger">*</span></label>
                <div class="mt-1">
                    @foreach ($findingCategories as $category)
                        <label class="mr-4">
                            <input type="radio" name="finding_category_id" x-model="form.finding_category_id"
                                value="{{ $category->id }}">
                            {{ ucfirst($category->name) }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                <h5 class="font-semibold text-gray-700">AUDITOR / INISIATOR</h5>

                <!-- FINDING -->
                <div>
                    <label class="font-semibold">Finding / Issue: <span class="text-danger">*</span></label>
                    <textarea name="finding_description" x-model="form.finding_description" class="w-full border rounded p-2 h-24" required></textarea>
                </div>

                <div class="flex justify-between items-start">
                    <!-- DUE DATE -->
                    <div>
                        <label class="font-semibold">Duedate: <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" x-model="form.due_date"
                            class="border-b border-gray-400 ml-2" required min="{{ now()->toDateString() }}">
                    </div>

                    <!-- CLAUSE SELECT -->
                    <div class="text-right">
                        <button type="button" onclick="openSidebar()"
                            class="px-3 py-1  bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                            Select Clause
                        </button>

                        <input type="hidden" id="selectedSub" name="sub_klausul_id[]">

                        <div id="selectedSubContainer" class="flex flex-wrap gap-2 mt-3 justify-end"></div>
                    </div>
                </div>
            </div>

            {{-- ATTACHMENT --}}
            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                <div class="font-semibold text-lg text-gray-700">Attachments</div>
                <p class="text-sm text-gray-400">Only PDF, png, jpg, and jpeg files are allowed.</p>
                <div>
                    <!-- Preview containers (sesuaikan posisi di form) -->
                    <div id="previewImageContainer" class="mt-2 flex flex-wrap gap-2"></div>
                    <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>

                    <!-- Attachment button (paperclip) -->
                    <div class="relative inline-block">
                        @if (in_array(optional(auth()->user()->roles->first())->name, ['Admin', 'Auditor']))
                            <button id="attachBtn" type="button"
                                class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                                aria-haspopup="true" aria-expanded="false" title="Attach files">
                                <i data-feather="paperclip" class="w-4 h-4"></i>
                                <span id="attachCount" class="text-xs text-gray-600 hidden">0</span>
                            </button>
                        @endif

                        <!-- Small menu seperti email (hidden, muncul saat klik) -->
                        <div id="attachMenu"
                            class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow-lg z-20">
                            <button id="attachImages" type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <i data-feather="image" class="w-4 h-4"></i>
                                <span class="text-sm">Upload Images</span>
                            </button>
                            <button id="attachDocs" type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <i data-feather="file-text" class="w-4 h-4"></i>
                                <span class="text-sm">Upload Documents</span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden file inputs -->
                    <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple class="hidden">
                    <input type="file" id="fileInput" name="files[]" accept=".pdf" multiple class="hidden">
                </div>
                <button type="button" onclick="saveHeaderOnly()"
                    class="ml-auto mt-2 bg-gradient-to-r from-primary to-primaryDark text-white px-3 py-1 rounded-md hover:from-primaryDark hover:to-primary transition-colors">
                    Save Finding
                </button>
            </div>
        </div>
    </div>
</form>
<!-- Sidebar Klausul -->
<div id="sidebarKlausul"
    class="fixed top-0 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
        <h2 class="font-semibold text-lg text-gray-800">Select Clause</h2>
        <button type="button" onclick="closeSidebar()"
            class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
            <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
        </button>
    </div>

    <!-- Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Clause:</label>
    <select id="selectKlausul"
        class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 mb-4">
        <option value="">-- Choose Clause --</option>
    </select>

    <!-- Head Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Head Clause:</label>
    <select id="selectHead" class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 disabled:opacity-50 mb-4"
        disabled>
        <option value="">-- Choose Head Clause --</option>
    </select>

    <!-- Sub Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Sub Clause:</label>
    <select id="selectSub" class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 disabled:opacity-50 mb-4"
        disabled>
        <option value="">-- Choose Sub Clause --</option>
    </select>

    <!-- Submit Button -->
    <button type="button" onclick="addSubKlausul()"
        class="flex items-center justify-center gap-2 px-4 py-2 w-full rounded-lg
               bg-gradient-to-r from-primary to-primaryDark text-white hover:from-primaryDark hover:to-primary transition-colors">
        <i data-feather="plus" class="w-4 h-4"></i> Add
    </button>
</div>

{{-- Sidebar Select Dept/Proc/Prod by Plant --}}
<div id="sidebarPlant"
    class="fixed top-0 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
        <h2 class="text-lg font-semibold text-gray-800">Select Plant / Department</h2>
        <button type="button" onclick="closePlantSidebar()"
            class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
            <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
        </button>
    </div>

    <!-- Plant -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Plant:</label>
    <select id="plantSelect"
        class="border border-gray-300 p-2 w-full rounded-lg mb-4 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
        <option value="">-- Choose Plant --</option>
        <option value="Body">Body</option>
        <option value="Unit">Unit</option>
        <option value="Electric">Electric</option>
        <option value="All">All</option>
    </select>

    <!-- Department -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Department:</label>
    <select id="sidebarDepartment" class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4"
        disabled>
        @foreach ($departments as $dept)
            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
        @endforeach
    </select>

    <!-- Process -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Process:</label>
    <select id="sidebarProcess" class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4"
        disabled>
        @foreach ($processes as $proc)
            <option value="{{ $proc->id }}">{{ ucfirst($proc->name) }}</option>
        @endforeach
    </select>

    <!-- Product -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Product:</label>
    <select id="sidebarProduct" class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4"
        disabled>
        @foreach ($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </select>

    <button type="button" onclick="submitSidebarPlant()"
        class="flex items-center justify-center gap-2 px-4 py-2 w-full rounded-lg bg-gradient-to-l from-primary to-primaryDark text-white
               hover:from-primaryDark hover:to-primary transition-colors shadow">
        <i data-feather="plus" class="w-4 h-4"></i> Add
    </button>
</div>

{{-- Sidebar select auditee --}}
<div id="auditeeSidebar"
    class="fixed top-0 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
        <h2 class="text-lg font-semibold text-gray-800">Select Auditee</h2>
        <button type="button" onclick="closeAuditeeSidebar()"
            class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
            <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
        </button>
    </div>

    <select id="auditeeSelect" multiple class="w-full border rounded-lg p-2 bg-gray-50"
        placeholder="Search or select auditee..."></select>

    <button type="button" onclick="saveAuditeeSelection()"
        class="flex items-center justify-center gap-2 mt-4 bg-blue-600 text-white px-4 py-2 w-full rounded-lg
               hover:bg-blue-700 transition shadow">
        <i data-feather="plus" class="w-4 h-4"></i> Add
    </button>
</div>

<x-flash-message />
@push('scripts')
    <script>
        // === SIDEBAR OPEN/CLOSE ===
        function openSidebar() {
            document.getElementById('sidebarKlausul').classList.remove('hidden');

            let auditType = document.querySelector('input[name="audit_type_id"]:checked')?.value;
            if (!auditType) return alert('Please choose audit type first');

            // Ambil klausul berdasarkan audit type
            fetch(`/filter-klausul/${auditType}`)
                .then(res => res.json())
                .then(data => {
                    let klausulSelect = document.getElementById('selectKlausul');
                    klausulSelect.innerHTML = '<option value="">-- Choose Clause --</option>';
                    data.forEach(item => {
                        klausulSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                });
        }

        function closeSidebar() {
            document.getElementById('sidebarKlausul').classList.add('hidden');
        }

        // === Audit type relation handler (sub audit type, auditor, klausul) ===
        document.addEventListener('DOMContentLoaded', function() {
            console.log("✅ FTTP script ready");

            // --- Event delegation untuk audit type (tetap pakai karena sudah aman) ---
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name="audit_type_id"]')) {
                    const auditTypeId = e.target.value;
                    console.log("✅ Audit Type dipilih:", auditTypeId);

                    // Ambil data ke server
                    fetch(`/ftpp/get-data/${auditTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("✅ Data diterima:", data);

                            document.getElementById('reg_number').value = data.reg_number;

                            const subContainer = document.getElementById('subAuditType');
                            subContainer.innerHTML = '';
                            if (data.sub_audit?.length) {
                                data.sub_audit.forEach(s => {
                                    subContainer.insertAdjacentHTML('beforeend', `
                                            <label class="block">
                                                <input type="radio" name="sub_audit_type_id" value="${s.id}">
                                                ${s.name}
                                            </label>
                                        `);
                                    const radio = document.querySelector(
                                        `#subAuditType input[value="${s.id}"]`);
                                    if (radio) radio.disabled = false;
                                });
                            } else {
                                subContainer.innerHTML =
                                    '<small class="text-gray-500">There is no sub audit type</small>';
                            }

                            // ✅ Filter Auditor berdasarkan audit type
                            let auditorSelect = document.querySelector('select[name="auditor_id"]');
                            auditorSelect.innerHTML = '<option value="">-- Choose Auditor --</option>';
                            data.auditors.forEach(auditor => {
                                auditorSelect.innerHTML += `
                                        <option value="${auditor.id}">${auditor.name}</option>
                                    `;
                            });
                        })
                        .catch(error => console.error("❌ Error:", error));
                }
            });

            // --- Open/Close sidebar (tidak berubah) ---
            window.openSidebar = function() {
                document.getElementById('sidebarKlausul').classList.remove('hidden');

                let auditType = document.querySelector('input[name="audit_type_id"]:checked')?.value;
                if (!auditType) return alert('Please choose audit type first');

                fetch(`/filter-klausul/${auditType}`)
                    .then(r => r.json())
                    .then(data => {
                        const klausulSelect = document.getElementById('selectKlausul');
                        klausulSelect.innerHTML = '<option value="">-- Choose Clause --</option>';
                        data.forEach(item => {
                            klausulSelect.insertAdjacentHTML('beforeend',
                                `<option value="${item.id}">${item.name}</option>`);
                        });

                        // reset head & sub
                        const headSelect = document.getElementById('selectHead');
                        const subSelect = document.getElementById('selectSub');
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                    })
                    .catch(err => console.error('Error filter-klausul:', err));
            };

            window.closeSidebar = function() {
                document.getElementById('sidebarKlausul').classList.add('hidden');
            };

            // --- Event delegation for klausul/head/sub selects ---
            document.addEventListener('change', async function(e) {
                const target = e.target;

                // user pilih klausul -> load head klausul
                if (target && target.id === 'selectKlausul') {
                    const klausulId = target.value;
                    console.log('Klausul selected:', klausulId);

                    const headSelect = document.getElementById('selectHead');
                    const subSelect = document.getElementById('selectSub');

                    // reset UI
                    headSelect.innerHTML = '<option value="">-- Loading... --</option>';
                    headSelect.disabled = true;
                    subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                    subSelect.disabled = true;

                    if (!klausulId) {
                        // jika di-set ke kosong, kembalikan disabled state
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                        return;
                    }

                    try {
                        const res = await fetch(`/head-klausul/${klausulId}`);
                        const data = await res.json();
                        console.log('head-klausul response', data);

                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        if (data.length > 0) {
                            data.forEach(h => {
                                headSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${h.id}">${h.code} - ${h.name}</option>`
                                );
                            });
                            headSelect.disabled = false;
                        } else {
                            headSelect.disabled = true;
                        }
                    } catch (err) {
                        console.error('Error fetching head-klausul:', err);
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                    }
                }

                // user pilih head -> load sub klausul
                if (target && target.id === 'selectHead') {
                    const headId = target.value;
                    console.log('Head selected:', headId);
                    const subSelect = document.getElementById('selectSub');
                    subSelect.innerHTML = '<option value="">-- Loading... --</option>';
                    subSelect.disabled = true;

                    if (!headId) {
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                        return;
                    }

                    try {
                        const res = await fetch(`/sub-klausul/${headId}`);
                        const data = await res.json();
                        console.log('sub-klausul response', data);

                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        if (data.length > 0) {
                            data.forEach(s => {
                                subSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${s.id}">${s.code} - ${s.name}</option>`
                                );
                            });
                            subSelect.disabled = false;
                        } else {
                            subSelect.disabled = true;
                        }
                    } catch (err) {
                        console.error('Error fetching sub-klausul:', err);
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                    }
                }
            });

            // pilihSubKlausul remains global function for button onclick
            window.pilihSubKlausul = function() {
                const subSelect = document.getElementById('selectSub');
                if (!subSelect || !subSelect.value) {
                    return alert('Please choose a sub clause first');
                }
                const subId = subSelect.value;
                const subText = subSelect.selectedOptions[0].text;
                document.getElementById('selectedSub').value = subId;
                closeSidebar();
            };

        });

        // === PILIH SUB KLAUSUL (masukkan ke form utama) ===
        function pilihSubKlausul() {
            let subId = document.getElementById('selectSub').value;
            let subText = document.getElementById('selectSub').selectedOptions[0].text;

            document.getElementById('selectedSub').value = subId;

            closeSidebar();
        }

        let selectedSubIds = []; // simpan id yang sudah dipilih

        function addSubKlausul() {
            const subSelect = document.getElementById('selectSub');
            const subId = subSelect.value;
            const subText = subSelect.selectedOptions[0]?.text;

            if (!subId) return alert('Please choose a sub clause first');
            if (selectedSubIds.includes(subId)) return alert('This sub clause is already added');

            selectedSubIds.push(subId);

            // Render preview di form utama
            const container = document.getElementById('selectedSubContainer');
            const span = document.createElement('span');
            span.className = "flex items-end gap-1 bg-blue-100 text-gray-700 px-2 py-1 rounded";
            span.dataset.id = subId;
            span.innerHTML =
                `${subText} <button type="button" class="text-red-500 font-bold"><i data-feather="x" class="w-4 h-4"></i></button>`;
            container.appendChild(span);
            feather.replace(); // render ulang icon feather


            // tombol hapus
            span.querySelector('button').onclick = function() {
                selectedSubIds = selectedSubIds.filter(id => id !== subId);
                span.remove();
            }

            container.appendChild(span);
        }

        // Handle select Department/Process/Product by plant
        let tsDept, tsProc, tsProd; // biar tidak double init

        function openPlantSidebar() {
            document.getElementById('sidebarPlant').classList.remove('hidden');

            const plantSelect = document.getElementById('plantSelect');
            const deptSelect = document.getElementById('sidebarDepartment');
            const procSelect = document.getElementById('sidebarProcess');
            const prodSelect = document.getElementById('sidebarProduct');

            async function loadPlantRelatedData(plant) {
                const deptSelect = document.getElementById('sidebarDepartment');
                const procSelect = document.getElementById('sidebarProcess');
                const prodSelect = document.getElementById('sidebarProduct');

                // Disable select dulu
                deptSelect.disabled = true;
                procSelect.disabled = true;
                prodSelect.disabled = true;

                // Hancurkan TomSelect lama jika ada
                if (tsDept) {
                    tsDept.destroy();
                    tsDept = null;
                }
                if (tsProc) {
                    tsProc.destroy();
                    tsProc = null;
                }
                if (tsProd) {
                    tsProd.destroy();
                    tsProd = null;
                }

                // Reset HTML <select>
                deptSelect.innerHTML = '<option value=""></option>';
                procSelect.innerHTML = '<option value=""></option>';
                prodSelect.innerHTML = '<option value=""></option>';

                if (!plant || plant === "") return;

                try {
                    const [dRes, pRes, prRes] = await Promise.all([
                        fetch(`/get-departments/${plant}`),
                        fetch(`/get-processes/${plant}`),
                        fetch(`/get-products/${plant}`)
                    ]);
                    const [depts, procs, prods] = await Promise.all([
                        dRes.json(),
                        pRes.json(),
                        prRes.json()
                    ]);

                    // Inisialisasi TomSelect lagi
                    tsDept = new TomSelect('#sidebarDepartment', {
                        allowEmptyOption: true,
                        placeholder: "Search Department...",
                        searchField: ['text'],
                        options: depts.map(d => ({
                            value: d.id,
                            text: d.name
                        }))
                    });
                    tsProc = new TomSelect('#sidebarProcess', {
                        allowEmptyOption: true,
                        placeholder: "Search Process...",
                        searchField: ['text'],
                        options: procs.map(p => ({
                            value: p.id,
                            text: p.name.charAt(0).toUpperCase() + p.name.slice(1)
                        }))
                    });
                    tsProd = new TomSelect('#sidebarProduct', {
                        allowEmptyOption: true,
                        placeholder: "Search Product...",
                        searchField: ['text'],
                        options: prods.map(p => ({
                            value: p.id,
                            text: p.name
                        }))
                    });

                    // Enable select
                    tsDept.enable();
                    tsProc.enable();
                    tsProd.enable();

                } catch (error) {
                    console.error('Error loading plant data:', error);
                }
            }

            // Pasang event listener sekali di sini
            plantSelect.addEventListener('change', function() {
                loadPlantRelatedData(this.value);
            });
        }

        function closePlantSidebar() {
            document.getElementById('sidebarPlant').classList.add('hidden');
        }

        async function submitSidebarPlant() {
            const plant = document.getElementById('plantSelect').value;
            const deptSelect = document.getElementById('sidebarDepartment');
            const procSelect = document.getElementById('sidebarProcess');
            const prodSelect = document.getElementById('sidebarProduct');

            const dept = deptSelect.value;
            const proc = procSelect.value;
            const prod = prodSelect.value;

            if (!plant) {
                return alert("Please choose plant first");
            }

            // ✅ harus pilih department
            if (!dept) {
                return alert("Please select Department.");
            }

            // Simpan ke input hidden (kalau kosong, biarkan kosong/null)
            document.getElementById('selectedPlant').value = plant;
            document.getElementById('selectedDepartment').value = dept || "";
            document.getElementById('selectedProcess').value = proc || "";
            document.getElementById('selectedProduct').value = prod || "";

            // Tampilkan teks di halaman
            // tampilkan teks sesuai yang dipilih
            let displayText = [];

            if (deptSelect.value) {
                displayText.push(deptSelect.selectedOptions[0]?.text);
            }
            if (procSelect.value) {
                displayText.push(procSelect.selectedOptions[0]?.text);
            }
            if (prodSelect.value) {
                displayText.push(prodSelect.selectedOptions[0]?.text);
            }

            // kalau tidak ada yang dipilih, kasih "-"
            document.getElementById('plantDeptDisplay').innerText =
                displayText.length > 0 ? displayText.join(' / ') : '-';

            closePlantSidebar();

        }

        let auditeeSelect;

        async function loadAuditeeOptions(dept) {
            try {
                const res = await fetch(`/get-auditee/${dept}`);
                const data = await res.json();

                const select = document.getElementById('auditeeSelect');
                select.innerHTML = ''; // kosongkan dulu

                // masukkan data ke <select>
                data.forEach(a => {
                    const option = document.createElement('option');
                    option.value = a.id;
                    option.textContent = a.name;
                    select.appendChild(option);
                });

                // aktifkan TomSelect kalau belum
                if (!auditeeSelect) {
                    auditeeSelect = new TomSelect('#auditeeSelect', {
                        plugins: ['remove_button'],
                        persist: false,
                        create: false,
                        maxItems: null,
                        placeholder: "Select or search auditee...",
                    });
                } else {
                    auditeeSelect.clear();
                    auditeeSelect.clearOptions();
                    auditeeSelect.addOptions(data.map(a => ({
                        value: a.id,
                        text: a.name
                    })));
                }

            } catch (err) {
                console.error("Error loading auditee:", err);
            }
        }

        function openAuditeeSidebar() {
            const dept = document.getElementById('selectedDepartment').value;
            if (!dept) return alert("Please choose department/plant first!");
            loadAuditeeOptions(dept);
            document.getElementById('auditeeSidebar').classList.remove('hidden');
        }

        function closeAuditeeSidebar() {
            document.getElementById('auditeeSidebar').classList.add('hidden');
        }

        let selectedAuditees = []; // Simpan id auditee

        function saveAuditeeSelection() {
            const auditeeSelect = document.getElementById('auditeeSelect');
            selectedAuditees = Array.from(auditeeSelect.selectedOptions).map(option => ({
                id: option.value,
                name: option.text
            }));

            // Deduplicate by id in case the UI/control returns duplicates
            selectedAuditees = Array.from(new Map(selectedAuditees.map(a => [a.id, a])).values());

            // Tampilkan ke UI
            document.getElementById('selectedAuditees').innerHTML = selectedAuditees.map(a => `
                <span data-id="${a.id}" class="bg-blue-100 px-2 py-1 rounded flex items-center gap-1">
                    ${a.name}
                    <button type="button" onclick="removeAuditee('${a.id}')">
                        <i data-feather="x" class="w-4 h-4 text-red-500"></i>
                    </button>
                </span>
            `).join('');

            // Simpan ke input hidden
            document.getElementById('auditee_ids').value = selectedAuditees.map(a => a.id).join(',');

            feather.replace();
            closeAuditeeSidebar();
        }

        function removeAuditee(id) {
            selectedAuditees = selectedAuditees.filter(a => a.id !== id);
            document.getElementById('auditee_ids').value = selectedAuditees.map(a => a.id).join(',');
            document.querySelector(`span[data-id="${id}"]`)?.remove();
        }
    </script>
@endpush

<!-- Attachment Upload Handle Script: trigger inputs, preview, count, click-outside -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const attachBtn = document.getElementById('attachBtn');
        const attachMenu = document.getElementById('attachMenu');
        const attachImages = document.getElementById('attachImages');
        const attachDocs = document.getElementById('attachDocs');
        const attachCount = document.getElementById('attachCount');

        const photoInput = document.getElementById('photoInput');
        const fileInput = document.getElementById('fileInput');


        const previewImageContainer = document.getElementById('previewImageContainer');
        const previewFileContainer = document.getElementById('previewFileContainer');

        // 🔹 Helper update file list setelah dihapus
        function updateFileInput(input, filesArray) {
            const dt = new DataTransfer();
            filesArray.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        // 🔹 Update badge total attachment
        function updateAttachCount() {
            const total = (photoInput.files?.length || 0) + (fileInput.files?.length || 0);
            if (total > 0) {
                attachCount.textContent = total;
                attachCount.classList.remove('hidden');
            } else {
                attachCount.classList.add('hidden');
            }
        }

        // 🔹 Preview Image + tombol delete
        function displayImages() {
            previewImageContainer.innerHTML = '';
            Array.from(photoInput.files).forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = "relative";

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = "w-24 h-24 object-cover border rounded";

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                btn.className = "absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs";
                btn.onclick = () => {
                    const newFiles = Array.from(photoInput.files);
                    newFiles.splice(index, 1);
                    updateFileInput(photoInput, newFiles);
                    displayImages();
                    updateAttachCount();
                };

                wrapper.appendChild(img);
                wrapper.appendChild(btn);
                previewImageContainer.appendChild(wrapper);
                feather.replace();
            });
        }

        // 🔹 Preview File + tombol delete
        function displayFiles() {
            previewFileContainer.innerHTML = '';
            Array.from(fileInput.files).forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = "flex items-center gap-2 text-sm border p-2 rounded";

                const icon = document.createElement('i');
                icon.setAttribute('data-feather', 'file-text');

                const name = document.createElement('span');
                name.textContent = file.name;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                btn.className = "ml-auto bg-red-600 text-white rounded-full p-1 text-xs";
                btn.onclick = () => {
                    const newFiles = Array.from(fileInput.files);
                    newFiles.splice(index, 1);
                    updateFileInput(fileInput, newFiles);
                    displayFiles();
                    updateAttachCount();
                };

                wrapper.append(icon, name, btn);
                previewFileContainer.appendChild(wrapper);
                feather.replace();
            });
        }

        // 🔹 Event Listener Input
        photoInput.addEventListener('change', () => {
            displayImages();
            updateAttachCount();
        });

        fileInput.addEventListener('change', () => {
            displayFiles();
            updateAttachCount();
        });

        // 🔹 Toggle menu
        attachBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            attachMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', () => attachMenu.classList.add('hidden'));

        attachImages.addEventListener('click', () => photoInput.click());
        attachDocs.addEventListener('click', () => fileInput.click());
    });
</script>

{{-- Store header data handler --}}
<script>
    async function saveHeaderOnly() {
        const form = document.querySelector('form[action="{{ route('ftpp.audit-finding.store') }}"]');
        if (!form) return alert('Form not found');

        // hapus pesan error lama
        document.querySelectorAll('.validation-error').forEach(n => n.remove());

        // Prevent the static single `selectedSub` hidden input from submitting an empty value
        const staticSelectedSub = document.getElementById('selectedSub');
        if (staticSelectedSub) {
            staticSelectedSub.removeAttribute('name');
        }

        // Prepare FormData
        const formData = new FormData(form);

        // Add action indicator
        formData.set('action', 'save_header');

        // Add selected auditees (deduplicated by id)
        if (typeof selectedAuditees !== 'undefined' && selectedAuditees.length) {
            const uniqueIds = Array.from(new Set(selectedAuditees.map(a => a.id)));
            uniqueIds.forEach(id => formData.append('auditee_ids[]', id));
        }

        // Add selected sub klausul ids
        if (typeof selectedSubIds !== 'undefined' && selectedSubIds.length) {
            selectedSubIds.forEach(id => formData.append('sub_klausul_id[]', Number(id)));
        }

        // Send AJAX request
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (res.status === 422) {
                const data = await res.json();
                const errors = data.errors || {};
                // tampilkan pesan error di tempat yang relevan
                let firstErrorEl = null;
                Object.keys(errors).forEach(key => {
                    const messages = errors[key];
                    // normalisasi key untuk array like "auditee_ids.0" -> "auditee_ids"
                    const baseKey = key.split('.')[0];

                    let targetEl = null;
                    if (baseKey === 'auditee_ids') {
                        targetEl = document.getElementById('selectedAuditees') || form;
                    } else if (baseKey === 'sub_klausul_id') {
                        targetEl = document.getElementById('selectedSubContainer') || form;
                    } else {
                        // coba cari elemen dengan name exact atau name[]'
                        targetEl = form.querySelector(`[name="${baseKey}"]`) || form.querySelector(`[name="${baseKey}[]"]`) || form;
                    }

                    // append all messages
                    messages.forEach(msg => {
                        const el = document.createElement('div');
                        el.className = 'validation-error text-sm text-red-600 mt-1';
                        el.textContent = msg;
                        // jika target adalah container, letakkan setelah container; jika input, setelah input
                        if (targetEl === form) {
                            form.appendChild(el);
                        } else {
                            targetEl.insertAdjacentElement('afterend', el);
                        }
                        if (!firstErrorEl) firstErrorEl = targetEl;
                    });
                });

                if (firstErrorEl && typeof firstErrorEl.focus === 'function') {
                    try { firstErrorEl.focus(); } catch(e){/* ignore */ }
                }
                return;
            }

            if (!res.ok) {
                const text = await res.text();
                console.error('Unexpected response', res.status, text);
                return alert('Server error. Check console.');
            }

            // success
            const json = await res.json();
            // kalau mau redirect setelah sukses:
            window.location.href = '/ftpp';
        } catch (err) {
            console.error('Save error', err);
            alert('Error saat menyimpan. Lihat console untuk detail.');
        }
    }
</script>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();
</script>
