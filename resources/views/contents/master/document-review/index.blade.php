@extends('layouts.app')
@section('title', 'Master Document Review')

@section('content')
    <div class="container mx-auto px-4 py-2" x-data="documentReviewTabs('{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}')">
        {{-- Header: Breadcrumbs + Add Button --}}
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li>Documents</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Review</li>
                </ol>
            </nav>

            {{-- Add Document Button --}}
            <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                <i class="bi bi-plus-circle"></i>
                <span>Add Document</span>
            </button>
            @include('contents.master.document-review.partials.modal-add')
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Tabs + Search --}}
            <div class="flex flex-wrap justify-between items-center border-b border-gray-100 p-4">
                {{-- Tabs --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ($groupedByPlant as $plant => $documents)
                        @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                        <button type="button" @click="setActiveTab('{{ $slug }}')"
                            :class="activeTab === '{{ $slug }}'
                                ?
                                'bg-gray-100 text-gray-800 border-gray-100' :
                                'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-lg border border-gray-200 text-sm font-medium transition">
                            <i data-feather="settings" class="inline w-4 h-4 me-1"></i>
                            {{ ucfirst(strtolower($plant)) }}
                        </button>
                    @endforeach
                </div>

                {{-- Search Bar --}}
                <form method="GET" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto overflow-y-auto">
                @foreach ($groupedByPlant as $plant => $documents)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <div x-show="activeTab === '{{ $slug }}'" x-transition>
                        <div class="overflow-auto rounded-bottom-lg h-[60vh]">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-600">
                                {{-- Header partial --}}
                                @include('contents.master.document-review.partials.table-header')

                                <tbody>
                                    @php
                                        $parents = $documents->filter(fn($doc) => is_null($doc->parent_id));
                                    @endphp

                                    @if ($parents->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center text-gray-500 py-4">
                                                <i data-feather="folder-x" class="mx-auto w-6 h-6 mb-1"></i>
                                                No Document found for this tab.
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($parents as $index => $parent)
                                            @include(
                                                'contents.master.document-review.partials.nested-row-recursive',
                                                [
                                                    'mapping' => $parent,
                                                    'documents' => $documents,
                                                    'loopIndex' => 'parent-' . $index,
                                                    'rowNumber' => $loop->iteration,
                                                    'depth' => 0,
                                                    'numbering' => $loop->iteration . '',
                                                ]
                                            )
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>

                            @foreach ($documents as $doc)
                                @include('contents.master.document-review.partials.modal-edit', [
                                    'mapping' => $doc,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <!-- 📄 Modal Fullscreen View File -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 rounded-0 shadow-none">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i> Document Viewer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <iframe id="fileViewer" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        function documentReviewTabs(defaultTab) {
            return {
                activeTab: localStorage.getItem('activeTab') || defaultTab,
                setActiveTab(tab) {
                    this.activeTab = tab;
                    localStorage.setItem('activeTab', tab);
                }
            }
        }
        // Fungsi debounce umum
        function debounce(fn, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), delay);
            };
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Clear Filters
            document.getElementById('clearFilters')?.addEventListener('click', function() {
                const form = document.getElementById('filterForm');
                form.querySelectorAll('input, select').forEach(el => el.value = '');
                form.submit();
            });

            // Clear Search only
            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", function() {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }

            feather.replace();

            // in form message
            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault(); // Stop form submit
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated'); // Tambahkan class validasi Bootstrap
                }, false);
            });

            //View File in tab
            const modal = document.getElementById('viewFileModal');
            const iframe = document.getElementById('fileViewer');

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file;
                    iframe.src = fileUrl;
                });
            });

            modal.addEventListener('hidden.bs.modal', () => {
                iframe.src = '';
            });

            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.toggle-children');
                if (!btn) return;

                // Ambil target class unik, misal: children-of-12-3
                const target = btn.dataset.target;
                if (!target) return;

                // Toggle semua anak dengan class itu
                document.querySelectorAll('.' + target).forEach(el => {
                    el.classList.toggle('d-none');
                });

                // Ganti ikon dari + jadi - dan sebaliknya
                const icon = btn.querySelector('i');
                if (icon) {
                    if (icon.classList.contains('bi-plus-square')) {
                        icon.classList.remove('bi-plus-square');
                        icon.classList.add('bi-dash-square');
                    } else {
                        icon.classList.remove('bi-dash-square');
                        icon.classList.add('bi-plus-square');
                    }
                }
            });

            // Deteksi posisi dropdown saat akan dibuka
            document.querySelectorAll('.dropdown').forEach(drop => {
                drop.addEventListener('show.bs.dropdown', function() {
                    const rect = drop.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    // Kalau ruang di bawah sempit dan di atas lebih luas → ubah jadi dropup
                    if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                        drop.classList.add('dropup');
                    } else {
                        drop.classList.remove('dropup');
                    }
                });

                // Hapus class dropup saat dropdown ditutup
                drop.addEventListener('hidden.bs.dropdown', function() {
                    drop.classList.remove('dropup');
                });
            });

            const container = document.getElementById("file-fields");
            const addBtn = document.getElementById("add-file");
            const documentNumberInput = document.getElementById('document_number');
            const documentSelect = document.getElementById('document_select');
            const departmentSelect = document.getElementById('department_select');
            const partNumberSelect = document.getElementById('partNumber_select');
            const parentDocumentSelect = document.getElementById('parent_document_select');
            const plantSelect = document.getElementById('plant_select');
            const hiddenInput = document.getElementById('notes_input_add');
            const form = document.querySelector('#addDocumentModal form');

            // --- Tambah file input dinamis ---
            addBtn.addEventListener("click", function() {
                const group = document.createElement("div");
                group.classList.add("col-md-12", "d-flex", "align-items-center", "mb-2",
                    "file-input-group");
                group.innerHTML = `
                <input type="file" class="form-control border-1 shadow-sm" name="files[]" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                    <i class="bi bi-trash"></i>
                </button>
            `;
                container.appendChild(group);
            });

            // --- Hapus file input ---
            container.addEventListener("click", function(e) {
                if (e.target.closest(".remove-file")) {
                    e.target.closest(".file-input-group").remove();
                }
            });

            // --- Inisialisasi TomSelect dengan debounce di API load ---
            const tsDocument = new TomSelect('#document_select', {
                create: false,
                preload: true,
                load: debounce(function(query, callback) {
                    fetch('/api/documents?q=' + encodeURIComponent(query))
                        .then(res => res.json())
                        .then(callback)
                        .catch(() => callback());
                }, 500)
            });

            const tsParentDocument = new TomSelect('#parent_document_select', {
                create: false,
                preload: true,
                persist: true,
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                shouldLoad: function(query) {
                    // Jangan reload otomatis saat submit
                    return true;
                },
                load: debounce(function(query, callback) {
                    const plant = tsPlant.getValue();
                    const params = new URLSearchParams();
                    if (query) params.append('q', query);
                    if (plant) params.append('plant', plant);

                    fetch('/api/parent-documents?' + params.toString())
                        .then(res => res.json())
                        .then(callback)
                        .catch(() => callback());
                }, 500)
            });
            const tsPlant = new TomSelect('#plant_select', {
                create: false
            });

            const tsPartNumber = new TomSelect('#partNumber_select', {
                create: false,
                options: []
            });

            const tsDepartment = new TomSelect('#department_select', {
                create: false,
                options: []
            });

            // Fungsi untuk fetch dan update options department berdasarkan document dan plant
            function updateDepartmentsByDocumentAndPlant() {
                const documentId = tsDocument.getValue();
                const plant = tsPlant.getValue();

                if (!documentId || !plant) {
                    tsDepartment.clearOptions();
                    tsDepartment.disable();
                    return;
                }

                fetch(
                        `/api/departments/filter?document_id=${encodeURIComponent(documentId)}&plant=${encodeURIComponent(plant)}`
                    )
                    .then(res => res.json())
                    .then(data => {
                        if (data.departments && data.departments.length > 0) {
                            const options = data.departments.map(dept => ({
                                value: dept.id,
                                text: dept.name
                            }));
                            tsDepartment.clearOptions();
                            tsDepartment.addOptions(options);
                            tsDepartment.enable();
                        } else {
                            tsDepartment.clearOptions();
                            tsDepartment.disable();
                        }
                    })
                    .catch(() => {
                        tsDepartment.clearOptions();
                        tsDepartment.disable();
                    });
            }

            // Pasang event listener pada change untuk tsDocument dan tsPlant
            tsDocument.on('change', updateDepartmentsByDocumentAndPlant);

            // Disable Part Number dan Department sampai plant dipilih
            tsPartNumber.disable();
            tsDepartment.disable();

            // Load Department satu kali (bukan via TomSelect load)
            fetch('/api/departments')
                .then(res => res.json())
                .then(data => {
                    const mapped = data.map(item => ({
                        value: item.id,
                        text: item.text
                    }));
                    tsDepartment.clearOptions();
                    tsDepartment.addOptions(mapped);
                })
                .catch(() => tsDepartment.clearOptions());

            // Event saat Plant berubah, load Part Number sesuai plant dan enable Part Number & Department
            tsPlant.on('change', function(value) {
                tsPartNumber.clearOptions();
                tsPartNumber.disable();

                tsDepartment.clearOptions();
                tsDepartment.disable();

                if (value) {
                    fetch(`/api/part-numbers?plant=${encodeURIComponent(value)}`)
                        .then(res => res.json())
                        .then(data => {
                            const mapped = data.map(item => ({
                                value: item.id,
                                text: item.text
                            }));
                            tsPartNumber.addOptions(mapped);
                            tsPartNumber.enable();
                        })
                        .catch(() => {
                            tsPartNumber.clearOptions();
                            tsPartNumber.disable();
                        });

                    tsParentDocument.clearOptions();
                    tsParentDocument.clear(true);
                    tsParentDocument.load();

                    // Panggil updateDepartmentsByDocumentAndPlant di sini supaya department ikut update
                    updateDepartmentsByDocumentAndPlant();

                } else {
                    tsPartNumber.clear(true);
                    tsPartNumber.disable();

                    tsDepartment.clear(true);
                    tsDepartment.disable();

                    tsParentDocument.clear(true);
                    documentNumberInput.value = '';
                }
            });


            // Fungsi cek apakah input kunci sudah lengkap untuk generate nomor dokumen
            function canGenerate() {
                return documentSelect.value && departmentSelect.value && partNumberSelect.value;
            }

            // Fungsi async generate nomor dokumen dengan fallback error handling
            async function generateDocumentNumber() {
                if (!canGenerate()) {
                    documentNumberInput.value = '';
                    return;
                }

                // Jika parent document dipilih, generate dengan parameter parent
                if (parentDocumentSelect.value) {
                    try {
                        const params = new URLSearchParams({
                            parent_id: parentDocumentSelect.value,
                            document_id: documentSelect.value,
                            department_id: departmentSelect.value,
                            part_number_id: partNumberSelect.value,
                        });

                        const response = await fetch(
                            `/api/generate-document-number-from-parent?${params.toString()}`);
                        if (!response.ok) throw new Error('Failed to fetch');
                        const data = await response.json();
                        documentNumberInput.value = data.document_number || '';
                    } catch (error) {
                        console.error('Error generating document number from parent:', error);
                        documentNumberInput.value = '';
                    }
                } else {
                    // Generate nomor dokumen default tanpa parent
                    try {
                        const params = new URLSearchParams({
                            document_id: documentSelect.value,
                            department_id: departmentSelect.value,
                            part_number_id: partNumberSelect.value,
                        });

                        const response = await fetch(`/api/generate-document-number?${params.toString()}`);
                        if (!response.ok) throw new Error('Failed to fetch');
                        const data = await response.json();
                        documentNumberInput.value = data.document_number || '';
                    } catch (error) {
                        console.error('Error generating document number:', error);
                        documentNumberInput.value = '';
                    }
                }
            }

            // Debounce pemanggilan generate nomor dokumen
            const generateDocumentNumberDebounced = debounce(generateDocumentNumber, 500);

            // Event change pada input kunci untuk nomor dokumen
            documentSelect.addEventListener('change', generateDocumentNumberDebounced);
            departmentSelect.addEventListener('change', generateDocumentNumberDebounced);
            partNumberSelect.addEventListener('change', generateDocumentNumberDebounced);
            parentDocumentSelect.addEventListener('change', generateDocumentNumberDebounced);

            // Legacy filter options Part Number berdasarkan Plant (jika kamu masih menggunakan native select)
            plantSelect.addEventListener('change', () => {
                const selectedPlant = plantSelect.value;

                // Reset pilihan Part Number dan nomor dokumen
                partNumberSelect.value = '';
                documentNumberInput.value = '';

                // Enable/disable Part Number dan Department select native
                const enableControls = selectedPlant !== '';
                partNumberSelect.disabled = !enableControls;
                departmentSelect.disabled = !enableControls;

                // Filter opsi Part Number berdasarkan plant (untuk native select)
                Array.from(partNumberSelect.options).forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                    } else if (option.dataset.plant === selectedPlant) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });

            // Inisialisasi Quill editor untuk Notes
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

            // Saat submit form, isi hidden input dengan html dari Quill
            form.addEventListener('submit', function() {
                hiddenInput.value = quill.root.innerHTML;
            });

            // === EDIT MODAL JS ===
            @foreach ($documentMappings as $mapping)
                $('#editModal{{ $mapping->id }}').on('shown.bs.modal', function() {

                    // ===============================
                    // 🔹 Inisialisasi Flag
                    // ===============================
                    let isInitializing = true;
                    let lastParentValue = null;

                    // ===============================
                    // 🔹 Inisialisasi TomSelect
                    // ===============================
                    const tsEditDoc = new TomSelect('#editDocumentSelect{{ $mapping->id }}', {
                        create: false,
                        preload: true,
                        load: debounce((query, callback) => {
                            fetch('/api/documents?q=' + encodeURIComponent(query))
                                .then(res => res.json())
                                .then(callback)
                                .catch(() => callback());
                        }, 500)
                    });

                    const tsEditPlant = new TomSelect('#editPlantSelect{{ $mapping->id }}', {
                        create: false
                    });
                    const tsEditDept = new TomSelect('#editDepartmentSelect{{ $mapping->id }}', {
                        create: false
                    });
                    const tsEditPart = new TomSelect('#editPartNumberSelect{{ $mapping->id }}', {
                        create: false
                    });
                    const tsEditParent = new TomSelect('#editParentSelect{{ $mapping->id }}', {
                        create: false,
                        valueField: 'value',
                        labelField: 'text',
                        searchField: ['text'],
                    });

                    window['tsEditParent{{ $mapping->id }}'] = tsEditParent;
                    window['tsEditPlant{{ $mapping->id }}'] = tsEditPlant;

                    // ===============================
                    // 🔹 Load Parent Documents
                    // ===============================
                    function loadParentDocuments(plant) {
                        return new Promise((resolve) => {
                            const selectedParent = $('#editParentSelect{{ $mapping->id }}').data(
                                'selected');
                            tsEditParent.clearOptions();

                            if (!plant) return resolve();

                            fetch(`/api/parent-documents?plant=${encodeURIComponent(plant)}`)
                                .then(res => res.json())
                                .then(data => {
                                    const options = data.map(doc => ({
                                        value: doc.value,
                                        text: doc.text
                                    }));
                                    tsEditParent.addOptions(options);

                                    if (selectedParent) tsEditParent.setValue(selectedParent);
                                    lastParentValue = tsEditParent.getValue();
                                    resolve();
                                })
                                .catch(() => {
                                    tsEditParent.clearOptions();
                                    resolve();
                                });
                        });
                    }

                    // ===============================
                    // 🔹 Load Initial Part Numbers
                    // ===============================
                    function loadInitialPartNumbers(plant = null) {
                        let url = '/api/part-numbers';
                        if (plant) url += `?plant=${encodeURIComponent(plant)}`;
                        return fetch(url)
                            .then(res => res.json())
                            .then(data => {
                                const options = data.map(item => ({
                                    value: item.id,
                                    text: item.text
                                }));
                                tsEditPart.addOptions(options);

                                const selectedPart = $('#editPartNumberSelect{{ $mapping->id }}')
                                    .val();
                                if (selectedPart) tsEditPart.setValue(selectedPart);
                            });
                    }

                    // ===============================
                    // 🔹 Update Departments
                    // ===============================
                    function updateEditDepartments() {
                        const documentId = tsEditDoc.getValue();
                        const plant = tsEditPlant.getValue();

                        if (!documentId || !plant) {
                            tsEditDept.clearOptions();
                            tsEditDept.disable();
                            return;
                        }

                        fetch(
                                `/api/departments/filter?document_id=${encodeURIComponent(documentId)}&plant=${encodeURIComponent(plant)}`
                            )
                            .then(res => res.json())
                            .then(data => {
                                if (data.departments && data.departments.length) {
                                    const options = data.departments.map(dept => ({
                                        value: dept.id,
                                        text: dept.name
                                    }));
                                    tsEditDept.clearOptions();
                                    tsEditDept.addOptions(options);
                                    tsEditDept.enable();
                                } else {
                                    tsEditDept.clearOptions();
                                    tsEditDept.disable();
                                }
                            })
                            .catch(() => {
                                tsEditDept.clearOptions();
                                tsEditDept.disable();
                            });
                    }

                    // ===============================
                    // 🔹 Auto-generate Document Number
                    // ===============================
                    const docNumberInput = document.getElementById(
                        'editDocumentNumber{{ $mapping->id }}');

                    async function generateDocumentNumber() {
                        const docId = tsEditDoc.getValue();
                        const deptId = tsEditDept.getValue();
                        const partId = tsEditPart.getValue();
                        const parentId = tsEditParent.getValue();

                        if (!docId || !deptId || !partId) return;

                        const params = new URLSearchParams({
                            document_id: docId,
                            department_id: deptId,
                            part_number_id: partId
                        });

                        if (parentId && parentId !== "") params.append('parent_id', parentId);

                        try {
                            const res = await fetch(
                                `/api/generate-document-number?${params.toString()}`);
                            if (!res.ok) throw new Error('Failed');
                            const data = await res.json();
                            docNumberInput.value = data.document_number || '';
                        } catch (err) {
                            console.error('Error generating number:', err);
                        }
                    }

                    const debouncedGenerate = debounce(generateDocumentNumber, 500);

                    // ===============================
                    // 🔹 Event Listeners
                    // ===============================
                    tsEditDoc.on('change', () => {
                        if (!isInitializing) debouncedGenerate();
                    });
                    tsEditDept.on('change', () => {
                        if (!isInitializing) debouncedGenerate();
                    });
                    tsEditPart.on('change', () => {
                        if (!isInitializing) debouncedGenerate();
                    });
                    tsEditParent.on('change', (newValue) => {
                        if (!isInitializing && newValue !== lastParentValue) {
                            lastParentValue = newValue;
                            debouncedGenerate();
                        }
                    });

                    tsEditPlant.on('change', function(value) {
                        tsEditPart.clearOptions();
                        tsEditPart.disable();
                        tsEditDept.clearOptions();
                        tsEditDept.disable();
                        tsEditParent.clearOptions();

                        if (value) {
                            loadInitialPartNumbers(value).then(() => tsEditPart.enable());
                            loadParentDocuments(value);
                            updateEditDepartments();
                        }
                    });

                    tsEditDoc.on('change', updateEditDepartments);
                    tsEditPlant.on('change', updateEditDepartments);

                    // ===============================
                    // 🔹 Initialize Modal Values
                    // ===============================
                    const initialPlant = tsEditPlant.getValue();

                    Promise.all([
                        loadParentDocuments(initialPlant),
                        loadInitialPartNumbers(initialPlant)
                    ]).finally(() => {
                        isInitializing = false; // semua selesai
                    });

                    // ===============================
                    // 🔹 Quill Editor
                    // ===============================
                    const quill = new Quill('#quill_editor_edit{{ $mapping->id }}', {
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

                    quill.root.innerHTML = document.getElementById('notes_input_edit{{ $mapping->id }}')
                        .value;

                    $(this).find('form').on('submit', function() {
                        document.getElementById('notes_input_edit{{ $mapping->id }}').value =
                            quill.root.innerHTML;
                    });

                    // ===============================
                    // 🔹 File Input Dinamis
                    // ===============================
                    const container = document.getElementById("editFileFields{{ $mapping->id }}");
                    const addBtn = document.getElementById("editAddFile{{ $mapping->id }}");

                    addBtn?.addEventListener("click", function() {
                        const group = document.createElement("div");
                        group.classList.add("col-12", "d-flex", "align-items-center", "mb-2",
                            "file-input-group");
                        group.innerHTML = `
                <input type="file" class="form-control border-1 shadow-sm" name="files[]" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                    <i class="bi bi-trash"></i>
                </button>
            `;
                        container.appendChild(group);
                    });

                    container?.addEventListener("click", function(e) {
                        if (e.target.closest(".remove-file")) {
                            e.target.closest(".file-input-group").remove();
                        }
                    });

                });
            @endforeach

        });
    </script>
    {{-- Script to open modal automatically if there are validation errors --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('addDocumentModal'));
                modal.show();

                // Load old notes value into Quill editor
                if (window.quill) {
                    window.quill.root.innerHTML = {!! json_encode(old('notes', '')) !!};
                }
            });
        </script>
    @endif
@endpush
@push('styles')
    <style>
        .toggle-children i.rotated {
            transform: rotate(90deg);
            transition: transform 0.15s ease-in-out;
        }

        #quill_editor {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        #quill_editor .ql-editor {
            word-wrap: break-word !important;
            white-space: pre-wrap !important;
            overflow-wrap: break-word !important;
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        #quill_editor .ql-editor span {
            white-space: normal !important;
            word-break: break-word !important;
        }
    </style>
@endpush
