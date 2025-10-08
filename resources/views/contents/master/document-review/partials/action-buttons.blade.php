<div class="flex items-center gap-2">
    {{-- File Viewer --}}
    @if ($mapping->files->count())
        @foreach ($mapping->files as $file)
            <button type="button" class="btn btn-link text-sky-600 p-0 m-0" data-bs-toggle="modal"
                data-bs-target="#viewFileModal" data-file-url="{{ asset('storage/' . $file->file_path) }}" title="View File">
                <i class="bi bi-file-earmark-text fs-5"></i>
            </button>
        @endforeach
    @else
        <span class="text-gray-400 text-sm">-</span>
    @endif

    {{-- Admin Actions --}}
    @if (auth()->user()->role->name == 'Admin')
        {{-- Edit Button --}}
        <button title="Edit Metadata"
            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition"
            data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
            <i class="bi bi-pencil-square text-base"></i>
        </button>

        {{-- Delete Button --}}
        <form action="{{ route('master.document-review.destroy', $mapping->id) }}" method="POST"
            class="inline delete-form" onsubmit="return confirm('Are you sure you want to delete this document?');">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete Document"
                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition">
                <i class="bi bi-trash text-base"></i>
            </button>
        </form>
    @endif
</div>
