<div class="overflow-hidden bg-white rounded-xl shadow border border-gray-100">
            <table class="min-w-full text-sm text-gray-700">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">No</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Document Number</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Archived File</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Department</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Replacement Date</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Hard Delete On</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php
                $globalIteration = ($reviewDocuments->currentPage() - 1) * $reviewDocuments->perPage() + 1;
            @endphp

            @forelse ($reviewDocuments as $mapping)
                @foreach ($mapping->files as $file)
                    <tr class="hover:bg-gray-50 transition-all duration-150">
                        <td class="px-4 py-3">{{ $globalIteration++ }}</td>

                        {{-- REVIEW uses document_number --}}
                        <td class="px-4 py-3">{{ $mapping->document_number ?? '-' }}</td>

                        <td class="px-4 py-3">{{ $file->original_name }}</td>

                        <td class="px-4 py-3">{{ $mapping->department?->name ?? '-' }}</td>

                        <td class="px-4 py-3">
                            {{ optional($file->updated_at)->format('Y-m-d') }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-red-600 font-semibold">
                            {{ optional($file->marked_for_deletion_at)->format('Y-m-d') ?? '-' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if ($file->file_path)
                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                    class="text-blue-500 hover:text-blue-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        No archived review documents found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $reviewDocuments->links('vendor.pagination.tailwind') }}
</div>
