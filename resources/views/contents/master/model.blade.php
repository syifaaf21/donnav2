@extends('layouts.app')
@section('title', 'Model')

@section('content')
    <div class="container mx-auto px-4 py-2">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-4">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-600">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-semibold">Model</li>
                </ol>
            </nav>

            {{-- Add Model Button (Bootstrap modal trigger) --}}
            <button type="button"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#addModelModal">
                <i class="bi bi-plus-circle"></i> Add Model
            </button>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-lg p-4">
            {{-- Search --}}
            <div class="flex justify-end mb-3">
                <form method="GET" id="searchForm" class="relative w-full max-w-xs">
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
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-700">
                    <thead class="bg-gray-100 uppercase text-xs font-semibold text-gray-700">
                        <tr>
                            <th class="px-4 py-2">No</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Plant</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($models as $model)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2">
                                    {{ ($models->currentPage() - 1) * $models->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-2">{{ $model->name }}</td>
                                <td class="px-4 py-2">{{ $model->plant }}</td>
                                <td class="px-4 py-2 text-center flex justify-center gap-2">
                                    {{-- Edit Button --}}
                                    <button data-bs-toggle="modal" data-bs-target="#editModelModal-{{ $model->id }}"
                                        data-bs-title="Edit Model"
                                        class="bg-blue-600 text-white hover:bg-blue-700 p-2 rounded">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    {{-- Delete Button --}}
                                    <form action="{{ route('master.models.destroy', $model->id) }}" method="POST"
                                        class="delete-form inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-bs-title="Delete Model"
                                            class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">No models found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $models->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>

        {{-- Edit Modals --}}
        @foreach ($models as $model)
            <div class="modal fade" id="editModelModal-{{ $model->id }}" tabindex="-1"
                aria-labelledby="editModelModalLabel-{{ $model->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('master.models.update', $model->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header bg-light text-dark rounded-top-4">
                                <h5 class="modal-title fw-semibold" id="editModelModalLabel-{{ $model->id }}">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Model
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Model Name</label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ $model->name }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Plant</label>
                                    <select name="plant" class="form-select" required>
                                        @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                            <option value="{{ $plant }}" @selected($model->plant === $plant)>
                                                {{ $plant }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer bg-gray-100 rounded-b-xl flex justify-between p-4">
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
        <div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.models.store') }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="addModelModalLabel">
                                <i class="bi bi-plus-circle me-2"></i>Add New Model
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Model Name</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Plant</label>
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
                        <div class="modal-footer bg-gray-100 rounded-b-xl flex justify-between p-4">
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
    </div>
@endsection

@push('scripts')
    <x-sweetalert-confirm />
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
