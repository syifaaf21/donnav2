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
                                {{-- <td class="px-6 py-4">
                                    @if ($mapping->notes)
                                        {!! $mapping->notes !!}
                                    @else
                                        -
                                    @endif
                                </td> --}}
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
                                        @if (auth()->user()->role->name == 'Admin')
                                            <button type="button" class="text-blue-500 hover:text-blue-700"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                            </button>
                                            <form action="{{ route('master.document-control.destroy', $mapping->id) }}"
                                                method="POST" class="inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">
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

            // Fungsi untuk inisialisasi TomSelect di dalam sebuah container/modal
            function initTomSelect(container) {
                container.querySelectorAll('.tomselect').forEach(selectEl => {
                    if (!selectEl.tomselect) {
                        new TomSelect(selectEl, {
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            }
                        });
                    }
                });
            }

            // Modal Add
            const addModal = document.getElementById('addDocumentControlModal');
            if (addModal) {
                addModal.addEventListener('shown.bs.modal', function() {
                    initTomSelect(addModal);
                });
            }

            // Modal Edit - karena bisa banyak modal edit dengan id unik
            document.querySelectorAll('[id^="editModal"]').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    initTomSelect(modal);
                });
            });

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

            // master checkbox handler
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    getRowCheckboxes().forEach(cb => cb.checked = this.checked);
                    updateSnackbar();
                });
            }

            // listen perubahan pada checkbox (event delegation)
            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList && e.target.classList.contains('row-checkbox')) {
                    // if any manual uncheck, uncheck master
                    if (!e.target.checked && selectAll) selectAll.checked = false;

                    // if all are checked, set master checked
                    const all = getRowCheckboxes();
                    if (selectAll && all.length > 0) {
                        selectAll.checked = all.every(cb => cb.checked);
                    }

                    updateSnackbar();
                }
            });

            bulkDeleteForm.addEventListener('submit', function(e) {
                e.preventDefault(); // hentikan default form submit

                const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                if (checkedBoxes.length === 0) return;

                // Ambil semua id yang dicentang
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
                    title: 'Are you sure?',
                    text: `You are about to delete ${checkedBoxes.length} document(s)!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit form jika user konfirmasi
                        bulkDeleteForm.submit();
                    }
                });
            });

            // init
            updateSnackbar();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('viewFileModal');
            const iframe = document.getElementById('fileViewer');

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file;
                    const extension = fileUrl.split('.').pop().toLowerCase();

                    // Cek format file
                    if (extension === 'pdf') {
                        iframe.src = fileUrl;
                    } else if (['doc', 'docx', 'xls', 'xlsx'].includes(extension)) {
                        iframe.src =
                            `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;
                    } else {
                        iframe.src = '';
                        alert('File format not supported for preview.');
                    }
                });
            });

            modal.addEventListener('hidden.bs.modal', () => {
                iframe.src = '';
            });

            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", function() {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }
        });

        // FILE FIELD DYNAMIC ADD/REMOVE
        const container = document.getElementById("file-fields");
        const addBtn = document.getElementById("add-file");

        addBtn.addEventListener("click", function() {
            let group = document.createElement("div");
            group.classList.add("col-md-12", "d-flex", "align-items-center", "mb-2",
                "file-input-group");
            group.innerHTML = `
            <input type="file" class="form-control" name="files[]" required>
            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                <i class="bi bi-trash"></i>
            </button>
        `;
            container.appendChild(group);
        });

        container.addEventListener("click", function(e) {
            if (e.target.closest(".remove-file")) {
                e.target.closest(".file-input-group").remove();
            }
        });

        // QUILL EDITOR INIT
        const quill = new Quill('#quill_editor', {
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

        // Ambil nilai old notes dari hidden input
        const oldNotes = document.getElementById('notes_input_add').value;

        // Jika ada old notes, isi Quill editor dengan HTML tersebut
        if (oldNotes) {
            quill.root.innerHTML = oldNotes;
        }

        // Saat submit form, sinkronkan isi Quill ke hidden input
        const form = document.querySelector('#addDocumentControlModal form');
        form.addEventListener('submit', function() {
            document.getElementById('notes_input_add').value = quill.root.innerHTML;
        });
        // Tampilkan modal otomatis jika ada error validasi
        @if ($errors->any())
            const modal = new bootstrap.Modal(document.getElementById('addDocumentControlModal'));
            modal.show();
        @endif
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
