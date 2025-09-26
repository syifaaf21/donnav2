@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="w-50">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by Document Name or Department" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-auto btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <button type="button" class="btn btn-outline-primary ms-auto btn-sm" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal">
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
                                    <th>Department</th>
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
                                        <td>{{ $document->department->name ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('documents.show', $document->id) }}"
                                                class="btn btn-sm btn-outline-info me-1">
                                                <i class="bi bi-diagram-3"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editDocumentModal-{{ $document->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form action="{{ route('documents.destroy', $document->id) }}" method="POST"
                                                class="delete-form d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
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
                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $documents->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Document --}}
        @foreach ($documents as $document)
            <div class="modal fade" id="editDocumentModal-{{ $document->id }}" tabindex="-1"
                aria-labelledby="editDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('documents.update', $document->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Document</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $document->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
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

                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select name="department_id"
                                        class="form-select @error('department_id') is-invalid @enderror" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ old('department_id', $document->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Save Changes</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- Modal Create Document --}}
        <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('documents.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
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

                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select name="department_id"
                                    class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Document</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
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
    </script>
@endpush
