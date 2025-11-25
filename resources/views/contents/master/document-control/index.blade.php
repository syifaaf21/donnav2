@extends('layouts.app')
@section('title', 'Document Control')

@section('content')
    <div class="container mx-auto px-4 py-2">

        {{-- Header --}}
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
                    <li class="text-gray-700 font-medium">Document Control</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addDocumentControlModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i> Add Document
            </button>
            @include('contents.master.document-control.partials.modal-add')
        </div>

        {{-- Table Card --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end items-center space-x-2">
                <form method="GET" id="searchForm" class="flex items-center w-full sm:w-96 relative">

                    <input type="text" name="search" value="{{ request('search') }}" id="searchInput"
                        placeholder="Search..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <!-- Search icon -->
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500">
                        <i class="bi bi-search"></i>
                    </button>

                    <!-- Clear button -->
                    <button type="button" id="clearSearch"
                        class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500"
                        onclick="document.getElementById('searchInput').value=''; document.getElementById('searchForm').submit();">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>

                {{-- Filter Button --}}
                <div class="relative ml-3">
                    <button id="filterBtn" type="button"
                        class="flex items-center gap-1 px-3 py-2 bg-gray-100 border rounded-lg text-gray-600
                       hover:bg-gray-200 hover:text-gray-800 transition">
                        <i class="bi bi-funnel-fill"></i>
                    </button>

                    {{-- Dropdown Filter --}}
                    <div id="filterDropdown" class="hidden bg-white w-64 border rounded-lg shadow-xl p-4 z-50"
                        style="position: fixed; top: 65px; right: 40px;">

                        <form id="filterForm">
                            <input type="hidden" name="search" value="{{ request('search') }}">

                            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                Filter by Department
                            </h3>

                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @foreach ($departments as $department)
                                    <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm">
                                        <input type="checkbox" name="department[]" value="{{ $department->id }}"
                                            class="rounded text-blue-600 border-gray-300 focus:ring-blue-500 departmentCheck"
                                            {{ is_array(request('department')) && in_array($department->id, request('department')) ? 'checked' : '' }}>
                                        <span>{{ $department->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div id="tableContainer">
                {{-- Table --}}
                <div class="overflow-x-auto overflow-y-auto max-h-96">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2">
                                    <input type="checkbox" id="selectAll" class="form-checkbox">
                                </th>
                                <th class="px-4 py-2">No</th>
                                <th class="px-4 py-2">Document Name</th>
                                <th class="px-4 py-2">Department</th>
                                <th class="px-4 py-2">Obsolete</th>
                                <th class="px-4 py-2">Reminder Date</th>
                                <th class="px-4 py-2">Document Period</th>
                                <th class="px-4 py-2 text-center action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($documentMappings as $mapping)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="row-checkbox form-checkbox"
                                            value="{{ $mapping->id }}">
                                    </td>
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4">{{ $mapping->document->name ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $mapping->department->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($mapping->period_years)
                                            {{ $mapping->period_years }}
                                            {{ $mapping->period_years == 1 ? 'Year' : 'Years' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 flex space-x-2 whitespace-nowrap action-column">
                                        @if ($mapping->files->count())
                                            <div class="relative inline-block">
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-sm view-files-btn relative flex items-center justify-center w-8 h-8"
                                                    data-mapping-id="{{ $mapping->id }}"
                                                    data-files='@json(
                                                        $mapping->files->map(fn($file) => [
                                                                'name' => $file->file_name ?? basename($file->file_path),
                                                                'url' => asset('storage/' . $file->file_path),
                                                            ]))'>
                                                    <i class="bi bi-paperclip text-lg"></i>

                                                    @if ($mapping->files->count() > 1)
                                                        <span
                                                            class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full shadow">
                                                            {{ $mapping->files->count() }}
                                                        </span>
                                                    @endif
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-gray-500">No File</span>
                                        @endif

                                        <div class="flex items-center gap-2">
                                            @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
                                                <button type="button"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200 shrink-0"
                                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
                                                    <i data-feather="edit" class="w-4 h-4"></i>
                                                </button>
                                                <form action="{{ route('master.document-control.destroy', $mapping->id) }}"
                                                    method="POST" class="inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="bg-red-600 text-white hover:bg-red-700 p-2 rounded shrink-0">
                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @include('contents.master.document-control.partials.modal-edit')
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $documentMappings->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>
    <!-- Global Files Dropdown -->
    <div id="globalFileDropdown"
        class="hidden absolute bg-white border border-gray-300 rounded shadow-lg z-50 min-w-[200px]">
        <ul id="globalFileList" class="p-2"></ul>
    </div>
    {{-- Snackbar Bulk Action --}}
    <div id="snackbar"
        class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white shadow-lg rounded-lg p-3 flex items-center justify-between space-x-4 w-full max-w-lg z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="flex items-center gap-2">
            <i class="bi bi-check-circle text-green-500"></i>
            <span id="selectedCount" class="text-gray-700 font-medium">0 selected</span>
        </div>

        <form id="bulkDeleteForm" action="{{ route('master.bulkDestroy') }}" method="POST" class="mb-0">
            @csrf
            {{-- container untuk input hidden ids[] yang akan dibuat oleh JS --}}
            <div id="bulkIdsContainer"></div>

            <button id="bulkDeleteBtn" type="submit"
                class="flex items-center gap-1 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <i data-feather="trash-2" class="w-4 h-4"></i>
                Delete Selected
            </button>
        </form>
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
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const globalDropdown = document.getElementById('globalFileDropdown');
            const globalFileList = document.getElementById('globalFileList');

            /** =======================
             * VIEW FILE DROPDOWN
             * ======================= */
            document.querySelectorAll('.view-files-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const files = JSON.parse(this.dataset.files);
                    globalFileList.innerHTML = '';

                    files.forEach(file => {
                        const li = document.createElement('li');
                        li.className =
                            'flex justify-between items-center p-1 hover:bg-gray-100 rounded';
                        li.innerHTML = `
                    <span class="truncate">${file.name}</span>
                    <a href="${file.url}" target="_blank" class="text-blue-600 hover:underline ml-2">
                        <i class="bi bi-eye"></i>
                    </a>
                `;
                        globalFileList.appendChild(li);
                    });

                    const rect = btn.getBoundingClientRect();
                    globalDropdown.style.top = `${rect.bottom + window.scrollY}px`;
                    globalDropdown.style.left = `${rect.left + window.scrollX}px`;
                    globalDropdown.classList.remove('hidden');
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.view-files-btn') && !e.target.closest('#globalFileDropdown')) {
                    globalDropdown.classList.add('hidden');
                }
            });

            /** =======================
             * Initialize TomSelect
             * ======================= */
            function initTomSelect(container) {
                container.querySelectorAll('.tomselect').forEach(selectEl => {
                    if (!selectEl.tomselect) {
                        new TomSelect(selectEl, {
                            create: false,
                            plugins: {
                                remove_button: {
                                    title: 'Remove this item'
                                }
                            },
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            persist: false
                        });
                    }
                });
            }

            /** =======================
             * Initialize Quill Editor
             * ======================= */
            function initQuill(editorDiv, hiddenInput) {
                if (!editorDiv || editorDiv.classList.contains('quill-initialized')) return;

                const quill = new Quill(editorDiv, {
                    theme: 'snow',
                    placeholder: 'Write your notes here...',
                    modules: {
                        toolbar: [
                            [{
                                'font': []
                            }, {
                                'size': []
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                'color': []
                            }, {
                                'background': []
                            }],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'align': []
                            }],
                            ['clean']
                        ]
                    }
                });

                quill.root.innerHTML = hiddenInput.value || '';

                const form = editorDiv.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        let content = quill.root.innerHTML;
                        hiddenInput.value = (content === '<p><br></p>') ? '' : content;
                    });
                }

                quill.on('text-change', () => {
                    hiddenInput.value = (quill.root.innerHTML === '<p><br></p>') ? '' : quill.root
                        .innerHTML;
                });

                editorDiv.classList.add('quill-initialized');
            }

            /** =======================
             * MODAL: Add Document
             * ======================= */
            const addModalEl = document.getElementById('addDocumentControlModal');
            if (addModalEl) {
                addModalEl.addEventListener('shown.bs.modal', () => {
                    initTomSelect(addModalEl);
                    setTimeout(() => {
                        const editorDiv = document.getElementById('quill_editor_add');
                        const hiddenInput = document.getElementById('notes_input_add');
                        initQuill(editorDiv, hiddenInput);
                    }, 100);
                });

                addModalEl.addEventListener('hidden.bs.modal', () => {
                    const form = addModalEl.querySelector('form');
                    form.reset();

                    const editorDiv = document.getElementById('quill_editor_add');
                    const hiddenInput = document.getElementById('notes_input_add');
                    const quill = Quill.find(editorDiv);
                    if (quill) {
                        quill.root.innerHTML = '';
                        hiddenInput.value = '';
                    }

                    addModalEl.querySelectorAll('.tomselect').forEach(sel => sel.tomselect.clear());

                    const fileContainer = document.getElementById("file-fields");
                    if (fileContainer) {
                        fileContainer.innerHTML = `
                    <div class="col-md-12 d-flex align-items-center mb-2 file-input-group">
                        <input type="file" class="form-control" name="files[]" required>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                    }

                    addModalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                });

                // CANCEL BUTTON
                const cancelBtn = addModalEl.querySelector('[data-bs-dismiss="modal"]');
                const form = addModalEl.querySelector('form');
                if (cancelBtn && form) {
                    cancelBtn.addEventListener('click', () => {
                        form.reset();

                        const editorDiv = document.getElementById('quill_editor_add');
                        const hiddenInput = document.getElementById('notes_input_add');
                        const quill = Quill.find(editorDiv);
                        if (quill) {
                            quill.root.innerHTML = '';
                            hiddenInput.value = '';
                        }

                        addModalEl.querySelectorAll('.tomselect').forEach(sel => sel.tomselect.clear());

                        const fileContainer = document.getElementById("file-fields");
                        if (fileContainer) {
                            fileContainer.innerHTML = `
                        <div class="col-md-12 d-flex align-items-center mb-2 file-input-group">
                            <input type="file" class="form-control" name="files[]" required>
                            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                        }

                        addModalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                            'is-invalid'));
                    });
                }
            }

            /** =======================
             * MODAL: Edit Document
             * ======================= */
            document.querySelectorAll('[id^="editModal"]').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    initTomSelect(modal);

                    const mappingId = modal.dataset.mappingId;
                    if (!mappingId) return;

                    setTimeout(() => {
                        const editorDiv = document.getElementById(
                            `quill_editor_edit${mappingId}`);
                        const hiddenInput = document.getElementById(
                            `notes_input_edit${mappingId}`);
                        initQuill(editorDiv, hiddenInput);
                    }, 50);

                    modal.querySelectorAll('input, select, textarea').forEach(el => {
                        const errorDiv = el.closest('.col-md-6, .col-12')?.querySelector(
                            '.invalid-feedback');
                        if (errorDiv && errorDiv.textContent.trim() !== '') {
                            el.classList.add('is-invalid');
                        }
                    });
                });

                modal.addEventListener('hidden.bs.modal', () => {
                    modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                });
            });

            /** =======================
             * BULK DELETE & SNACKBAR
             * ======================= */
            const selectAll = document.getElementById('selectAll');
            const snackbar = document.getElementById('snackbar');
            const selectedCount = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const bulkIdsContainer = document.getElementById('bulkIdsContainer');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

            function getRowCheckboxes() {
                return Array.from(document.querySelectorAll('.row-checkbox'));
            }

            function updateSnackbar() {
                const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                selectedCount.textContent = `${checkedBoxes.length} selected`;
                bulkDeleteBtn.disabled = checkedBoxes.length === 0;

                if (checkedBoxes.length > 0) {
                    snackbar.classList.remove('opacity-0', 'pointer-events-none');
                    snackbar.classList.add('opacity-100', 'pointer-events-auto');
                } else {
                    snackbar.classList.remove('opacity-100', 'pointer-events-auto');
                    snackbar.classList.add('opacity-0', 'pointer-events-none');
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    getRowCheckboxes().forEach(cb => cb.checked = this.checked);
                    updateSnackbar();
                });
            }

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('row-checkbox')) {
                    if (!e.target.checked && selectAll) selectAll.checked = false;
                    if (selectAll) selectAll.checked = getRowCheckboxes().every(cb => cb.checked);
                    updateSnackbar();
                }
            });

            if (bulkDeleteForm) {
                bulkDeleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                    if (!checkedBoxes.length) return;

                    bulkIdsContainer.innerHTML = '';
                    checkedBoxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkIdsContainer.appendChild(input);
                    });

                    Swal.fire({
                        title: 'Delete selected documents?',
                        text: `You are about to delete ${checkedBoxes.length} document(s). This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete them'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(bulkDeleteForm.action, {
                                method: 'POST',
                                body: new FormData(bulkDeleteForm),
                            }).then(response => {
                                if (response.redirected) window.location.href = response
                                    .url;
                                else throw new Error('Unexpected response');
                            }).catch(() => {
                                Swal.fire('Error',
                                    'Failed to delete documents. Please try again.',
                                    'error');
                            });
                        }
                    });
                });
            }

            updateSnackbar();

            /** =======================
             * FILE INPUT DYNAMICALLY
             * ======================= */
            const addFileBtn = document.getElementById('add-file');
            const fileContainer = document.getElementById('file-fields');
            if (addFileBtn && fileContainer) {
                addFileBtn.addEventListener('click', function() {
                    const newFileGroup = document.createElement('div');
                    newFileGroup.classList.add('col-md-12', 'd-flex', 'align-items-center', 'mb-2',
                        'file-input-group');
                    newFileGroup.innerHTML = `
                <input type="file" class="form-control" name="files[]" required>
                <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                    <i class="bi bi-trash"></i>
                </button>
            `;
                    fileContainer.appendChild(newFileGroup);
                    updateRemoveButtons();
                });

                function updateRemoveButtons() {
                    const fileGroups = fileContainer.querySelectorAll('.file-input-group');
                    fileGroups.forEach((group, index) => {
                        const removeBtn = group.querySelector('.remove-file');
                        if (removeBtn) {
                            removeBtn.classList.toggle('d-none', fileGroups.length === 1);
                            removeBtn.onclick = () => {
                                group.remove();
                                updateRemoveButtons();
                            };
                        }
                    });
                }
                updateRemoveButtons();
            }

            /** =======================
             * SEARCH & FILTER (FORM SUBMIT BIASA)
             * ======================= */
            const searchInput = document.getElementById('searchInput');
            const filterCheckboxes = document.querySelectorAll('.departmentCheck');

            // Submit search on enter
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') searchInput.form.submit();
                });
            }

            // Submit filter when checkbox changes
            filterCheckboxes.forEach(chk => {
                chk.addEventListener('change', function() {
                    this.form.submit();
                });
            });

            // =======================
            // FILTER DROPDOWN TOGGLE
            // =======================
            const filterBtn = document.getElementById('filterBtn');
            const filterDropdown = document.getElementById('filterDropdown');

            if (filterBtn && filterDropdown) {
                filterBtn.addEventListener('click', () => {
                    filterDropdown.classList.toggle('hidden');

                    if (!filterDropdown.classList.contains('hidden')) {
                        const rect = filterBtn.getBoundingClientRect();
                        const dropdownWidth = filterDropdown.offsetWidth;
                        let left = rect.left;
                        const top = rect.bottom + window.scrollY;

                        // Pastikan dropdown tidak keluar viewport kanan
                        if (left + dropdownWidth > window.innerWidth - 100) {
                            left = window.innerWidth - dropdownWidth - 120;
                        }

                        filterDropdown.style.top = `${top}px`;
                        filterDropdown.style.left = `${left}px`;
                    }
                });

                // Klik di luar untuk tutup dropdown
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#filterDropdown') && !e.target.closest('#filterBtn')) {
                        filterDropdown.classList.add('hidden');
                    }
                });
            }


            // Clear search/filter
            const clearFilterBtn = document.getElementById('clearFilter');
            if (clearFilterBtn) {
                clearFilterBtn.addEventListener('click', () => {
                    filterCheckboxes.forEach(chk => chk.checked = false);
                    if (searchInput) searchInput.value = '';
                    searchInput?.form.submit();
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
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

        /* Hilangkan pagination global kalau Tailwind render dua kali di bawah card */
        nav[role="navigation"]:not(:last-of-type) {
            display: none !important;
        }

        #globalFileDropdown li span {
            display: block;
            max-width: 150px;
            /* atau sesuai kebutuhan */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-column {
            min-width: 120px;
            /* atau sesuai kebutuhan */
            white-space: nowrap;
        }
    </style>
@endpush
