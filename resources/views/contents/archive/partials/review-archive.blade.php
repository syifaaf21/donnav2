<div class="overflow-hidden rounded-xl border border-gray-100 shadow-sm">
    <table class="min-w-full text-gray-700">
        <thead style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
            <tr>
                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Document Number</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Archived File</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Hard Delete On</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @php
                $globalIteration = ($reviewDocuments->currentPage() - 1) * $reviewDocuments->perPage() + 1;
            @endphp

            @forelse ($reviewDocuments as $file)
                <tr class="hover:bg-gray-50 transition-all duration-150">
                    <td class="px-2 py-3 border-r border-gray-200 text-center">{{ $globalIteration++ }}</td>

                    {{-- Document Number + Department --}}
                    <td class="px-4 py-3 text-xs text-center border-r border-gray-200">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-800">{{ $file->mapping->document_number ?? '-' }}</span>
                            @if ($file->mapping->department->name ?? false)
                                <span class="text-gray-500 text-xs mt-1">{{ $file->mapping->department->name }}</span>
                            @endif
                        </div>
                    </td>

                    {{-- File Name --}}
                    <td class="px-4 py-3 text-xs text-center border-r border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-file-earmark-text text-cyan-500"></i>
                            <span class="truncate max-w-[150px]"
                                title="{{ $file->original_name }}">{{ $file->original_name }}</span>
                        </div>
                    </td>
                    {{-- Hard Delete Badge --}}
                    <td class="px-4 py-3 text-xs text-center border-r border-gray-200">
                        @if ($file->marked_for_deletion_at)
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                                {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    {{-- Action --}}
                    <td class="px-4 py-3 text-center border-r border-gray-200">
                        @if ($file->file_path)
                            <button onclick="openFileViewer('{{ asset('storage/' . $file->file_path) }}')"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-500 text-white shadow hover:scale-110 transition-transform duration-200"
                                title="View File">
                                <i class="bi bi-eye text-lg"></i>
                            </button>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
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

{{-- Pagination --}}
<div class="mt-4 px-4">
    @if ($reviewDocuments->hasPages())
        {{ $reviewDocuments->appends(request()->except('page_review'))->links() }}
    @endif
</div>
