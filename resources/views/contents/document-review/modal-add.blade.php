{{-- ✅ Modal Add Document Review (Modern + Font Lebih Elegan) --}}
@if (auth()->user()->role->name == 'Admin')
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('document-review.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            <div class="modal-content border-0 rounded-4 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif; font-size: 1.1rem;">
                        <i class="bi bi-plus-circle me-2"></i> Add Document Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-3">
                        {{-- Document Name --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Document Name <span class="text-danger">*</span></label>
                            <select name="document_id" id="documentSelect" class="form-select border-1 shadow-sm" required>
                                <option value="">-- Pilih Document --</option>
                                @foreach ($documentsMaster as $doc)
                                    <option value="{{ $doc->id }}" data-department="{{ $doc->department->name ?? '' }}">
                                        {{ $doc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Document Number --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Document Number <span class="text-danger">*</span></label>
                            <input type="text" name="document_number" class="form-control border-1 shadow-sm" placeholder="Masukkan nomor dokumen" required>
                        </div>

                        {{-- Part Number --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Part Number <span class="text-danger">*</span></label>
                            <select name="part_number_id" id="partNumberSelect" class="form-select border-1 shadow-sm" required>
                                <option value="">-- Pilih Part Number --</option>
                                @foreach ($partNumbers as $part)
                                    <option value="{{ $part->id }}" data-plant="{{ $part->plant }}">
                                        {{ $part->part_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Department --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Department</label>
                            <input type="text" id="departmentField" class="form-control bg-light border-1 shadow-sm" placeholder="Auto-filled" readonly>
                        </div>

                        {{-- Reminder Date --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Reminder Date <span class="text-danger">*</span></label>
                            <input type="date" name="reminder_date" class="form-control border-1 shadow-sm" required>
                        </div>

                        {{-- Deadline --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Deadline <span class="text-danger">*</span></label>
                            <input type="date" name="deadline" class="form-control border-1 shadow-sm" required>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label fw-medium">Notes</label>
                            <input type="text" name="notes" class="form-control border-1 shadow-sm" placeholder="Tambahkan catatan jika perlu">
                        </div>

                        {{-- File --}}
                        <div class="col-12">
                            <label class="form-label fw-medium">Upload File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control border-1 shadow-sm" required>
                            <small class="text-muted">Format yang diizinkan: PDF, DOCX, XLSX</small>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save2 me-1"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
