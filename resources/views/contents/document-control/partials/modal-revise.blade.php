{{-- ✅ Dynamic Revise Modal --}}
<div class="modal fade" id="reviseModalDynamic" tabindex="-1" aria-labelledby="reviseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="reviseFormDynamic" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="reviseModalTitle">
                        <i class="bi bi-arrow-clockwise me-2"></i> Revisi Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div id="reviseFilesContainer"></div>

                    <div class="mt-2">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control border-1 shadow-sm"
                            placeholder="Catatan revisi untuk file ini...">
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-save2 me-1"></i> Submit Revisi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
