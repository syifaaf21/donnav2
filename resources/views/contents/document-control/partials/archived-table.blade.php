{{-- KONTEN TABEL DAN PAGINASI (DIMUAT ULANG OLEH AJAX) --}}
<div class="overflow-x-auto bg-white rounded-lg shadow p-4">
    <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
            <tr>
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Document Name</th>
                <th class="px-4 py-2">Archived File</th>
                <th class="px-4 py-2">Department</th>
                <th class="px-4 py-2">Replacement Date</th>
                <th class="px-4 py-2">Hard Delete On</th>
                <th class="px-4 py-2 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                $globalIteration = ($documentsMapping->currentPage() - 1) * $documentsMapping->perPage() + 1;
            @endphp
            @forelse ($documentsMapping as $mapping)
                @php
                    $archivedFiles = $mapping->files->filter(
                        fn($f) => !$f->is_active && $f->marked_for_deletion_at > now(),
                    );
                @endphp

                @if ($archivedFiles->isNotEmpty())
                    @foreach ($archivedFiles as $file)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-gray-600 text-sm">
                                {{ $globalIteration++ }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-sm">
                                <strong>{{ $mapping->document->name ?? 'N/A' }}</strong>
                                @if ($mapping->status?->name === 'Obsolete')
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-800">OBSOLETE</span>
                                @else
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">REPLACED</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm">
                                {{ $file->original_name }} (Old Version)
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm">{{ $mapping->department->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-gray-600 text-sm text-nowrap">
                                {{ $file->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm text-nowrap">
                                <span class="text-red-600 font-semibold">
                                    {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('d F Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50
              text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition"
                                    title="View File">
                                    <i data-feather="eye" class="w-4 h-4"></i>
                                </a>
                            </td>

                        </tr>
                    @endforeach
                @elseif ($mapping->status?->name === 'Obsolete')
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-2 text-gray-600 text-sm">{{ $globalIteration++ }}</td>
                        <td class="px-4 py-2 text-gray-700 text-sm">
                            <strong>{{ $mapping->document->name ?? 'N/A' }}</strong>
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-800">OBSOLETE</span>
                        </td>
                        <td colspan="3"><em class="text-muted text-gray-500">No active archived files remaining for
                                this document.</em></td>
                        <td class="px-4 py-2 text-center">-</td>
                        <td class="px-4 py-2 text-center">-</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-box-open fa-2x text-gray-400 mb-3"></i>
                        <p class="mb-0 text-gray-600">No documents or files are currently archived.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- Pagination Links --}}
<div class="mt-4">
    {{ $documentsMapping->links('vendor.pagination.tailwind') }}
</div>
