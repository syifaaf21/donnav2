<!-- Modal Revise -->
<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-700">
                Upload Document
            </h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeReviseModal()">✕</button>
        </div>

        <!-- Form -->
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data" class="px-4 py-4">
            @csrf

            <!-- Existing Files (dynamically rendered) -->
            <div id="reviseFilesContainer" class="mb-4"></div>

            <!-- Add New Files -->
            <div class="mb-4">
                <div id="new-files-container"></div>
                <button type="button" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded" id="add-file">+
                    Add File</button>
                <p class="text-xs text-gray-500 mt-1">Allowed formats: PDF, DOCX, EXCEL</p>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                {{-- Cancel --}}
                <button type="button" class="btn btn-outline-secondary" onclick="closeReviseModal()">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>

                {{-- Submit Revision --}}
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>
