{{-- Modal Revise --}}
<div id="modal-revise" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-4 shadow-lg w-full max-w-lg relative">

        {{-- Header --}}
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

        {{-- Form Body --}}
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data"
            class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto"
            style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            @csrf

            {{-- Tips Alert --}}
            <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2">
                <i class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                    <p class="text-xs text-yellow-700 leading-relaxed">
                        Gunakan tombol <strong>Replace</strong> untuk mengganti file. Menghapus file akan menghapusnya secara <strong>permanen</strong> dan tidak dapat dipulihkan.
                    </p>
                </div>
            </div>

            {{-- Existing Files (dynamic by JS) --}}
            <div id="reviseFilesContainer" class="mb-4"></div>

            {{-- Add New Files --}}
            <div class="mb-4">
                <div id="new-files-container" class="space-y-2"></div>
                <button type="button" id="add-file"
                    class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition">
                    + Add File
                </button>
                <p class="text-xs text-gray-500 mt-1">Allowed formats: PDF, DOCX, XLSX, JPG, PNG, JPEG</p>
            </div>

            {{-- Footer --}}
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
