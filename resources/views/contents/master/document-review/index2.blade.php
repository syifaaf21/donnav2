@extends('layouts.app')
@section('title', 'Master Document Review')

@section('content')
    <div class="container mx-auto px-4 py-4" x-data="documentReviewTabs('{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}')">

        {{-- Header: Breadcrumbs + Add Button --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li>Documents</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Review</li>
                </ol>
            </nav>

            {{-- Add Document Button --}}
            <button
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md"
                data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                <i class="bi bi-plus-circle"></i> Add Document
            </button>
            @include('contents.master.document-review.partials.modal-add2')
        </div>

        {{-- Tabs + Search + Filter --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="flex flex-wrap justify-between items-center border-b border-gray-100 p-3 md:p-4 gap-2">
                {{-- Tabs di kiri --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ($groupedByPlant as $plant => $documents)
                        @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                        <button type="button" @click="setActiveTab('{{ $slug }}')"
                            :class="activeTab === '{{ $slug }}'
                                ?
                                'bg-blue-50 text-blue-700 border-b-2 border-blue-600' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-t-lg border border-gray-200 text-sm font-medium transition">
                            {{ ucwords(strtolower($plant)) }}
                        </button>
                    @endforeach
                </div>

                {{-- Search + Filter di kanan --}}
                <div class="flex items-center gap-2 ml-auto">
                    {{-- Search Bar --}}
                    <form id="searchForm" method="GET" class="relative w-60 md:w-80">
                        <input type="text" name="search" id="searchInput"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                            placeholder="Search..." value="{{ request('search') }}">
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button" id="clearSearch"
                            class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </form>

                    {{-- Filter Icon --}}
                    <button
                        class="flex items-center gap-2 px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shadow-md"
                        data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Modal --}}
        <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-xl shadow-xl border-0 overflow-hidden">
                    <form id="filterFormModal" method="GET" class="bg-white">
                        <div class="modal-header border-b px-6 py-4">
                            <h5 class="text-lg font-semibold text-gray-800">
                                <i class="bi bi-funnel-fill text-blue-600 me-2"></i> Filter Documents
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Document Name --}}
                            <div class="flex flex-col">
                                <label for="filterDocumentName" class="text-sm font-medium text-gray-700 mb-1">Document
                                    Name</label>
                                <select name="document_name" id="filterDocumentName"
                                    class="tom-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <div class="flex flex-col">
                                <label for="filterPartNumber" class="text-sm font-medium text-gray-700 mb-1">Part
                                    Number</label>
                                <select name="part_number_id" id="filterPartNumber"
                                    class="tom-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <div class="flex flex-col">
                                <label for="filterModel" class="text-sm font-medium text-gray-700 mb-1">Model</label>
                                <select name="model_id" id="filterModel"
                                    class="tom-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <div class="flex flex-col">
                                <label for="filterProduct" class="text-sm font-medium text-gray-700 mb-1">Product</label>
                                <select name="product_id" id="filterProduct"
                                    class="tom-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <div class="flex flex-col">
                                <label for="filterProcess" class="text-sm font-medium text-gray-700 mb-1">Process</label>
                                <select name="process_id" id="filterProcess"
                                    class="tom-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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

                        <div class="modal-footer flex justify-end gap-3 px-6 py-4 border-t">
                            <button type="button" id="clearFilterModal"
                                class="px-4 py-2 bg-gray-200 rounded-lg text-gray-700 hover:bg-gray-300 transition">Clear</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        {{-- Table per Plant --}}
        <div class="overflow-x-auto max-h-[60vh]">
            @foreach ($groupedByPlant as $plant => $documents)
                @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                <div x-show="activeTab === '{{ $slug }}'" x-transition>
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-600">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">No</th>
                                <th class="px-4 py-2 text-left font-semibold">Document Number</th>
                                <th class="px-4 py-2 text-left font-semibold">Part Number</th>
                                <th class="px-4 py-2 text-left font-semibold">Model</th>
                                <th class="px-4 py-2 text-left font-semibold">Product</th>
                                <th class="px-4 py-2 text-left font-semibold">Process</th>
                                <th class="px-4 py-2 text-left font-semibold">Department</th>
                                <th class="px-4 py-2 text-left font-semibold">Status</th>
                                <th class="px-4 py-2 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @if ($documents->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center text-gray-400 py-6">
                                        <i data-feather="folder-x" class="mx-auto w-6 h-6 mb-2"></i>
                                        No Document found for this tab.
                                    </td>
                                </tr>
                            @else
                                @foreach ($documents as $index => $doc)
                                    <tr class="hover:bg-blue-50 transition">
                                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 font-medium">{{ $doc->document_number }}</td>
                                        <td class="px-4 py-2">{{ $doc->partNumber->part_number ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $doc->productModel->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $doc->product->name ?? '-' }}</td>
                                        <td class="px-4 py-2 capitalize">{{ $doc->process->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $doc->department->name ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            @php
                                                $statusClasses = [
                                                    'approved' =>
                                                        'inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded',
                                                    'rejected' =>
                                                        'inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded',
                                                    'need review' =>
                                                        'inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded',
                                                ];

                                                $statusName = strtolower($doc->status?->name ?? '');
                                                $class =
                                                    $statusClasses[$statusName] ??
                                                    'inline-block px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded';
                                            @endphp

                                            @if ($doc->status)
                                                <span class="{{ $class }}">
                                                    {{ $doc->status->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <div class="relative inline-block overflow-visible">
                                                @php $files = $doc->files->map(fn($f) => ['name' => $f->file_name ?? basename($f->file_path), 'url' => asset('storage/' . $f->file_path)])->toArray(); @endphp
                                                @if (count($files) > 1)
                                                    <button id="viewFilesBtn-{{ $doc->id }}" type="button"
                                                        class="relative focus:outline-none text-gray-700 hover:text-blue-600 toggle-files-dropdown mr-2"
                                                        title="View File">
                                                        <i data-feather="file-text" class="w-5 h-5"></i>
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
                                                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate"
                                                                    data-file="{{ $file['url'] }}" title="View File">
                                                                    📄 {{ $file['name'] }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif(count($files) === 1)
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 bg-white hover:bg-gray-50 view-file-btn"
                                                        data-file="{{ $files[0]['url'] }}" title="View File">
                                                        <i data-feather="file-text" class="w-4 h-4"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <button data-bs-toggle="modal"
                                                data-bs-target="#editDocumentModal-{{ $doc->id }}"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200"
                                                title="Edit">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </button>
                                            <form action="{{ route('master.document-review.destroy', $doc->id) }}"
                                                method="POST" class="delete-form d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Document"
                                                    class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
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
            @endforeach
        </div>
    </div>
    </div>

    {{-- 📄 All Edit Modals --}}
    @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
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
                    <iframe id="fileViewer" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        function documentReviewTabs(defaultTab) {
            return {
                activeTab: localStorage.getItem('activeTab') || defaultTab,
                setActiveTab(tab) {
                    this.activeTab = tab;
                    localStorage.setItem('activeTab', tab);
                }
            }
        }

        // Clear search input
        document.addEventListener('DOMContentLoaded', function() {
            // Clear search input
            const clearBtn = document.getElementById('clearSearch');
            const searchInput = document.getElementById('searchInput');
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                document.getElementById('searchForm').submit(); // penting
            });


            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file; // sesuaikan dengan data-file
                    const viewer = document.getElementById('fileViewer');
                    viewer.src = fileUrl;
                    const modal = new bootstrap.Modal(document.getElementById('viewFileModal'));
                    modal.show();
                });
            });


            // Clear iframe saat modal ditutup
            const viewModal = document.getElementById('viewFileModal');
            viewModal.addEventListener('hidden.bs.modal', () => {
                document.getElementById('fileViewer').src = '';
            });
            document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dropdown = document.getElementById(btn.id.replace('Btn', 'Dropdown'));

                    const isVisible = !dropdown.classList.contains('hidden');

                    // Tutup semua dropdown lain
                    document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList
                        .add('hidden'));

                    // Kalau yang diklik sedang terbuka → tutup saja
                    if (isVisible) {
                        dropdown.classList.add('hidden');
                        return;
                    }

                    // Hitung posisi
                    const rect = btn.getBoundingClientRect();
                    const offsetX = -120;
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = `${rect.bottom + 6}px`;
                    dropdown.style.left = `${rect.left + offsetX}px`;
                    dropdown.classList.remove('hidden');
                    dropdown.classList.add('dropdown-fixed');
                });
            });

            // Tutup dropdown saat scroll
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add(
                    'hidden'));
            });

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(dropdown => {
                    const button = document.getElementById(dropdown.id.replace('Dropdown', 'Btn'));
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
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

            // --- Clear filter modal ---
            document.getElementById('clearFilterModal').addEventListener('click', function() {
                // Clear all TomSelect fields
                tsDocumentName.clear();
                tsPartNumber.clear();
                tsModel.clear();
                tsProduct.clear();
                tsProcess.clear();

                // Optional: reset search input
                const searchInput = document.getElementById('searchInput');
                if (searchInput) searchInput.value = '';

                // Submit form untuk reload tanpa filter
                document.getElementById('filterFormModal').submit();
            });

        });
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
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Tambahan: untuk isi dropdown agar tidak transparan juga */
    .dropdown-fixed .py-1 {
        background-color: #fff;
    }
</style>
