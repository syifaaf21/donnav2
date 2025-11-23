@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    {{-- Main Container: Modern background and clean padding --}}
    <div class="p-8 bg-gray-50 min-h-screen space-y-8">

        {{-- Flash Message --}}
        <x-flash-message />

        {{-- Breadcrumb (Tetap Sama) --}}
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-reset flex items-center space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="text-blue-600 hover:text-blue-800 transition-colors duration-150 flex items-center gap-1">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 font-semibold flex items-center gap-1">
                    Document Review
                </li>
            </ol>
        </nav>

        {{-- Plant Tabs Container --}}
        <div class="overflow-x-auto">
            {{-- ✅ Border dihilangkan dari UL agar tab aktif bisa menempel sempurna --}}
            <ul class="flex space-x-1 border-b border-gray-200" role="tablist">
                @php $lastTab = old('last_selected_plant') ?? null; @endphp
                @foreach ($groupedByPlant as $plant => $documentsByCode)
                    @php
                        $slug = \Illuminate\Support\Str::slug($plant);
                        $isActive = ($loop->first && !$lastTab) || ($lastTab && $lastTab === $slug);
                    @endphp
                    <li role="presentation" class="flex-shrink-0">
                        <button
                            class="nav-link relative px-5 py-3 text-sm tracking-wide transition-all duration-300 ease-in-out border-b-0
                            {{-- Class Logic (Matches JS for Active/Inactive) --}}
                            {{ $isActive
                                ? 'bg-white text-blue-700 border border-gray-200 border-b-0 -mb-px font-bold rounded-t-lg shadow-md' // ✅ shadow-md untuk tab aktif
                                : 'text-gray-600 hover:text-blue-700 hover:bg-gray-100 bg-gray-50 border-b border-gray-200' }}"
                            // ✅ hover dan bg-gray-50 id="tab-{{ $slug }}" data-bs-toggle="tab"
                            data-bs-target="#tab-content-{{ $slug }}" type="button" role="tab"
                            aria-controls="tab-content-{{ $slug }}"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}">

                            {{-- ✅ Ikon Dihapus --}}
                            {{ ucfirst($plant) }}

                            <span
                                class="ml-2 text-xs font-semibold 
                                {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}">
                                ({{ $documentsByCode->count() }})
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Tab Content Container --}}
        <div
            class="tab-content bg-white border border-gray-200 rounded-b-xl rounded-tr-xl shadow-xl p-6 transition-all duration-300">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                @php
                    $slug = \Illuminate\Support\Str::slug($plant);
                    $isActive = ($loop->first && !$lastTab) || ($lastTab && $lastTab === $slug);

                    $plantRoots = $documents
                        ->where('parent_id', null)
                        ->filter(fn($doc) => $documentsByCode->has($doc->code));
                @endphp
                <div id="tab-content-{{ $slug }}" role="tabpanel" aria-labelledby="tab-{{ $slug }}"
                    class="tab-pane fade {{ $isActive ? 'show active' : '' }}">
                    <ul class="space-y-2">
                        @foreach ($plantRoots as $document)
                            @include('contents.document-review.partials.tree-node', [
                                'document' => $document,
                                'plant' => $plant,
                            ])
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ✅ REVISI AKTIF: Border 4 sisi (top, left, right) dan shadow-md
        const ACTIVE_TAB_CLASSES = [
            "bg-white", "text-blue-700", "border", "border-gray-200",
            "border-b-0", "-mb-px", "font-bold", "shadow-md"
        ];
        // ✅ REVISI NON-AKTIF: Lebih sederhana, background abu-abu, hover biru
        const INACTIVE_TAB_CLASSES = [
            "text-gray-600", "hover:text-blue-700", "hover:bg-gray-100",
            "bg-gray-50"
        ];
        const ACTIVE_TEXT_COUNT_CLASSES = ["text-blue-600", "font-bold"];
        const INACTIVE_TEXT_COUNT_CLASSES = ["text-gray-500", "font-semibold"];


        function updateTabState(button, content, isActive) {
            const spanCount = button.querySelector("span.ml-2");

            // 1. Update Button Classes
            button.classList.toggle("active", isActive);

            // Mengganti kelas aktif dan tidak aktif
            ACTIVE_TAB_CLASSES.forEach(cls => button.classList.toggle(cls, isActive));
            INACTIVE_TAB_CLASSES.forEach(cls => button.classList.toggle(cls, !isActive));

            // Khusus untuk border-bottom pada tab non-aktif
            if (isActive) {
                // Hapus border-b pada tab aktif agar menempel ke content container
                button.classList.remove("border-b");
            } else {
                // Tambahkan border-b pada tab non-aktif
                button.classList.add("border-b");
            }

            button.setAttribute("aria-selected", isActive ? "true" : "false");

            // 2. Update Content Pane Classes
            if (content) {
                content.classList.toggle("show", isActive);
                content.classList.toggle("active", isActive);
            }

            // 3. Update Count Span Classes
            if (spanCount) {
                ACTIVE_TEXT_COUNT_CLASSES.forEach(cls => spanCount.classList.toggle(cls, isActive));
                INACTIVE_TEXT_COUNT_CLASSES.forEach(cls => spanCount.classList.toggle(cls, !isActive));
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Expand/collapse tree node (Logika ini sudah benar)
            document.querySelectorAll(".tree-toggle").forEach(btn => {
                btn.addEventListener("click", function() {
                    const childList = this.closest('li').querySelector("ul");
                    if (childList) childList.classList.toggle("hidden");
                    this.querySelector("i").classList.toggle("rotate-90");
                });
            });

            const tabButtons = document.querySelectorAll(".nav-link");
            const tabPanes = document.querySelectorAll(".tab-pane");

            // --- 1. RESTORE LAST SELECTED TAB ---
            const lastTab = localStorage.getItem("last_selected_plant");
            let isRestored = false;

            if (lastTab) {
                const lastPlantButton = document.getElementById("tab-" + lastTab);
                const lastPlantContent = document.getElementById("tab-content-" + lastTab);

                if (lastPlantButton && lastPlantContent) {
                    // Reset semua tab ke status non-aktif
                    tabButtons.forEach(btn => {
                        const content = document.querySelector(btn.dataset.bsTarget);
                        updateTabState(btn, content, false);
                    });

                    // Aktifkan tab yang tersimpan di localStorage
                    updateTabState(lastPlantButton, lastPlantContent, true);
                    isRestored = true;
                }
            }

            // Jika tidak ada localStorage yang valid, aktifkan tab pertama (yang sudah ditangani Blade)
            if (!isRestored) {
                const firstButton = tabButtons[0];
                const firstContent = document.querySelector(firstButton.dataset.bsTarget);
                if (firstButton) {
                    updateTabState(firstButton, firstContent, true);
                }
            }


            // --- 2. UPDATE LOCALSTORAGE ON TAB CHANGE ---
            tabButtons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault(); // Mencegah default jika menggunakan data-bs-toggle native

                    const plantSlug = this.id.replace("tab-", "");
                    localStorage.setItem("last_selected_plant", plantSlug);

                    // Reset semua tab
                    tabButtons.forEach(b => {
                        const content = document.querySelector(b.dataset.bsTarget);
                        updateTabState(b, content, false);
                    });

                    // Aktifkan tab yang baru diklik
                    const content = document.querySelector(this.dataset.bsTarget);
                    updateTabState(this, content, true);
                });
            });
        });
    </script>
@endpush
