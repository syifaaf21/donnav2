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
                            <label class="form-label fw-medium">Document Name <span class="text-danger">*</span></label>
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
                                <option value="body"
                                    {{ strtolower($mapping->partNumber->plant ?? '') == 'body' ? 'selected' : '' }}>Body
                                </option>
                                <option value="unit"
                                    {{ strtolower($mapping->partNumber->plant ?? '') == 'unit' ? 'selected' : '' }}>
                                    Unit</option>
                                <option value="electric"
                                    {{ strtolower($mapping->partNumber->plant ?? '') == 'electric' ? 'selected' : '' }}>
                                    Electric</option>
                            </select>
                            @error('plant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback">Plant is required.</div>
                        </div>

                        {{-- Department --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
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
                            <select id="editParentSelect{{ $mapping->id }}" name="parent_id"
                                class="form-select border-1 shadow-sm" data-selected="{{ $mapping->parent_id ?? '' }}">
                                {{-- kosongkan option di sini --}}
                            </select>
                            <small class="text-muted fst-italic">Leave blank if it doesn't have parent</small>
                        </div>

                        {{-- Part Number --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Part Number <span class="text-danger">*</span></label>
                            <select id="editPartNumberSelect{{ $mapping->id }}" name="part_number_id"
                                class="form-select border-1 shadow-sm" required>
                                @foreach ($partNumbers as $part)
                                    <option value="{{ $part->id }}" data-plant="{{ $part->plant }}"
                                        {{ $mapping->part_number_id == $part->id ? 'selected' : '' }}>
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
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primaryLight text-white rounded-lg hover:bg-pr transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
