<!-- Modal di modal-add.blade.php -->
<div class="modal fade" id="addDocumentControlModal" tabindex="-1" aria-labelledby="addDocumentControlModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title" id="addDocumentControlModalLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Add Document Control
                </h5>
            </div>
            <div class="modal-body">
                <!-- Paste form Anda di sini -->
                <form action="{{ route('master.document-control.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="document_name" class="form-label">Document Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('document_name') is-invalid @enderror"
                                id="document_name" name="document_name" value="{{ old('document_name') }}" required>
                            @error('document_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="department" class="form-label">Department <span
                                    class="text-danger">*</span></label>
                            <select class="form-select tomselect @error('department') is-invalid @enderror"
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
                    @php
                        $today = now()->format('Y-m-d');
                    @endphp
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <label for="obsolete_date" class="form-label">Obsolete Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('obsolete_date') is-invalid @enderror"
                                id="obsolete_date" name="obsolete_date" value="{{ old('obsolete_date') }}"
                                min="{{ $today }}" required>
                            @error('obsolete_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="reminder_date" class="form-label">Reminder Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('reminder_date') is-invalid @enderror"
                                id="reminder_date" name="reminder_date" value="{{ old('reminder_date') }}"
                                min="{{ $today }}" required>
                            @error('reminder_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="period_years" class="form-label">Period (Years) <span
                                    class="text-danger">*</span></label>
                            <input type="number" min="1"
                                class="form-control @error('period_years') is-invalid @enderror" id="period_years"
                                name="period_years" value="{{ old('period_years', 1) }}" required>
                            @error('period_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="col-12 mt-3">
                        <label class="form-label">Notes <span class="text-danger">*</span></label>
                        <input type="hidden" name="notes" id="notes_input_add" value="{{ old('notes') }}" required>
                        <div id="quill_editor_add" class="bg-white border-1 shadow-sm rounded"
                            style="min-height: 100px; max-height: 80px; overflow-y: auto; word-wrap: break-word; white-space: pre-wrap; width: 100%;">
                        </div>
                        <small class="text-muted">You can format your notes with bold, italic, underline, colors, and
                            more.</small>
                    </div>
                    {{-- File --}}
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

                    {{-- Modal Footer --}}
                    <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4 mt-3">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
