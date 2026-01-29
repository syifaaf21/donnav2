@extends('layouts.app')
@section('title', 'Master Document Review')
@section('subtitle', 'Manage document reviews')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-500 font-medium">Master</li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Document Review</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="mx-auto px-4 py-4" x-data="documentReviewTabs('{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}')">

        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2 text-white">
                    <h3 class="fw-bold">Document Review Master</h3>
                    <p class="text-sm" style="font-size: 0.85rem;">
                        Manage document reviews. Use the "Add Document Review" button to create new entries and the actions
                        column
                        to edit or delete existing records.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500 font-medium">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">Document Review</li>
                </ol>
            </nav>
        </div> --}}

        <div class="overflow-hidden">
            {{-- Tabs + Search + Filter --}}
            <div class="flex items-center px-4">
                {{-- Tabs (left) --}}
                <nav role="tablist" aria-label="Plant tabs" class="flex items-center flex-1 -mb-4 overflow-auto mt-6">
                    @foreach ($groupedByPlant as $plant => $documents)
                        @php
                            $slug = \Illuminate\Support\Str::slug($plant);
                        @endphp
                        <button type="button" @click="setActiveTab('{{ $slug }}')" role="tab"
                            aria-controls="tableContainer" :aria-selected="activeTab === '{{ $slug }}'"
                            :class="activeTab === '{{ $slug }}'
                                ?
                                'bg-gradient-to-b from-blue-200 to-white fw-semibold text-md text-gray-700 shadow-sm transition-shadow duration-200' :
                                'text-white hover:text-gray-700 transition-shadow duration-200'"
                            class="flex items-center gap-2 px-4 py-2 rounded-t-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1">
                            <span class="truncate max-w-[10rem]">{{ ucwords(strtolower($plant)) }}</span>
                            <span
                                class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-blue-100 text-blue-700">
                                {{ $totalDocumentsByPlant[$plant] ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </nav>

                {{-- Search + Filter (right) --}}
                <div class="flex items-center gap-2 ml-auto mt-2 pt-2">
                    {{-- Search Bar --}}
                    <form id="searchForm" method="GET" class="flex items-end w-auto">
                        <div class="relative w-96">
                            <input type="text" name="search" id="searchInput"
                                class="peer w-full rounded-xl border border-gray-200 bg-white0 px-4 py-2.5 text-sm text-gray-700
                                    focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm
                                    placeholder-transparent"
                                placeholder="Type to search..." />

                            <label for="searchInput"
                                class="absolute left-4 px-1 bg-white rounded transition-all duration-150
                                    pointer-events-none
                                    -top-3 text-xs text-sky-600
                                    peer-placeholder-shown:top-2.5
                                    peer-placeholder-shown:text-sm
                                    peer-placeholder-shown:text-gray-400
                                    peer-focus:-top-3
                                    peer-focus:text-xs
                                    peer-focus:text-sky-600">
                                Type to search...
                            </label>
                        </div>
                    </form>

                    {{-- Filter Icon --}}
                    <button type="button"
                        class="bg-white border border-gray-200 rounded-xl shadow p-2.5 hover:bg-gray-100 transition mb-3"
                        data-bs-toggle="modal" data-bs-target="#filterModal" aria-haspopup="dialog"
                        aria-controls="filterModal" title="Open filters">
                        <i data-feather="filter" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Filter Modal --}}
            <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 760px;">
                    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                        <form id="filterFormModal" method="GET" class="bg-white">

                            {{-- Modal Header --}}
                            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                                style="background-color: #f5f5f7;">
                                <h5 class="modal-title fw-semibold text-dark" id="filterModalLabel"
                                    style="font-family: 'Inter', sans-serif; font-size: 1.15rem;">
                                    <i class="bi bi-funnel me-2 text-primary"></i> Filter Documents
                                </h5>

                                {{-- Close button --}}
                                <button type="button"
                                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                                    data-bs-dismiss="modal" aria-label="Close"
                                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="modal-body p-5 bg-gray-50"
                                style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                                <div class="row g-4">
                                    {{-- Document Name --}}
                                    <div class="col-md-6">
                                        <label for="filterDocumentName" class="form-label fw-semibold">Document Name</label>
                                        <select name="document_name" id="filterDocumentName"
                                            class="tom-select form-select border-0 shadow-sm rounded-3 px-3 py-2 text-sm">
                                            <option value="">All Document Names</option>
                                            @foreach ($documentsMaster as $doc)
                                                <option value="{{ $doc->name }}"
                                                    {{ request('document_name') == $doc->name ? 'selected' : '' }}>
                                                    {{ $doc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Part Number --}}
                                    <div class="col-md-6">
                                        <label for="filterPartNumber" class="form-label fw-semibold">Part Number</label>
                                        <select name="part_number_id" id="filterPartNumber"
                                            class="tom-select form-select border-0 shadow-sm rounded-3 px-3 py-2 text-sm">
                                            <option value="">All Part Numbers</option>
                                            @foreach ($partNumbers as $pn)
                                                <option value="{{ $pn->id }}"
                                                    {{ request('part_number_id') == $pn->id ? 'selected' : '' }}>
                                                    {{ $pn->part_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Model --}}
                                    <div class="col-md-6">
                                        <label for="filterModel" class="form-label fw-semibold">Model</label>
                                        <select name="model_id" id="filterModel"
                                            class="tom-select form-select border-0 shadow-sm rounded-3 px-3 py-2 text-sm">
                                            <option value="">All Models</option>
                                            @foreach ($models as $model)
                                                <option value="{{ $model->id }}"
                                                    {{ request('model_id') == $model->id ? 'selected' : '' }}>
                                                    {{ $model->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Product --}}
                                    <div class="col-md-6">
                                        <label for="filterProduct" class="form-label fw-semibold">Product</label>
                                        <select name="product_id" id="filterProduct"
                                            class="tom-select form-select border-0 shadow-sm rounded-3 px-3 py-2 text-sm">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Process --}}
                                    <div class="col-md-6">
                                        <label for="filterProcess" class="form-label fw-semibold">Process</label>
                                        <select name="process_id" id="filterProcess"
                                            class="tom-select form-select border-0 shadow-sm rounded-3 px-3 py-2 text-sm">
                                            <option value="">All Processes</option>
                                            @foreach ($processes as $process)
                                                <option value="{{ $process->id }}"
                                                    {{ request('process_id') == $process->id ? 'selected' : '' }}>
                                                    {{ $process->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                                <button type="button" id="clearFilterModal"
                                    class="btn btn-link text-secondary fw-semibold px-4 py-2"
                                    style="text-decoration: none; transition: background-color 0.3s ease;">
                                    Clear
                                </button>
                                <button type="submit"
                                    class="btn px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="tableContainer" class="bg-white p-4 rounded-xl shadow-lg">
                {{-- Table per Plant --}}
                @foreach ($groupedByPlant as $plant => $documents)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <div x-show="activeTab === '{{ $slug }}'" x-transition class="flex flex-col">
                        <div class="mb-4 flex items-center justify-end">
                            {{-- Add Document Button --}}
                            <div class="flex items-center gap-2">
                                <button
                                    class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
                                    data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                                    <i class="bi bi-plus-circle me-2"></i> Add Document
                                </button>
                            </div>
                        </div>

                        {{-- add modal is included once globally (moved out of per-plant loop) --}}
                        {{-- @include moved to below, outside loop to avoid duplicate IDs / double-init --}}
                        <div
                            class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[60vh]">
                            <table class="min-w-full text-sm text-gray-700">
                                <thead class="sticky top-0 z-10"
                                    style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                                    <tr>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            No
                                        </th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Document Number</th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Part
                                            Number</th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Product</th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Model
                                        </th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Process</th>
                                        {{-- <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Reminder Date</th>
                                        <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Deadline</th> --}}
                                        <th class="px-4 py-3 border-r border-gray-200 text-center text-sm font-bold uppercase tracking-wider"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class=" divide-y divide-gray-200">
                                    @if ($documents->isEmpty())
                                        <tr class="hover:bg-gray-50 transition-all duration-150">
                                            <td colspan="9" class="text-center text-gray-400 py-6">
                                                <i data-feather="folder" class="mx-auto w-6 h-6 mb-2"></i>
                                                No Document found for this tab.
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($documents as $index => $doc)
                                            <tr class="hover:bg-gray-50 transition-all duration-150">
                                                <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->index + 1 }}
                                                </td>
                                                <td class="px-4 py-3 border-r border-gray-200 font-medium">
                                                    {{ $doc->document_number }}</td>
                                                <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ $doc->partNumber->pluck('part_number')->join(', ') ?: '-' }}
                                                </td>

                                                <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ $doc->product->pluck('name')->join(', ') ?: '-' }}
                                                </td>

                                                <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ $doc->productModel->pluck('name')->join(', ') ?: '-' }}</td>

                                                <td class="px-4 py-3 border-r border-gray-200 capitalize">
                                                    {{ $doc->process->pluck('name')->join(', ') ?: '-' }}
                                                </td>
                                                {{-- <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ $doc->reminder_date?->format('d M Y') ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 border-r border-gray-200">
                                                    {{ $doc->deadline?->format('d M Y') ?? '-' }}
                                                </td> --}}
                                                <td
                                                    class="px-4 py-3 border-r border-gray-200 flex space-x-2 whitespace-nowrap action-column">
                                                    {{-- FILE BUTTON AREA — fixed width --}}
                                                    {{-- <div
                                                        class="relative inline-block w-8 h-8 flex items-center justify-center">
                                                        @php $files = $doc->files->map(fn($f) => ['name' => $f->file_name ?? basename($f->file_path), 'url' => asset('storage/' . $f->file_path)])->toArray(); @endphp
                                                        @if (count($files) > 1)
                                                            <button id="viewFilesBtn-{{ $doc->id }}" type="button"
                                                                class="relative focus:outline-none text-gray-700 hover:text-blue-600 toggle-files-dropdown mr-2"
                                                                title="View File">
                                                                <i data-feather="file-text" class="w-6 h-6"></i>
                                                                <span
                                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                                    {{ count($files) }}
                                                                </span>
                                                            </button>
                                                            <div id="viewFilesDropdown-{{ $doc->id }}"
                                                                class="hidden absolute right-0 bottom-full mb-2 w-60 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] origin-bottom-right translate-x-2">
                                                                <div class="py-1 text-sm max-h-80 overflow-y-auto">
                                                                    @foreach ($files as $file)
                                                                        <button type="button"
                                                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate skip-style"
                                                                            data-file="{{ $file['url'] }}"
                                                                            title="View File">
                                                                            📄 {{ $file['name'] }}
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @elseif(count($files) === 1)
                                                            <button type="button"
                                                                class="inline-flex items-center gap-1 text-xs font-medium px-3 p-2 rounded-md border border-gray-200 bg-white hover:bg-gray-50 view-file-btn skip-style "
                                                                data-file="{{ $files[0]['url'] }}" title="View File">
                                                                <i data-feather="file-text" class="w-4 h-4"></i>
                                                            </button>
                                                        @endif
                                                    </div> --}}
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#editDocumentModal-{{ $doc->id }}"
                                                        class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200 skip-style"
                                                        title="Edit Document">
                                                        <i data-feather="edit" class="w-4 h-4"></i>
                                                    </button>
                                                    <form action="{{ route('master.document-review.destroy', $doc->id) }}"
                                                        method="POST" class="delete-form d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Delete Document"
                                                            class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2 skip-style">
                                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        {{-- PAGINATION --}}
                        @if ($documents instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="px-4 py-2">
                                {{ $documents->withQueryString()->links('vendor.pagination.tailwind') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Include Add Modal only once (prevent duplicate scripts / TomSelect init) --}}
        @if (in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']))
            @include('contents.master.document-review.partials.modal-add2')
        @endif
        +
        {{-- 📄 All Edit Modals --}}
        @if (in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']))
            @foreach ($groupedByPlant as $plant => $documents)
                @foreach ($documents as $doc)
                    @include('contents.master.document-review.partials.modal-edit2', [
                        'mapping' => $doc,
                    ])
                @endforeach
            @endforeach
        @endif

        {{-- 📄 Modal Fullscreen View File --}}
        <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content border-0 rounded-0 shadow-none">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-semibold">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i> Document Viewer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="fileViewer" src="" width="100%" height="100%"
                            style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // show/hide clear button for search input (UI-only, doesn't change logic)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearSearch');
            if (!searchInput || !clearBtn) return;

            function toggleClear() {
                if (searchInput.value && searchInput.value.trim().length) {
                    clearBtn.classList.remove('hidden');
                    clearBtn.setAttribute('aria-hidden', 'false');
                } else {
                    clearBtn.classList.add('hidden');
                    clearBtn.setAttribute('aria-hidden', 'true');
                }
            }

            searchInput.addEventListener('input', toggleClear);
            toggleClear();

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('keyup')); // reuse existing logic
                toggleClear();
            });
        });
    </script>
    <script>
        // Simpan data filter dari controller
        window.filterDataByPlant = @json($filterDataByPlant);

        // Tab manager
        function documentReviewTabs(defaultTab = 'body') {
            // Cek query parameter 'plant' dari URL TERLEBIH DAHULU
            const urlParams = new URLSearchParams(window.location.search);
            const plantParam = urlParams.get('plant');

            // Prioritas: plant dari URL > localStorage > parameter default > fallback 'body'
            const initialTab = plantParam || localStorage.getItem('activeTab') || defaultTab || 'body';

            // Simpan ke localStorage juga jika dari URL
            if (plantParam) {
                localStorage.setItem('activeTab', plantParam);
            }

            // Clean URL dari query parameter setelah membaca plant
            setTimeout(() => {
                if (urlParams.get('plant')) {
                    const cleanUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            }, 100);

            return {
                activeTab: initialTab,
                setActiveTab(tab) {
                    this.activeTab = tab;
                    localStorage.setItem('activeTab', tab);
                    updateFiltersForPlant(tab);
                }
            };
        }
        window.documentTabs = documentReviewTabs();

        // Data master fallback
        window.allPartNumbers = @json($partNumbers->map(fn($p) => ['id' => $p->id, 'label' => $p->part_number]));
        window.allModels = @json($models->map(fn($m) => ['id' => $m->id, 'label' => $m->name]));
        window.allProducts = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->name]));
        window.allProcesses = @json($processes->map(fn($p) => ['id' => $p->id, 'label' => $p->name]));

        function slugToPlant(slug) {
            return {
                "body": "Body",
                "unit": "Unit",
                "electric": "Electric",
                "other-manual-entry": "Other / Manual Entry"
            } [slug] || null;
        }

        // Update dropdown berdasarkan plant
        function updateFiltersForPlant(slug) {
            const plantName = slugToPlant(slug);
            if (!plantName) return;

            const data = window.filterDataByPlant[plantName] || {};

            if (plantName === "Other / Manual Entry") {
                updateTomSelect('#filterPartNumber', window.allPartNumbers);
                updateTomSelect('#filterModel', window.allModels);
                updateTomSelect('#filterProduct', window.allProducts);
                updateTomSelect('#filterProcess', window.allProcesses);
                return;
            }

            updateTomSelect('#filterPartNumber', data.part_numbers || window.allPartNumbers);
            updateTomSelect('#filterModel', data.models || window.allModels);
            updateTomSelect('#filterProduct', data.products || window.allProducts);
            updateTomSelect('#filterProcess', data.processes || window.allProcesses);
        }

        // Helper TomSelect
        function updateTomSelect(selector, items) {
            const el = document.querySelector(selector);
            if (!el) return;

            // If an instance exists, destroy it and clear reference to avoid double-init
            if (el.tomselect) {
                try {
                    el.tomselect.destroy();
                } catch (e) {
                    /* ignore */ }
                el.tomselect = null;
            }

            el.innerHTML = '<option value="">All</option>';

            (items || []).forEach(i => {
                const option = document.createElement('option');
                option.value = i.id ?? '';

                // Jika field process, ubah label menjadi Title Case
                if (selector === '#filterProcess' && i.label) {
                    option.textContent = i.label.split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                } else {
                    option.textContent = i.label ?? '';
                }

                el.appendChild(option);
            });

            new TomSelect(selector, {
                maxItems: 1,
                placeholder: "Select"
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi TomSelect
            const tsDocumentName = new TomSelect("#filterDocumentName", {
                maxItems: 1,
                placeholder: "Select Document Name"
            });
            const tsPartNumber = new TomSelect("#filterPartNumber", {
                maxItems: 1,
                placeholder: "Select Part Number"
            });
            const tsModel = new TomSelect("#filterModel", {
                maxItems: 1,
                placeholder: "Select Model"
            });
            const tsProduct = new TomSelect("#filterProduct", {
                maxItems: 1,
                placeholder: "Select Product"
            });
            const tsProcess = new TomSelect("#filterProcess", {
                maxItems: 1,
                placeholder: "Select Process"
            });
            // Cascade filter: PN → Model/Product/Process
            function cascadeFromPartNumber(value) {
                const activeTab = window.documentTabs.activeTab;
                const plantName = slugToPlant(activeTab);
                const plantData = window.filterDataByPlant[plantName];
                if (!plantData) return;

                const selectedPN = plantData.part_numbers.find(pn => pn.id == value);

                if (selectedPN) {
                    updateTomSelect('#filterModel', selectedPN.models.length ? selectedPN.models : plantData
                        .models);
                    updateTomSelect('#filterProduct', selectedPN.products.length ? selectedPN.products : plantData
                        .products);
                    updateTomSelect('#filterProcess', selectedPN.processes.length ? selectedPN.processes : plantData
                        .processes);
                } else {
                    updateTomSelect('#filterModel', plantData.models);
                    updateTomSelect('#filterProduct', plantData.products);
                    updateTomSelect('#filterProcess', plantData.processes);
                }
            }

            tsPartNumber.on('change', cascadeFromPartNumber);
            // AJAX Live Search & Pagination
            const tableContainer = document.getElementById("tableContainer");
            const searchInput = document.getElementById("searchInput");
            const clearBtn = document.getElementById("clearSearch");
            let timer;
            const delay = 300;

            function fetchData(url) {
                fetch(url, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return res.text();
                    })
                    .then(html => {
                        const dom = new DOMParser().parseFromString(html, "text/html");
                        tableContainer.innerHTML = dom.querySelector("#tableContainer").innerHTML;

                        bindPagination();
                        rebindListeners();
                    });
            }

            searchInput.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const q = searchInput.value;
                    const url =
                        `{{ route('master.document-review.index2') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }, delay);
            });

            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    fetchData(
                        `{{ route('master.document-review.index2') }}?search=${encodeURIComponent(searchInput.value)}`
                    );
                }
            });

            clearBtn?.addEventListener("click", function() {
                searchInput.value = "";
                fetchData(`{{ route('master.document-review.index2') }}`);
            });

            function bindPagination() {
                document.querySelectorAll("#tableContainer .pagination a").forEach(a => {
                    a.addEventListener("click", function(e) {
                        e.preventDefault();
                        fetchData(this.href);
                    });
                });
            }
            // Rebind listeners after AJAX
            function rebindListeners() {
                // Feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }

                // File view buttons (single file)
                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const fileViewer = document.getElementById('fileViewer');
                        fileViewer.src = this.dataset.file;
                        new bootstrap.Modal(document.getElementById('viewFileModal')).show();
                    });
                });

                // Dropdown toggle for multiple files
                document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.id.replace('viewFilesBtn-', '');
                        const dropdown = document.getElementById('viewFilesDropdown-' + id);
                        if (dropdown) dropdown.classList.toggle('hidden');
                    });
                });

                // Delete confirmation (localized + clearer impact)
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Hapus dokumen ini?',
                            text: 'Menghapus dokumen akan menghapus semua file terkait secara permanen. Tindakan ini tidak dapat dibatalkan.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
                document.querySelectorAll('#tableContainer button:not(.skip-style)').forEach(btn => {
                    btn.classList.add('inline-flex', 'items-center', 'justify-center');
                    btn.style.minWidth = "2.5rem"; // hanya untuk tombol tertentu
                });
            }

            // Clear filters modal
            document.getElementById('clearFilterModal').addEventListener('click', function() {
                tsDocumentName.clear();
                tsPartNumber.clear();
                tsModel.clear();
                tsProduct.clear();
                tsProcess.clear();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) searchInput.value = '';
                document.getElementById('filterFormModal').submit();
            });
            // Inisialisasi dropdown sesuai tab aktif
            updateFiltersForPlant(window.documentTabs.activeTab);

            // Bind initial event handlers (including delete confirmation)
            if (typeof rebindListeners === 'function') {
                rebindListeners();
            }

            // Delegated click handler: toggle dropdowns and open file viewer
            document.addEventListener('click', function(e) {
                // --- Toggle file dropdown ---
                const btn = e.target.closest('.toggle-files-dropdown');
                if (btn) {
                    const id = btn.id.replace('viewFilesBtn-', '');
                    const target = document.getElementById('viewFilesDropdown-' + id);

                    // Close other dropdowns (restore if moved)
                    document.querySelectorAll('[id^="viewFilesDropdown-"]').forEach(d => {
                        if (d === target) return;
                        if (d.dataset.moved === '1' && d._origParent) {
                            d.style.position = '';
                            d.style.left = '';
                            d.style.top = '';
                            d.classList.remove('dropdown-fixed');
                            d.dataset.moved = '0';
                            d._origParent.insertBefore(d, d._nextSibling);
                        } else {
                            d.classList.add('hidden');
                        }
                    });

                    if (!target) return;

                    const isHidden = target.classList.contains('hidden');

                    if (isHidden) {
                        // Move dropdown to body to escape overflow clipping and position it
                        const rect = btn.getBoundingClientRect();
                        target._origParent = target.parentNode;
                        target._nextSibling = target.nextSibling;
                        document.body.appendChild(target);
                        target.style.position = 'fixed';
                        target.style.left = (rect.left + window.scrollX) + 'px';
                        target.style.top = (rect.bottom + window.scrollY + 8) + 'px';
                        target.classList.remove('hidden');
                        target.classList.add('dropdown-fixed');
                        target.dataset.moved = '1';
                    } else {
                        // Close and restore
                        if (target.dataset.moved === '1' && target._origParent) {
                            target.style.position = '';
                            target.style.left = '';
                            target.style.top = '';
                            target.classList.remove('dropdown-fixed');
                            target.dataset.moved = '0';
                            target._origParent.insertBefore(target, target._nextSibling);
                        } else {
                            target.classList.add('hidden');
                        }
                    }

                    return;
                }

                // --- Click on a file button (view) ---
                const fileBtn = e.target.closest('.view-file-btn');
                if (fileBtn) {
                    // Close and restore any open dropdowns
                    document.querySelectorAll('[id^="viewFilesDropdown-"]').forEach(d => {
                        if (d.dataset.moved === '1' && d._origParent) {
                            d.style.position = '';
                            d.style.left = '';
                            d.style.top = '';
                            d.classList.remove('dropdown-fixed');
                            d.dataset.moved = '0';
                            d._origParent.insertBefore(d, d._nextSibling);
                        } else {
                            d.classList.add('hidden');
                        }
                    });

                    // Remove any leftover backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

                    const viewer = document.getElementById('fileViewer');
                    viewer.src = fileBtn.dataset.file;

                    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                        'viewFileModal'));
                    modal.show();
                    return;
                }

                // --- Click outside any dropdown: close all ---
                if (!e.target.closest('[id^="viewFilesDropdown-"]')) {
                    document.querySelectorAll('[id^="viewFilesDropdown-"]').forEach(d => {
                        if (d.dataset.moved === '1' && d._origParent) {
                            d.style.position = '';
                            d.style.left = '';
                            d.style.top = '';
                            d.classList.remove('dropdown-fixed');
                            d.dataset.moved = '0';
                            d._origParent.insertBefore(d, d._nextSibling);
                        } else {
                            d.classList.add('hidden');
                        }
                    });
                }
            });

        });
    </script>
    <script>
        let activeRequests = new Map(); // Track active requests
        let requestCache = new Map(); // Cache responses

        async function fetchJson(url) {
            // Check cache first
            if (requestCache.has(url)) {
                return requestCache.get(url);
            }

            // If request is already in progress, wait for it
            if (activeRequests.has(url)) {
                return await activeRequests.get(url);
            }

            // Create new request promise
            const requestPromise = fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    // Cache successful response for 5 minutes
                    requestCache.set(url, data);
                    setTimeout(() => requestCache.delete(url), 300000);
                    return data;
                })
                .catch(err => {
                    console.error('fetchJson error:', url, err);
                    throw err;
                })
                .finally(() => {
                    activeRequests.delete(url);
                });

            activeRequests.set(url, requestPromise);
            return await requestPromise;
        }
    </script>
@endpush

<style>
    /* --- Dropdown fix style --- */
    .dropdown-fixed {
        position: fixed !important;
        z-index: 999999 !important;
        background-color: #ffffff !important;
        /* warna putih solid */
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        border-radius: 8px !important;
        box-shadow: 0 6px 16px rgba(233, 217, 217, 0.2);
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Tambahan: untuk isi dropdown agar tidak transparan juga */
    .dropdown-fixed .py-1 {
        background-color: #fff;
    }

    /* Default border */
    #addDocumentModal input.form-control,
    #addDocumentModal select.form-select {
        border: 1px solid #d1d5db !important;
        /* abu-abu halus */
        box-shadow: none !important;
    }

    /* Hover (opsional) */
    #addDocumentModal input.form-control:hover,
    #addDocumentModal select.form-select:hover {
        border-color: #bfc3ca !important;
    }

    /* Fokus / diklik */
    #addDocumentModal input.form-control:focus,
    #addDocumentModal select.form-select:focus {
        border-color: #3b82f6 !important;
        /* biru */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
        /* efek biru lembut */
    }

    [id^="editDocumentModal-"] input.form-control,
    [id^="editDocumentModal-"] select.form-select {
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
    }

    /* Hover */
    [id^="editDocumentModal-"] input.form-control:hover,
    [id^="editDocumentModal-"] select.form-select:hover {
        border-color: #bfc3ca !important;
    }

    /* Fokus */
    [id^="editDocumentModal-"] input.form-control:focus,
    [id^="editDocumentModal-"] select.form-select:focus {
        border-color: #3b82f6 !important;
        /* biru */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
    }
</style>
