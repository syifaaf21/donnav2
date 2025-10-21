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
                <form action="{{ route('master.document-control.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="document_name" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="document_name" name="document_name"
                                placeholder="Enter document name" required>
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
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label for="obsolete_date" class="form-label">Obsolete Date</label>
                            <input type="date" class="form-control" id="obsolete_date" name="obsolete_date">
                        </div>
                        <div class="col-md-6">
                            <label for="reminder_date" class="form-label">Reminder Date</label>
                            <input type="date" class="form-control" id="reminder_date" name="reminder_date">
                        </div>
                    </div>
                    {{-- Notes --}}
                    {{-- <div class="col-12 mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <input type="hidden" name="notes" id="notes_input_add">
                        <div id="quill_editor" class="bg-white border-1 shadow-sm rounded"
                            style="min-height: 80px; max-height: 100px; overflow-y: auto; word-wrap: break-word; white-space: pre-wrap; width: 100%;">
                        </div>
                        <small class="text-muted">You can format your notes with bold, italic, underline,
                            colors, and more.</small>
                    </div> --}}
                    <div class="row g-2 mt-2" id="file-fields">
                        <div class="col-md-12 d-flex align-items-center mb-2 file-input-group">
                            <input type="file" class="form-control" name="files[]" required>
                            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-file">
                        <i class="bi bi-plus-square"></i>
                    </button>

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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById("file-fields");
        const addBtn = document.getElementById("add-file");

        addBtn.addEventListener("click", function() {
            // Buat group baru
            let group = document.createElement("div");
            group.classList.add("col-md-12", "d-flex", "align-items-center", "mb-2",
                "file-input-group");

            group.innerHTML = `
            <input type="file" class="form-control" name="files[]" required>
            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                <i class="bi bi-trash"></i>
            </button>
        `;

            container.appendChild(group);
        });

        // Event delegation untuk tombol remove
        container.addEventListener("click", function(e) {
            if (e.target.closest(".remove-file")) {
                e.target.closest(".file-input-group").remove();
            }
        });
    });
</script>
{{-- <style>
    #quill_editor {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    #quill_editor .ql-editor {
        word-wrap: break-word !important;
        white-space: pre-wrap !important;
        overflow-wrap: break-word !important;
        max-width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
    }

    #quill_editor .ql-editor span {
        white-space: normal !important;
        word-break: break-word !important;
    }
</style> --}}
