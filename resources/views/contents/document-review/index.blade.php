@extends('layouts.app')
@section('title', 'Document Review')
@section('subtitle', 'Review and manage documents across different plants.')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Document Review</li>
        </ol>
    </nav>
@endsection

@section('content')
    {{-- Main Container: Modern background and clean padding --}}
    <div class="p-6 space-y-6 bg-white rounded-lg shadow-md">
        {{-- Flash Message --}}
        <x-flash-message />
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Document Review</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Review and manage documents across different plants. Select a plant tab to view its document
                        hierarchy.
                    </p>
                </div>
            </div>

            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">Document Review</li>
                </ol>
            </nav>
        </div> --}}

        {{-- Plant Tabs Container --}}
        <div>
            {{-- Tabs wrapper: subtle bg and rounded top to visually separate from content --}}
            <div class="inline-flex items-center space-x-2">
                <ul class="flex -mb-px" role="tablist" aria-label="Plants">
                    @php $lastTab = old('last_selected_plant') ?? null; @endphp
                    @foreach ($groupedByPlant as $plant => $documentsByCode)
                        @php
                            $slug = \Illuminate\Support\Str::slug($plant);
                            $isActive = ($loop->first && !$lastTab) || ($lastTab && $lastTab === $slug);
                        @endphp
                        <li role="presentation" class="flex-shrink-0">
                            <button id="tab-{{ $slug }}" type="button" role="tab"
                                aria-controls="tab-content-{{ $slug }}"
                                aria-selected="{{ $isActive ? 'true' : 'false' }}" title="{{ ucfirst($plant) }}"
                                class="nav-link relative px-4 py-2 rounded-t-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1
                {{ $isActive
                    ? 'bg-gradient-to-b from-blue-200 to-white text-gray-700 -mb-px font-bold'
                    : 'text-gray-700 hover:text-gray-700 hover:border-t hover:border-gray-200' }}"
                                data-bs-toggle="tab" data-bs-target="#tab-content-{{ $slug }}">

                                {{-- Plant name: truncate to avoid overflow on small screens --}}
                                <span class="inline-block max-w-[12rem] truncate align-middle">
                                    {{ ucfirst($plant) }}
                                </span>

                                {{-- Count badge: kept as span.ml-2 for JS compatibility --}}
                                <span
                                    class="ml-2 inline-flex items-center justify-center text-xs font-semibold px-2 py-0.5 rounded-full
                {{ $isActive ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500' }}">
                                    ({{ $documentsByCode->count() }})
                                </span>

                                {{-- Focus ring helper for keyboard users (visual only) --}}
                                <span class="sr-only">{{ $isActive ? 'Active' : 'Inactive' }} plant tab</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Tab Content Container --}}
            <div class="tab-content bg-white shadow rounded-lg transition-all duration-300 min-h-[10rem] p-4">
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
    </div>
@endsection

@push('scripts')
    <style>
        #folderLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(17,24,39,0.72);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
            display: none;
        }
        .custom-loader-folder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }
        .loader-gradient-folder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(#0ea5e9 10%,#38bdf8 30%,#818cf8 60%,#0ea5e9 100%);
            animation: spin 1.1s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 32px 0 #0ea5e955;
        }
        .loader-dot-folder {
            width: 24px;
            height: 24px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 4px #0ea5e9, 0 0 16px 0 #38bdf8aa;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
        .loader-text-folder {
            background: linear-gradient(90deg, #0ea5e9, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-shadow: 0 4px 24px #0ea5e955;
            filter: drop-shadow(0 2px 8px #818cf855);
            text-align: center;
        }
    </style>
    <div id="folderLoadingOverlay">
        <div class="custom-loader-folder">
            <div class="loader-gradient-folder">
                <div class="loader-dot-folder"></div>
            </div>
            <div class="loader-text-folder">Loading...</div>
        </div>
    </div>
    <script>
        // ✅ REVISI AKTIF: Border 4 sisi (top, left, right) dan shadow-md
        const ACTIVE_TAB_CLASSES = [
            "bg-gradient-to-b", "from-blue-200", "to-white", "text-gray-700",
            "-mb-px", "font-bold", "shadow-top"
        ];
        // ✅ REVISI NON-AKTIF: Lebih sederhana, background abu-abu, hover biru
        const INACTIVE_TAB_CLASSES = [
            "text-gray-700", "hover:text-blue-700", "hover:border-x",
            "hover:border-gray-200"
        ];
        const ACTIVE_TEXT_COUNT_CLASSES = ["text-gray-700", "font-bold"];
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

            // Loading overlay saat klik folder (tree node)
            // Tangkap semua <a> yang mengarah ke folder (harus punya class folder-link atau data-folder-link)
            document.querySelectorAll('a.folder-link, a[data-folder-link], a[href*="/document-review/"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Hanya tampilkan loading jika link menuju folder (bukan # atau javascript:void(0))
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript')) {
                        const overlay = document.getElementById('folderLoadingOverlay');
                        if (overlay) overlay.style.display = 'flex';
                    }
                });
            });
        });
    </script>
@endpush

<style>
    /* Ensure active tab always shows the desired gradient and styles,
                       including when JS toggles aria-selected="true" */
    /* Default: reserve space for left/right borders so layout doesn't jump */
    .nav-link {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        transition: border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }

    /* Show blue borders and subtle shadow on hover */
    .nav-link:hover {
        border-left-color: #1d4ed8;
        /* blue-700 */
        border-right-color: #1d4ed8;
        color: #1d4ed8;
        box-shadow: 0 -4px 10px rgba(29, 78, 216, 0.1);
    }

    /* Active state: keep gradient, bold text and solid blue borders */
    .nav-link[aria-selected="true"] {
        background-image: linear-gradient(to bottom, #bfdbfe 0%, #ffffff 100%);
        background-repeat: no-repeat;
        color: #1d4ed8;
        /* blue-700 */
        font-weight: 700;
        border-left-color: #1d4ed8;
        border-right-color: #1d4ed8;
    }

    /* Count badge adjustments for active state */
    .nav-link[aria-selected="true"] .ml-2 {
        background-color: #eff6ff;
        /* blue-50 */
        color: #2563eb;
        /* blue-600 */
    }
</style>
