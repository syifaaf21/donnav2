<div class="flex items-center gap-1">
    @if ($mapping->files->count())
        @foreach ($mapping->files as $file)
            <button type="button" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm"
                data-bs-toggle="modal" data-bs-target="#viewFileModal"
                data-file="{{ asset('storage/' . $file->file_path) }}">
                <i class="bi bi-file-earmark-text me-1"></i>
            </button>
        @endforeach
    @else
        <span class="text-gray-400">-</span>
    @endif
    @if (auth()->user()->role->name == 'Admin')
        {{-- Edit --}}
        <button title="Edit Metadata" class="p-1.5 rounded text-blue-600 hover:bg-blue-50" data-bs-toggle="modal"
            data-bs-target="#editModal{{ $mapping->id }}">
            <i class="bi bi-pencil-square"></i>
        </button>

        {{-- Delete --}}
        <form action="{{ route('master.document-review.destroy', $mapping->id) }}" method="POST"
            class="inline delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete Document" class="p-1.5 rounded text-red-600 hover:bg-red-50">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</div>
