@extends('layouts.app')
@section('title', 'Document Control')
@section('content')
    <div class="container mx-auto my-2">
        <div class="flex justify-end p-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentControlModal">
                <i class="bi bi-plus-lg"></i> Add Document
            </button>
            @include('contents.master.document-control.partials.modal-add')
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Search Bar -->
            <div class="d-flex justify-content-end m-3">
                <form method="GET" class="flex items-center gap-2 flex-wrap" id="searchForm">
                    <div class="relative max-w-md w-full">
                        <input type="text" name="search" id="searchInput"
                            class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search..." value="{{ request('search') }}">
                        <button
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                            type="submit" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button"
                            class="absolute right-8 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                            id="clearSearch" title="Clear">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto text-sm text-gray-700">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">
                                <input type="checkbox" id="selectAll" class="form-checkbox">
                            </th>
                            <th class="px-6 py-3">No.</th>
                            <th class="px-6 py-3">Document Name</th>
                            <th class="px-6 py-3">Document Number</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Obsolete</th>
                            <th class="px-6 py-3">Reminder Date</th>
                            <th class="px-6 py-3">Notes</th>
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
                                <td class="px-6 py-4">{{ $mapping->document_number }}</td>
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
                                                        {{ $file->file_name ?? basename($file->file_path) }}
                                                    </li>
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
                                    @if (auth()->user()->role->name == 'Admin')
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $mapping->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('master.document-control.destroy', $mapping->id) }}"
                                            method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @include('contents.master.document-control.partials.modal-edit')
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Snackbar Bulk Action --}}
    <div id="snackbar" class="snackbar shadow-lg d-flex justify-content-between align-items-center">
        <div>
            <span id="selectedCount">0 selected</span>
        </div>

        <form id="bulkDeleteForm" action="{{ route('master.bulkDestroy') }}" method="POST" class="mb-0">
            @csrf
            {{-- container untuk input hidden ids[] yang akan dibuat oleh JS --}}
            <div id="bulkIdsContainer"></div>

            <button id="bulkDeleteBtn" type="submit" class="btn btn-outline-danger btn-sm" disabled>
                <i class="bi bi-trash"></i> Delete Selected
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
                snackbar.classList.toggle('show', checkedBoxes.length > 0);
                bulkDeleteBtn.disabled = checkedBoxes.length === 0;
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

            // on submit: build hidden inputs ids[] then submit
            bulkDeleteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                if (checkedBoxes.length === 0) {
                    alert('No documents selected.');
                    return;
                }

                if (!confirm(
                        `Are you sure you want to delete ${checkedBoxes.length} selected document(s)?`)) {
                    return;
                }

                // clear previous inputs
                bulkIdsContainer.innerHTML = '';

                // create hidden inputs ids[]
                checkedBoxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    bulkIdsContainer.appendChild(input);
                });

                // submit native
                bulkDeleteForm.submit();
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
        });

        document.getElementById('clearSearch').addEventListener('click', function() {
            const input = document.querySelector('input[name="search"]');
            input.value = '';
            document.getElementById('filterForm').submit();
        });
    </script>
@endpush
