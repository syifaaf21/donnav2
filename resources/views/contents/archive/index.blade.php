@extends('layouts.app')

@section('title', 'Archive')

@section('content')
    <div class="mx-auto px-4 py-6">
        <div class="py-6 mt-4 text-white">
            <div class="mb-4 text-white">
                <h1 class="fw-bold ">Archive</h1>
                <p style="font-size: 0.9rem;">
                    Manage and review archived documents efficiently.
                </p>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-2" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Archive</li>
            </ol>
        </nav>

        <div>
            <div class="flex items-center w-full">
                {{-- TAB (keep button markup & classes unchanged) --}}
                <div id="typeTab" role="tablist" aria-label="Archive tabs" class="flex ml-4 mt-10 -mb-px space-x-2">
                    <button id="tab-btn-control" role="tab" aria-controls="tab-control" aria-selected="true"
                        data-target="#tab-control"
                        class="tab-btn px-4 py-2 font-semibold rounded-t-lg bg-white text-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        Document Control
                    </button>

                    <button id="tab-btn-review" role="tab" aria-controls="tab-review" aria-selected="false"
                        data-target="#tab-review"
                        class="tab-btn px-4 py-2 font-semibold rounded-t-lg bg-transparent text-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        Document Review
                    </button>
                </div>

                {{-- Search (ke kanan dengan ml-auto sehingga sejajar dengan tab) --}}
                <div id="archivedSearchBar" class="flex items-center ml-auto">
                    <form id="archiveFilterForm" action="{{ route('archive.search') }}"
                        class="flex flex-col items-end w-auto space-y-1">
                        <div class="relative w-96">
                            <input type="text" name="search" id="archiveSearchInput"
                                class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
                         focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                                placeholder="Search archived documents..." value="{{ request('search') }}">

                            <label for="archiveSearchInput"
                                class="absolute left-4 transition-all duration-150 bg-white px-1 rounded text-gray-400 text-sm
                                    top-2.5
                                    peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5
                                    peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600
                                    floating-label">
                                Search archived documents...
                            </label>

                            <!-- Clear Button -->
                            @if (request('search'))
                                <a href="{{ route('archive.index') }}"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5
                                        rounded-lg text-gray-400
                                        hover:text-red-600 transition">
                                    <i data-feather="x" class="w-5 h-5"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- CONTENT --}}
            <div id="tab-control" class="tab-content block">
                @include('contents.archive.partials.control-archive', [
                    'controlDocuments' => $controlDocuments,
                ])
            </div>

            <div id="tab-review" class="tab-content hidden">
                @include('contents.archive.partials.review-archive', [
                    'reviewDocuments' => $reviewDocuments,
                ])
            </div>
        </div>

        <!-- SLIDER FILE VIEWER (like Notion) -->
        <div id="fileViewerOverlay" class="fixed inset-0 bg-black/30 z-[9998] hidden" onclick="closeFileViewer()"></div>

        <div id="fileViewerPanel"
            class="fixed right-0 top-0 h-full w-[70%] max-w-4xl bg-white shadow-2xl z-[9999]
            translate-x-full transition-transform duration-300 ease-in-out flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">File Viewer</h2>
                <button onclick="closeFileViewer()" class="text-gray-500 hover:text-gray-700 text-xl">
                    &times;
                </button>
            </div>

            <!-- File content -->
            <div class="flex-1 overflow-auto">
                <iframe id="fileViewerIframe" src="" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    {{-- SCRIPT TABS --}}
    <script>
        // Tabs: accessible behavior, keyboard nav, hash + localStorage persistence
        function setActiveTabButton(btn, pushHistory = true) {
            if (!btn) return;
            // update buttons
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.setAttribute('aria-selected', 'false');
                b.classList.remove('text-blue-600', 'bg-white');
                b.classList.add('text-gray-600', 'bg-transparent');
            });

            btn.setAttribute('aria-selected', 'true');
            btn.classList.remove('text-gray-600', 'bg-transparent');
            btn.classList.add('text-blue-600', 'bg-white');

            // update contents
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            const target = document.querySelector(btn.dataset.target);
            if (target) target.classList.remove('hidden');

            // update URL hash (without scrolling)
            if (pushHistory) {
                history.replaceState(null, '', btn.dataset.target);
            }

            // persist selection as fallback
            try {
                localStorage.setItem('archive_active_tab', btn.dataset.target);
            } catch (e) {}
        }

        function activateTabByHash(hash) {
            if (!hash) return null;
            const normalized = hash.startsWith('#') ? hash : `#${hash}`;
            const btn = document.querySelector(`.tab-btn[data-target="${normalized}"]`);
            if (btn) setActiveTabButton(btn, false);
            return btn;
        }

        function initTabInterface() {
            const buttons = Array.from(document.querySelectorAll('.tab-btn'));

            buttons.forEach((btn, idx) => {
                btn.addEventListener('click', (e) => {
                    setActiveTabButton(e.currentTarget);
                });

                btn.addEventListener('keydown', (e) => {
                    if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(e.key)) return;
                    e.preventDefault();
                    let newIdx = idx;
                    if (e.key === 'ArrowRight') newIdx = (idx + 1) % buttons.length;
                    if (e.key === 'ArrowLeft') newIdx = (idx - 1 + buttons.length) % buttons.length;
                    if (e.key === 'Home') newIdx = 0;
                    if (e.key === 'End') newIdx = buttons.length - 1;
                    buttons[newIdx].focus();
                    setActiveTabButton(buttons[newIdx]);
                });
            });

            // initial activation: hash -> saved -> default
            const hash = window.location.hash;
            const saved = localStorage.getItem('archive_active_tab');
            if (hash && document.querySelector(`.tab-btn[data-target="${hash}"]`)) {
                activateTabByHash(hash);
            } else if (saved && document.querySelector(`.tab-btn[data-target="${saved}"]`)) {
                activateTabByHash(saved);
            } else {
                // default
                const first = document.querySelector('.tab-btn[data-target="#tab-control"]') || buttons[0];
                if (first) setActiveTabButton(first, false);
            }
        }

        // initialize tabs when script runs
        initTabInterface();

        // === AJAX SEARCH ===
        const searchInput = document.getElementById('archiveSearchInput');
        const archiveFilterForm = document.getElementById('archiveFilterForm');

        searchInput.addEventListener('input', debounce(function() {
            performSearch();
        }, 300));

        function performSearch() {
            const query = searchInput.value.trim();

            // Show loading state on both tabs
            const controlContent = document.getElementById('tab-control');
            const reviewContent = document.getElementById('tab-review');

            if (controlContent) controlContent.style.opacity = '0.6';
            if (controlContent) controlContent.style.pointerEvents = 'none';
            if (reviewContent) reviewContent.style.opacity = '0.6';
            if (reviewContent) reviewContent.style.pointerEvents = 'none';

            // AJAX request - get results for both tabs
            fetch(`{{ route('archive.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update control tab
                        if (controlContent && data.control.html) {
                            controlContent.innerHTML = data.control.html;
                        }

                        // Update review tab
                        if (reviewContent && data.review.html) {
                            reviewContent.innerHTML = data.review.html;
                        }

                        // Ensure currently active tab remains visible after content replace
                        // re-run tab init only if tab buttons have been re-rendered; otherwise keep current selection
                        initTabInterface();
                    }

                    // Remove loading state
                    if (controlContent) {
                        controlContent.style.opacity = '1';
                        controlContent.style.pointerEvents = 'auto';
                    }
                    if (reviewContent) {
                        reviewContent.style.opacity = '1';
                        reviewContent.style.pointerEvents = 'auto';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);

                    // Remove loading state on error
                    if (controlContent) {
                        controlContent.style.opacity = '1';
                        controlContent.style.pointerEvents = 'auto';
                    }
                    if (reviewContent) {
                        reviewContent.style.opacity = '1';
                        reviewContent.style.pointerEvents = 'auto';
                    }
                });
        }

        // Debounce utility
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function openFileViewer(url) {
            const overlay = document.getElementById("fileViewerOverlay");
            const panel = document.getElementById("fileViewerPanel");
            const iframe = document.getElementById("fileViewerIframe");

            // Set file URL
            iframe.src = url;

            // Show overlay
            overlay.classList.remove("hidden");

            // Slide panel IN
            panel.classList.remove("translate-x-full");
        }

        function closeFileViewer() {
            const overlay = document.getElementById("fileViewerOverlay");
            const panel = document.getElementById("fileViewerPanel");
            const iframe = document.getElementById("fileViewerIframe");

            // Slide panel OUT
            panel.classList.add("translate-x-full");

            // Hide overlay after animation
            setTimeout(() => {
                overlay.classList.add("hidden");
                iframe.src = ""; // Stop PDF from continuing to load
            }, 300);
        }

        // ... function performSearch() di atas ...

        // TAMBAHAN: Handle klik pagination agar tetap via AJAX (mencegah reload)
        document.addEventListener('click', function(e) {
            // Cek apakah yang diklik adalah link pagination di dalam tab-content
            const link = e.target.closest('.tab-content .pagination a') || e.target.closest(
                'nav[role="navigation"] a');

            if (link) {
                e.preventDefault(); // Stop reload halaman

                const url = link.getAttribute('href');
                if (!url) return;

                // Ambil parameter query dari URL pagination tersebut
                const urlObj = new URL(url);
                const params = new URLSearchParams(urlObj.search);

                // Pastikan search query yang ada di input juga terbawa (jika user sedang searching)
                const currentSearch = document.getElementById('archiveSearchInput').value;
                if (currentSearch) {
                    params.set('q',
                        currentSearch
                    ); // Controller Anda pakai 'q' di method search, tapi 'search' di form HTML. Sesuaikan.
                }

                // Tentukan kita sedang di tab mana untuk loading state
                const isControl = !!link.closest('#tab-control');
                const contentDiv = isControl ? document.getElementById('tab-control') : document.getElementById(
                    'tab-review');

                // UI Loading
                if (contentDiv) {
                    contentDiv.style.opacity = '0.5';
                    contentDiv.style.pointerEvents = 'none';
                }

                // Panggil endpoint search controller (bukan index) karena kita butuh return JSON
                // Kita ganti base URL nya ke route('archive.search')
                const searchUrl = `{{ route('archive.search') }}?${params.toString()}`;

                fetch(searchUrl)
                    .then(response => response.json())
                    .then(data => {
                        const controlContent = document.getElementById('tab-control');
                        const reviewContent = document.getElementById('tab-review');

                        // Update content sesuai response
                        if (data.control && data.control.html) {
                            controlContent.innerHTML = data.control.html;
                        }
                        if (data.review && data.review.html) {
                            reviewContent.innerHTML = data.review.html;
                        }

                        // Restore opacity
                        if (controlContent) {
                            controlContent.style.opacity = '1';
                            controlContent.style.pointerEvents = 'auto';
                        }
                        if (reviewContent) {
                            reviewContent.style.opacity = '1';
                            reviewContent.style.pointerEvents = 'auto';
                        }

                        // Scroll ke atas tab agar user sadar halaman berubah
                        const activeBtn = document.querySelector('.tab-btn[aria-selected="true"]');
                        if (activeBtn) activeBtn.scrollIntoView({
                            behavior: 'smooth'
                        });
                    })
                    .catch(err => console.error(err));
            }
        });
    </script>

@endsection

<style>
    /* Base styling for tab buttons */
    #typeTab .tab-btn {
        color: #4B5563;
        padding: 0.5rem 1rem;
        transition: all 200ms;
        background: transparent;
        border-top: 2px solid transparent !important;
    }

    /* Hover style */
    #typeTab .tab-btn:hover {
        box-shadow: 2px 4px 12px rgba(148, 148, 148, 0.1);
    }

    /* ACTIVE TAB: Gradient + Shadow
       Apply when JS sets aria-selected="true" or when an 'active' class exists.
       Use higher specificity and !important for background to override utility classes like bg-white.
    */
    #typeTab .tab-btn[aria-selected="true"],
    #typeTab .tab-btn.active {
        background-image: linear-gradient(to bottom, #bfdbfe 0%, #ffffff 100%) !important;
        background-color: transparent !important;
        color: #2563eb !important;
        /* text-blue-600 */
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
        font-weight: 600;
    }

    /* Ensure focus ring remains visible */
    #typeTab .tab-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
</style>
