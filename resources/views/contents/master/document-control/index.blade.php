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
                <i class="bi bi-plus-circle"></i>
                <span>Add Document</span>
            </button>
            @include('contents.master.document-control.partials.modal-add')
        </div>

        {{-- Card --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <form method="GET" id="searchForm" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-auto rounded-bottom-lg h-[60vh]">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-600">
                    {{-- Header tetap sama --}}
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">
                                <input type="checkbox" id="selectAll" class="form-checkbox">
                            </th>
                            <th class="px-6 py-3">No.</th>
                            <th class="px-6 py-3">Document Name</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Obsolete</th>
                            <th class="px-6 py-3">Reminder Date</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($documentMappings as $mapping)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="row-checkbox form-checkbox" value="{{ $mapping->id }}">
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
                                <td class="px-6 py-4 flex space-x-2">
                                    @if ($mapping->files->count())
                                        <div class="relative">
                                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                <i class="bi bi-paperclip"></i> Files
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach ($mapping->files as $file)
                                                    @php
                                                        $fileUrl = asset('storage/' . $file->file_path);
                                                        $extension = strtolower(
                                                            pathinfo($file->file_path, PATHINFO_EXTENSION),
                                                        );
                                                        $isPdf = $extension === 'pdf';
                                                        $isOffice = in_array($extension, [
                                                            'doc',
                                                            'docx',
                                                            'xls',
                                                            'xlsx',
                                                        ]);
                                                        $viewerUrl = $isPdf
                                                            ? $fileUrl
                                                            : 'https://docs.google.com/gview?url=' .
                                                                urlencode($fileUrl) .
                                                                '&embedded=true';
                                                    @endphp
                                                    <li class="px-3 small text-muted text-truncate">
                                                        {{ $file->file_name ?? basename($file->file_path) }}</li>
                                                    <li>
                                                        @if ($isPdf || $isOffice)
                                                            <button type="button" class="dropdown-item view-file-btn"
                                                                data-bs-toggle="modal" data-bs-target="#viewFileModal"
                                                                data-file="{{ $viewerUrl }}">
                                                                <i class="bi bi-eye me-1"></i> View
                                                            </button>
                                                        @else
                                                            <span class="dropdown-item text-muted disabled">Preview Not
                                                                Supported</span>
                                                        @endif
                                                    </li>
                                                    <li>
                                                        <a href="{{ $fileUrl }}" class="dropdown-item" download>
                                                            <i class="bi bi-download me-1"></i> Download
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-gray-500">No File</span>
                                    @endif

                                    <div class="flex items-center gap-2">
                                        @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
                                            <button type="button"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </button>
                                            <form action="{{ route('master.document-control.destroy', $mapping->id) }}"
                                                method="POST" class="inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
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

            /** =======================
             * FUNCTION: Initialize TomSelect
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
             * FUNCTION: Initialize Quill Editor
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

                // Set nilai awal dari hidden input
                quill.root.innerHTML = hiddenInput.value || '';

                // Sinkron saat submit
                const form = editorDiv.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        let content = quill.root.innerHTML;

                        // Hapus konten kosong <p><br></p>
                        if (content === '<p><br></p>') content = '';
                        hiddenInput.value = content;
                    });
                }

                // Optional: sinkron real-time saat user mengetik
                quill.on('text-change', () => {
                    let content = quill.root.innerHTML;
                    hiddenInput.value = (content === '<p><br></p>') ? '' : content;
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

                    // ✅ Perbaikan utama: tambahkan delay sebelum inisialisasi Quill
                    setTimeout(() => {
                        const editorDiv = document.getElementById('quill_editor_add');
                        const hiddenInput = document.getElementById('notes_input_add');
                        initQuill(editorDiv, hiddenInput);
                    }, 100);
                });

                // ===== RESET MODAL ADD saat ditutup =====
                addModalEl.addEventListener('hidden.bs.modal', () => {
                    const form = addModalEl.querySelector('form');
                    form.reset();

                    // Reset Quill
                    const editorDiv = document.getElementById('quill_editor_add');
                    const hiddenInput = document.getElementById('notes_input_add');
                    const quill = Quill.find(editorDiv);
                    if (quill) {
                        quill.root.innerHTML = '';
                        hiddenInput.value = '';
                    }

                    // Reset TomSelect
                    addModalEl.querySelectorAll('.tomselect').forEach(sel => sel.tomselect.clear());

                    // Reset file fields
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

                    // Remove error classes
                    addModalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                });
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
                    // Tambah validasi error display
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
             * SEARCH CLEAR BUTTON
             * ======================= */
            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", () => {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }

            /** =======================
             * SHOW MODAL IF VALIDATION ERRORS
             * ======================= */
            @if ($errors->any())
                @if (session('editModalId'))
                    const editModalEl = document.getElementById('editModal{{ session('editModalId') }}');
                    if (editModalEl) {
                        const modal = new bootstrap.Modal(editModalEl);
                        modal.show();
                    }
                @else
                    const addModalElShow = document.getElementById('addDocumentControlModal');
                    if (addModalElShow) {
                        const modal = new bootstrap.Modal(addModalElShow);
                        modal.show();
                    }
                @endif
            @endif

            /** =======================
             * CANCEL BUTTON (Add Modal)
             * ======================= */
            if (addModalEl) {
                const cancelBtn = addModalEl.querySelector('[data-bs-dismiss="modal"]');
                const form = addModalEl.querySelector('form');

                if (cancelBtn && form) {
                    cancelBtn.addEventListener('click', () => {
                        form.reset();

                        // Reset Quill
                        const editorDiv = document.getElementById('quill_editor_add');
                        const hiddenInput = document.getElementById('notes_input_add');
                        const quill = Quill.find(editorDiv);
                        if (quill) {
                            quill.root.innerHTML = '';
                            hiddenInput.value = '';
                        }

                        // Reset TomSelect
                        addModalEl.querySelectorAll('.tomselect').forEach(sel => sel.tomselect.clear());

                        // Reset file fields
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
             * ADD FILE INPUT DYNAMICALLY
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

                // Fungsi untuk aktifkan tombol "hapus file"
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

                // Inisialisasi pertama
                updateRemoveButtons();
            }


            /** =======================
             * BULK DELETE (Snackbar)
             * =======================
             */
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

            // ✅ Checkbox select all
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    getRowCheckboxes().forEach(cb => cb.checked = this.checked);
                    updateSnackbar();
                });
            }

            // ✅ Checkbox per-row
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('row-checkbox')) {
                    if (!e.target.checked && selectAll) selectAll.checked = false;

                    const all = getRowCheckboxes();
                    if (selectAll) selectAll.checked = all.every(cb => cb.checked);

                    updateSnackbar();
                }
            });

            // ✅ Handler submit bulk delete
            if (bulkDeleteForm) {
                bulkDeleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                    if (!checkedBoxes.length) return;

                    // isi ulang kontainer id
                    bulkIdsContainer.innerHTML = '';
                    checkedBoxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkIdsContainer.appendChild(input);
                    });

                    // SweetAlert konfirmasi
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
                            // Submit via fetch biar tetap smooth
                            fetch(bulkDeleteForm.action, {
                                    method: 'POST',
                                    body: new FormData(bulkDeleteForm),
                                })
                                .then(response => {
                                    if (response.redirected) {
                                        // Redirect (Laravel redirect()->route()) akan tetap jalan
                                        window.location.href = response.url;
                                    } else {
                                        throw new Error('Unexpected response');
                                    }
                                })
                                .catch(() => {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Failed to delete documents. Please try again.',
                                        icon: 'error',
                                    });
                                });
                        }
                    });
                });
            }

            // Inisialisasi pertama
            updateSnackbar();

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file;
                    const iframe = document.getElementById('fileViewer');
                    iframe.src = fileUrl;
                });
            });

            // Optional: reset src saat modal ditutup supaya tidak tetap loaded
            const modal = document.getElementById('viewFileModal');
            modal.addEventListener('hidden.bs.modal', () => {
                document.getElementById('fileViewer').src = '';
            });
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
    </style>
@endpush
