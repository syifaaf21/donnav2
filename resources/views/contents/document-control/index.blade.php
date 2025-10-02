@extends('layouts.app')
@section('title', 'Document Control')
@section('content')
    <div class="container my-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-2">Total Documents</h6>
                        <span class="display-6 text-primary">
                            {{ $documentMappings->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-2">Need Review</h6>
                        <span class="display-6 text-warning">
                            {{ $documentMappings->where('status.name', 'Need Review')->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-2">Active</h6>
                        <span class="display-6 text-success">
                            {{ $documentMappings->where('status.name', 'Active')->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-2">Obsolete</h6>
                        <span class="display-6 text-secondary">
                            {{ $documentMappings->where('status.name', 'Obsolete')->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">📄 Document Control List</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addDocumentControlModal">
                            <i class="bi bi-plus-lg"></i> Add Document
                        </button>
                        @include('contents.document-control.partials.modal-add')
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table modern-table table-hover align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>#</th>
                                        <th>Document</th>
                                        <th>Number</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Version</th>
                                        <th>Updated By</th>
                                        <th>File</th>
                                        <th>Obsolete</th>
                                        <th>Reminder</th>
                                        <th>Notes</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documentMappings as $mapping)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="row-checkbox form-check-input"
                                                    value="{{ $mapping->id }}">
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $mapping->document->name ?? '-' }}</td>
                                            <td>{{ $mapping->document_number }}</td>
                                            <td>{{ $mapping->department->name ?? '-' }}</td>
                                            <td>
                                                <span
                                                    class="badge
                                                @if ($mapping->status->name == 'Active') bg-success
                                                @elseif($mapping->status->name == 'Need Review') bg-warning text-dark
                                                @elseif($mapping->status->name == 'Rejected') bg-danger
                                                @else bg-secondary @endif">
                                                    {{ $mapping->status->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td>{{ $mapping->version }}</td>
                                            <td>{{ $mapping->user->name ?? '-' }}</td>
                                            <td>
                                                @if ($mapping->file_path)
                                                    <a href="{{ asset('storage/' . $mapping->file_path) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($mapping->obsolete_date)
                                                    {{ \Carbon\Carbon::parse($mapping->obsolete_date)->format('d-m-Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('d-m-Y') : '-' }}
                                            </td>
                                            <td>{{ $mapping->notes ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($mapping->created_at)->format('d-m-Y') }}</td>
                                            <td class="text-nowrap">
                                                @if (auth()->user()->role->name == 'Admin')
                                                    {{-- Edit --}}
                                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $mapping->id }}"
                                                        data-bs-title="Edit Metadata">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    {{-- Revisi --}}
                                                    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#reviseModal{{ $mapping->id }}"
                                                        data-bs-title="Revise Document">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>

                                                    {{-- Status Specific Actions --}}
                                                    @if ($mapping->status->name == 'Need Review')
                                                        {{-- Approve --}}
                                                        <form
                                                            action="{{ route('document-control.approve', $mapping->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success btn-sm"
                                                                data-bs-title="Approve Document">
                                                                <i class="bi bi-check2-circle"></i>
                                                            </button>
                                                        </form>

                                                        {{-- Reject --}}
                                                        <form action="{{ route('document-control.reject', $mapping->id) }}"
                                                            method="POST" class="d-inline reject-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                data-bs-title="Reject Document">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </form>
                                                    @elseif ($mapping->status->name == 'Active')
                                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                            disabled>
                                                            <i class="bi bi-check2-all"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            disabled>
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    @elseif ($mapping->status->name == 'Rejected')
                                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            disabled>
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            disabled>
                                                            <i class="bi bi-x-circle-fill"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                                            <i class="bi bi-slash-circle"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Delete --}}
                                                    <form action="{{ route('document-control.destroy', $mapping->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            data-bs-title="Delete Document">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @include('contents.document-control.partials.modal-edit')
                                        @include('contents.document-control.partials.modal-revise')
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center text-muted">No Data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Snackbar Bulk Action --}}
    <div id="snackbar" class="snackbar shadow-lg d-flex justify-content-between align-items-center">
        <div>
            <span id="selectedCount">0 selected</span>
        </div>

        <form id="bulkDeleteForm" action="{{ route('bulkDestroy') }}" method="POST" class="mb-0">
            @csrf
            {{-- container untuk input hidden ids[] yang akan dibuat oleh JS --}}
            <div id="bulkIdsContainer"></div>

            <button id="bulkDeleteBtn" type="submit" class="btn btn-outline-danger btn-sm" disabled>
                <i class="bi bi-trash"></i> Delete Selected
            </button>
        </form>
    </div>

@endsection

@push('scripts')
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
    </script>
@endpush
