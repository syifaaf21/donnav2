<!-- ✅ Modal Approve Document -->
<div class="modal fade" id="approveModal{{ $mapping->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header  bg-light text-dark">
                <h5 class="modal-title d-flex align-items-center" id="approveModalLabel{{ $mapping->id }}">
                    <i class="bi bi-check-circle-fill me-2"></i> Approve Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('document-review.approveWithDates', $mapping->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="reminder_date{{ $mapping->id }}" class="form-label fw-semibold"> Reminder Date</label>
                        <input
                            type="date"
                            name="reminder_date"
                            id="reminder_date{{ $mapping->id }}"
                            class="form-control form-control-lg border-success-subtle"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="deadline{{ $mapping->id }}" class="form-label fw-semibold"> Deadline</label>
                        <input
                            type="date"
                            name="deadline"
                            id="deadline{{ $mapping->id }}"
                            class="form-control form-control-lg border-danger-subtle"
                            required
                        >
                    </div>
                </div>

                <div class="modal-footer bg-light">
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
