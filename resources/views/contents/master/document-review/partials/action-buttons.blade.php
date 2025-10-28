<div class="flex items-center gap-2">
    {{-- File Viewer Dropdown --}}
    <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-paperclip"></i> Files
            <i class="bi bi-caret-down-fill small"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-files shadow-sm">
            @forelse ($mapping->files as $file)
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

                <li class="px-2 py-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-truncate small fw-medium" style="max-width: 160px;">
                            {{ $file->file_name ?? basename($file->file_path) }}
                        </span>
                        <div class="d-flex gap-2">
                            @if ($viewerUrl)
                                <button type="button" class="btn btn-sm btn-outline-primary view-file-btn"
                                    data-bs-toggle="modal" data-bs-target="#viewFileModal"
                                    data-file="{{ $viewerUrl }}" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Preview Not Supported">
                                    <i class="bi bi-ban"></i>
                                </button>
                            @endif
                            <a href="{{ $fileUrl }}" class="btn btn-sm btn-outline-success" download
                                title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                </li>

                @if (!$loop->last)
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                @endif
            @empty
                <li><span class="dropdown-item-text text-muted small">No files available</span></li>
            @endforelse
        </ul>
    </div>

    {{-- Admin Actions --}}
    @if (auth()->user()->role->name == 'Admin')
        {{-- Edit --}}
        <button title="Edit Metadata"
            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition"
            data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
            <i data-feather="edit-2" class="w-4 h-4"></i>
        </button>

        {{-- Delete --}}
        <form action="{{ route('master.document-review.destroy', $mapping->id) }}" method="POST"
            class="delete-form d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete Document"
                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition">
                <i data-feather="trash-2" class="w-4 h-4"></i>
            </button>
        </form>
    @endif
</div>
