<li class="list-group-item">
    @php
        $filePath = optional($mapping->files->first())->file_path;
    @endphp

    <a href="javascript:void(0)" class="view-file-btn d-block text-decoration-none"
        data-document-name="{{ $mapping->document->name }}" data-document-number="{{ $mapping->document_number }}"
        data-department="{{ optional($mapping->department)->name }}" data-status="{{ optional($mapping->status)->name }}"
        data-updated-at="{{ $mapping->updated_at->format('Y-m-d H:i') }}" data-user="{{ optional($mapping->user)->name }}"
        data-product="{{ optional($mapping->partNumber->product)->name }}"
        data-model="{{ optional($mapping->partNumber->productModel)->name }}"
        data-process="{{ $mapping->partNumber->process }}" data-notes="{{ $mapping->notes }}"
        data-file="{{ optional($mapping->files->first())->file_path ? asset('storage/' . $mapping->files->first()->file_path) : '' }}"
        data-mapping-id="{{ $mapping->id }}">
        <i class="bi bi-file-earmark-text"></i>
        {{ $mapping->document->name }}
    </a>
    
    @php
        $children = $allDocuments->filter(fn($d) => optional($d->document)->parent_id === $mapping->document_id);
    @endphp

    @if ($children->isNotEmpty())
        <ul class="list-group list-group-flush ms-3 mt-1">
            @foreach ($children as $child)
                @include('contents.document-review.partials.tree-node', [
                    'mapping' => $child,
                    'allDocuments' => $allDocuments,
                ])
            @endforeach
        </ul>
    @endif
</li>
