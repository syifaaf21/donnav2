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
                            <select name="document_id" class="form-select border-1 shadow-sm" required>
                                @foreach ($documentsMaster as $doc)
                                    <option value="{{ $doc->id }}"
                                        @if ($mapping->document_id == $doc->id) selected @endif>
                                        {{ $doc->name }}
                                    </option>
                                @endforeach
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
                            <select id="editPartNumberSelect{{ $mapping->id }}" name="part_number_id"
                                class="form-select border-1 shadow-sm" required>
                                @foreach ($partNumbers as $part)
                                    <option value="{{ $part->id }}" data-plant="{{ $part->plant }}"
                                        @if ($mapping->part_number_id == $part->id) selected @endif>
                                        {{ $part->part_number }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Part Number is required.
                            </div>

                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select border-1 shadow-sm" required>
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        @if ($mapping->department_id == $dept->id) selected @endif>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Department is required.
                            </div>
                        </div>

                        {{-- Reminder Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Reminder Date</label>
                            <input type="date" name="reminder_date" class="form-control border-1 shadow-sm"
                                value="{{ $mapping->reminder_date?->format('Y-m-d') }}"
                                @if ($mapping->status->name != 'Approved') readonly @endif>
                        </div>

                        {{-- Deadline --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Deadline</label>
                            <input type="date" name="deadline" class="form-control border-1 shadow-sm"
                                value="{{ $mapping->deadline?->format('Y-m-d') }}"
                                @if ($mapping->status->name != 'Approved') readonly @endif>
                        </div>


                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save2 me-1"></i> Save Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
