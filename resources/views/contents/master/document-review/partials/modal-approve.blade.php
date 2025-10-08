<!-- ✅ Modal Approve (Global) -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title d-flex align-items-center" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> Approve Document
                </h5>
            </div>

<<<<<<<< HEAD:resources/views/contents/master/document-review/partials/modal-approve.blade.php
            <form
                action="{{ route('master.document-review.approveWithDates', $mapping->id) }}"
                method="POST"
                onsubmit="return validateDates{{ $mapping->id }}(event)">
========
            <form id="approveForm" method="POST" onsubmit="return validateDates(event)">
>>>>>>>> f3899fec09a8a4677370e9e4d17fc02c74b3f381:resources/views/contents/document-review/partials/modal-approve.blade.php
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="_method" value="POST" />
                    {{-- Reminder Date --}}
                    <div class="mb-3">
                        <label for="reminder_date" class="form-label fw-semibold">Reminder Date <span class="text-danger">*</span></label>
                        <input type="date" name="reminder_date" id="reminder_date" class="form-control" required>
                        <div id="reminderError" class="text-danger small mt-1" style="display:none;">
                            Reminder Date must be earlier than or equal to Deadline.
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold">Deadline <span class="text-danger">*</span></label>
                        <input type="date" name="deadline" id="deadline" class="form-control" required>
                        <div id="deadlineError" class="text-danger small mt-1" style="display:none;">
                            Deadline must be later than or equal to Reminder Date.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
