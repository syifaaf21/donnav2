<tr data-id="{{ $node['document']->id }}" data-parent-id="{{ $parentId ?? 0 }}">
    <td>
        @if($node['children'] && count($node['children']) > 0)
            <button type="button" class="btn btn-sm toggle-children">
                <i class="bi bi-plus-square"></i>
            </button>
        @endif
        {{ $node['document']->name }}
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->document_number ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->partNumber->part_number ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>
                    @if($mapping->file_path)
                        <button type="button" class="btn btn-outline-primary btn-sm view-file-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#viewFileModal"
                            data-file="{{ asset('storage/' . $mapping->file_path) }}">
                            <i class="bi bi-file-earmark-text me-1"></i> View
                        </button>
                    @endif
                </div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->department->name ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->reminder_date ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->deadline ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->status->name ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->version ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->notes ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        @if($node['mappings']->count())
            @foreach($node['mappings'] as $mapping)
                <div>{{ $mapping->user->name ?? '-' }}</div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        {{-- Actions --}}
        @foreach($node['mappings'] as $mapping)
            @include('contents.document-review.row-actions', ['mapping' => $mapping])
        @endforeach
    </td>
</tr>

@if($node['children'])
    @foreach($node['children'] as $child)
        @include('contents.document-review.row', ['node' => $child, 'parentId' => $node['document']->id])
    @endforeach
@endif
