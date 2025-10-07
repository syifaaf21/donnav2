@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        {{-- Left side: Document list --}}
        <div class="col-md-3 border-end" style="height: 85vh">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-12">
                        <select name="department_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            {{-- Accordion per Department --}}
            <div class="accordion" id="documentAccordion">
                @forelse ($groupedDocuments as $department => $mappings)
                    <div class="accordion-item mb-2 border-0 shadow-sm">
                        <h2 class="accordion-header" id="heading{{ $loop->index }}">
                            <button class="accordion-button collapsed bg-light fw-semibold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}"
                                aria-expanded="false" aria-controls="collapse{{ $loop->index }}">
                                {{ $department }} <span class="ms-2 text-muted">({{ count($mappings) }})</span>
                            </button>
                        </h2>
                        <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse"
                            aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#documentAccordion">
                            <div class="accordion-body">
                                @foreach ($mappings as $mapping)
                                    <div class="card mb-2 border-0 shadow-sm">
                                        <div class="card-body py-2">
                                            <h6 class="fw-bold mb-1">{{ $mapping->document->name }}</h6>
                                            <p class="text-muted small mb-1">No: {{ $mapping->document_number }}</p>
                                            <p class="text-muted small mb-1">Updated by: {{ $mapping->user->name }}</p>
                                            <p class="text-muted small mb-1">Last Update: {{ $mapping->updated_at }}</p>
                                            <p class="text-muted small mb-1">Note: {{ $mapping->notes }}</p>
                                            <p>
                                                <span class="text-muted small mb-1">Valid until:
                                                    {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                                                </span>
                                            </p>
                                            <p class="small mb-1">
                                                <span
                                                    class="badge
                                                    @if ($mapping->status->name == 'Active') bg-success
                                                    @elseif($mapping->status->name == 'Need Review') bg-warning text-dark
                                                    @elseif($mapping->status->name == 'Rejected') bg-danger
                                                    @else bg-secondary @endif">
                                                    {{ $mapping->status->name ?? '-' }}
                                                </span>
                                            </p>

                                            {{-- File list --}}
                                            @foreach ($mapping->files as $file)
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary me-1 view-file-btn"
                                                    data-file="{{ asset('storage/' . $file->file_path) }}"
                                                    data-docid="{{ $mapping->id }}"
                                                    data-doc-title="{{ $mapping->document->title }}"
                                                    data-status="{{ $mapping->status->name }}"
                                                    data-files='@json($mapping->files->map(function($f){ return ["id"=>$f->id,"name"=>basename($f->file_path),"url"=>asset("storage/".$f->file_path)]; }))'>
                                                    <i class="bi bi-file-earmark-text me-1"></i>
                                                    View {{ $loop->iteration }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No documents found.</p>
                @endforelse
            </div>
        </div>

        {{-- Right side: File Preview --}}
        <div class="col-md-9">
            <div class="border rounded-3 shadow-sm bg-white h-100">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                    <div class="fw-semibold">
                        <i class="bi bi-eye me-2 text-primary"></i> <span id="previewTitle">File Preview</span>
                    </div>

                    {{-- Action Buttons (muncul hanya jika dokumen sudah dipilih) --}}
                    <div id="actionButtons" style="display: none;">
                        {{-- Revisi --}}
                        <button class="btn btn-outline-warning btn-sm" id="reviseBtn" data-bs-toggle="modal"
                            data-bs-target="#reviseModalDynamic" data-bs-title="Revise Document">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>

                        {{-- Approve --}}
                        <form id="approveForm" method="POST" class="d-inline approve-form" action="#">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm" data-bs-title="Approve Document">
                                <i class="bi bi-check2-circle"></i>
                            </button>
                        </form>

                        {{-- Reject --}}
                        <form id="rejectForm" method="POST" class="d-inline reject-form" action="#">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-title="Reject Document">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-0 h-100 d-flex align-items-center justify-content-center" id="previewContainer"
                    style="height: calc(85vh - 60px);">
                    <p class="text-muted">Select a file to preview</p>
                </div>
            </div>
        </div>

        {{-- Hidden input to track selected doc --}}
        <input type="hidden" id="currentDocId">
    </div>
</div>
@include('contents.document-control.partials.modal-revise')

@push('scripts')
    <x-sweetalert-confirm />

    <script>
        (function () {
            const baseUrl = "{{ url('document-control') }}";

            // tombol view file
            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const fileUrl = btn.dataset.file;
                    const docId = btn.dataset.docid;
                    const docTitle = btn.dataset.docTitle || 'File Preview';
                    const previewContainer = document.getElementById('previewContainer');
                    const actionButtons = document.getElementById('actionButtons');

                    // Simpan doc ID untuk aksi selanjutnya
                    document.getElementById('currentDocId').value = docId;

                    // Update form action
                    document.getElementById('approveForm').action = `${baseUrl}/${docId}/approve`;
                    document.getElementById('rejectForm').action = `${baseUrl}/${docId}/reject`;

                    // Tampilkan tombol action
                    actionButtons.style.display = 'block';

                    // Update preview title
                    document.getElementById('previewTitle').textContent = docTitle;

                    // Tampilkan file di preview kanan
                    previewContainer.innerHTML = `
                        <iframe src="${fileUrl}" width="100%" height="100%" style="border:none;"></iframe>
                    `;

                    // simpan current mapping files untuk modal (dapat diambil juga dari data-files ketika dibuka)
                    try {
                        window.currentMappingFiles = JSON.parse(btn.dataset.files || '[]');
                    } catch (err) {
                        window.currentMappingFiles = [];
                    }
                });
            });

            // Populate revise modal saat dibuka
            const reviseModalEl = document.getElementById('reviseModalDynamic');
            reviseModalEl.addEventListener('show.bs.modal', (event) => {
                const docId = document.getElementById('currentDocId').value;
                if (!docId) {
                    // prevent modal show if no doc selected
                    event.preventDefault();
                    alert('Please choose file you want to revise.');
                    return;
                }

                // Ambil files dari window.currentMappingFiles (diset saat klik View)
                let files = window.currentMappingFiles || [];

                // Fallback: cari tombol sample untuk docId lalu ambil data-files
                if ((!files || files.length === 0) && document.querySelector(`.view-file-btn[data-docid="${docId}"]`)) {
                    const sampleBtn = document.querySelector(`.view-file-btn[data-docid="${docId}"]`);
                    try { files = JSON.parse(sampleBtn.dataset.files || '[]'); } catch (e) { files = []; }
                }

                const container = document.getElementById('reviseFilesContainer');
                let html = '';

                if (files.length === 0) {
                    html = '<p class="text-muted">No file available</p>';
                } else {
                    files.forEach(f => {
                        html += `
                        <div class="mb-4 border rounded p-3 bg-light">
                            <label class="form-label fw-medium">Current File:</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="${f.url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i> ${f.name}
                                </a>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Upload Revised File</label>
                                <input type="file" name="files[${f.id}]" class="form-control border-1 shadow-sm">
                            </div>
                        </div>
                        `;
                    });
                }

                container.innerHTML = html;

                // Set action form
                document.getElementById('reviseFormDynamic').action = `${baseUrl}/${docId}/revise`;
            });

        })();
    </script>
@endpush

@endsection
