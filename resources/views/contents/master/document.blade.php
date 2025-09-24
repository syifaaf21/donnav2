@extends('layouts.app')

@section('content')
<div>
    <h1>Document List</h1>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
        Add Document
    </button>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Document Number</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $document->name }}</td>
                    <td>{{ $document->document_number }}</td>
                    <td>{{ $document->department->name ?? '-' }}</td>
                    <td>{{ ucfirst($document->type) }}</td>
                    <td>{{ $document->version }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDocumentModal-{{ $document->id }}">
                            Edit
                        </button>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Edit Document -->
                <div class="modal fade" id="editDocumentModal-{{ $document->id }}" tabindex="-1" aria-labelledby="editDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('document.update', $document->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editDocumentModalLabel-{{ $document->id }}">Edit Document</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $document->name }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Document Number</label>
                                        <input type="text" name="document_number" class="form-control" value="{{ $document->document_number }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="review" {{ $document->type == 'review' ? 'selected' : '' }}>Review</option>
                                            <option value="control" {{ $document->type == 'control' ? 'selected' : '' }}>Control</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Version</label>
                                        <input type="text" name="version" class="form-control" value="{{ $document->version }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Department</label>
                                        <select name="department_id" class="form-select" required>
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ $document->department_id == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Update Document</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Create Document -->
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('documents.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDocumentModalLabel">Create New Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Document Number</label>
                            <input type="text" name="document_number" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="review">Review</option>
                                <option value="control">Control</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Version</label>
                            <input type="text" name="version" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
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
