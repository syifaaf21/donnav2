<li class="ml-{{ $level ?? 0 * 4 }}">
    <div class="flex items-center space-x-2 py-1 px-2 rounded group hover:bg-gray-50 focus-within:ring-2 focus-within:ring-sky-200">
        {{-- Toggle Button --}}
        @if ($document->childrenRecursive->count())
            <button type="button"
                class="tree-toggle flex items-center justify-center w-6 h-6 rounded focus:outline-none focus:ring-2 focus:ring-sky-300"
                aria-expanded="true" aria-label="Toggle children">
                <i class="bi bi-caret-right-fill transition-transform duration-200 rotate-90"></i>
            </button>
        @else
            <span class="w-6 inline-block"></span>
        @endif

        {{-- Icon berdasarkan level --}}
        @php
            // Tentukan icon dan warna sesuai level
            $iconClass = match ($level ?? 0) {
                0 => 'bi-folder text-yellow-500',
                1 => 'bi-folder text-green-500',
                2 => 'bi-folder text-blue-500',
                default => 'bi-file-text text-gray-400',
            };

            $finalIconClass = $document->childrenRecursive->count() ? $iconClass : 'bi-file-text text-gray-400';
        @endphp

        <i class="bi {{ $finalIconClass }} text-lg"></i>

        {{-- Document Name --}}
        <a href="{{ route('document-review.showFolder', [
            'plant' => $plant,
            'docCode' => base64_encode($document->code),
        ]) }}"
            class="text-gray-800 no-underline hover:no-underline font-medium truncate flex-1">
            {{ $document->name }}
            <span class="text-xs text-gray-500 ml-2">
                ({{ $document->allMappingsForPlant($plant)->count() }} docs)
            </span>
        </a>
    </div>

    {{-- Recursive Children — default expanded --}}
    @if ($document->childrenRecursive->count())
        <ul class="ml-6 mt-1 space-y-1">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.tree-toggle').forEach(btn => {
                if (btn.dataset._treeInit) return;
                btn.dataset._treeInit = '1';

                // ensure initial aria state and icon rotation (default expanded)
                if (!btn.hasAttribute('aria-expanded')) btn.setAttribute('aria-expanded', 'true');
                const icon = btn.querySelector('i');
                if (btn.getAttribute('aria-expanded') === 'true' && icon && !icon.classList.contains('rotate-90')) {
                    icon.classList.add('rotate-90');
                }

                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const parentDiv = this.closest('div');
                    const childrenUl = parentDiv ? parentDiv.nextElementSibling : null;
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    const icon = this.querySelector('i');

                    if (childrenUl && childrenUl.tagName === 'UL') {
                        if (isExpanded) {
                            childrenUl.classList.add('hidden');
                            this.setAttribute('aria-expanded', 'false');
                            if (icon) icon.classList.remove('rotate-90');
                        } else {
                            childrenUl.classList.remove('hidden');
                            this.setAttribute('aria-expanded', 'true');
                            if (icon) icon.classList.add('rotate-90');
                        }
                    }
                });

                btn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        });
    </script>
@endpush
