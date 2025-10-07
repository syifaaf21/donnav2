<!-- ✅ Modal Approve Document -->
<div class="modal fade" id="approveModal{{ $mapping->id }}" tabindex="-1"
    aria-labelledby="approveModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title d-flex align-items-center" id="approveModalLabel{{ $mapping->id }}">
                    <i class="bi bi-check-circle-fill me-2"></i> Approve Document
                </h5>
            </div>

            <form
                action="{{ route('master.document-review.approveWithDates', $mapping->id) }}"
                method="POST"
                onsubmit="return validateDates{{ $mapping->id }}(event)">
                @csrf
                <div class="modal-body p-4">
                    {{-- Reminder Date --}}
                    <div class="mb-3">
                        <label for="reminder_date{{ $mapping->id }}" class="form-label fw-semibold">
                            Reminder Date <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            name="reminder_date"
                            id="reminder_date{{ $mapping->id }}"
                            class="form-control form-control-lg border-success-subtle"
                            value="{{ old('reminder_date') }}"
                            required>
                        <div id="reminderError{{ $mapping->id }}"
                             class="text-danger small mt-1"
                             style="display:none;">
                             Reminder Date must be earlier than or equal to Deadline.
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div class="mb-3">
                        <label for="deadline{{ $mapping->id }}" class="form-label fw-semibold">
                            Deadline <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            name="deadline"
                            id="deadline{{ $mapping->id }}"
                            class="form-control form-control-lg border-danger-subtle"
                            value="{{ old('deadline') }}"
                            required>
                        <div id="deadlineError{{ $mapping->id }}"
                             class="text-danger small mt-1"
                             style="display:none;">
                             Deadline must be later than or equal to Reminder Date.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check2-circle me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ Script Validasi -->
<script>
function validateDates{{ $mapping->id }}(event) {
    const reminderInput = document.getElementById('reminder_date{{ $mapping->id }}');
    const deadlineInput = document.getElementById('deadline{{ $mapping->id }}');
    const reminderError = document.getElementById('reminderError{{ $mapping->id }}');
    const deadlineError = document.getElementById('deadlineError{{ $mapping->id }}');

    const reminderDate = new Date(reminderInput.value);
    const deadlineDate = new Date(deadlineInput.value);

    // Reset error state
    reminderError.style.display = 'none';
    deadlineError.style.display = 'none';
    reminderInput.classList.remove('is-invalid');
    deadlineInput.classList.remove('is-invalid');

    // Validasi dua arah: Reminder <= Deadline
    if (reminderDate > deadlineDate) {
        reminderError.style.display = 'block';
        deadlineError.style.display = 'block';
        reminderInput.classList.add('is-invalid');
        deadlineInput.classList.add('is-invalid');
        event.preventDefault();
        return false;
    }

    return true;
}
</script>
