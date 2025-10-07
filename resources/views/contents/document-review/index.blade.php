@extends('layouts.app')
@section('title', 'Document Review')
@section('content')

    <div class="container-fluid">
        {{-- Tabs per Plant --}}
        <ul class="nav nav-tabs mb-3" id="plantTabs" role="tablist">
            @foreach ($groupedByPlant as $plant => $documentsByPart)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif" id="tab-{{ $loop->index }}"
                        data-bs-toggle="tab" data-bs-target="#plant-{{ $loop->index }}" type="button" role="tab">
                        <i class="bi bi-building"></i> {{ $plant }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="plantTabsContent">
            @foreach ($groupedByPlant as $plant => $partGroups)
                <div class="tab-pane fade @if ($loop->first) show active @endif row"
                    id="plant-{{ $loop->index }}" role="tabpanel" style="height: 80vh;">

                    {{-- Left Panel: Document Tree --}}
                    <div class="col-md-3 border-end" style="max-height: 80vh; overflow-y: auto;">
                        @foreach ($partGroups as $partNumber => $docs)
                            <div class="mb-3">
                                <button class="btn btn-sm btn-outline-secondary w-100 text-start" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ Str::slug($plant . '-' . $partNumber) }}">
                                    <i class="bi bi-box"></i> {{ $partNumber }}
                                </button>

                                <div class="collapse mt-2" id="collapse-{{ Str::slug($plant . '-' . $partNumber) }}">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($docs->whereNull('document.parent_id') as $parent)
                                            @include('contents.document-review.partials.tree-node', [
                                                'mapping' => $parent,
                                                'allDocuments' => $docs,
                                                'level' => 1,
                                            ])
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Right Panel: Preview & Info --}}
                    <div class="col-md-9 d-flex flex-column">
                        <div class="border rounded-3 shadow-sm bg-white flex-grow-1 d-flex flex-column">
                            {{-- Bar atas --}}
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                                <div class="fw-semibold">
                                    <i class="bi bi-eye me-2 text-primary"></i> File Preview:
                                    <span class="docNameDisplay">–</span>
                                </div>
                                <div class="mt-1 d-flex gap-1">
                                    {{-- Approve --}}
                                    <button type="button"
                                        class="btn btn-outline-success btn-sm btnTriggerApprove btnApprove"
                                        data-bs-toggle="modal" data-bs-target="#approveModal" data-mapping-id=""
                                        title="Approve Document">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>

                                    {{-- Reject --}}
                                    <form action="#" method="POST" class="d-inline reject-form">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm btnReject"
                                            data-mapping-id="" title="Reject Document">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>

                                    {{-- Revise --}}
                                    <button type="button" class="btn btn-outline-warning btn-sm btnTriggerRevise btnRevise"
                                        data-bs-toggle="modal" data-bs-target="#reviseModal" data-mapping-id=""
                                        title="Request Revision">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>

                                </div>
                                @include('contents.document-review.partials.modal-revise')
                                @include('contents.document-review.partials.modal-approve')
                            </div>

                            {{-- Detail Info --}}
                            <div class="detailInfo px-3 py-2 border-bottom" style="max-height: 250px; overflow-y: auto;">
                                <p><strong>Document Number:</strong> <span class="detailDocumentNumber">–</span></p>
                                <p><strong>Department:</strong> <span class="detailDepartment">–</span></p>
                                <p><strong>Status:</strong> <span class="detailStatus">–</span></p>
                                <p><strong>Last Update:</strong> <span class="detailUpdatedAt">–</span></p>
                                <p><strong>Updated By:</strong> <span class="detailUpdatedBy">–</span></p>
                                <p><strong>Product:</strong> <span class="detailProduct">–</span></p>
                                <p><strong>Model:</strong> <span class="detailModel">–</span></p>
                                <p><strong>Process:</strong> <span class="detailProcess">–</span></p>
                                <p><strong>Notes:</strong> <span class="detailNotes">–</span></p>
                            </div>

                            {{-- Preview File --}}
                            <div class="previewContainer flex-grow-1 d-flex align-items-center justify-content-center p-3">
                                <p class="text-muted">Select a file to preview</p>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

@endsection

@push('scripts')
    <x-sweetAlert-confirm />
    <script>
        document.querySelectorAll('.view-file-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const container = btn.closest('.tab-pane');

                // Update tampilan dokumen (judul, detail, dll)
                container.querySelector('.docNameDisplay').textContent = btn.dataset.documentName || '-';
                container.querySelector('.detailDocumentNumber').textContent = btn.dataset.documentNumber ||
                    '-';
                container.querySelector('.detailDepartment').textContent = btn.dataset.department || '-';
                container.querySelector('.detailStatus').textContent = btn.dataset.status || '-';
                container.querySelector('.detailUpdatedAt').textContent = btn.dataset.updatedAt || '-';
                container.querySelector('.detailUpdatedBy').textContent = btn.dataset.user || '-';
                container.querySelector('.detailProduct').textContent = btn.dataset.product || '-';
                container.querySelector('.detailModel').textContent = btn.dataset.model || '-';
                container.querySelector('.detailProcess').textContent = btn.dataset.process || '-';
                container.querySelector('.detailNotes').textContent = btn.dataset.notes || '-';

                const fileUrl = btn.dataset.file;
                const previewContainer = container.querySelector('.previewContainer');
                if (fileUrl) {
                    previewContainer.innerHTML =
                        `<iframe src="${fileUrl}" style="width:100%; height:100%; border:none;"></iframe>`;
                } else {
                    previewContainer.innerHTML = `<p class="text-muted">No file available for preview</p>`;
                }

                // Simpan mapping ID ke semua tombol aksi di panel ini
                const mappingId = btn.dataset.mappingId;
                container.querySelectorAll('.btnApprove, .btnReject, .btnRevise').forEach(actionBtn => {
                    actionBtn.dataset.mappingId = mappingId;
                });

                // Simpan juga ke form action reject
                const rejectForm = container.querySelector('.reject-form');
                if (rejectForm) {
                    rejectForm.action = `/document-review/reject/${mappingId}`;
                }

                // Set form action approve
                const approveForm = document.querySelector('#approveModal form');
                if (approveForm) {
                    approveForm.action = `/document-review/${mappingId}/approve`;
                }

                // Set form revise
                const reviseForm = document.querySelector('#reviseModal form');
                if (reviseForm) {
                    reviseForm.action = `/document-review/revise/${mappingId}`;
                }

                // =============================
                // 🔁 Generate file list for revise modal
                // =============================
                const reviseFilesContainer = document.querySelector(
                    '#reviseModal .existing-files-container');
                const filesRaw = btn.dataset.files;

                if (reviseFilesContainer) {
                    try {
                        const files = filesRaw ? JSON.parse(filesRaw) : [];

                        if (files.length > 0) {
                            let html = '';
                            files.forEach(file => {
                                const fileUrl = file.file_path ? `/storage/${file.file_path}` : '#';
                                html += `
                            <div class="mb-4 border rounded p-3 bg-light">
                                <label class="form-label fw-medium">Current File:</label>
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="${fileUrl}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        ${file.name}
                                    </a>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">Upload Revised File</label>
                                    <input type="file" name="files[${file.id}]" class="form-control border-1 shadow-sm">
                                </div>
                            </div>
                        `;
                            });
                            reviseFilesContainer.innerHTML = html;
                        } else {
                            reviseFilesContainer.innerHTML =
                                `<p class="text-muted">No files available for revision.</p>`;
                        }
                    } catch (e) {
                        console.error('Invalid JSON in data-files:', e);
                        reviseFilesContainer.innerHTML =
                            `<p class="text-danger">Failed to load file list.</p>`;
                    }
                }

                // Update nama dokumen di header modal revise
                const docNameDisplay = document.querySelector('#reviseModal .docNameDisplay');
                if (docNameDisplay) {
                    docNameDisplay.textContent = btn.dataset.documentName || '';
                }
            });
        });
    </script>
@endpush
