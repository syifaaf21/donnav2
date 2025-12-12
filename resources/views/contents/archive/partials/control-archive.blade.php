<div class="overflow-hidden rounded-xl border border-gray-100 overflow-x-auto overflow-y-auto max-h-[520px]">
    <table class="min-w-full text-gray-700">
        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                    style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                    style="color: #1e2b50; letter-spacing: 0.5px;">Document Name</th>
                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                    style="color: #1e2b50; letter-spacing: 0.5px;">Inactive File</th>
                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                    style="color: #1e2b50; letter-spacing: 0.5px;">Hard Delete On</th>
                <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                    style="color: #1e2b50; letter-spacing: 0.5px;">Action</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
            @php
                $globalIteration = ($controlDocuments->currentPage() - 1) * $controlDocuments->perPage() + 1;
            @endphp

            @forelse ($controlDocuments as $file)
                <tr class="hover:bg-gray-50 transition-all duration-150">
                    <td class="px-4 py-3 font-medium text-gray-600 border-r border-gray-200">{{ $globalIteration++ }}
                    </td>

                    <td class="px-4 py-3 border-r border-gray-200">
                        <div class="flex flex-col">
                            <span
                                class="font-semibold text-gray-800 truncate">{{ $file->mapping->document->name ?? '-' }}</span>
                            <span class="text-gray-500 text-xs mt-1 truncate capitalize">
                                {{ $file->mapping->department->name ?? '-' }}
                            </span>
                        </div>
                    </td>

                    <td class="px-4 py-3 border-r border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-file-earmark-text text-cyan-500"></i>
                            <span class="block max-w-[180px] truncate" title="{{ $file->original_name }}">
                                {{ $file->original_name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-200">
                        @if ($file->marked_for_deletion_at)
                            <span
                                class="inline-block bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded-full text-xs">
                                {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center border-r border-gray-200">
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
                        <div
                            class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
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
