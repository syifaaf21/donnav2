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
                                <select id="document_select" name="document_id" class="form-select border-1 shadow-sm"
                                    required>
                                    <option value="">-- Select Document --</option>
                                    @foreach ($documentsMaster as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Document Name is required.</div>
                            </div>

                            {{-- Document Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Document Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="document_number" class="form-control border-1 shadow-sm"
                                    placeholder="Input Document Number" required>
                                <div class="invalid-feedback">Document Number is required.</div>
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
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
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
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById("file-fields");
        const addBtn = document.getElementById("add-file");

        // Tambah file input
        addBtn.addEventListener("click", function() {
            const group = document.createElement("div");
            group.classList.add("col-md-12", "d-flex", "align-items-center", "mb-2",
                "file-input-group");

            group.innerHTML = `
            <input type="file" class="form-control" name="files[]" required accept=".pdf,.doc,.docx,.xls,.xlsx">
            <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-file">
                <i class="bi bi-trash"></i>
            </button>
        `;
            container.appendChild(group);
        });

        // Hapus file input
        container.addEventListener("click", function(e) {
            if (e.target.closest(".remove-file")) {
                e.target.closest(".file-input-group").remove();
            }
        });

        // Document Name - pakai TomSelect + API
        new TomSelect('#document_select', {
            create: false,
            preload: true,
            load: function(query, callback) {
                fetch('/api/documents?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(callback)
                    .catch(() => callback());
            }
        });

        // Plant - statis
        const tsPlant = new TomSelect('#plant_select', {
            create: false
        });

        // Part Number & Department - kosong awal
        const tsPartNumber = new TomSelect('#partNumber_select', {
            create: false,
            options: []
        });

        const tsDepartment = new TomSelect('#department_select', {
            create: false,
            options: []
        });

        tsPartNumber.disable();
        tsDepartment.disable();

        // Load Department sekali dari API
        fetch('/api/departments')
            .then(res => res.json())
            .then(data => {
                const mapped = data.map(item => ({
                    value: item.id,
                    text: item.text
                }));
                tsDepartment.clearOptions();
                tsDepartment.addOptions(mapped);
            })
            .catch(() => {
                tsDepartment.clearOptions();
            });

        // Saat Plant berubah
        tsPlant.on('change', function(value) {
            if (value) {
                tsPartNumber.enable();
                tsPartNumber.clearOptions();

                fetch(`/api/part-numbers?plant=${encodeURIComponent(value)}`)
                    .then(res => res.json())
                    .then(data => {
                        const mapped = data.map(item => ({
                            value: item.id,
                            text: item.text
                        }));
                        tsPartNumber.addOptions(mapped);
                    })
                    .catch(() => {
                        tsPartNumber.clearOptions();
                    });

                // Department bisa langsung enable karena tidak tergantung plant
                tsDepartment.enable();
            } else {
                tsPartNumber.disable();
                tsPartNumber.clearOptions();

                tsDepartment.disable();
                tsDepartment.clearOptions();
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Quill
        const quill = new Quill('#quill_editor', {
            theme: 'snow',
            placeholder: 'Write your notes here...',
            modules: {
                toolbar: [
                    [{
                        'font': []
                    }, {
                        'size': []
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    ['clean']
                ]
            }
        });

        // Ambil hidden input
        const hiddenInput = document.getElementById('notes_input_add');

        // Sync ke hidden input saat submit form
        const form = document.querySelector('#addDocumentModal form');
        form.addEventListener('submit', function() {
            hiddenInput.value = quill.root.innerHTML;
        });
    });
</script>
<style>
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
</style>
