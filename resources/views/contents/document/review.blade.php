@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewModal">
            <i class="bi bi-plus-circle me-1"></i> Add Review Document
        </button>
    </div>

    {{-- Tabs berdasarkan Plant --}}
    <ul class="nav nav-tabs mb-3" id="plantTabs" role="tablist">
        @foreach ($groupedByPlant as $plant => $documents)
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link @if ($loop->first) active @endif"
                    id="{{ Str::slug($plant) }}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#{{ Str::slug($plant) }}"
                    type="button"
                    role="tab"
                    aria-controls="{{ Str::slug($plant) }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $plant }}
                </button>
            </li>
        @endforeach
    </ul>

    {{-- Isi tab --}}
    <div class="tab-content" id="plantTabsContent">
        @foreach ($groupedByPlant as $plant => $documents)
            <div
                class="tab-pane fade @if ($loop->first) show active @endif"
                id="{{ Str::slug($plant) }}"
                role="tabpanel"
                aria-labelledby="{{ Str::slug($plant) }}-tab">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <strong>Plant: {{ $plant }}</strong>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Document Name</th>
                                        <th>Document Number</th>
                                        <th>Version</th>
                                        <th>File</th>
                                        <th>Notes</th>
                                        <th>Reminder Date</th>
                                        <th>Deadline</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($documents as $index => $doc)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $doc->document->name ?? '-' }}</td>
                                            <td>{{ $doc->document_number ?? '-' }}</td>
                                            <td class="text-center">{{ $doc->version ?? '-' }}</td>
                                            <td class="text-center">
                                                @if ($doc->file_path)
                                                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                @else
                                                    <span class="text-muted">No File</span>
                                                @endif
                                            </td>
                                            <td>{{ $doc->notes ?? '-' }}</td>
                                            <td class="text-center">{{ optional($doc->reminder_date)->format('d M Y') ?? '-' }}</td>
                                            <td class="text-center">{{ optional($doc->deadline)->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $doc->document->department->name ?? '-' }}</td>
                                            <td>{{ $doc->status->name ?? '-' }}</td>
                                            <td class="text-center">
                                                <a href="#" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('document-mappings.destroy', $doc->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus dokumen ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">Tidak ada dokumen review untuk plant ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- MODAL ADD DOCUMENT REVIEW --}}
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('document-mappings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addReviewModalLabel">Add Document Review</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Document Name</label>
                            <select name="document_id" class="form-select" required>
                                <option value="">-- Select Document --</option>
                                @foreach ($documentsMaster as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Part Number / Plant</label>
                            <select name="part_number_id" class="form-select" required>
                                <option value="">-- Select Part Number --</option>
                                @foreach ($partNumbers as $part)
                                    <option value="{{ $part->id }}">{{ $part->part_number }} — {{ $part->plant }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Document Number</label>
                            <input type="text" name="document_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Version</label>
                            <input type="text" name="version" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Reminder Date</label>
                            <input type="date" name="reminder_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File (PDF)</label>
                        <input type="file" name="file_path" class="form-control" accept=".pdf">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_id" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Document</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
