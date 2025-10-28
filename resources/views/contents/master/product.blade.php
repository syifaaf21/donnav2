@extends('layouts.app')
@section('title', 'Product')

@section('content')
<div class="container mx-auto px-4 py-2">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-3">
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li>Master</li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Product</li>
            </ol>
        </nav>

        {{-- Add Button --}}
        <button type="button" data-bs-toggle="modal" data-bs-target="#addProductModal"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="bi bi-plus-circle"></i>
            <span>Add Product</span>
        </button>
    </div>

    <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
        {{-- Search Bar --}}
        <div class="p-4 border-b border-gray-100 flex justify-end">
            <form method="GET" id="searchForm" class="flex items-center w-full max-w-sm relative">
                <input type="text" name="search" id="searchInput"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
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
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto overflow-y-auto max-h-96">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Code</th>
                        <th class="px-4 py-2">Plant</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2">{{ $product->code }}</td>
                            <td class="px-4 py-2">{{ $product->plant }}</td>
                            <td class="px-4 py-2">
                                <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#editProductModal-{{ $product->id }}"
                                        data-bs-title="Edit Product"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                <form action="{{ route('master.products.destroy', $product->id) }}" method="POST"
                                    class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" data-bs-title="Delete Product"
                                        class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
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


        {{-- Edit Modals --}}
        @foreach ($products as $product)
            <div class="modal fade" id="editProductModal-{{ $product->id }}" tabindex="-1"
                aria-labelledby="editProductModalLabel-{{ $product->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('master.products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit">

                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header bg-light text-dark rounded-top-4">
                                <h5 class="modal-title fw-semibold" id="editProductModalLabel-{{ $product->id }}">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Product
                                </h5>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    {{-- Name --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Name</label>
                                        <input type="text" name="name"
                                            class="form-control rounded-3 @error('name') is-invalid @enderror"
                                            value="{{ ucwords($product->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Code --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Code</label>
                                        <input type="text" name="code"
                                            class="form-control rounded-3 @error('code') is-invalid @enderror"
                                            value="{{ $product->code }}" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Plant --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Plant</label>
                                        <select name="plant" class="form-select" required>
                                            @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                                <option value="{{ $plant }}" @selected($product->plant === $plant)>
                                                    {{ $plant }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                                <button type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                                    data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

{{-- Add Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('master.products.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="_form" value="add">
            {{-- Header --}}
            <div class="modal-header bg-light text-dark rounded-top-4 py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-semibold" id="addProductModalLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4">
                <div class="row g-3">
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Name</label>
                        <input type="text" name="name"
                            class="form-control rounded-3 @error('name') is-invalid @enderror"
                            placeholder="Enter product name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Code --}}
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Code</label>
                        <input type="text" name="code"
                            class="form-control rounded-3 @error('code') is-invalid @enderror"
                            placeholder="Enter product code" value="{{ old('code') }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Plant --}}
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Plant</label>
                        <select name="plant"
                            class="form-select rounded-3 @error('plant') is-invalid @enderror"
                            required>
                            <option value="">-- Select Plant --</option>
                            @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                <option value="{{ $plant }}" @selected(old('plant') === $plant)>
                                    {{ $plant }}
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
            <div class="modal-footer bg-light rounded-bottom-4 d-flex justify-content-between align-items-center p-4">
                <button type="button"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-200"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
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
        // Clear Search functionality
        document.addEventListener("DOMContentLoaded", function() {
            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", function() {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }

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
