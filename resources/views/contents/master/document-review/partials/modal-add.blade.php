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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>


                    {{-- Modal Body --}}
                    <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div class="row g-3">
                            {{-- Document Name --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Name <span
                                        class="text-danger">*</span></label>
                                <select name="document_id" class="form-select border-1 shadow-sm" required>
                                    <option value="">-- Select Document --</option>
                                    @foreach ($documentsMaster as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Document Name is required.
                                </div>
                            </div>

                            {{-- Document Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="document_number" class="form-control border-1 shadow-sm"
                                    placeholder="Input Document Number" required>
                                <div class="invalid-feedback">
                                    Document Number is required.
                                </div>
                            </div>

                            {{-- Part Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Part Number <span
                                        class="text-danger">*</span></label>
                                <select id="addPartNumberSelect" name="part_number_id"
                                    class="form-select border-1 shadow-sm" required>
                                    <option value="">-- Select Part Number --</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part->id }}" data-plant="{{ $part->plant }}">
                                            {{ $part->part_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Part Number is required.
                                </div>
                            </div>

                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Department <span
                                        class="text-danger">*</span></label>
                                <select name="department_id" class="form-select border-1 shadow-sm" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Department is required.
                                </div>
                            </div>
                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label fw-medium">Notes</label>
                                <input type="text" name="notes" class="form-control border-1 shadow-sm"
                                    placeholder="Add any relevant notes here">
                            </div>



                        </div>
                        {{-- File --}}
                        <div class="row g-2 mt-2" id="file-fields">
                            <div class="col-12 d-flex align-items-center mb-2 file-input-group">
                                <input type="file" name="files[]" class="form-control border-1 shadow-sm" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                                <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file d-none">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-file">
                            <i class="bi bi-plus-square"></i>
                        </button>

                        <small class="text-muted">Allowed Format: PDF, DOCX, EXCEL</small>
                        <div class="invalid-feedback">
<<<<<<< HEAD
                            Document Fi is required.
=======
                            Document File is required.
>>>>>>> f3899fec09a8a4677370e9e4d17fc02c74b3f381
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                        <button type="submit" class="btn btn-outline-primary px-4">
                            <i class="bi bi-save2 me-1"></i> Save Document
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endif
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
