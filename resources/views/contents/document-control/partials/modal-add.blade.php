<!-- Modal di modal-add.blade.php -->
<div class="modal fade" id="addDocumentControlModal" tabindex="-1" aria-labelledby="addDocumentControlModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title" id="addDocumentControlModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add Document Control
                </h5>
            </div>
            <div class="modal-body">
                <!-- Paste form Anda di sini -->
                <form action="{{ route('document-control.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="document_name" class="form-label">Document Name</label>
                            <select class="form-select" id="document_id" name="document_id" required>
                                <option value="">-- Select Document --</option>
                                @foreach ($documents as $doc)
                                    @if ($doc->type === 'control')
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="document_number" class="form-label">Document Number</label>
                            <input type="text" class="form-control" id="document_number" name="document_number"
                                required>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <label for="file" class="form-label">File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        <div class="col-md-4">
                            <label for="obsolete_date" class="form-label">Obsolete Date</label>
                            <input type="date" class="form-control" id="obsolete_date" name="obsolete_date">
                        </div>
                        <div class="col-md-4">
                            <label for="reminder_date" class="form-label">Reminder Date</label>
                            <input type="date" class="form-control" id="reminder_date" name="reminder_date">
                        </div>
                    </div>
                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-save2 me-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
