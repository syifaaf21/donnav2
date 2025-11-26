<div class="overflow-x-auto bg-white rounded-lg shadow p-4">
    <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
            <tr>
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Document Number</th>
                <th class="px-4 py-2">Archived File</th>
                <th class="px-4 py-2">Department</th>
                <th class="px-4 py-2">Replacement Date</th>
                <th class="px-4 py-2">Hard Delete On</th>
                <th class="px-4 py-2 text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            @php
                $globalIteration = ($reviewDocuments->currentPage() - 1) * $reviewDocuments->perPage() + 1;
            @endphp

            @forelse ($reviewDocuments as $mapping)
                @foreach ($mapping->files as $file)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $globalIteration++ }}</td>

                        {{-- REVIEW uses document_number --}}
                        <td class="px-4 py-2">{{ $mapping->document_number ?? '-' }}</td>

                        <td class="px-4 py-2">{{ $file->original_name }}</td>

                        <td class="px-4 py-2">{{ $mapping->department?->name ?? '-' }}</td>

                        <td class="px-4 py-2">
                            {{ optional($file->updated_at)->format('Y-m-d') }}
                        </td>

                        <td class="px-4 py-2">
                            <span class="text-red-600 font-semibold">
                            {{ optional($file->marked_for_deletion_at)->format('Y-m-d') ?? '-' }}
                            </span>
                        </td>

                        <td class="px-4 py-2 text-center">
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
