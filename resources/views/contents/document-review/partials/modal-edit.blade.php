{{-- ✅ Modal Revise Document --}}
<div class="modal fade" id="reviseModal" tabindex="-1" aria-labelledby="reviseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="" enctype="multipart/form-data" id="reviseForm"
            class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            {{-- Header --}}
            <div class="modal-header bg-light text-dark rounded-top-4">
                <h5 class="modal-title fw-semibold" id="reviseModalLabel">
                    <i class="bi bi-arrow-clockwise me-2"></i> Edit Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">

                {{-- List existing files (filled by JS) --}}
                <div class="existing-files-container mb-3"></div>

                {{-- Upload new file --}}
                <div class="mt-3">
                    <label class="form-label fw-semibold">Upload New File (Optional)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">Jika Anda upload file baru, file lama akan digantikan.</small>
                </div>


                {{-- Notes revisi --}}
                <div class="mt-3">
                    <label for="notesInput" class="form-label fw-semibold">Notes Revision <span
                            class="text-danger">*</span></label>
                    <input type="text" name="notes" id="notesInput" class="form-control border-1 shadow-sm"
                        placeholder="Catatan revisi untuk file ini..." required>
                </div>
            </div>

            {{-- Footer --}}
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
