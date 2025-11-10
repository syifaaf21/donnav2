<div class="overflow-x-auto">
    <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
            <tr>
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Document Number</th>
                <th class="px-4 py-2">Notes</th>
                <th class="px-4 py-2">Reminder Date</th>
                <th class="px-4 py-2">Deadline</th>
                <th class="px-4 py-2">Last Update</th>
                <th class="px-4 py-2">Updated By</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groupedData as $index => $group)
                @foreach ($group as $doc)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $doc->document_number ?? '-' }}</td>
                        <td class="px-4 py-2">{!! $doc->notes ?? '-' !!}</td>
                        <td class="px-4 py-2">{{ $doc->reminder_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $doc->deadline?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $doc->updated_at?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $doc->user?->name ?? '-' }}</td>
                        @php
                            $statusName = strtolower($doc->status?->name ?? '');
                            $statusClass = match($statusName) {
                                'approved' => 'inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded',
                                'rejected' => 'inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded',
                                'need review' => 'inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded',
                                default => 'inline-block px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded',
                            };
                        @endphp
                        <td class="px-4 py-2">
                            <span class="{{ $statusClass }}">{{ ucfirst($statusName ?: '-') }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-1 flex-wrap">
                                <div class="dropup d-inline">
                                    <button type="button"
                                        class="btn btn-outline-secondary px-2 py-1 rounded text-xs dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-paperclip"></i> Files
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end text-sm" style="min-width: 220px;">
                                        @forelse ($doc->files as $file)
                                            @php
                                                $fileUrl = asset('storage/' . $file->file_path);
                                            @endphp
                                            <li class="px-3 py-1 d-flex justify-content-between align-items-center">
                                                <span class="text-truncate small" style="max-width: 140px;">
                                                    {{ $file->file_name ?? basename($file->file_path) }}
                                                </span>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ $fileUrl }}" class="btn btn-outline-success btn-sm" download title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="dropdown-item-text text-muted small">No files available</li>
                                        @endforelse
                                    </ul>
                                </div>
                                {{-- Tombol edit --}}
                                @php
                                    $fileList = $doc->files->map(fn($f) => [
                                        'id' => $f->id,
                                        'name' => $f->file_name ?? basename($f->file_path),
                                        'url' => asset('storage/' . $f->file_path),
                                    ]);
                                    $statusNameLower = strtolower($doc->status?->name ?? '');
                                @endphp
                                <button type="button"
                                    class="btn btn-outline-warning btn-sm edit-doc-btn px-2 py-1 rounded text-xs"
                                    data-doc-id="{{ $doc->id }}"
                                    data-notes="{{ $doc->notes }}"
                                    data-route="{{ route('document-review.revise', $doc->id) }}"
                                    data-files='@json($fileList)' title="Edit Document"
                                    @if (!in_array($statusNameLower, ['approved','rejected'])) disabled @endif>
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9" class="text-center py-8 text-gray-400">
                        <p class="text-sm">No data found. Apply filters to see results.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
