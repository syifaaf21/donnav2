{{-- File View & Download: untuk semua user --}}
@if ($mapping->files->count())
    <div class="btn-group dropup">
        <button type="button"
            class="btn btn-outline-secondary btn-sm dropdown-toggle"
            data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
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
                        : 'https://docs.google.com/gview?url=' . urlencode($fileUrl) . '&embedded=true';
                @endphp

                {{-- File name --}}
                <li class="px-3 small text-muted text-truncate" style="max-width: 200px;">
                    {{ $file->file_name ?? basename($file->file_path) }}
                </li>

                {{-- View file --}}
                <li>
                    @if ($isPdf || $isOffice)
                        <button type="button"
                            class="dropdown-item view-file-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#viewFileModal"
                            data-file="{{ $viewerUrl }}">
                            <i class="bi bi-eye me-1"></i> View
                        </button>
                    @else
                        <span class="dropdown-item text-muted disabled">
                            <i class="bi bi-slash-circle me-1"></i> Preview Not Supported
                        </span>
                    @endif
                </li>

                {{-- Download file --}}
                <li>
                    <a href="{{ $fileUrl }}" class="dropdown-item" download>
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>
            @endforeach
        </ul>
    </div>
@else
    <span class="text-muted">No File</span>
@endif


{{-- Admin-only actions --}}
@if (auth()->user()->role->name == 'Admin')
    {{-- Edit --}}
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
        data-bs-target="#editModal{{ $mapping->id }}" data-bs-title="Edit Metadata">
        <i class="bi bi-pencil-square"></i>
    </button>

    {{-- Delete --}}
    <form action="{{ route('master.document-control.destroy', $mapping->id) }}"
        method="POST" class="d-inline delete-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-title="Delete Document">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif




    {{-- Tombol aksi lain di sini, misal edit, delete, dll --}}

    {{-- Revisi --}}
    {{-- <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}" data-bs-title="Update Document">
        <i class="bi bi-arrow-clockwise"></i>
    </button> --}}


    {{-- Approve / Reject --}}
    {{-- @if ($mapping->status->name == 'Need Review') --}}
    {{-- Tombol Approve buka modal --}}
    {{-- <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#approveModal{{ $mapping->id }}" data-bs-title="Approve Document">
            <i class="bi bi-check2-circle"></i>
        </button> --}}

    {{-- Tombol Reject tetap form --}}
    {{-- <form action="{{ route('master.document-review.reject', $mapping->id) }}" method="POST"
            class="d-inline reject-form">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-title="Reject Document">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    @elseif ($mapping->status->name == 'Approved') --}}
    {{-- Sudah Approved --}}
    {{-- <button type="button" class="btn btn-outline-success btn-sm" disabled>
            <i class="bi bi-check2-all"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-x-circle"></i>
        </button>
    @elseif ($mapping->status->name == 'Rejected') --}}
    {{-- Sudah Rejected --}}
    {{-- <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-check2-circle"></i>
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm" disabled>
            <i class="bi bi-x-circle-fill"></i>
        </button>
    @else --}}
    {{-- Status lain --}}
    {{-- <button class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-slash-circle"></i>
        </button>
    @endif
@else --}}
    {{-- User hanya revisi --}}
    {{-- <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}">
        <i class="bi bi-arrow-clockwise"></i>
    </button> --}}
