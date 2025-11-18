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
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data" class="px-4 py-4 space-y-4">
            @csrf

            <!-- Existing Files (dynamically rendered by JS) -->
            <div id="reviseFilesContainer" class="mb-4"></div>

            <!-- Add New Files -->
            <div class="mb-4">
                <div id="new-files-container"></div>
                <button type="button" id="add-file"
                    class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition">
                    + Add File
                </button>
                <p class="text-xs text-gray-500 mt-1">Allowed formats: PDF, DOCX, XLSX</p>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center border-t pt-3">
                <button type="button"
                    class="px-4 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-100"
                    onclick="closeReviseModal()">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>

                <button type="submit" class="px-4 py-1.5 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
                    <i class="bi bi-check2-circle me-1"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>
