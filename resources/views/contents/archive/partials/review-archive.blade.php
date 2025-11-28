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
                                <button onclick="openFileViewer('{{ asset('storage/' . $file->file_path) }}')"
                                    class="inline-flex items-center justify-center
                   w-8 h-8 rounded-full
                   bg-cyan-500 text-white
                   hover:bg-cyan-600 transition-colors
                   shadow-md"
                                    title="View File">

                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr colspan="12">
                    <td colspan="12">
                        <div
                            class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                            <i class="bi bi-inbox text-4xl"></i>
                            <span>No archived review documents found.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $reviewDocuments->links('vendor.pagination.tailwind') }}
</div>
