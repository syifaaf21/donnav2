@if (auth()->user()->role->name == 'Admin')
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $mapping->id }}">
        <i class="bi bi-pencil-square"></i>
    </button>
    <form action="{{ route('document-review.destroy', $mapping->id) }}" method="POST" class="d-inline delete-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash"></i>
        </button>
    </form>
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}">
        <i class="bi bi-arrow-clockwise"></i>
    </button>
    @if ($mapping->status->name == 'Need Review')
        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#approveModal{{ $mapping->id }}">
            <i class="bi bi-check2-circle"></i>
        </button>
        <form action="{{ route('document-review.reject', $mapping->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    @endif
@else
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
        data-bs-target="#reviseModal{{ $mapping->id }}">
        <i class="bi bi-arrow-clockwise"></i>
    </button>
@endif
