<!-- Reject Document Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectDocumentId">
                <div class="mb-3">
                    <label for="rejectNotes">Notes</label>
                    <div id="quillRejectEditor" style="height: 200px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitReject">Reject</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Inisialisasi Quill
    var rejectQuill = new Quill('#quillRejectEditor', {
        theme: 'snow',
        placeholder: 'Write rejection notes here...'
    });


    // Buka modal & set content Quill
    function openRejectModal(documentId, existingNotes = '') {
        document.getElementById('rejectDocumentId').value = documentId;
        rejectQuill.root.innerHTML = existingNotes;

        var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }

    // Submit reject via fetch
    document.getElementById('submitReject').addEventListener('click', function() {
        const documentId = document.getElementById('rejectDocumentId').value;
        const notes = rejectQuill.root.innerHTML;

        fetch(`/document-control/${documentId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notes
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Failed to reject');
                }
            });
    });
</script>
