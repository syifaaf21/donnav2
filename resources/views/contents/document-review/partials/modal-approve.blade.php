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
                    {{-- Reminder Date --}}
                    <div class="mb-3">
                        <label for="reminder_date" class="form-label fw-semibold">
                            Reminder Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="reminder_date" id="reminder_date" class="form-control"
                            min="{{ now()->toDateString() }}" required>
                        <div id="reminderError" class="text-danger small mt-1" style="display:none;">
                            Reminder Date must be earlier than or equal to Deadline.
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold">
                            Deadline <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="deadline" id="deadline" class="form-control"
                            min="{{ now()->toDateString() }}" required>
                        <div id="deadlineError" class="text-danger small mt-1" style="display:none;">
                            Deadline must be later than or equal to Reminder Date.
                        </div>
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
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const approveForm = document.getElementById("approveForm");
        const reminderInput = document.getElementById("reminder_date");
        const deadlineInput = document.getElementById("deadline");


        // ===== FUNC: Tampilkan error mirip modal revise =====
        function showApproveError(message) {

            // Hapus alert lama
            let oldAlert = document.getElementById("approve-alert");
            if (oldAlert) oldAlert.remove();

            const alertDiv = document.createElement("div");
            alertDiv.id = "approve-alert";
            alertDiv.className = "alert alert-danger mt-2";
            alertDiv.innerText = message;

            approveForm.prepend(alertDiv);
        }


        // ===== VALIDASI SUBMIT =====
        approveForm.addEventListener("submit", function(e) {

            let reminder = reminderInput.value;
            let deadline = deadlineInput.value;

            // Wajib isi
            if (!reminder || !deadline) {
                e.preventDefault();
                showApproveError("Reminder Date and Deadline are required.");
                return;
            }

            // Convert ke date
            const reminderDate = new Date(reminder);
            const deadlineDate = new Date(deadline);

            // Reminder tidak boleh > deadline
            if (reminderDate > deadlineDate) {
                e.preventDefault();
                showApproveError("Reminder Date must be earlier than or equal to Deadline.");
                return;
            }

            // Deadline tidak boleh < Reminder
            if (deadlineDate < reminderDate) {
                e.preventDefault();
                showApproveError("Deadline must be later than or equal to Reminder Date.");
                return;
            }
        });

    });
</script>
