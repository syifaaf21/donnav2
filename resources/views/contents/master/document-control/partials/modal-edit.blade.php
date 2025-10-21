{{-- ✅ Modal Edit Document Review (Modern + Department Dropdown) --}}
<div class="modal fade" id="editModal{{ $mapping->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $mapping->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('master.document-control.update', $mapping->id) }}" method="POST" class="needs-validation"
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
                                @foreach ($documents as $doc)
                                    <option value="{{ $doc->id }}"
                                        @if ($mapping->document_id == $doc->id) selected @endif>
                                        {{ $doc->name }}
                                    </option>
                                @endforeach
                            </select>
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
                        </div>

                        {{-- Reminder Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Reminder Date</label>
                            <input type="date" name="reminder_date" class="form-control border-1 shadow-sm"
                                value="{{ \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') }}" >
                        </div>

                        {{-- Obsolete --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Obsolete Date</label>
                            <input type="date" name="deadline" class="form-control border-1 shadow-sm"
                                value="{{ \Carbon\Carbon::parse($mapping->obsolete_date)->format('Y-m-d') }}">
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
