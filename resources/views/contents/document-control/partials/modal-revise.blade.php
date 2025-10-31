<!-- Modal Revise -->
<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-700">
                Revise Document
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
                <button type="button" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded" id="add-file">+ Add File</button>
                <p class="text-xs text-gray-500 mt-1">Allowed formats: PDF, DOCX, EXCEL</p>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 border-t pt-3">
                <button type="button" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded" onclick="closeReviseModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">Submit Revision</button>
            </div>
        </form>
    </div>
</div>
