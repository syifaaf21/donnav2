@extends('layouts.app')
@section('title', 'Product')

@section('content')
    <div class="mx-auto px-4 py-2">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-2" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500 font-medium">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Product</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addProductModal"
               class="px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                <i class="bi bi-plus-circle"></i>
                <span>Add Product</span>
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <form id="searchForm" method="GET" class="flex items-end w-auto">
                    <div class="relative w-96">
                        <input type="text" name="search" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
             focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Type to search..." value="{{ request('search') }}">

                        <label for="searchInput"
                            class="absolute left-4 transition-all duration-150 bg-white px-1 rounded
             text-gray-400 text-sm
             {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>
                    </div>
                </form>
            </div>
            <div id="tableContainer">
                {{-- Table --}}
                <div
                    class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                            <tr>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Name</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Code</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Plant</th>
                                <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200"
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50 transition-all duration-150">
                                    <td class="px-4 py-3">
                                        {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold">{{ ucwords($product->name) }}</td>
                                    <td class="px-4 py-3">{{ $product->code }}</td>
                                    <td class="px-4 py-3">{{ $product->plant }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#editProductModal-{{ $product->id }}"
                                            data-bs-title="Edit Product"
                                            class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                            <i data-feather="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.products.destroy', $product->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" data-bs-title="Delete Product"
                                                class=" w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-4">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $products->withQueryString()->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($products as $product)
        <div class="modal fade" id="editProductModal-{{ $product->id }}" tabindex="-1"
            aria-labelledby="editProductModalLabel-{{ $product->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.products.update', $product->id) }}" method="POST"
                    class="modal-content rounded-4 shadow-lg border-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    {{-- Header --}}
                    <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                        style="background-color: #f5f5f7;">
                        <h5 class="modal-title fw-semibold text-dark" id="editProductModalLabel-{{ $product->id }}"
                            style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Product
                        </h5>
                        <button type="button"
                            class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="width: 36px; height: 36px; border: 1px solid #ddd;">
                            <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div class="row g-4">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="Enter product name"
                                    class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Code --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" placeholder="Enter product code"
                                    class="form-control border-0 shadow-sm rounded-3 @error('code') is-invalid @enderror"
                                    value="{{ old('code', $product->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-select border-0 shadow-sm rounded-3" required>
                                    <option value="">-- Select Plant --</option>
                                    @foreach (['Body', 'Unit', 'Electric'] as $optPlant)
                                        <option value="{{ $optPlant }}" @selected(old('plant', $product->plant) === $optPlant)>
                                            {{ $optPlant }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                        <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                            data-bs-dismiss="modal"
                            style="text-decoration: none; transition: background-color 0.3s ease;">
                            Cancel
                        </button>
                        <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach


    {{-- Add Modal --}}
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.products.store') }}" method="POST"
                class="modal-content rounded-4 shadow-lg border-0">
                @csrf
                <input type="hidden" name="_form" value="add">

                {{-- Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold text-dark" id="addProductModalLabel"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Product
                    </h5>
                    <button type="button"
                        class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="width: 36px; height: 36px; border: 1px solid #ddd;">
                        <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-4">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name"
                                class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                                placeholder="Enter product name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Code --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code"
                                class="form-control border-0 shadow-sm rounded-3 @error('code') is-invalid @enderror"
                                placeholder="Enter product code" value="{{ old('code') }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Plant --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                            <select name="plant" class="form-select border-0 shadow-sm rounded-3" required>
                                <option value="">-- Select Plant --</option>
                                @foreach (['Body', 'Unit', 'Electric'] as $optPlant)
                                    <option value="{{ $optPlant }}" @selected(old('plant') === $optPlant)>
                                        {{ $optPlant }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                    <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                        data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                        Cancel
                    </button>
                    <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    @if ($errors->any() && session('edit_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("editProductModal-{{ session('edit_modal') }}")).show();
            });
        </script>
    @endif

    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addProductModal")).show();
            });
        </script>
    @endif
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize TomSelect for PLANT in Add Modal
            const addSelect = document.querySelector('#addProductModal select[name="plant"]');
            if (addSelect) {
                new TomSelect(addSelect, {
                    create: false,
                    maxItems: 1,
                    allowEmptyOption: false,
                    placeholder: "Select Plant"
                });
            }

            // Initialize TomSelect for PLANT in each Edit Modal
            document.querySelectorAll('[id^="editProductModal-"]').forEach(modal => {
                const select = modal.querySelector('select[name="plant"]');

                if (select) {
                    new TomSelect(select, {
                        create: false,
                        maxItems: 1,
                        allowEmptyOption: false,
                        placeholder: "Select Plant"
                    });
                }
            });

            function bindDeleteConfirm() {
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action cannot be undone.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Setelah konfirmasi, submit form
                                form.submit();
                            }
                        });
                    });
                });
            }
            const searchInput = document.getElementById("searchInput");
            const clearBtn = document.getElementById("clearSearch");
            const tableContainer = document.getElementById("tableContainer");

            let timer;
            const delay = 300;

            function fetchData(url) {
                fetch(url, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const dom = new DOMParser().parseFromString(html, "text/html");

                        // Replace isi table & pagination
                        tableContainer.innerHTML = dom.querySelector("#tableContainer").innerHTML;

                        // Rebind pagination links
                        bindPagination();

                        // Re-init feather icons
                        if (window.feather) {
                            feather.replace();
                        }

                        bindDeleteConfirm();
                        // Re-init tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
                        tooltipTriggerList.map(function(el) {
                            return new bootstrap.Tooltip(el, {
                                title: el.getAttribute('data-bs-title'),
                                placement: 'top',
                                trigger: 'hover'
                            });
                        });
                    });
            }

            // ========== LIVE SEARCH ==========
            searchInput.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    let q = searchInput.value;
                    let url =
                        `{{ route('master.products.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }, delay);
            });

            // Enter submit via AJAX
            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    let q = searchInput.value;
                    let url = `{{ route('master.products.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }
            });

            // Clear Search
            clearBtn.addEventListener("click", function() {
                searchInput.value = "";
                fetchData(`{{ route('master.products.index') }}`);
            });

            // ========== AJAX PAGINATION ==========
            function bindPagination() {
                document.querySelectorAll("#tableContainer .pagination a").forEach(a => {
                    a.addEventListener("click", function(e) {
                        e.preventDefault();
                        fetchData(this.href);
                    });
                });
            }

            bindPagination();

            //Cancel button modal
            const addDepartmentModal = document.getElementById("addProductModal");
            const formAdd = addDepartmentModal.querySelector("form");

            if (addDepartmentModal && formAdd) {
                // Reset form ketika modal ditutup
                addDepartmentModal.addEventListener('hidden.bs.modal', function() {
                    formAdd.reset();

                    // Hapus is-invalid dan error message
                    formAdd.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                    formAdd.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                });
            }

            const editModals = document.querySelectorAll('[id^="editProductModal-"]');
            editModals.forEach(modal => {
                const form = modal.querySelector("form");

                if (form) {
                    modal.addEventListener('hidden.bs.modal', function() {
                        form.reset();

                        // Hapus class is-invalid dan error feedback
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                            'is-invalid'));
                        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                    });
                }
            });
        });
        //Tooltip
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el, {
                    title: el.getAttribute('data-bs-title'),
                    placement: 'top',
                    trigger: 'hover'
                });
            });
        });
    </script>
@endpush
