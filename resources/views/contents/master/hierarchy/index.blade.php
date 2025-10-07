@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap" id="searchForm">
                <div class="input-group" style="width: 600px; max-width: 100%;">
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm"
                        placeholder="Search by Document Name" value="{{ request('search') }}">

                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>

                    @if (true)
                        <button type="button" class="btn btn-outline-danger btn-sm " id="clearSearch">
                            Clear
                        </button>
                    @endif
                </div>
            </form>
            <button type="button" class="btn btn-outline-primary btn-sm shadow-sm d-flex align-items-center gap-2 me-3" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal" data-bs-title="Add New Document">
                <i class="bi bi-plus-circle me-1"></i> Add Document
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-wrapper mb-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table modern-table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $document)
                                    <tr>
                                        <td>{{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                                        </td>
                                        <td>{{ $document->name }}</td>
                                        <td>{{ ucfirst($document->type) ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('master.hierarchy.show', $document->id) }}"
                                                class="btn btn-sm btn-outline-info me-1"
                                                data-bs-title="View Child Document">
                                                <i class="bi bi-diagram-3"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editDocumentModal-{{ $document->id }}"
                                                data-bs-title="Edit Document">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form action="{{ route('master.hierarchy.destroy', $document->id) }}" method="POST"
                                                class="delete-form d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-bs-title="Delete Document">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No documents found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $documents->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Document --}}
        @foreach ($documents as $document)
            <div class="modal fade" id="editDocumentModal-{{ $document->id }}" tabindex="-1"
                aria-labelledby="editDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('master.hierarchy.update', $document->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header bg-light text-dark rounded-top-4">
                                <h5 class="modal-title fw-semibold">
                                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Document
                                </h5>
                            </div>

                            <div class="modal-body px-4 py-3">
                                {{-- Document Name --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Name</label>
                                    <input type="text" name="name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ old('name', $document->name) }}" placeholder="Enter document name"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Document Type --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach (\App\Models\Document::getTypes() as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('type', $document->type) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-outline-success px-4">
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- 📄 Create Document Modal --}}
        <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('master.hierarchy.store') }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold">
                                <i class="bi bi-file-earmark-plus me-2 text-primary"></i> Create New Document
                            </h5>
                        </div>

                        <div class="modal-body px-4 py-3">
                            {{-- Document Name --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Enter document name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Document Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type</label>
                                <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror"
                                    required>
                                    <option value="">Select Type</option>
                                    @foreach (\App\Models\Document::getTypes() as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('type') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // Clear Search functionality
            document.addEventListener("DOMContentLoaded", function() {
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

            //Tooltip
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
                tooltipTriggerList.map(function(el) {
                    return new bootstrap.Tooltip(el, {
                        title: el.getAttribute('data-bs-title'),
                        placement: 'top',
                        trigger: 'hover'
                    });
                });
            });
        </script>
    @endpush
