@php
    $level = $level ?? 0;
@endphp

@forelse ($documents as $mapping)
    <tr>
        <td>
            <input type="checkbox" class="row-checkbox form-check-input"
                value="{{ $mapping->id }}">
        </td>
        <td>{{ $loop->iteration }}</td>
        <td>
            {{-- Indentasi sesuai level --}}
            @for ($i = 0; $i < $level; $i++)
                &nbsp;&nbsp;&nbsp;&nbsp;
                <i class="bi bi-arrow-return-right text-muted"></i>
            @endfor
            {{ $mapping->document?->name ?? '-' }}
        </td>
        <td>{{ $mapping->document_number }}</td>
        <td>{{ $mapping->partNumber?->part_number ?? '-' }}</td>
        <td>
            @if ($mapping->file_path)
                <button type="button"
                    class="btn btn-outline-primary btn-sm view-file-btn"
                    data-bs-toggle="modal" data-bs-target="#viewFileModal"
                    data-file="{{ asset('storage/' . $mapping->file_path) }}">
                    <i class="bi bi-file-earmark-text me-1"></i> View
                </button>
            @endif
        </td>

        <td>{{ $mapping->department?->name ?? '-' }}</td>
        <td>{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}</td>
        <td>{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>

        <td>
            @switch($mapping->status?->name)
                @case('Approved')
                    <span class="badge bg-success">Approved</span>
                    @break
                @case('Rejected')
                    <span class="badge bg-danger">Rejected</span>
                    @break
                @case('Need Review')
                    <span class="badge bg-warning text-dark">Need Review</span>
                    @break
                @default
                    <span class="badge bg-secondary">{{ $mapping->status?->name ?? '-' }}</span>
            @endswitch
        </td>

        <td>{{ $mapping->version }}</td>
        <td>{{ $mapping->notes }}</td>
        <td>{{ $mapping->user?->name ?? '-' }}</td>

        <td class="text-nowrap">
            @if (auth()->user()->role->name == 'Admin')
                {{-- Edit --}}
                <button class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal{{ $mapping->id }}"
                    data-bs-title="Edit Metadata">
                    <i class="bi bi-pencil-square"></i>
                </button>

                {{-- Delete --}}
                <form action="{{ route('document-review.destroy', $mapping->id) }}"
                    method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                        data-bs-title="Delete Document">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

                {{-- Revisi --}}
                <button class="btn btn-outline-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#reviseModal{{ $mapping->id }}"
                    data-bs-title="Revise Document">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>

                {{-- Approve / Reject --}}
                @if ($mapping->status?->name == 'Need Review')
                    <button type="button" class="btn btn-outline-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#approveModal{{ $mapping->id }}"
                        data-bs-title="Approve Document">
                        <i class="bi bi-check2-circle"></i>
                    </button>

                    <form action="{{ route('document-review.reject', $mapping->id) }}"
                        method="POST" class="d-inline reject-form">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                            data-bs-title="Reject Document">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </form>
                @elseif ($mapping->status?->name == 'Approved')
                    <button type="button" class="btn btn-outline-success btn-sm" disabled>
                        <i class="bi bi-check2-all"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="bi bi-x-circle"></i>
                    </button>
                @elseif ($mapping->status?->name == 'Rejected')
                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="bi bi-check2-circle"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" disabled>
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                @else
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="bi bi-slash-circle"></i>
                    </button>
                @endif
            @else
                <button class="btn btn-outline-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#reviseModal{{ $mapping->id }}">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            @endif
        </td>
    </tr>

    {{-- Rekursif: render children --}}
    {{-- @if ($mapping->children && $mapping->children->isNotEmpty())
        @include('contents.document-review.partials.nested-rows', [
            'documents' => $mapping->children,
            'level' => $level + 1
        ])
    @endif --}}
@empty
    <tr>
        <td colspan="13" class="text-center py-4 text-muted">
            <i class="bi bi-search me-2"></i>
            No documents found for this tab.
        </td>
    </tr>
@endforelse
