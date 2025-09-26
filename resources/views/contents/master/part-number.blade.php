@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="w-50">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by Part Number, Product, Model, or Process" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-auto btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <button class="btn btn-outline-primary ms-auto btn-sm" data-bs-toggle="modal"
                data-bs-target="#createPartNumberModal">
                <i class="bi bi-plus-circle me-1"></i> Add Part Number
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
                                    <th>Part Number</th>
                                    <th>Product</th>
                                    <th>Model</th>
                                    <th>Process</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partNumbers as $part)
                                    <tr>
                                        <td>{{ ($partNumbers->currentPage() - 1) * $partNumbers->perPage() + $loop->iteration }}</td>
                                        <td>{{ $part->part_number }}</td>
                                        <td>{{ $part->product->name ?? '-' }}</td>
                                        <td>{{ $part->productModel->name ?? '-' }}</td>
                                        <td>{{ ucfirst($part->process) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editPartNumberModal-{{ $part->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form action="{{ route('part_numbers.destroy', $part->id) }}" method="POST"
                                                class="d-inline delete-form">
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
                                        <td colspan="6" class="text-center">No part number found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $partNumbers->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modals Edit --}}
        @foreach ($partNumbers as $part)
            <div class="modal fade" id="editPartNumberModal-{{ $part->id }}" tabindex="-1"
                aria-labelledby="editPartNumberModalLabel-{{ $part->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('part_numbers.update', $part->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Part Number</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Part Number</label>
                                    <input type="text" name="part_number"
                                        class="form-control @error('part_number') is-invalid @enderror"
                                        value="{{ old('part_number', $part->part_number) }}" required>
                                    @error('part_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label>Product</label>
                                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id', $part->product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label>Model</label>
                                    <select name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                                        <option value="">Select Model</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model->id }}" {{ old('model_id', $part->model_id) == $model->id ? 'selected' : '' }}>
                                                {{ $model->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('model_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label>Process</label>
                                    <select name="process" class="form-select @error('process') is-invalid @enderror" required>
                                        <option value="">Select Process</option>
                                        @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric'] as $process)
                                            <option value="{{ $process }}" {{ old('process', $part->process) == $process ? 'selected' : '' }}>
                                                {{ ucfirst($process) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('process')
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

        {{-- Modal Create --}}
        <div class="modal fade" id="createPartNumberModal" tabindex="-1" aria-labelledby="createPartNumberModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('part_numbers.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Part Number</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Part Number</label>
                                <input type="text" name="part_number" class="form-control @error('part_number') is-invalid @enderror" value="{{ old('part_number') }}" required>
                                @error('part_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Product</label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Model</label>
                                <select name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                                    <option value="">Select Model</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>
                                            {{ $model->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('model_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Process</label>
                                <select name="process" class="form-select @error('process') is-invalid @enderror" required>
                                    <option value="">Select Process</option>
                                    @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric'] as $process)
                                        <option value="{{ $process }}" {{ old('process') == $process ? 'selected' : '' }}>
                                            {{ ucfirst($process) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('process') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Part Number</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone.',
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
