@if (auth()->user()->role->name == 'Admin')
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('master.document-review.store') }}" method="POST" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    {{-- Modal Header --}}
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif;"
                            id="addDocumentModalLabel">
                            <i class="bi bi-plus-circle me-2"></i> Add Document Review
                        </h5>
                    </div>


                    {{-- Modal Body --}}
                    <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div class="row g-3">
                            {{-- Document Name --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Name <span
                                        class="text-danger">*</span></label>
                                <select id="document_select" name="document_id" class="form-select border-1 shadow-sm"
                                    required>
                                    <option value="">-- Select Document --</option>
                                    @foreach ($documentsMaster as $doc)
                                        <option value="{{ $doc->id }}" data-code="{{ strtoupper($doc->code) }}">
                                            {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Document Name is required.</div>
                            </div>

                            {{-- Document Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Number</label>
                                <input type="text" name="document_number" id="document_number"
                                    class="form-control border-1 shadow-sm" readonly>
                            </div>

                            {{-- Parent Document --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Parent Document (Optional)</label>
                                <select id="parent_document_select" name="parent_document_id"
                                    class="form-select border-1 shadow-sm">
                                    <option value="">-- Select Parent Document --</option>
                                    @foreach ($existingDocuments as $docMap)
                                        {{-- Ambil dari Controller --}}
                                        <option value="{{ $docMap->id }}"
                                            data-product="{{ $docMap->partNumber->product->code ?? '' }}"
                                            data-process="{{ $docMap->partNumber->process->code ?? '' }}"
                                            data-model="{{ $docMap->partNumber->productModel->name ?? '' }}">
                                            {{ $docMap->document_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Only required for child documents.</small>
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-4">
                                <label for="addPlantSelect" class="form-label fw-medium">Plant <span
                                        class="text-danger">*</span></label>
                                <select name="plant" id="plant_select" class="form-select border-1 shadow-sm"
                                    required>
                                    <option value="">-- Select Plant --</option>
                                    <option value="body">Body</option>
                                    <option value="unit">Unit</option>
                                    <option value="electric">Electric</option>
                                </select>
                                <div class="invalid-feedback">Plant is required.</div>
                            </div>

                            {{-- Part Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Part Number <span
                                        class="text-danger">*</span></label>
                                <select id="partNumber_select" name="part_number_id"
                                    class="form-select border-1 shadow-sm" required disabled>
                                    <option value="">-- Select Part Number --</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part->id }}" data-plant="{{ $part->plant }}">
                                            {{ $part->part_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Part Number is required.</div>
                            </div>

                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Department <span
                                        class="text-danger">*</span></label>
                                <select id="department_select" name="department_id"
                                    class="form-select border-1 shadow-sm" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" data-code="{{ strtoupper($dept->code) }}">
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Department is required.</div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 mb-3">
                                <label class="form-label fw-medium">Notes</label>
                                <input type="hidden" name="notes" id="notes_input_add">
                                <div id="quill_editor" class="bg-white border-1 shadow-sm rounded"
                                    style="min-height: 80px; max-height: 100px; overflow-y: auto; word-wrap: break-word; white-space: pre-wrap; width: 100%;">
                                </div>
                                <small class="text-muted">You can format your notes with bold, italic, underline,
                                    colors, and more.</small>
                            </div>

                            {{-- File Upload --}}
                            <div class="col-12">
                                <label class="form-label fw-medium">Upload File <span
                                        class="text-danger">*</span></label>
                                <div id="file-fields">
                                    <div class="d-flex align-items-center mb-2 file-input-group">
                                        <input type="file" name="files[]" class="form-control border-1 shadow-sm"
                                            required accept=".pdf,.doc,.docx,.xls,.xlsx">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-file">
                                    <i class="bi bi-plus-square"></i>
                                </button>
                                <small class="text-muted d-block mt-1">Allowed Format: PDF, DOCX, EXCEL</small>
                                <div class="invalid-feedback">Document File is required.</div>
                            </div>
                            {{-- Modal Footer --}}
                            <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-outline-secondary px-4"
                                    data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i> Close
                                </button>
                                <button type="submit" class="btn btn-outline-primary px-4">
                                    <i class="bi bi-save2 me-1"></i> Save Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

