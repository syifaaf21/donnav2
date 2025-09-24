@extends('layouts.app')

@section('content')
    <!-- resources/views/contents/master/document/show.blade.php -->

    <h1>{{ $document->name }}</h1>

    <p>Department: {{ $document->department->name }}</p>

    <h4>Children Documents:</h4>
    <!-- Button Add Document -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
        Add Document
    </button>
    <table id="dataTable" class="table table-hover align-middle mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Department</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($children as $child)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $child->name }}</td>
                    <td>{{ $child->department->name }}</td>
                    <td>
                        <a href="{{ route('documents.show', $child->id) }}" class="btn btn-sm btn-info">View Children</a>

                        <!-- Edit Button -->
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                            data-bs-target="#editDocumentModal-{{ $child->id }}">
                            Edit
                        </button>

                        <!-- Delete Button -->
                        <form action="{{ route('documents.destroy', $child->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Modal Edit Document -->
                <div class="modal fade" id="editDocumentModal-{{ $child->id }}" tabindex="-1"
                    aria-labelledby="editDocumentModalLabel-{{ $child->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('documents.update', $child->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editDocumentModalLabel-{{ $child->id }}">Edit Document
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ $child->name }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Department</label>
                                        <select name="department_id" class="form-select" required>
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ $child->department_id == $department->id ? 'selected' : '' }}>
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
    <!-- Modal Create Document -->
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('documents.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDocumentModalLabel">Create New Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="parent_id" value="{{ $document->id }}">

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
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
    <a href="{{ route('documents.index') }}" class="btn btn-secondary">Back to Documents List</a>
@endsection
