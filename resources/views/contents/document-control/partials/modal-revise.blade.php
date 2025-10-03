{{-- ✅ Modal Revise Document --}}
<div class="modal fade" id="reviseModal{{ $mapping->id }}" tabindex="-1"
    aria-labelledby="reviseModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('document-control.revise', $mapping->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 rounded-4 shadow-lg">
                {{-- Header --}}
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-arrow-clockwise me-2"></i> Revisi Document: {{ $mapping->document->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">

                    @forelse ($mapping->files as $file)
                        <div class="mb-4 border rounded p-3 bg-light">
                            <label class="form-label fw-medium">Current File:</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ basename($file->file_path) }}
                                </a>
                            </div>

                            {{-- Upload file revisi --}}
                            <div class="mt-2">
                                <label class="form-label">Upload Revised File</label>
                                <input type="file" name="files[{{ $file->id }}]"
                                    class="form-control border-1 shadow-sm">
                            </div>
                        </div>
                    @empty
                        <span class="text-muted">No file available</span>
                    @endforelse
                    {{-- Notes revisi --}}
                    <div class="mt-2">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control border-1 shadow-sm"
                            placeholder="Catatan revisi untuk file ini...">
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-save2 me-1"></i> Submit Revisi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
