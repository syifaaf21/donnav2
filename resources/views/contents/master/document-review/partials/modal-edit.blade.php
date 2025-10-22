{{-- ✅ Modal Edit Document Review (Modern + Department Dropdown) --}}
<div class="modal fade" id="editModal{{ $mapping->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $mapping->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('master.document-review.update', $mapping->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 rounded-4 shadow-lg">

                {{-- Modal Header --}}
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif;">
                        <i class="bi bi-pencil-square me-2"></i> Edit Metadata Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-3">

                        {{-- Document Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Document Name <span class="text-danger">*</span></label>
                            <select name="document_id" id="editDocumentSelect{{ $mapping->id }}" class="form-select"
                                required>
                                <option value="{{ $mapping->document->id }}" selected>
                                    {{ $mapping->document->name }}
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Document Name is required.
                            </div>
                        </div>

                        {{-- Document Number --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Document Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="document_number" class="form-control border-1 shadow-sm"
                                value="{{ $mapping->document_number }}" required>
                            <div class="invalid-feedback">
                                Document Number is required.
                            </div>
                        </div>

                        {{-- Part Number --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Part Number <span class="text-danger">*</span></label>
                            <select name="part_number_id" id="editPartNumberSelect{{ $mapping->id }}"
                                class="form-select" required>
                                <option value="{{ $mapping->partNumber->id }}" selected>
                                    {{ $mapping->partNumber->part_number }}
                                </option>
                            </select>

                            <div class="invalid-feedback">
                                Part Number is required.
                            </div>

                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="editDepartmentSelect{{ $mapping->id }}"
                                class="form-select" required>
                                <option value="{{ $mapping->department->id }}" selected>
                                    {{ $mapping->department->name }}
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Department is required.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-outline-primary px-4">
                        <i class="bi bi-save2 me-1"></i> Save Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
            });
</script>
