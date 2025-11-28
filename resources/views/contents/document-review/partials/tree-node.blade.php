<li class="ml-{{ $level ?? 0 * 4 }}">
    <div class="flex items-center space-x-2">
        {{-- Toggle Button --}}
        @if ($document->childrenRecursive->count())
            <button type="button" class="tree-toggle focus:outline-none">
                <i class="bi bi-caret-right-fill transition-transform"></i>
            </button>
        @else
            <span class="w-4 inline-block"></span>
        @endif

        {{-- Icon berdasarkan level --}}
        @php
            // Tentukan icon dan warna sesuai level
            $iconClass = match ($level ?? 0) {
                0 => 'bi-folder text-yellow-500', // parent (outline)
                1 => 'bi-folder text-green-500', // child
                2 => 'bi-folder text-blue-500', // grandchild
                default => 'bi-file-text text-gray-400', // default file
            };

            // Jika punya children, tetap pakai folder, kalau tidak, pakai file
            $finalIconClass = $document->childrenRecursive->count() ? $iconClass : 'bi-file-text text-gray-400';
        @endphp

        <i class="bi {{ $finalIconClass }} text-lg"></i>


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
    @if ($document->childrenRecursive->count())
        <ul class="ml-6 mt-1 hidden space-y-1">
            @foreach ($document->childrenRecursive as $child)
                @include('contents.document-review.partials.tree-node', [
                    'document' => $child,
                    'plant' => $plant,
                    'level' => ($level ?? 0) + 1,
                ])
            @endforeach
        </ul>
    @endif
</li>
