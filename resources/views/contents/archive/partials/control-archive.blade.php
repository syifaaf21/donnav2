<div class="overflow-hidden bg-white rounded-xl shadow border border-gray-100">
    <table class="min-w-full text-sm text-gray-700">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-300">
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">No</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Document Name</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Inactive File</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Department</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Hard Delete On</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Action</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
            @php
                $globalIteration = ($controlDocuments->currentPage() - 1) * $controlDocuments->perPage() + 1;
            @endphp

            @forelse ($controlDocuments as $mapping)
                @foreach ($mapping->files->filter(fn($f) => $f->marked_for_deletion_at > now()) as $file)
                    <tr class="hover:bg-gray-50 transition-all duration-150">

                        <!-- No -->
                        <td class="px-4 py-3">{{ $globalIteration++ }}</td>

                        <!-- Document Name -->
                        <td class="px-4 py-3">
                            {{ $mapping->document?->name ?? '-' }}
                        </td>

                        <!-- Inactive File -->
                        <td class="px-4 py-3">
                            {{ $file->original_name }}
                        </td>

                        <!-- Department -->
                        <td class="px-4 py-3">
                            {{ $mapping->department?->name ?? '-' }}
                        </td>

                        <!-- Hard Delete On -->
                        <td class="px-4 py-3">
                            <span class="text-red-600 font-semibold">
                                {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('Y-m-d') }}
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="px-4 py-3 text-center">
                            @if ($file->file_path)
                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                    class="text-blue-600 hover:text-blue-800">
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
                    <td colspan="6" class="text-center py-4">
                        No archived control documents found.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

<div class="mt-4">
    {{ $controlDocuments->links('vendor.pagination.tailwind') }}
</div>
