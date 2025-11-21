<!-- Reject Document Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title d-flex align-items-center" id="rejectModalLabel">
                    <i class="bi bi-x-circle-fill me-2"></i> Reject Document
                </h5>
            </div>

            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="doc_id" id="rejectDocumentId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label fw-semibold">Notes</label>
                        <div id="quillRejectEditor" style="height: 200px;"></div>
                        <input type="hidden" name="notes" id="rejectNotes">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="submitReject">
                        <i class="bi bi-x-circle-fill me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
