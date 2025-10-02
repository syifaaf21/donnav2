{{-- ✅ Modal Revise Document (Modernized) --}}
<div class="modal fade" id="reviseModal{{ $mapping->id }}" tabindex="-1"
    aria-labelledby="reviseModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('document-control.revise', $mapping->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 rounded-4 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif;">
                        <i class="bi bi-arrow-clockwise me-2"></i> Revise Document: {{ $mapping->document->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    {{-- Preview dokumen lama --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Current File:</label>
                        @if ($mapping->file_path)
                            <a href="{{ asset('storage/' . $mapping->file_path) }}" target="_blank"
                                class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-text me-1"></i> View Current File
                            </a>
                        @else
                            <span class="text-muted">No file available</span>
                        @endif
                    </div>

                    {{-- Upload file baru --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Upload New File</label>
                        <input type="file" name="file" class="form-control border-1 shadow-sm" required>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <input type="text" name="notes" class="form-control border-1 shadow-sm" value="{{ old('notes') }}" required>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-save2 me-1"></i> Submit
                    </button>
                </div>               
            </div>
        </form>
    </div>
</div>
