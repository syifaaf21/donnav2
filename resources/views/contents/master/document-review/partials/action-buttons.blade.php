<div class="flex items-center gap-2">
    {{-- File Viewer --}}
    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"
        data-bs-display="static" aria-expanded="false">
        <i class="bi bi-paperclip"></i> Files
    </button>

    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($mapping->files as $file)
            @php
                $fileUrl = asset('storage/' . $file->file_path);
                $extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                $isPdf = $extension === 'pdf';
                $isOffice = in_array($extension, ['doc', 'docx', 'xls', 'xlsx']);
                $viewerUrl = $isPdf
                    ? $fileUrl
                    : ($isOffice
                        ? 'https://docs.google.com/gview?url=' . urlencode($fileUrl) . '&embedded=true'
                        : null);
            @endphp

            <li>
                <span class="dropdown-item-text small text-muted text-truncate" style="max-width: 250px;">
                    {{ $file->file_name ?? basename($file->file_path) }}
                </span>
            </li>

            <li>
                @if ($viewerUrl)
                    <button type="button" class="dropdown-item view-file-btn" data-bs-toggle="modal"
                        data-bs-target="#viewFileModal" data-file="{{ $viewerUrl }}">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                @else
                    <span class="dropdown-item text-muted disabled">Preview Not Supported</span>
                @endif
            </li>

            <li>
                <a href="{{ $fileUrl }}" class="dropdown-item" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </li>

            @if (!$loop->last)
                <li>
                    <hr class="dropdown-divider">
                </li>
            @endif
        @endforeach
    </ul>

    {{-- Admin Actions --}}
    @if (auth()->user()->role->name == 'Admin')
        {{-- Edit --}}
        <button title="Edit Metadata"
            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition"
            data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
            <i class="bi bi-pencil-square text-base"></i>
        </button>

        {{-- Delete --}}
        <form action="{{ route('master.document-review.destroy', $mapping->id) }}" method="POST" class="delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete Document"
                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition">
                <i class="bi bi-trash text-base"></i>
            </button>
        </form>
    @endif
</div>
