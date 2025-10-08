@php
    $children = $documents->filter(function ($doc) use ($mapping) {
        return $doc->document->parent_id === $mapping->document->id
            && $doc->part_number_id == $mapping->part_number_id;
    });
@endphp

<!-- Parent Row -->
<tr x-data="{ open: false }" class="border-b hover:bg-gray-50">
    <!-- Expand Button -->
    <td class="px-2 py-2">
        @if ($children->count() > 0)
            <button @click="open = !open" class="text-gray-500 hover:text-blue-500 transition">
                <i :class="open ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
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
    <td class="px-3 py-2">{{ $mapping->document->name }}</td>
    <td class="px-3 py-2">{{ $mapping->document_number }}</td>
    <td class="px-3 py-2">{{ $mapping->partNumber->part_number ?? '-' }}</td>
    <td class="px-3 py-2">{{ $mapping->department->name ?? '-' }}</td>
    <td class="px-3 py-2 text-nowrap">
        @include('contents.master.document-review.partials.action-buttons', ['mapping' => $mapping])
    </td>
</tr>

<!-- Child Rows -->
@if ($children->count())
    @foreach ($children as $index => $child)
        <tr x-show="open" x-transition>
            @include('contents.master.document-review.partials.nested-row-recursive', [
                'mapping' => $child,
                'documents' => $documents,
                'loopIndex' => ($loopIndex ?? 'row') . '-' . $loop->iteration,
                'depth' => ($depth ?? 0) + 1,
                'numbering' => ($numbering ?? '') . '.' . $loop->iteration,
            ])
        </tr>
    @endforeach
@endif
