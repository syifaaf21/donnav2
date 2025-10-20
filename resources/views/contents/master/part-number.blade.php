@extends('layouts.app')
@section('title', 'Part Number')

@section('content')
    <div class="container py-2">
        <div class="d-flex justify-between items-center mb-3">
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
                            <a href="#" class="text-decoration-none text-secondary">Part Number</a>
                        </li>
                    </ol>
                </nav>
            {{-- Tombol Add Part Number --}}
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold btn btn-primary rounded-md shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                data-bs-toggle="modal" data-bs-target="#createPartNumberModal">
                <i class="bi bi-plus-circle"></i> Add Part Number
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <form method="GET" class="flex items-center gap-2 flex-wrap" id="searchForm">
                        <div class="relative max-w-md w-full">
                            <input type="text" name="search" id="searchInput"
                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search..." value="{{ request('search') }}">
                            <button
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                                type="submit" title="Search">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button"
                                class="absolute right-8 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                                id="clearSearch" title="Clear">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="table-wrapper mb-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="min-w-full table-auto text-sm text-left text-gray-600">
                            <thead class="bg-gray-100 text-gray-700 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2">No</th>
                                    <th class="px-4 py-2">Part Number</th>
                                    <th class="px-4 py-2">Product</th>
                                    <th class="px-4 py-2">Model</th>
                                    <th class="px-4 py-2">Process</th>
                                    <th class="px-4 py-2">Plant</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partNumbers as $part)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            {{ ($partNumbers->currentPage() - 1) * $partNumbers->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-2">{{ $part->part_number }}</td>
                                        <td class="px-4 py-2">{{ $part->product->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $part->productModel->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ ucfirst($part->process) }}</td>
                                        <td class="px-4 py-2">{{ ucfirst($part->plant) }}</td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <button class="text-blue-600 hover:text-blue-700" data-bs-toggle="modal"
                                                data-bs-target="#editPartNumberModal-{{ $part->id }}">
                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                            </button>
                                            <form action="{{ route('master.part_numbers.destroy', $part->id) }}"
                                                method="POST" class="inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center px-4 py-2">No part number found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    <div class="mt-3">
                        {{ $partNumbers->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
        {{-- Modals Edit --}}
        @foreach ($partNumbers as $part)
            <div class="modal fade" id="editPartNumberModal-{{ $part->id }}" tabindex="-1"
                aria-labelledby="editPartNumberModalLabel-{{ $part->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('master.part_numbers.update', $part->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit">
                        <div class="modal-content border-0 rounded-4 shadow-lg">
                            <div class="modal-header bg-gray-100 text-gray-800 rounded-t-lg">
                                <h5 class="modal-title flex items-center gap-2 font-semibold"
                                    id="editPartNumberModalLabel-{{ $part->id }}">
                                    <i class="bi bi-nut-fill"></i> Edit Part Number
                                </h5>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="block font-medium">Part Number</label>
                                    <input type="text" name="part_number"
                                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        value="{{ $part->part_number }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="block font-medium">Product</label>
                                    <select name="product_id"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ $part->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="block font-medium">Model</label>
                                    <select name="model_id"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Model</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model->id }}"
                                                {{ $part->model_id == $model->id ? 'selected' : '' }}>
                                                {{ $model->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="block font-medium">Process</label>
                                    <select name="process"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Process</option>
                                        @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric'] as $process)
                                            <option value="{{ $process }}"
                                                {{ $part->process == $process ? 'selected' : '' }}>
                                                {{ ucfirst($process) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="block font-medium">Plant</label>
                                    <select name="plant"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Plant</option>
                                        @foreach (['body', 'unit', 'electric'] as $plant)
                                            <option value="{{ $plant }}"
                                                {{ $part->plant == $plant ? 'selected' : '' }}>
                                                {{ ucfirst($plant) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-3 justify-between bg-gray-100 rounded-b-lg">
                                <button type="button" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md"
                                    data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                    <i class="bi bi-check-circle"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach


        {{-- Modal Create --}}
        <div class="modal fade" id="createPartNumberModal" tabindex="-1" aria-labelledby="createPartNumberModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('master.part_numbers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form" value="add">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-gray-100 text-gray-800 rounded-t-lg">
                            <h5 class="modal-title flex items-center gap-2 font-semibold" id="createPartNumberModalLabel">
                                <i class="bi bi-gear-fill"></i> Add Part Number
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium">Part Number</label>
                                    <input type="text" name="part_number"
                                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        value="{{ old('part_number') }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium">Product</label>
                                    <select name="product_id"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">-- Select Product --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium">Model</label>
                                    <select name="model_id"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">-- Select Model --</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model->id }}"
                                                {{ old('model_id') == $model->id ? 'selected' : '' }}>
                                                {{ $model->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium">Process</label>
                                    <select name="process"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">-- Select Process --</option>
                                        @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'mounting', 'assembling electric', 'inspection'] as $process)
                                            <option value="{{ $process }}"
                                                {{ old('process') == $process ? 'selected' : '' }}>
                                                {{ ucfirst($process) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium">Plant</label>
                                    <select name="plant"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">-- Select Plant --</option>
                                        @foreach (['body', 'unit', 'electric'] as $plant)
                                            <option value="{{ $plant }}"
                                                {{ old('plant') == $plant ? 'selected' : '' }}>
                                                {{ ucfirst($plant) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-between bg-gray-100 rounded-b-lg">
                            <button type="button" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md"
                                data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                <i class="bi bi-save2"></i> Save Part Number
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

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
        @if ($errors->any() && session('edit_modal'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    new bootstrap.Modal(document.getElementById("editPartNumberModal-{{ session('edit_modal') }}")).show();
                });
            </script>
        @endif

        @if ($errors->any() && old('_form') === 'add')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    new bootstrap.Modal(document.getElementById("createPartNumberModal")).show();
                });
            </script>
        @endif
    @endpush
