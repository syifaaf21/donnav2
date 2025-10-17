@php
    $rowId = 'row-' . $document->id;
    $parentClass = $parent_id ? 'child-of-' . $parent_id : '';
@endphp

<tr id="{{ $rowId }}" class="{{ $parentClass }} {{ $parent_id ? 'hidden' : '' }}">
    {{-- Document Name & toggle --}}
    <td class="px-4 py-2">
        <div class="flex items-center" style="margin-left: {{ $level * 20 }}px">
            @if ($document->children->isNotEmpty())
                <button type="button" class="toggle-children mr-1" data-target="child-of-{{ $document->id }}">
                    <i data-feather="chevron-right" class="w-4 h-4 transition-transform"></i>
                </button>
            @else
                <span class="w-4 h-4 inline-block mr-1"></span>
            @endif

            @if ($document->children->isNotEmpty())
                <i data-feather="folder" class="w-4 h-4 text-yellow-500 mr-1"></i>
            @else
                <i data-feather="file-text" class="w-4 h-4 text-blue-500 mr-1"></i>
            @endif

            <span class="text-gray-800 font-medium">{{ $document->name }}</span>
        </div>
    </td>

    {{-- Type dengan ucwords --}}
    <td class="px-4 py-2 text-gray-500">
        {{ ucwords($document->type) }}
    </td>

    {{-- Actions --}}
    <td class="px-4 py-2">
        <div class="flex gap-2">
            <button type="button" class="text-blue-500 hover:text-blue-700" data-bs-toggle="modal"
                data-bs-target="#editDocumentModal-{{ $document->id }}">
                <i data-feather="edit-2" class="w-4 h-4"></i>
            </button>

            <form method="POST" action="{{ route('master.hierarchy.destroy', $document->id) }}"
                class="inline delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

@if ($document->children->isNotEmpty())
    @foreach ($document->children as $child)
        @include('contents.master.hierarchy.partials.tree-node', [
            'document' => $child,
            'level' => $level + 1,
            'number' => $number,
            'parent_id' => $document->id,
        ])
    @endforeach
@endif
