<table class="w-full border border-black text-sm">
    <tr>
        <td class="border border-black p-1 w-1/3 align-top">
            <span class="font-semibold">Audit Type:</span>
            <div class="mt-2 space-y-2">
                @foreach ($auditTypes as $type)
                    <label class="block">
                        <input type="radio" name="audit_type_id" value="{{ $type->id }}"
                            {{ request('audit_type_id') == $type->id ? 'checked' : '' }}>
                        {{ $type->name }}
                    </label>
                @endforeach
            </div>

            {{-- Level 2: Sub Audit Type (muncul hanya jika audit type dipilih) --}}
            <div id="subAuditType" class="mt-2 ml-4 flex flex-wrap gap-2"></div>
        </td>

        <td class="border border-black p-1">
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 text-gray-700 font-semibold w-1/3">Department / Process / Product:</td>
                    <td class="py-2">
                        <button type="button"
                            class="px-3 py-1 border text-gray-600 rounded hover:bg-blue-600 hover:text-white"
                            onclick="openPlantSidebar()">
                            Select Department / Process / Product by Plant
                        </button>
                        <input type="hidden" id="selectedPlant" name="plant">
                        <input type="hidden" id="selectedDepartment" name="department_id">
                        <input type="hidden" id="selectedProcess" name="process_id">
                        <input type="hidden" id="selectedProduct" name="product_id">
                        <div id="plantDeptDisplay" class="mt-1 text-gray-700"></div>
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Auditee:</td>
                    <td class="py-2">
                        <div class="flex items-center gap-2 mb-2">
                            <select id="auditeeSelect" class="border-b border-gray-400 w-full focus:outline-none">
                                <option value="">-- Select Auditee --</option>
                            </select>
                            <button type="button" onclick="addAuditee()"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Add
                            </button>
                        </div>

                        <!-- Tempat tampil list auditee -->
                        <div id="auditeeContainer" class="flex flex-wrap gap-2"></div>

                        <!-- Data final yang dikirim ke backend -->
                        <input type="hidden" name="auditee_id" id="auditeeHidden">
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Auditor / Inisiator:</td>
                    <td class="py-2">
                        <select name="auditor_id" class="border-b border-gray-400 w-full focus:outline-none" required>
                            <option value="">-- Choose Auditor --</option>
                            @foreach ($auditors as $auditor)
                                <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Date:</td>
                    <td class="py-2">
                        <input type="date" name="created_at"
                            class="border-b border-gray-400 w-full focus:outline-none"
                            value="{{ now()->toDateString() }}">
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Registration Number:</td>
                    <td class="py-2">
                        <input type="text" id="reg_number" name="reg_number"
                            class="border-b border-gray-400 w-full focus:outline-none bg-gray-100" readonly>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="border border-black p-1">
            <span class="font-semibold">Finding Category:</span>
            @foreach ($findingCategories as $category)
                <label class="ml-2">
                    <input type="radio" name="finding_category" value="{{ $category->id }}">
                    {{ ucfirst($category->name) }}
                </label>
            @endforeach
        </td>
    </tr>

</table>

{{-- TEMUAN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold">
        <td class="border border-black p-1">AUDITOR / INISIATOR</td>
    </tr>
    <tr>
        <td class="border border-black p-2">
            <div>
                <label class="font-semibold">Finding / Issue:</label>
                <textarea class="w-full border border-gray-400 rounded p-1 h-24" required></textarea>
            </div>
            <div class="flex justify-between mt-2">
                <div class="mr-8">
                    <span class="font-semibold">Duedate:</span>
                    <input type="date" class="border-b border-gray-400" required>
                </div>
                <div class="mt-2">
                    <div class="flex items-end gap-2 mb-1">
                        <!-- Tombol Trigger -->
                        <div class="ml-auto">
                            <button type="button" onclick="openSidebar()"
                                class="px-3 py-1 border text-gray-600 rounded hover:bg-blue-600 hover:text-white">
                                Select Clause
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="selectedSub" name="sub_klausul_id" class="">
                    <div id="selectedSubContainer" class="flex flex-wrap gap-2 mt-2"></div>

                </div>
            </div>
            <!-- Preview containers (sesuaikan posisi di form) -->
            <div id="previewImageContainer" class="mt-2 flex flex-wrap gap-2"></div>
            <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>

            <!-- Attachment button (paperclip) -->
            <div class="relative inline-block">
                <button id="attachBtn" type="button"
                    class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                    aria-haspopup="true" aria-expanded="false" title="Attach files">
                    <i data-feather="paperclip" class="w-4 h-4"></i>
                    <span id="attachCount" class="text-xs text-gray-600 hidden">0</span>
                </button>

                <!-- Small menu seperti email (hidden, muncul saat klik) -->
                <div id="attachMenu" class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow z-20">
                    <button id="attachImages"
                        class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                        <i data-feather="image" class="w-4 h-4"></i>
                        <span class="text-sm">Upload Images</span>
                    </button>
                    <button id="attachDocs"
                        class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                        <i data-feather="file-text" class="w-4 h-4"></i>
                        <span class="text-sm">Upload Documents</span>
                    </button>
                    <div class="border-t mt-1"></div>
                    <button id="attachBoth"
                        class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                        <i data-feather="upload" class="w-4 h-4"></i>
                        <span class="text-sm">Open Combined Picker</span>
                    </button>
                </div>
            </div>

            <!-- Hidden file inputs -->
            <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple class="hidden">
            <input type="file" id="fileInput" name="files[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple
                class="hidden">
            <!-- Optional combined input -->
            <input type="file" id="combinedInput" name="attachments[]"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
        </td>
    </tr>
</table>

<!-- Sidebar Klausul -->
<div id="sidebarKlausul"
    class="fixed top-0 right-0 w-full md:w-1/4 h-full bg-white shadow-lg p-5 hidden overflow-y-auto">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg text-gray-700">Select Clause</h2>
        <button onclick="closeSidebar()" class="text-gray-600 hover:text-red-500">
            <i data-feather="x"></i>
        </button>
    </div>

    <!-- Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Clause:</label>
    <select id="selectKlausul" class="border border-gray-300 p-2 w-full rounded mb-4">
        <option value="">-- Choose Clause --</option>
    </select>

    <!-- Head Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Head Clause:</label>
    <select id="selectHead" class="border border-gray-300 p-2 w-full rounded mb-4" disabled>
        <option value="">-- Choose Head Clause --</option>
    </select>

    <!-- Sub Clause -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Sub Clause:</label>
    <select id="selectSub" class="border border-gray-300 p-2 w-full rounded mb-4" disabled>
        <option value="">-- Choose Sub Clause --</option>
    </select>

    <!-- Submit Button -->
    <button type="button" onclick="addSubKlausul()"
        class="bg-blue-500 text-white px-4 py-2 w-2/3 rounded hover:bg-blue-600 transition mt-2">
        Add
    </button>
</div>

{{-- Sidebar Select Dept/Proc/Prod by Plant --}}
<div id="sidebarPlant"
    class="fixed top-0 right-0 w-full md:w-1/4 h-full bg-white shadow-lg p-5 hidden overflow-y-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700">Select Plant / Department</h2>
        <button onclick="closePlantSidebar()" class="text-gray-600 hover:text-red-500">
            <i data-feather="x"></i> <!-- Feather icon -->
        </button>
    </div>

    <!-- Plant -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Plant:</label>
    <select id="plantSelect" class="border border-gray-300 p-2 w-full rounded mb-4">
        <option value="">-- Choose Plant --</option>
        <option value="Body">Body</option>
        <option value="Unit">Unit</option>
        <option value="Electric">Electric</option>
        <option value="All">All</option>
    </select>

    <!-- Department -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Department:</label>
    <select id="sidebarDepartment" class="border border-gray-300 p-2 w-full rounded mb-4" disabled>
        <option value="">-- Select Department --</option>
        @foreach ($departments as $dept)
            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
        @endforeach
    </select>

    <!-- Process -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Process:</label>
    <select id="sidebarProcess" class="border border-gray-300 p-2 w-full rounded mb-4" disabled>
        <option value="">-- Select Process --</option>
        @foreach ($processes as $proc)
            <option value="{{ $proc->id }}">{{ $proc->name }}</option>
        @endforeach
    </select>

    <!-- Product -->
    <label class="block text-sm font-medium text-gray-700 mb-1">Product:</label>
    <select id="sidebarProduct" class="border border-gray-300 p-2 w-full rounded mb-4" disabled>
        <option value="">-- Select Product --</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </select>

    <!-- Submit -->
    <button onclick="submitSidebarPlant()" class="bg-blue-500 text-white px-4 py-2 w-full rounded hover:bg-green-600">
        Submit
    </button>
</div>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();
</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("✅ Script berjalan...");

            // Gunakan event delegation untuk menangkap perubahan audit_type
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name="audit_type_id"]')) {
                    const auditTypeId = e.target.value;
                    console.log("✅ Audit Type dipilih:", auditTypeId);

                    // Ambil data ke server
                    fetch(`/ftpp/get-data/${auditTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("✅ Data diterima:", data);

                            // Tampilkan nomor registrasi
                            document.getElementById('reg_number').value = data.reg_number;

                            // Tampilkan Sub Audit Type
                            let subContainer = document.getElementById('subAuditType');
                            subContainer.innerHTML = '';
                            data.sub_audit.forEach(item => {
                                subContainer.innerHTML += `
                            <label class="block">
                                <input type="radio" name="sub_audit_type_id" value="${item.id}">
                                ${item.name}
                            </label>
                        `;
                            });
                        })
                        .catch(error => console.error("❌ Error:", error));
                }
            });

            console.log("✅ Event listener sudah siap");
        });


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

        // === KLAUSUL HANDLER ===
        document.addEventListener('DOMContentLoaded', function() {
            console.log("✅ FTTP script ready");

            // --- Event delegation untuk audit type (tetap pakai karena sudah aman) ---
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name="audit_type_id"]')) {
                    const auditTypeId = e.target.value;
                    console.log("Audit type chosen:", auditTypeId);
                    fetch(`/ftpp/get-data/${auditTypeId}`)
                        .then(r => r.json())
                        .then(data => {
                            console.log('get-data response', data);
                            document.getElementById('reg_number').value = data.reg_number ?? '';
                            // render sub audit
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
                                });
                            } else {
                                subContainer.innerHTML =
                                    '<small class="text-gray-500">Tidak ada sub audit type</small>';
                            }
                        })
                        .catch(err => console.error('Error fetching get-data:', err));
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
        function openPlantSidebar() {
            document.getElementById('sidebarPlant').classList.remove('hidden');

            // reset selects
            const plantSelect = document.getElementById('plantSelect');
            const deptSelect = document.getElementById('sidebarDepartment');
            const procSelect = document.getElementById('sidebarProcess');
            const prodSelect = document.getElementById('sidebarProduct');

            deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
            procSelect.innerHTML = '<option value="">-- Select Process --</option>';
            prodSelect.innerHTML = '<option value="">-- Select Product --</option>';

            // unbind existing bound flag if any (safe)
            if (plantSelect.dataset.bound !== 'true') {
                plantSelect.addEventListener('change', async function() {
                    const plant = this.value;
                    // reset
                    deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
                    procSelect.innerHTML = '<option value="">-- Select Process --</option>';
                    prodSelect.innerHTML = '<option value="">-- Select Product --</option>';
                    deptSelect.disabled = true;
                    procSelect.disabled = true;
                    prodSelect.disabled = true;

                    if (!plant) return;

                    try {
                        // Ambil departments, processes, products berdasarkan plant
                        const [dRes, pRes, prRes] = await Promise.all([
                            fetch(`/get-departments/${encodeURIComponent(plant)}`),
                            fetch(`/get-processes/${encodeURIComponent(plant)}`),
                            fetch(`/get-products/${encodeURIComponent(plant)}`)
                        ]);

                        if (!dRes.ok || !pRes.ok || !prRes.ok) {
                            console.error('One of the requests failed', dRes, pRes, prRes);
                            // kalau mau, tampilkan message ke user
                        }

                        const [depts, procs, prods] = await Promise.all([
                            dRes.json(),
                            pRes.json(),
                            prRes.json()
                        ]);

                        // safety: pastikan array
                        if (Array.isArray(depts)) {
                            depts.forEach(item => {
                                deptSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${item.id}">${item.name}</option>`);
                            });
                            deptSelect.disabled = false;
                        }

                        if (Array.isArray(procs)) {
                            procs.forEach(item => {
                                procSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${item.id}">${item.name}</option>`);
                            });
                            procSelect.disabled = false;
                        }

                        if (Array.isArray(prods)) {
                            prods.forEach(item => {
                                prodSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${item.id}">${item.name}</option>`);
                            });
                            prodSelect.disabled = false;
                        }

                    } catch (err) {
                        console.error("❌ Error loading plant data", err);
                    }
                });

                plantSelect.dataset.bound = "true";
            }
        }

        function closePlantSidebar() {
            document.getElementById('sidebarPlant').classList.add('hidden');
        }

        function submitSidebarPlant() {
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

            // pilihan dept/proc/prod optional? kalau wajib, cek semua
            // contoh: require all
            if (!dept || !proc || !prod) {
                return alert("Please select department, process and product.");
            }

            document.getElementById('selectedPlant').value = plant;
            document.getElementById('selectedDepartment').value = dept;
            document.getElementById('selectedProcess').value = proc;
            document.getElementById('selectedProduct').value = prod;

            document.getElementById('plantDeptDisplay').innerText =
                `${deptSelect.selectedOptions[0]?.text || '-'} / ${procSelect.selectedOptions[0]?.text || '-'} / ${prodSelect.selectedOptions[0]?.text || '-'}`;

            closePlantSidebar();
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

            if (!dept || !proc || !prod) {
                return alert("Please select department, process and product.");
            }

            // isi input hidden
            document.getElementById('selectedPlant').value = plant;
            document.getElementById('selectedDepartment').value = dept;
            document.getElementById('selectedProcess').value = proc;
            document.getElementById('selectedProduct').value = prod;

            // tampilkan teks
            document.getElementById('plantDeptDisplay').innerText =
                `${deptSelect.selectedOptions[0]?.text || '-'} / ${procSelect.selectedOptions[0]?.text || '-'} / ${prodSelect.selectedOptions[0]?.text || '-'}`;

            closePlantSidebar();

            // === ⬇️ FILTER AUDITEE BERDASARKAN DEPARTMENT YANG DIPILIH
            try {
                const res = await fetch(`/get-auditee/${dept}`);
                const data = await res.json();

                const auditeeSelect = document.querySelector('select[name="auditee_id"]') ||
                    document.querySelector('select:has(option:contains("-- Select Auditee --"))');

                if (auditeeSelect) {
                    auditeeSelect.innerHTML = '<option value="">-- Select Auditee --</option>';
                    data.forEach(a => {
                        auditeeSelect.insertAdjacentHTML('beforeend',
                            `<option value="${a.id}">${a.name}</option>`);
                    });
                }
            } catch (err) {
                console.error("❌ Error loading auditee:", err);
            }

            let selectedAuditees = [];

        function addAuditee() {
            const select = document.getElementById('auditeeSelect');
            const id = select.value;
            const text = select.selectedOptions[0]?.text;

            if (!id) return alert("Please select an auditee");
            if (selectedAuditees.includes(id)) return alert("Already added");

            selectedAuditees.push(id);
            document.getElementById('auditeeHidden').value = JSON.stringify(selectedAuditees);

            const container = document.getElementById('auditeeContainer');
            const tag = document.createElement('span');
            tag.className = "flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded";
            tag.dataset.id = id;
            tag.innerHTML = `${text}
        <button type="button" class="text-red-500 font-bold" onclick="removeAuditee('${id}', this)">
            &times;
        </button>`;

            container.appendChild(tag);
        }

        function removeAuditee(id, btn) {
            selectedAuditees = selectedAuditees.filter(x => x !== id);
            document.getElementById('auditeeHidden').value = JSON.stringify(selectedAuditees);
            btn.parentElement.remove();
        }
        }
    </script>
@endpush

<!-- Attachment Upload Handle Script: trigger inputs, preview, count, click-outside -->
<script>
    const attachBtn = document.getElementById('attachBtn');
    const attachMenu = document.getElementById('attachMenu');
    const attachImages = document.getElementById('attachImages');
    const attachDocs = document.getElementById('attachDocs');
    const attachBoth = document.getElementById('attachBoth');
    const attachCount = document.getElementById('attachCount');

    const photoInput = document.getElementById('photoInput');
    const fileInput = document.getElementById('fileInput');
    const combinedInput = document.getElementById('combinedInput');

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
            btn.innerHTML = '<i data-feather="x"></i>';
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

    // 🔹 Combined Input (Pisahkan otomatis jadi image vs file)
    combinedInput.addEventListener('change', (e) => {
        const images = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
        const docs = Array.from(e.target.files).filter(f => !f.type.startsWith('image/'));
        updateFileInput(photoInput, [...Array.from(photoInput.files), ...images]);
        updateFileInput(fileInput, [...Array.from(fileInput.files), ...docs]);
        displayImages();
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
    attachBoth.addEventListener('click', () => combinedInput.click());
</script>
