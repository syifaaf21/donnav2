<!-- Reject Document Modal (Notes Only) -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">

            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;" id="rejectModalLabel">
                    <i class="bi bi-x-circle-fill me-2 text-primary"></i> Reject Document
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="doc_id" id="rejectDocumentId">

                <div class="modal-body p-4">
                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label fw-semibold">Notes <span
                                class="text-danger">*</span></label>
                        <div id="quillRejectEditor" style="height: 150px;"></div>
                        <input type="hidden" name="notes" id="rejectNotes" required>
                    </div>
                </div>

                <div class="modal-footer justify-content-between p-3 bg-light border-top rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger fw-semibold" id="submitReject">
                        <i class="bi bi-x-circle-fill me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        var quill = new Quill('#quillRejectEditor', {
            theme: 'snow',
            placeholder: 'Write the rejection notes...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link']
                ]
            }
        });

        var form = document.getElementById('rejectForm');

        form.addEventListener('submit', function(e) {

            let plainText = quill.getText().trim();
            let htmlContent = quill.root.innerHTML.trim();

            // Jika kosong, Quill tetap menghasilkan <p><br></p>
            if (plainText.length === 0 || htmlContent === "<p><br></p>") {
                e.preventDefault();

                showRejectError("Notes cannot be empty.");
                return;
            }

            // Kirim ke backend
            document.getElementById('rejectNotes').value = htmlContent;
        });

        // Fungsi helper untuk menampilkan error
        function showRejectError(message) {
            let oldAlert = document.getElementById('reject-alert');
            if (oldAlert) oldAlert.remove();

            let alertBox = document.createElement('div');
            alertBox.id = 'reject-alert';
            alertBox.className = "alert alert-danger mt-3";
            alertBox.innerText = message;

            let modalBody = document.querySelector('#rejectModal .modal-body');
            modalBody.prepend(alertBox);
        }

    });
</script>

