@php
    $doc = $mapping->document;
    $docId = $doc->id ?? 'unknown';
    $partKey = $mapping->part_number_id;
    $parentId = $mapping->parent_id; // Parent dari mapping, relasi nyata antar dokumen (mapping.parent_id)

    // Apakah ini baris anak (depth > 0)
    $isChild = ($depth ?? 0) > 0;

    // Class untuk row anak, harus sama dengan data-target tombol expand
    // Format: children-of-{parentMappingId} (gunakan parent_id dari mapping)
    $rowClass = $isChild ? "child-row children-of-{$parentId} d-none" : '';

    // Cari children berdasarkan mapping.parent_id = current mapping id
    $children = $documents->filter(function ($d) use ($mapping) {
        return $d->parent_id == $mapping->id;
    });

@endphp

{{-- @if ($children->count() === 0)
    <tr>
        <td colspan="7" style="color: red; padding-left: {{ ($depth ?? 0) * 20 }}px;">
            No children found for parent ID: {{ $mapping->id }}
        </td>
    </tr>
@endif --}}

<tr class="{{ $rowClass }} align-middle">
    <!-- Expand Button -->
    <td class="px-2 py-2">
        @if ($children->count() > 0)
            <button type="button" class="btn btn-sm btn-link toggle-children"
                data-target="children-of-{{ $mapping->id }}">
                <i class="bi bi-plus-square"></i>
            </button>
        @else
            <span class="ms-4"></span>
        @endif
    </td>

    <!-- Numbering -->
    <td class="px-3 py-2">
        <span style="padding-left: {{ ($depth ?? 0) * 20 }}px;">
            {{ $numbering ?? '-' }}
        </span>
    </td>

    <!-- Other Columns -->
    <td class="px-3 py-2">{{ $doc->code ?? '-' }}</td>
    <td class="px-3 py-2">{{ $mapping->document_number ?? '-' }}</td>
    <td class="px-3 py-2">{{ optional($mapping->partNumber)->part_number ?? '-' }}</td>
    <td class="px-3 py-2">{{ optional($mapping->partNumber)->product->name ?? '-' }}</td>
    <td class="px-3 py-2 text-nowrap">
        @include('contents.master.document-review.partials.action-buttons', ['mapping' => $mapping])
    </td>
</tr>

@if ($children->count())
    @foreach ($children as $index => $child)
        @include('contents.master.document-review.partials.nested-row-recursive', [
            'mapping' => $child,
            'documents' => $documents,
            'depth' => ($depth ?? 0) + 1,
            'numbering' => ($numbering ?? '') . '.' . ($loop->iteration ?? $index + 1),
        ])
    @endforeach
@endif
