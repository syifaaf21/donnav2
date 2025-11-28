@extends('layouts.app')

@section('title', 'Archive')

@section('content')
    <div class="mx-auto px-4 py-6">
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
        <p class="text-gray-500 mb-6">
            This list shows outdated files that have been replaced and are within the 1-year retention period before
            permanent hard deletion.
        </p>

        {{-- Search --}}
        <div id="archivedSearchBar" class="flex justify-end w-full mb-4">
            <form id="archiveFilterForm" class="flex flex-col items-end w-auto space-y-1">
                <div class="relative w-96">
                    <input type="text" name="search" id="archiveSearchInput"
                        class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
                        placeholder="Search
                        archived documents..." value="{{ request('search') }}">
                    <label for="archiveSearchInput"
                        class="absolute left-4 transition-all duration-150 bg-white px-1 rounded text-gray-400 text-sm
                        {{ request('search')
                            ? '-top-3 text-xs text-sky-600'
                            : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm
                                                                                                                                                                                                                                                                        peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                        Search archived documents...
                    </label>
                </div>
            </form>
        </div>
        <div class="bg-white p-6 rounded shadow">

            {{-- TAB --}}
            <ul class="flex border-b mb-4">
                <li class="mr-4">
                    <a href="#tab-control" class="tab-link active px-4 py-2 inline-block font-semibold text-blue-600">
                        Document Control
                    </a>
                </li>
                <li>
                    <a href="#tab-review" class="tab-link px-4 py-2 inline-block font-semibold text-gray-600">
                        Document Review
                    </a>
                </li>
            </ul>

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
        // Tab click handling and hash persistence
        function activateTab(hash) {
            const target = hash || window.location.hash || '#tab-control';
            // Normalize
            const normalized = target.startsWith('#') ? target : `#${target}`;

            // Toggle active/inactive styles explicitly to avoid Tailwind utility conflicts
            document.querySelectorAll('.tab-link').forEach(t => {
                t.classList.remove('active', 'text-blue-600');
                // ensure inactive tab uses gray color
                t.classList.add('text-gray-600');
            });

            const activeLink = document.querySelector(`.tab-link[href="${normalized}"]`);
            if (activeLink) {
                activeLink.classList.add('active', 'text-blue-600');
                activeLink.classList.remove('text-gray-600');
            }

            // Show/hide contents
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            const content = document.querySelector(normalized);
            if (content) content.classList.remove('hidden');
        }

        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                // update hash without scrolling
                history.replaceState(null, '', href);
                activateTab(href);
            });
        });

        // On load, activate based on hash (or default)
        activateTab(window.location.hash || '#tab-control');

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
                        activateTab(window.location.hash || '#tab-control');
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
    </script>

@endsection
