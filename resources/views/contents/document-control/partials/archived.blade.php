@php
    // Tentukan apakah ini permintaan AJAX atau pemuatan halaman normal
    $isAjax = request()->ajax();
@endphp

@if (!$isAjax)
    @extends('layouts.app')

    @section('title', 'Archived Document')

    @section('content')
        <div class="container mx-auto px-4 py-6">

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

            <div class="flex justify-end w-full mb-4">
                <form id="archiveFilterForm" class="flex flex-col items-end w-auto space-y-1">
                    <div class="relative w-96">
                        <input type="text" name="search" id="archiveSearchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
                           placeholder-transparent focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Search archived documents..." value="{{ request('search') }}">

                        <label for="archiveSearchInput"
                            class="absolute left-4 transition-all duration-150 bg-white px-1 rounded
                        text-gray-400 text-sm
                        {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Search archived documents...
                        </label>
                    </div>
                </form>
            </div>

            {{-- Table Wrapper untuk diisi oleh AJAX --}}
            <div id="archivedTableWrapper">
    @endif

    {{-- KONTEN TABEL DAN PAGINASI (DIMUAT ULANG OLEH AJAX) --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow p-4">
        <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Document Name</th>
                    <th class="px-4 py-2">Archived File</th>
                    <th class="px-4 py-2">Department</th>
                    <th class="px-4 py-2">Replacement Date</th>
                    <th class="px-4 py-2">Hard Delete On</th>
                    <th class="px-4 py-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $globalIteration = ($documentsMapping->currentPage() - 1) * $documentsMapping->perPage() + 1;
                @endphp
                @forelse ($documentsMapping as $mapping)
                    @php
                        $archivedFiles = $mapping->files->filter(
                            fn($f) => !$f->is_active && $f->marked_for_deletion_at > now(),
                        );
                    @endphp

                    @if ($archivedFiles->isNotEmpty())
                        @foreach ($archivedFiles as $file)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-4 py-2 text-gray-600 text-sm">
                                    {{ $globalIteration++ }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 text-sm">
                                    <strong>{{ $mapping->document->name ?? 'N/A' }}</strong>
                                    @if ($mapping->status?->name === 'Obsolete')
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-800">OBSOLETE</span>
                                    @else
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">REPLACED</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-600 text-sm">
                                    {{ $file->original_name }} (Old Version)
                                </td>
                                <td class="px-4 py-2 text-gray-600 text-sm">{{ $mapping->department->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-gray-600 text-sm text-nowrap">
                                    {{ $file->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-2 text-gray-600 text-sm text-nowrap">
                                    <span class="text-red-600 font-semibold">
                                        {{ \Carbon\Carbon::parse($file->marked_for_deletion_at)->format('d F Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 transition" title="View File">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @elseif ($mapping->status?->name === 'Obsolete')
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-gray-600 text-sm">{{ $globalIteration++ }}</td>
                            <td class="px-4 py-2 text-gray-700 text-sm">
                                <strong>{{ $mapping->document->name ?? 'N/A' }}</strong>
                                <span
                                    class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-800">OBSOLETE</span>
                            </td>
                            <td colspan="3"><em class="text-muted text-gray-500">No active archived files remaining for
                                    this document.</em></td>
                            <td class="px-4 py-2 text-center">-</td>
                            <td class="px-4 py-2 text-center">-</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-box-open fa-2x text-gray-400 mb-3"></i>
                            <p class="mb-0 text-gray-600">No documents or files are currently archived.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $documentsMapping->links() }}
    </div>

    @if (!$isAjax)
        </div> {{-- Penutup id="archivedTableWrapper" --}}
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
                    tableWrapper.innerHTML =
                        '<div class="text-center py-8 text-blue-500"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Searching...</p></div>';

                    fetch(routeUrl + '?search=' + encodeURIComponent(searchQuery), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Ganti konten tabel dengan hasil AJAX
                            tableWrapper.innerHTML = html;

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
                    tableWrapper.innerHTML =
                        '<div class="text-center py-8 text-blue-500"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading page...</p></div>';

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableWrapper.innerHTML = html;
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
