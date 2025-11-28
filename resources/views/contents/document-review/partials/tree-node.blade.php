<li class="ml-{{ $level ?? 0 * 4 }}">
    <div class="flex items-center space-x-2">
        {{-- Toggle Button --}}
        @if($document->childrenRecursive->count())
            <button type="button" class="tree-toggle focus:outline-none">
                <i class="bi bi-caret-right-fill transition-transform"></i>
            </button>
        @else
            <span class="w-4 inline-block"></span>
        @endif

        {{-- Icon berdasarkan level --}}
        @php
            $iconClass = match($level ?? 0) {
                0 => 'bi-folder-fill text-yellow-300', // parent
                1 => 'bi-folder-fill text-green-300',  // child
                2 => 'bi-folder-fill text-blue-300',   // grandchild
                default => 'bi-file-text-fill text-gray-400',
            };
        @endphp
        <i class="bi {{ $document->childrenRecursive->count() ? $iconClass : 'bi-file-text-fill text-gray-400' }}"></i>

        {{-- Document Name --}}
        <a href="{{ route('document-review.showFolder', [
                'plant' => $plant,
                'docCode' => base64_encode($document->code),
            ]) }}"
           class="text-gray-800 hover:text-blue-600 font-medium">
            {{ $document->name }}
        </a>

        {{-- Document count --}}
        <span class="text-xs text-gray-500">
            ({{ $document->allMappingsForPlant($plant)->count() }} docs)
        </span>
    </div>

    {{-- Recursive Children --}}
    @if($document->childrenRecursive->count())
        <ul class="ml-6 mt-1 hidden space-y-1">
            @foreach($document->childrenRecursive as $child)
                @include('contents.document-review.partials.tree-node', [
                    'document' => $child,
                    'plant' => $plant,
                    'level' => ($level ?? 0) + 1
                ])
            @endforeach
        </ul>
    @endif
</li>
