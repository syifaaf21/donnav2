@php
    // Tentukan apakah ini permintaan AJAX atau pemuatan halaman normal
    $isAjax = request()->ajax();
@endphp

@if (!$isAjax)
    @extends('layouts.app')

    @section('title', 'Archived Document')

    @section('content')
        <div class="mx-auto px-4 py-6">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li><a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center"><i
                                class="bi bi-house-door me-1"></i> Dashboard</a></li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Archive</li>
                </ol>
            </nav>
            <p class="text-gray-500 mb-6">
                This list shows outdated files that have been replaced and are within the 1-year retention period before
                permanent hard deletion.
            </p>

            {{-- Table Wrapper untuk diisi oleh AJAX --}}
            <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100" id="archivedTableWrapper">

                {{-- Search --}}
                <div id="archivedSearchBar" class="flex justify-end w-full mb-4">
                    <form id="archiveFilterForm" class="flex flex-col items-end w-auto space-y-1">
                        <div class="relative w-96">
                            <input type="text" name="search" id="archiveSearchInput"
                                class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
                    placeholder-transparent focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                                placeholder="Search archived documents..." value="{{ request('search') }}">

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

                {{-- TABLE CONTENT YANG AKAN DIREPLACE AJAX --}}
                <div id="archivedTableContent">
                    @include('contents.document-control.partials.archived-table')
                </div>

            </div>

        </div> {{-- Penutup class="container" --}}
    @endsection

    @push('scripts')
        <script>
            // Logika JavaScript AJAX Search dan Pagination (sama seperti sebelumnya)
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('archiveSearchInput');
                const tableWrapper = document.getElementById('archivedTableWrapper');
                const routeUrl = "{{ route('archive.archived') }}";
                let timeout = null;

                if (searchInput && tableWrapper) {
                    searchInput.addEventListener('keyup', function() {
                        clearTimeout(timeout);

                        timeout = setTimeout(function() {
                            fetchData(searchInput.value);
                        }, 300);
                    });
                }

                function fetchData(searchQuery) {
                    // Tampilkan loading state
                    document.getElementById('archivedTableContent').innerHTML =
                        '<div class="text-center py-8 text-blue-500"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Searching...</p></div>';


                    fetch(routeUrl + '?search=' + encodeURIComponent(searchQuery), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Ganti konten tabel dengan hasil AJAX
                            document.getElementById('archivedTableContent').innerHTML = html;

                            // Render ulang Feather Icons jika ada
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            tableWrapper.innerHTML =
                                '<div class="text-center py-8 text-red-500"><p>Failed to load data. Please try again.</p></div>';
                        });
                }

                // --- Handle Pagination Links via AJAX ---
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.pagination a')) {
                        e.preventDefault();
                        const url = e.target.closest('.pagination a').href;
                        const urlObj = new URL(url);

                        const currentSearch = searchInput ? searchInput.value : '';
                        urlObj.searchParams.set('search', currentSearch);

                        fetchDataByUrl(urlObj.toString());
                    }
                });

                function fetchDataByUrl(url) {
                    document.getElementById('archivedTableContent').innerHTML =
                        '<div class="text-center py-8 text-blue-500"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading page...</p></div>';


                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('archivedTableContent').innerHTML = html;
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching pagination data:', error);
                        });
                }
            });
        </script>
    @endpush
@endif
