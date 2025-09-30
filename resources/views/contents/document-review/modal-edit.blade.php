{{-- ✅ Modal Edit Document Metadata (Modernized) --}}
<div class="modal fade" id="editModal{{ $mapping->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $mapping->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('document-review.update', $mapping->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 rounded-4 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif;">
                        <i class="bi bi-pencil-square me-2"></i> Edit Document Metadata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Document Name</label>
                        <select name="document_id" class="form-select border-1 shadow-sm" required>
                            @foreach ($documentsMaster as $doc)
                                <option value="{{ $doc->id }}" @if ($mapping->document_id == $doc->id) selected @endif>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Document Number</label>
                        <input type="text" name="document_number" class="form-control border-1 shadow-sm"
                            value="{{ $mapping->document_number }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Part Number</label>
                        <select name="part_number_id" class="form-select border-1 shadow-sm" required>
                            @foreach ($partNumbers as $part)
                                <option value="{{ $part->id }}" @if ($mapping->part_number_id == $part->id) selected @endif>
                                    {{ $part->part_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Department</label>
                        <input type="text" class="form-control border-1 shadow-sm"
                            value="{{ $mapping->document->department->name ?? '' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Reminder Date</label>
                        <input type="date" name="reminder_date" class="form-control border-1 shadow-sm"
                            value="{{ \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Deadline</label>
                        <input type="date" name="deadline" class="form-control border-1 shadow-sm"
                            value="{{ \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save2 me-1"></i> Save Metadata
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
