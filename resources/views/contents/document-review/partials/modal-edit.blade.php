<!-- Modal Revise -->
<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg relative">

        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-700">
                Upload Document Revision
            </h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeReviseModal()">✕</button>
        </div>

        <!-- Form -->
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data"
            class="px-4 py-4 space-y-4 max-h-[80vh] overflow-y-auto">
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
                <label class="block text-sm font-medium text-gray-700">Notes Revision <span
                        class="text-red-500">*</span></label>

                <!-- Quill container -->
                <div id="quillEditor" class="bg-white border border-gray-300 rounded p-2" style="height: 150px;">
                </div>

                <!-- Hidden input untuk submit -->
                <input type="hidden" name="notes" id="notesInput" required>
            </div>


            <!-- Footer -->
            <div class="flex justify-between items-center border-t pt-3">
                <button type="button"
                    class="px-4 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-100"
                    onclick="closeReviseModal()">
                    Cancel
                </button>

                <button type="submit" class="px-4 py-1.5 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Quill
        var quill = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Write your revision notes...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'], // basic formatting
                    ['link'] // link
                ]
            }
        });

        // Saat submit, pindahkan konten Quill ke input hidden
        var form = document.getElementById('reviseFormDynamic');
        form.addEventListener('submit', function(e) {
            document.getElementById('notesInput').value = quill.root.innerHTML.trim();
        });
    });
</script>
