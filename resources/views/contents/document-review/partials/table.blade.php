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
                <td class="px-4 py-2">
                    {{ ucwords($first->partNumber?->process?->name ?? '-') }}
                </td>
                <td class="px-4 py-2 text-center space-x-1">
                    <!-- 👁 Toggle Detail -->
                    @if ($group->count() > 0)
                        <button type="button"
                            class="toggle-detail bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs"
                            data-target="#detail-{{ $index }}" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    @endif
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
                                    @php
                                        $statusName = strtolower($doc->status?->name ?? '');
                                        $statusClass = match ($statusName) {
                                            'approved'
                                                => 'inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded',
                                            'rejected'
                                                => 'inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded',
                                            'need review'
                                                => 'inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded',
                                            default
                                                => 'inline-block px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded',
                                        };
                                    @endphp

                                    <td class="px-2 py-1">
                                        <span class="{{ $statusClass }}">
                                            {{ ucfirst($statusName ?: '-') }}
                                        </span>
                                    </td>
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
                                                                        data-file="{{ $viewerUrl }}"
                                                                        data-status="{{ strtolower($doc->status?->name) }}"
                                                                        data-doc-id="{{ $doc->id }}"
                                                                        title="View">
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
                                        @php
                                            $fileList = $doc->files->map(function ($f) {
                                                return [
                                                    'id' => $f->id,
                                                    'name' => $f->file_name ?? basename($f->file_path),
                                                    'url' => asset('storage/' . $f->file_path),
                                                ];
                                            });
                                        @endphp
                                        @php
                                            $statusName = strtolower($doc->status?->name ?? '');
                                            $canRevise = in_array($statusName, ['approved', 'rejected']); // hanya approved & rejected yang boleh revisi
                                        @endphp

                                        <button type="button"
                                            class="btn btn-outline-warning btn-sm edit-doc-btn px-2 py-1 rounded text-xs"
                                            data-doc-id="{{ $doc->id }}" data-notes="{{ $doc->notes }}"
                                            data-route="{{ route('document-review.revise', $doc->id) }}"
                                            data-files='@json($fileList)' title="Edit Document"
                                            @if (!in_array(strtolower($doc->status?->name ?? ''), ['approved', 'rejected'])) disabled @endif>
                                            <i class="bi bi-pencil"></i>
                                        </button>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
