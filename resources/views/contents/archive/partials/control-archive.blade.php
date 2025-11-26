<div class="overflow-x-auto bg-white rounded-lg shadow p-4">
    <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
            <tr>
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Document Name</th>
                <th class="px-4 py-2">Inactive File</th>
                <th class="px-4 py-2">Department</th>
                <th class="px-4 py-2">Hard Delete On</th>
                <th class="px-4 py-2">Action</th>
            </tr>
        </thead>

        <tbody>
            @php
                $globalIteration = ($controlDocuments->currentPage() - 1) * $controlDocuments->perPage() + 1;
            @endphp

            @forelse ($controlDocuments as $mapping)
                @foreach ($mapping->files->filter(fn($f) => $f->marked_for_deletion_at > now()) as $file)
                    <tr class="border-b">

                        <!-- No -->
                        <td class="px-4 py-2">{{ $globalIteration++ }}</td>

                        <!-- Document Name -->
                        <td class="px-4 py-2">
                            {{ $mapping->document?->name ?? '-' }}
                        </td>

                        <!-- Inactive File -->
                        <td class="px-4 py-2">
                            {{ $file->original_name }}
                        </td>

                        <!-- Department -->
                        <td class="px-4 py-2">
                            {{ $mapping->department?->name ?? '-' }}
                        </td>

                        <!-- Hard Delete On -->
                        <td class="px-4 py-2">
                            <span class="text-red-600 font-semibold">
                            {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('Y-m-d') }}
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="px-4 py-2 text-center">
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
