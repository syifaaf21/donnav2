<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-700">
                Revise Document
            </h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeReviseModal()">
                ✕
            </button>
        </div>

        <!-- Form -->
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="p-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Revision Note</label>
                    <textarea name="revision_note" class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500" rows="3" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Revision File</label>
                    <input type="file" name="revision_file" class="w-full text-sm border rounded px-3 py-2 focus:ring focus:border-blue-500" required>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 px-4 py-3 border-t">
                <button type="button" onclick="closeReviseModal()" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
                    Submit Revision
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeReviseModal() {
        document.getElementById('modal-revise').classList.add('hidden');
    }
</script>
