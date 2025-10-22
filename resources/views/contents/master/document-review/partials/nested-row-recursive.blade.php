@php
    $doc = $mapping->document;
    $docId = $doc->id ?? 'unknown';
    $partKey = $mapping->part_number_id;
    $parentDocId = $doc->parent_id; // parent document id (nullable)

    // Apakah ini baris anak (depth > 0)
    $isChild = ($depth ?? 0) > 0;

    // Jika anak, tambahkan class yang menunjuk ke parent + part key (unique per part)
    $rowClass = $isChild ? 'child-row children-of-' . ($parentDocId ?? 'root') . '-' . $partKey . ' d-none' : '';
@endphp

@php
    // cari anak langsung (document parent_id == $docId) dan untuk part_number yang sama
    $children = $documents->filter(function ($d) use ($docId, $partKey) {
        return optional($d->document)->parent_id === $docId && $d->part_number_id == $partKey;
    });
@endphp

<tr class="{{ $rowClass }} align-middle">
    <!-- Expand Button -->
    <td class="px-2 py-2">
        @if ($children->count() > 0)
            {{-- data-target harus unik: children-of-{docId}-{partKey} --}}
            <button type="button" class="btn btn-sm btn-link toggle-children"
                data-target="children-of-{{ $docId }}-{{ $partKey }}">
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

{{-- Render children (rekursif). Anak sudah diberi class d-none default, dan tombol parent akan toggle class tersebut --}}
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
