@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Model List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModelModal">
            <i class="bi bi-plus-circle"></i> Add Model
        </button>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search model...">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            <tr>
                                <td>{{ ($models->currentPage() - 1) * $models->perPage() + $loop->iteration }}</td>
                                <td>{{ $model->name }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editModelModal-{{ $model->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('master.models.destroy', $model->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editModelModal-{{ $model->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('master.models.update', $model->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title">Edit Model</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" value="{{ $model->name }}" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Save</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No models found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $models->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('master.models.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Add New Model</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Model Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                        @error('name')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Model</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
