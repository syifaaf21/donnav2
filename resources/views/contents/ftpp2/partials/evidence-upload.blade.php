@php
    $finding = $finding ?? null;
@endphp

<!-- Modal Upload Evidence -->
<div id="modal-upload-evidence"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] hidden">
    <div class="bg-white rounded-4 shadow-lg w-full max-w-lg relative">

        <!-- Header -->
        <div class="modal-header justify-center position-relative p-4 rounded-top-4" style="background-color: #f5f5f7;">
            <h5 class="modal-title fw-semibold text-dark" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                <i class="bi bi-cloud-upload text-primary"></i> Upload Evidence for Finding
                {{ $finding->registration_number ?? '-' }}
            </h5>
            <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                onclick="closeEvidenceModal()" aria-label="Close"
                style="width: 36px; height: 36px; border: 1px solid #ddd;">
                <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ $finding ? route('ftpp.evidence.upload.post', $finding->id) : '#' }}"
            enctype="multipart/form-data" class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto"
            style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            @csrf

            <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2 mb-4">
                <i class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                    <ul class="text-xs text-yellow-700 leading-relaxed list-disc ms-4">
                        <li>Allowed formats: <strong>JPG, JPEG, PNG, PDF</strong>. Max per file: <strong>10 MB</strong>.
                        </li>
                        <li>Total upload evidence (existing + new) tidak boleh melebihi <strong>20 MB</strong>.</li>
                    </ul>
                </div>
            </div>

            <!-- Existing Evidence List -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Existing Evidence/Attachment</label>

                <ul class="list-none space-y-2">
                    @php
                        $evidenceFiles = collect();
                        if ($finding) {
                            if ($finding->auditeeAction && $finding->auditeeAction->file) {
                                $evidenceFiles = $evidenceFiles->merge($finding->auditeeAction->file);
                            }
                            if ($finding->file) {
                                $evidenceFiles = $evidenceFiles->merge($finding->file);
                            }
                        }
                        // FILTER = hanya yang punya auditee_action_id
                        $evidenceFiles = $evidenceFiles->filter(function ($f) {
                            return !empty($f->auditee_action_id);
                        });
                        $totalSize = $evidenceFiles->sum(function ($f) {
                            $path = storage_path('app/public/' . $f->file_path);
                            return file_exists($path) ? filesize($path) : 0;
                        });
                    @endphp
                    @forelse($evidenceFiles as $file)
                        <li
                            class="flex items-center justify-between bg-white border border-gray-200 rounded-lg shadow-sm px-3 py-2 mb-1 hover:shadow-md transition-all">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                        class="text-blue-700 font-semibold truncate hover:underline"
                                        style="max-width: 220px;">
                                        {{ $file->original_name ?? basename($file->file_path) }}
                                    </a>
                                    <span class="text-xs text-gray-500 mt-1">
                                        {{ strtoupper(pathinfo($file->file_path, PATHINFO_EXTENSION)) }}
                                        &bull;
                                        {{ number_format((file_exists(storage_path('app/public/' . $file->file_path)) ? filesize(storage_path('app/public/' . $file->file_path)) : 0) / 1024, 2) }}
                                        KB
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm btn-light border-0 text-danger ms-2"
                                title="Delete Evidence" onclick="deleteEvidenceFile({{ $file->id }}, this)">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </li>
                    @empty
                        <li class="text-gray-400">No evidence uploaded yet.</li>
                    @endforelse
                </ul>

                <div class="text-xs text-gray-600 mt-2">
                    Total used: <span id="evidence-total-size">{{ number_format($totalSize / 1024 / 1024, 2) }}</span>
                    MB / 20 MB
                </div>
            </div>

            <!-- Upload New Evidence -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Evidence</label>

                <div id="evidence-inputs">
                    <div class="evidence-input-item">
                        <input type="file" name="evidence[]"
                            class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200"
                            accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMoreEvidenceInput()">
                        <i class="bi bi-plus-circle me-1"></i> Add File
                    </button>
                    <span class="text-xs text-gray-500">You can upload multiple files.</span>
                </div>
            </div>


            <div class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="closeEvidenceModal()">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success fw-semibold">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
