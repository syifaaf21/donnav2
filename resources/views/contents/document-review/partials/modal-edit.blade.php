<!-- Modal Revise -->
<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-4 shadow-lg w-full max-w-lg relative">

        <!-- Header -->
        <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
            style="background-color: #f5f5f7;">
            <h5 class="modal-title fw-semibold text-dark" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;"><i
                    class="bi bi-cloud-upload text-primary"></i>
                Upload Document
            </h5>
            <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                onclick="closeReviseModal()" aria-label="Close"
                style="width: 36px; height: 36px; border: 1px solid #ddd;">
                <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
            </button>
        </div>

        <!-- Form -->
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data"
            class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto"
            style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            @csrf

            <!-- Existing Files -->
            <div id="reviseFilesContainer" class="mb-4"></div>

            <!-- Add New Files -->
            <div class="mb-4">
                <div id="new-files-container"></div>

                <button type="button" id="add-file"
                    class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition">
                    + Add File
                </button>

                <p class="text-xs text-gray-500 mt-1">
                    Allowed formats: PDF, DOCX, XLSX
                </p>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-3">Notes Revision <span
                        class="text-red-500">*</span></label>

                <!-- Quill container -->
                <div id="quillEditor" class="bg-white border border-gray-300 rounded p-2" style="height: 150px;">
                </div>

                <!-- Hidden input untuk submit -->
                <input type="hidden" name="notes" id="notesInput" required>
            </div>


            <!-- Footer -->
            <div class="flex justify-between items-center border-top pt-3 mt-3">
                <button type="button"
                    class="px-4 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-100 fw-semibold"
                    onclick="closeReviseModal()">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>

                <button type="submit"
                    class="px-4 py-1.5 bg-sky-600 text-white rounded hover:bg-sky-700 fw-semibold transition">
                    <i class="bi bi-check2-circle me-1"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ====== INIT QUILL ======
        let quillRevise = new Quill("#quillEditor", {
            theme: "snow",
            placeholder: "Write your revision notes...",
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link']
                ]
            }
        });

        // ====== ELEMENT ======
        const reviseForm = document.getElementById("reviseFormDynamic");
        const newFilesContainer = document.getElementById("new-files-container");
        const reviseNotesInput = document.getElementById("notesInput");


        // ====== TAMPILKAN ERROR DALAM MODAL ======
        function showReviseError(message) {
            let oldAlert = document.getElementById("revise-alert");
            if (oldAlert) oldAlert.remove();

            const alertDiv = document.createElement("div");
            alertDiv.id = "revise-alert";
            alertDiv.className = "alert alert-danger mt-2";
            alertDiv.innerText = message;

            reviseForm.prepend(alertDiv);
        }


        // ====== VALIDASI SAAT SUBMIT ======
        reviseForm.addEventListener("submit", function(e) {

            let isValid = true;

            // 1) VALIDASI: FILE WAJIB ADA MINIMAL SATU
            const allFileInputs = newFilesContainer.querySelectorAll('input[type="file"]');
            let hasFile = false;

            allFileInputs.forEach(input => {
                if (input.files.length > 0) {
                    hasFile = true;
                }
            });

            if (!hasFile) {
                e.preventDefault();
                showReviseError("Please upload at least one new file.");
                isValid = false;
            }


            // 2) VALIDASI: NOTES QUILL WAJIB ISI
            let plain = quillRevise.getText().trim();
            let html = quillRevise.root.innerHTML.trim();

            if (plain.length === 0 || html === "<p><br></p>") {
                e.preventDefault();
                showReviseError("Notes cannot be empty.");
                isValid = false;
            } else {
                reviseNotesInput.value = html;
            }

            if (!isValid) return;
        });

    });
</script>
<style>
    #reviseFilesContainer div > span {
        display: inline-block;
        max-width: 70%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
</style>
