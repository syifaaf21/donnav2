@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="w-50 d-flex align-items-center" id="searchForm">
                <div class="input-group">
                    <input type="text" name="search" id="searchInput" class="form-control"
                        placeholder="Search by Document Name" value="{{ request('search') }}">

                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>

                    @if (true)
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="clearSearch">
                            Clear
                        </button>
                    @endif
                </div>
            </form>
            <button type="button" class="btn btn-outline-primary ms-auto btn-sm" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal" data-bs-title="Add New Document">
                <i class="bi bi-plus-circle me-1"></i> Add Document
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="px-3 py-3">
                <p class="mb-0"><strong>Parent:</strong> {{ $document->name }}</p>
            </div>
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
                                @forelse ($children as $child)
                                    <tr>
                                        <td>{{ ($children->currentPage() - 1) * $children->perPage() + $loop->iteration }}
                                        </td>
                                        <td>{{ $child->name }}</td>
                                        <td>{{ ucfirst($child->type) ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('documents.show', $child->id) }}"
                                                class="btn btn-sm btn-outline-info me-1" title="View Children"
                                                data-bs-title="View Child Document">
                                                <i class="bi bi-diagram-3"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editDocumentModal-{{ $child->id }}" title="Edit"
                                                data-bs-title="Edit Document">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form action="{{ route('documents.destroy', $child->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
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
                        <div class="mt-3">
                            {{ $children->withQueryString()->links() }}
                        </div>
                    </div>
                    <div class="d-flex justify-content-start mt-4">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left-circle me-1"></i>Documents List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Modal Create Document -->
        <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <form action="{{ route('documents.store') }}" method="POST">
                    @csrf
                    <div class="modal-content shadow-lg border-0 rounded-4">

                        {{-- 🔹 Header --}}
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold mb-0" id="createDocumentModalLabel">
                                <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                                Create New Document
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        {{-- 🔹 Body --}}
                        <div class="modal-body px-4 py-3">
                            <input type="hidden" name="parent_id" value="{{ $document->id }}">

                            {{-- Name --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Name</label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    placeholder="Enter document name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Type</label>
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

                        {{-- 🔹 Footer --}}
                        <div class="modal-footer bg-light border-0 rounded-bottom-4 p-3 justify-content-between">
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
        <!-- Modal Edit Document -->
        @foreach ($children as $child)
            <div class="modal fade" id="editDocumentModal-{{ $child->id }}" tabindex="-1"
                aria-labelledby="editDocumentModalLabel-{{ $child->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('documents.update', $child->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content shadow-lg border-0 rounded-4">

                            {{-- 🔹 Header --}}
                            <div class="modal-header bg-light text-dark rounded-top-4">
                                <h5 class="modal-title fw-semibold mb-0" id="editDocumentModalLabel-{{ $child->id }}">
                                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                                    Edit Document
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            {{-- 🔹 Body --}}
                            <div class="modal-body px-4 py-3">
                                {{-- Document Name --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-secondary">Name</label>
                                    <input type="text" name="name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ old('name', $child->name) }}" placeholder="Enter document name"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Document Type --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-secondary">Type</label>
                                    <select name="type"
                                        class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        @foreach (\App\Models\Document::getTypes() as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('type', $child->type) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 🔹 Footer --}}
                            <div class="modal-footer bg-light border-0 rounded-bottom-4 p-3 justify-content-between">
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

    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
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
