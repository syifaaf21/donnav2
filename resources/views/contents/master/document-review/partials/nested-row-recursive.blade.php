@php
    $children = $documents->filter(fn($doc) => $doc->document->parent_id === $mapping->document->id && $doc->part_number_id == $mapping->part_number_id);
@endphp

<tr x-data="{ open: false }" class="border-b hover:bg-gray-50">
    <td class="px-2 py-2" style="padding-left: {{ ($depth ?? 0) * 20 }}px;">
        @if ($children->count() > 0)
            <button @click="open = !open" class="text-gray-500 hover:text-blue-500 transition">
                <i :class="open ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
            </button>
        @endif
    </td>

    <td class="px-3 py-2">{{ $numbering ?? '-' }}</td>
    <td class="px-3 py-2">{{ $mapping->document->name }}</td>
    <td class="px-3 py-2">{{ $mapping->document_number }}</td>
    <td class="px-3 py-2">{{ $mapping->partNumber->part_number ?? '-' }}</td>

    {{-- File --}}
    <td class="px-3 py-2">
        @if ($mapping->files->count())
            @foreach ($mapping->files as $file)
                <button type="button"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm"
                    data-bs-toggle="modal" data-bs-target="#viewFileModal"
                    data-file="{{ asset('storage/' . $file->file_path) }}">
                    <i class="bi bi-file-earmark-text me-1"></i>
                </button>
            @endforeach
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>

    <td class="px-3 py-2">{{ $mapping->department->name ?? '-' }}</td>
    <td class="px-3 py-2">{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}</td>
    <td class="px-3 py-2">{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>
    <td class="px-3 py-2 text-nowrap">
        @include('contents.master.document-review.partials.action-buttons', ['mapping' => $mapping])
    </td>
</tr>

{{-- Children --}}
@if ($children->count())
    <tr x-show="open" x-transition>
        <td colspan="14" class="p-0">
            <table class="w-full border-l border-gray-200">
                @foreach ($children as $index => $child)
                    @include('contents.master.document-review.partials.nested-row-recursive', [
                        'mapping' => $child,
                        'documents' => $documents,
                        'loopIndex' => $loopIndex . '-' . $loop->iteration,
                        'rowNumber' => '-',
                        'depth' => ($depth ?? 0) + 1,
                        'numbering' => ($numbering ?? '') . '.' . $loop->iteration,
                    ])
                @endforeach
            </table>
        </td>
    </tr>
@endif
