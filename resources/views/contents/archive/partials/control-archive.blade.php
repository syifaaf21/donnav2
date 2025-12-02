<div class="overflow-hidden rounded-xl border border-gray-100 overflow-x-auto overflow-y-auto max-h-[520px]">
    <table class="min-w-full text-sm text-gray-700">
        <thead class="sticky top-0 z-10 bg-gray-50">
            <tr class="border-b border-gray-200">
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide text-left">No</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide text-left">Document</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide text-left">Inactive File</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide text-left">Hard Delete</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Action</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
            @php
                $globalIteration = ($controlDocuments->currentPage() - 1) * $controlDocuments->perPage() + 1;
            @endphp

            @forelse ($controlDocuments as $file)
                <tr class="hover:bg-gray-50 transition-all duration-150">
                    <td class="px-4 py-3 font-medium text-gray-600">{{ $globalIteration++ }}</td>

                    <td class="px-4 py-3">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-800 truncate">{{ $file->mapping->document->name ?? '-' }}</span>
                            <span class="text-gray-500 text-xs mt-1 truncate capitalize">
                                {{ $file->mapping->department->name ?? '-' }}
                            </span>
                        </div>
                    </td>

                    <td class="px-4 py-3 text-gray-700 text-sm truncate">
                        {{ $file->original_name }}
                    </td>

                    <td class="px-4 py-3">
                        @if($file->marked_for_deletion_at)
                            <span class="inline-block bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded-full text-xs">
                                {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        @if ($file->file_path)
                            <button onclick="openFileViewer('{{ asset('storage/' . $file->file_path) }}')"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-500 text-white shadow hover:scale-110 transition-transform duration-200"
                                title="View File">
                                <i class="bi bi-eye text-sm"></i>
                            </button>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                            <i class="bi bi-inbox text-4xl"></i>
                            <span>No archived control documents found.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4 px-4">
    @if ($controlDocuments->hasPages())
        {{ $controlDocuments->appends(request()->except('page_control'))->links() }}
    @endif
</div>
