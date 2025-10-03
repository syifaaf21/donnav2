@if (auth()->user()->role->name == 'Admin')
    {{-- Edit --}}
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}"
        data-bs-title="Edit Metadata">
        <i class="bi bi-pencil-square"></i>
    </button>


    {{-- Delete --}}
    <form action="{{ route('document-review.destroy', $mapping->id) }}" method="POST" class="d-inline delete-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-title="Delete Document">
            <i class="bi bi-trash"></i>
        </button>
    </form>


    {{-- Revisi --}}
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}" data-bs-title="Revise Document">
        <i class="bi bi-arrow-clockwise"></i>
    </button>


    {{-- Approve / Reject --}}
    @if ($mapping->status->name == 'Need Review')
        {{-- Tombol Approve buka modal --}}
        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#approveModal{{ $mapping->id }}" data-bs-title="Approve Document">
            <i class="bi bi-check2-circle"></i>
        </button>

        {{-- Tombol Reject tetap form --}}
        <form action="{{ route('document-review.reject', $mapping->id) }}" method="POST" class="d-inline reject-form">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-title="Reject Document">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    @elseif ($mapping->status->name == 'Approved')
        {{-- Sudah Approved --}}
        <button type="button" class="btn btn-outline-success btn-sm" disabled>
            <i class="bi bi-check2-all"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-x-circle"></i>
        </button>
    @elseif ($mapping->status->name == 'Rejected')
        {{-- Sudah Rejected --}}
        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-check2-circle"></i>
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm" disabled>
            <i class="bi bi-x-circle-fill"></i>
        </button>
    @else
        {{-- Status lain --}}
        <button class="btn btn-outline-secondary btn-sm" disabled>
            <i class="bi bi-slash-circle"></i>
        </button>
    @endif
@else
    {{-- User hanya revisi --}}
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}">
        <i class="bi bi-arrow-clockwise"></i>
    </button>
@endif
