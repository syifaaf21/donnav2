<table class="min-w-full table-auto text-sm text-left text-gray-700">
    <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
        <tr>
            <th class="px-4 py-2">No</th>
            <th class="px-4 py-2">Part Number</th>
            <th class="px-4 py-2">Model</th>
            <th class="px-4 py-2">Process</th>
            <th class="px-4 py-2 text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($groupedData as $index => $group)
            @php
                $first = $group->first();
            @endphp
            <tr class="border-b border-gray-200 bg-white hover:bg-gray-50">
                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                <td class="px-4 py-2">{{ $first->partNumber?->part_number ?? '-' }}</td>
                <td class="px-4 py-2">{{ $first->partNumber?->productModel?->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ ucwords($first->partNumber?->process ?? '-') }}</td>

                <td class="px-4 py-2 text-center space-x-1">
                    <!-- 👁 Toggle Detail -->
                    <button type="button"
                        class="toggle-detail bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs"
                        data-target="#detail-{{ $index }}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            </tr>

            <!-- 🔽 Detail Row -->
            <tr id="detail-{{ $index }}" class="hidden bg-gray-50 border-t border-b border-gray-200">
                <td colspan="5" class="p-4">
                    <table class="w-full table-fixed text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-2 py-1">Document Name</th>
                                <th class="px-2 py-1">Document Number</th>
                                <th class="px-2 py-1">Notes</th>
                                <th class="px-2 py-1">Reminder Date</th>
                                <th class="px-2 py-1">Deadline</th>
                                <th class="px-2 py-1">Last Update</th>
                                <th class="px-2 py-1">Updated By</th>
                                <th class="px-2 py-1">Status</th>
                                <th class="px-2 py-1 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $i => $doc)
                                <tr class="border-b">
                                    <td class="px-2 py-1">{{ $doc->document?->name ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $doc->document_number ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $doc->notes ?? '-' }}</td>
                                    <td class="px-2 py-1">
                                        {{ $doc->reminder_date ? $doc->reminder_date->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-2 py-1">
                                        {{ $doc->deadline ? $doc->deadline->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-2 py-1">
                                        {{ $doc->updated_at ? $doc->updated_at->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-2 py-1">{{ $doc->user?->name ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $doc->status?->name ?? '-' }}</td>
                                    <td class="px-2 py-1 text-center">
                                        <div class="dropdown d-inline">
                                            <button type="button"
                                                class="btn btn-outline-secondary px-2 py-1 rounded text-xs dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-paperclip"></i> Files
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end text-sm"
                                                style="min-width: 220px;">
                                                @forelse ($doc->files as $file)
                                                    @php
                                                        $fileUrl = asset('storage/' . $file->file_path);
                                                        $extension = strtolower(
                                                            pathinfo($file->file_path, PATHINFO_EXTENSION),
                                                        );
                                                        $isPdf = $extension === 'pdf';
                                                        $isOffice = in_array($extension, [
                                                            'doc',
                                                            'docx',
                                                            'xls',
                                                            'xlsx',
                                                        ]);
                                                        $viewerUrl = $isPdf
                                                            ? $fileUrl
                                                            : ($isOffice
                                                                ? 'https://docs.google.com/gview?url=' .
                                                                    urlencode($fileUrl) .
                                                                    '&embedded=true'
                                                                : null);
                                                    @endphp
                                                    <li class="px-3 py-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="text-truncate small" style="max-width: 140px;">
                                                                {{ $file->file_name ?? basename($file->file_path) }}
                                                            </span>
                                                            <div class="d-flex gap-1">
                                                                @if ($viewerUrl)
                                                                    <button type="button"
                                                                        class="btn btn-outline-primary btn-sm view-file-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#viewFileModal"
                                                                        data-file="{{ $viewerUrl }}" title="View">
                                                                        <i class="bi bi-eye"></i>
                                                                    </button>
                                                                @else
                                                                    <button class="btn btn-outline-secondary btn-sm"
                                                                        disabled title="Preview Not Supported">
                                                                        <i class="bi bi-ban"></i>
                                                                    </button>
                                                                @endif
                                                                <a href="{{ $fileUrl }}"
                                                                    class="btn btn-outline-success btn-sm" download
                                                                    title="Download">
                                                                    <i class="bi bi-download"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    @if (!$loop->last)
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                    @endif
                                                @empty
                                                    <li class="dropdown-item-text text-muted small">No files available
                                                    </li>
                                                @endforelse
                                            </ul>
                                        </div>
                                        <!-- ✏️ Revisi -->
                                        <button type="button"
                                            class="btn btn-warning hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs revise-btn"
                                            title="Revisi" data-bs-toggle="modal" data-bs-target="#reviseModal"
                                            data-doc-id="{{ $doc->id }}"
                                            data-doc-name="{{ $doc->document?->name }}"
                                            data-doc-number="{{ $doc->document_number }}"
                                            data-files='@json(
                                                $doc->files->map(fn($f) => [
                                                        'name' => $f->file_name ?? basename($f->file_path),
                                                        'url' => asset('storage/' . $f->file_path),
                                                    ]))'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- ✅ Modal Revise Document --}}
                    <div class="modal fade" id="reviseModal" tabindex="-1" aria-labelledby="reviseModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <form method="POST" action="" enctype="multipart/form-data" id="reviseForm">
                                @csrf
                                <div class="modal-content border-0 rounded-4 shadow-lg">
                                    <div class="modal-header bg-light text-dark rounded-top-4">
                                        <h5 class="modal-title fw-semibold">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Revisi Dokumen
                                            <span class="docNameDisplay text-primary"></span>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-4"
                                        style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                                        {{-- List Files --}}
                                        <div class="existing-files-container mb-3"></div>

                                        {{-- Notes revisi --}}
                                        <div>
                                            <label class="form-label">Notes</label>
                                            <input type="text" name="notes"
                                                class="form-control border-1 shadow-sm"
                                                placeholder="Catatan revisi untuk file ini..." required>
                                        </div>
                                    </div>

                                    <div
                                        class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                                        <button type="button" class="btn btn-outline-secondary px-4"
                                            data-bs-dismiss="modal">
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
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">
                        <p class="text-sm">No data found. Apply filters to see results.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- toggle-detail JS (pastikan hanya dipanggil sekali di index) --}}
    <script>
        document.querySelectorAll('.toggle-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                if (target) target.classList.toggle('hidden');
            });
        });
    </script>
