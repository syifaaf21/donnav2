<!-- Modal Archive Files -->
<div class="modal fade" id="modal-archive-files" tabindex="-1" aria-labelledby="modalArchiveFilesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 shadow-lg border-0">
            <!-- Header -->
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4" style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark d-flex align-items-center gap-2" id="modalArchiveFilesLabel" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-archive-fill text-primary"></i>
                    Archive or Delete Active Files
                </h5>
                <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>
            <form id="archiveFilesForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.97rem;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2">Select active files to delete <span class="text-danger">*</span></label>
                        <div id="archiveFilesList" class="border rounded p-2 bg-light min-h-[48px] max-h-40 overflow-y-auto text-sm">
                            <!-- File checkboxes will be injected here by JS -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2">What do you want to do with the deleted files?</label>
                        <!-- Action buttons moved to footer, removed from here to avoid double buttons -->
                        <div class="form-text mt-2 text-xs text-gray-500">
                            If you choose <b>Archive</b>, the files will be moved to the archive for future reference.<br>
                            If you choose <b>Delete without archiving</b>, the files will be permanently deleted and cannot be recovered.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4 pb-4 px-4 justify-content-center bg-light rounded-bottom-4">
                    <div class="d-flex gap-3 justify-content-center w-100">
                        <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="btnArchiveSelectedFooter" style="min-width: 210px; font-size: 1.05rem;">
                            <i class="bi bi-archive"></i> Archive the deleted file
                        </button>
                        <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="btnDeleteSelectedFooter" style="min-width: 210px; font-size: 1.05rem;">
                            <i class="bi bi-trash"></i> Delete without archiving
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
