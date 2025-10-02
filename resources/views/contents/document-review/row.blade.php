@php
    // pastikan children aman (jika controller belum attach children, ini akan jadi collection kosong)
    $children = $mapping->children ?? collect();
@endphp

<tr data-id="{{ $mapping->id }}" data-parent-id="{{ $mapping->document->parent_id ?? 0 }}">
    {{-- No (hanya tampil jika rowIndex diberikan dari loop top-level) --}}
    <td>{{ $rowIndex ?? '' }}</td>

    {{-- Nama dokumen dengan indentasi sesuai level --}}
    <td style="padding-left: {{ ($level ?? 0) * 20 }}px;">
        @if($children->isNotEmpty())
            <button class="btn btn-sm btn-link p-0 toggle-children" type="button">
                <i class="bi bi-plus-square"></i>
            </button>
        @endif
        {{ $mapping->document->name }}
    </td>

    <td>{{ $mapping->document_number }}</td>
    <td>{{ $mapping->partNumber->part_number ?? '-' }}</td>

    <td>
        @if ($mapping->file_path)
            <button type="button"
                class="btn btn-outline-primary btn-sm view-file-btn"
                data-bs-toggle="modal"
                data-bs-target="#viewFileModal"
                data-file="{{ asset('storage/' . $mapping->file_path) }}">
                <i class="bi bi-file-earmark-text me-1"></i> View
            </button>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    <td>{{ $mapping->department->name ?? '-' }}</td>
    <td>{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}</td>
    <td>{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>

    <td>
        @php
            $statusName = $mapping->status->name ?? '-';
            $badgeClass = match($statusName) {
                'Approved' => 'bg-success',
                'Rejected' => 'bg-danger',
                'Need Review' => 'bg-warning text-dark',
                default => 'bg-secondary'
            };
        @endphp
        <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
    </td>

    <td>{{ $mapping->version }}</td>
    <td>{{ $mapping->notes }}</td>
    <td>{{ $mapping->user->name ?? '-' }}</td>

    <td class="text-nowrap">
        @include('contents.document-review.row-actions', ['mapping' => $mapping])
    </td>
</tr>

{{-- Rekursif: tampilkan children jika ada --}}
@if($children->isNotEmpty())
    @foreach($children as $child)
        @include('contents.document-review.partials.row', [
            'mapping' => $child,
            'level' => ($level ?? 0) + 1,
            'rowIndex' => null  // child tidak mewarisi nomor top-level
        ])
    @endforeach
@endif
