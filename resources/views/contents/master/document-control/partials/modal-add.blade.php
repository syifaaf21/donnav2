<!-- Modal di modal-add.blade.php -->
<div class="modal fade" id="addDocumentControlModal" tabindex="-1" aria-labelledby="addDocumentControlModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

            {{-- Modal Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">

                <h5 class="modal-title fw-semibold text-dark text-center" id="addDocumentControlModalLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Add Document Control
                </h5>
                {{-- Close button (optional if needed) --}}
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            <div class="modal-body p-5 bg-gray-50" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <form action="{{ route('master.document-control.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="document_name" class="form-label fw-semibold">Document Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" placeholder="Input document name"
                                class="form-control border-0 shadow-sm rounded-3 @error('document_name') is-invalid @enderror"
                                id="document_name" name="document_name" value="{{ old('document_name') }}" required>
                            @error('document_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="department" class="form-label fw-semibold">Department <span
                                    class="text-danger">*</span></label>
                            <select
                                class="form-select tomselect border-0 shadow-sm rounded-3 @error('department') is-invalid @enderror"
                                id="department" name="department[]" multiple required>
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ collect(old('department'))->contains($dept->id) ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @php $today = now()->format('Y-m-d'); @endphp

                    <div class="row g-4 mt-2">
                        <div class="col-md-4">
                            <label for="obsolete_date" class="form-label fw-semibold">Obsolete Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control border-0 shadow-sm rounded-3 @error('obsolete_date') is-invalid @enderror"
                                id="obsolete_date" name="obsolete_date" value="{{ old('obsolete_date') }}"
                                min="{{ $today }}" required>
                            @error('obsolete_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="reminder_date" class="form-label fw-semibold">Reminder Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control border-0 shadow-sm rounded-3 @error('reminder_date') is-invalid @enderror"
                                id="reminder_date" name="reminder_date" value="{{ old('reminder_date') }}"
                                min="{{ $today }}" required>
                            @error('reminder_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="period_years" class="form-label fw-semibold">Period (Years) <span
                                    class="text-danger">*</span></label>
                            <input type="number" min="1"
                                class="form-control border-0 shadow-sm rounded-3 @error('period_years') is-invalid @enderror"
                                id="period_years" name="period_years" value="{{ old('period_years', 1) }}" required>
                            @error('period_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold">Notes <span class="text-danger">*</span></label>
                        <input type="hidden" name="notes" id="notes_input_add" value="{{ old('notes') }}" required>
                        <div id="quill_editor_add" class="bg-white rounded-3 shadow-sm p-2"
                            style="min-height: 120px; max-height: 160px; overflow-y: auto; border: 1px solid #e2e8f0;">
                        </div>
                        <small class="text-muted">You can format your notes with bold, italic, underline, colors,
                            and more.</small>
                    </div>

                    {{-- File
                    <div class="row g-2 mt-2" id="file-fields">
                        <label for="reminder_date" class="form-label">Upload File</label>
                        <div class="col-md-12 d-flex align-items-center mb-2 file-input-group">
                            <label class="form-label visually-hidden">Upload File <span
                                    class="text-danger">*</span></label>
                            <input type="file"
                                class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror"
                                name="files[]">
                            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                                <i class="bi bi-trash"></i>
                            </button>
                            @error('files.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-success btn-sm mt-2" id="add-file">
                        <i class="bi bi-plus"></i> Add File
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Allowed formats: PDF, DOCX, XLSX, JPG, PNG, JPEG</p> --}}


                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 p-4 justify-content-between bg-white mt-4 rounded-bottom-4">
                        <button type="button"
                            class="btn btn-link text-secondary fw-semibold px-4 py-2"
                            data-bs-dismiss="modal"
                            style="text-decoration: none; transition: background-color 0.3s ease;">
                            Cancel
                        </button>
                        <button type="submit"
                            class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
