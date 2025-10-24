{{-- Modal Edit Document Review --}}
<div class="modal fade" id="editModal{{ $mapping->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $mapping->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('master.document-review.update', $mapping->id) }}" method="POST" class="needs-validation"
            novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-content border-0 rounded-4 shadow-lg">
                {{-- Header --}}
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i> Edit Document Review
                    </h5>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Document Name --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Document Name</label>
                            <select id="editDocumentSelect{{ $mapping->id }}" name="document_id" class="form-select"
                                required>
                                @foreach ($documentsMaster as $doc)
                                    <option value="{{ $doc->id }}"
                                        @if ($mapping->document_id == $doc->id) selected @endif>
                                        {{ $doc->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        {{-- Document Number (readonly autogenerate) --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Document Number</label>
                            <input type="text" name="document_number" id="editDocumentNumber{{ $mapping->id }}"
                                class="form-control border-1 shadow-sm" value="{{ $mapping->document_number }}"
                                readonly>
                        </div>

                        {{-- Plant --}}
                        <div class="col-md-4">
                            <label for="addPlantSelect" class="form-label fw-medium">Plant <span
                                    class="text-danger">*</span></label>
                            <select name="plant" id="editPlantSelect{{ $mapping->id }}"
                                class="form-select border-1 shadow-sm @error('plant') is-invalid @enderror" required>
                                <option value="">-- Select Plant --</option>
                                <option value="body" {{ $mapping->plant == 'body' ? 'selected' : '' }}>Body</option>
                                <option value="unit" {{ $mapping->plant == 'unit' ? 'selected' : '' }}>Unit</option>
                                <option value="electric" {{ $mapping->plant == 'electric' ? 'selected' : '' }}>Electric
                                </option>
                            </select>
                            @error('plant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback">Plant is required.</div>
                        </div>

                        {{-- Department --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Department</label>
                            <select id="editDepartmentSelect{{ $mapping->id }}" name="department_id"
                                class="form-select border-1 shadow-sm" required>
                                <option value="{{ $mapping->department_id }}" selected>
                                    {{ $mapping->department->name ?? '-' }}
                                </option>
                            </select>
                        </div>

                        {{-- Parent Document --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Parent Document</label>
                            <select id="editParentDocumentSelect{{ $mapping->id }}" name="parent_document_id"
                                class="form-select border-1 shadow-sm">
                                @if ($mapping->parent_document)
                                    <option value="{{ $mapping->parent_document_id }}" selected>
                                        {{ $mapping->parent_document->document_number }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        {{-- Part Number --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Part Number</label>
                            <select id="editPartNumberSelect{{ $mapping->id }}" name="part_number_id"
                                class="form-select border-1 shadow-sm" required>
                                 <option value="">-- Select Part Number --</option>
                                @foreach ($partNumbers as $part)
                                    <option value="{{ $part->id }}" data-plant="{{ $part->plant }}"
                                        {{ old('part_number_id') == $part->id ? 'selected' : '' }}>
                                        {{ $part->part_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Notes (Quill editable) --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-medium">Notes</label>
                            <input type="hidden" name="notes" id="notes_input_edit{{ $mapping->id }}"
                                value="{{ old('notes', $mapping->notes) }}">
                            <div id="quill_editor_edit{{ $mapping->id }}" class="bg-white border-1 shadow-sm rounded"
                                style="min-height: 100px; max-height: 130px; overflow-y: auto;">
                            </div>
                            <small class="text-muted">You can format your notes with bold, italic, colors, and
                                more.</small>
                        </div>

                        {{-- File Upload --}}
                        <div class="col-12">
                            <label class="form-label fw-medium">Upload File</label>

                            @if ($mapping->files->count())
                                <p>Existing file:
                                    @foreach ($mapping->files as $file)
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                            style="color: blue;">
                                            {{ $file->original_name }}
                                        </a>
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </p>
                            @endif

                            <div id="editFileFields{{ $mapping->id }}">
                                <div class="col-md-12 d-flex align-items-center mb-2 file-input-group">
                                    <input type="file" class="form-control border-1 shadow-sm" name="files[]"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Leave empty if you don’t want to change the
                                file.</small>

                            <button type="button" class="btn btn-outline-success btn-sm mt-2"
                                id="editAddFile{{ $mapping->id }}">
                                <i class="bi bi-plus"></i> Add File
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-outline-primary px-4">
                        <i class="bi bi-save2 me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
