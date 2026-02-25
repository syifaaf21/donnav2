<!-- resources/views/contents/document-review/partials/modal-approve.blade.php -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">

            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2 text-primary"></i> Approve Document
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            <form id="approveForm" method="POST" class="px-5 py-4">
                @csrf
                <div class="modal-body p-4">
                    <div class="d-flex align-items-start gap-3 p-3 border rounded-3 bg-light mb-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary"
                             style="width: 40px; height: 40px;">
                            <i class="bi bi-question-circle" style="font-size: 1.1rem;"></i>
                        </div>
                        <p class="mb-0 text-dark" style="line-height: 1.5;">
                            Are you sure you want to approve this document?
                        </p>
                    </div>
                </div>

                <div class="modal-footer justify-content-between p-3 bg-light border-top rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
