@extends('layouts.app')

@section('content')
    <div class="container py-3">
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Master</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Product</a>
                    </li>
                </ol>
            </nav>

            {{-- Add Product Button --}}
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- Search Bar --}}
                <div class="flex justify-content-end mb-3">
                    <form method="GET" id="searchForm" class="flex items-center gap-2 flex-wrap">
                        <div class="relative max-w-md w-full">
                            <input type="text" name="search" id="searchInput"
                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search..." value="{{ request('search') }}">
                            <button type="submit" title="Search"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button" id="clearSearch" title="Clear"
                                class="absolute right-8 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="table-wrapper mb-3">
                    <div class="table-responsive">
                        <table class="min-w-full table-auto text-sm text-left text-gray-600">
                            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Plant</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-3">{{ $product->name }}</td>
                                        <td class="px-4 py-3">{{ $product->code }}</td>
                                        <td class="px-4 py-3">{{ $product->plant }}</td>
                                        <td class="px-4 py-3">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editProductModal-{{ $product->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('master.products.destroy', $product->id) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No products found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        {{ $products->withQueryString()->links() }}
                    </div>
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
                                    <i class="bi bi-person-lines-fill me-2"></i>Edit Product
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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

                            <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-outline-success px-4">
                                    <i class="bi bi-check-circle me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- Add Modal --}}
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.products.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form" value="add">

                    <div class="modal-content border-0 shadow-lg rounded-4">
                        {{-- Header --}}
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="addProductModalLabel">
                                <i class="bi bi-person-plus-fill me-2"></i>Create New Product
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Name</label>
                                    <input type="text" name="name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Code --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Code</label>
                                    <input type="text" name="code"
                                        class="form-control rounded-3 @error('code') is-invalid @enderror"
                                        value="{{ old('code') }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Plant --}}
                                <div class="mb-3">
                                    <label class="form-label">Plant</label>
                                    <select name="plant" class="form-select" required>
                                        <option value="">-- Select Plant --</option>
                                        @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                            <option value="{{ $plant }}" @selected(old('plant') === $plant)>
                                                {{ $plant }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save2 me-1"></i>Save Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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
        });
    </script>
@endpush
