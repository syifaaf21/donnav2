@extends('layouts.app')
@section('title', 'Model')

@section('content')
    <div class="mx-auto px-4 py-2">
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
                    <li class="text-gray-700 font-medium">Model</li>
                </ol>
            </nav>

            {{-- Add Model Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addModelModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Model</span>
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
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Name</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">Plant</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $model)
                                <tr class="hover:bg-gray-50 transition-all duration-150">
                                    <td class="px-4 py-3">
                                        {{ ($models->currentPage() - 1) * $models->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3">{{ $model->name }}</td>
                                    <td class="px-4 py-3">{{ $model->plant }}</td>
                                    <td class="px-4 py-3 flex justify-center gap-2">
                                        {{-- Edit Button --}}
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#editModelModal-{{ $model->id }}" data-bs-title="Edit Model"
                                            class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                            <i data-feather="edit" class="w-4 h-4"></i>
                                        </button>
                                        {{-- Delete Button --}}
                                        <form action="{{ route('master.models.destroy', $model->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" data-bs-title="Delete Model"
                                                class=" w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 py-4">No models found.</td>
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
        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($models as $model)
        <div class="modal fade" id="editModelModal-{{ $model->id }}" tabindex="-1"
            aria-labelledby="editModelModalLabel-{{ $model->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.models.update', $model->id) }}" method="POST"
                    class="modal-content rounded-4 shadow-lg border-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    {{-- Header --}}
                    <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                        style="background-color: #f5f5f7;">
                        <h5 class="modal-title fw-semibold text-dark" id="editModelModalLabel-{{ $model->id }}"
                            style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Model
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
                            {{-- Model Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Model Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="Enter model name"
                                    class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ $model->name }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                                <select name="plant"
                                    class="form-select border-0 shadow-sm rounded-3 tom-plant @error('plant') is-invalid @enderror"
                                    required>
                                    <option value="">-- Select Plant --</option>
                                    @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                        <option value="{{ $plant }}" @selected($model->plant === $plant)>
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
                    <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                        <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                            data-bs-dismiss="modal"
                            style="text-decoration: none; transition: background-color 0.3s ease;">
                            Cancel
                        </button>
                        <button type="submit" class="btn px-5 py-2 rounded-3 fw-semibold"
                            style="background-color: #3b82f6; border: 1px solid #3b82f6; color: white; transition: background-color 0.3s ease;">
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endforeach

    {{-- Add Modal --}}
    <div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.models.store') }}" method="POST"
                class="modal-content rounded-4 shadow-lg border-0">
                @csrf
                <input type="hidden" name="_form" value="add">

                {{-- Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold text-dark" id="addModelModalLabel"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Add New Model
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
                        {{-- Model Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" placeholder="Enter model name"
                                class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Plant --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                            <select name="plant" class="form-select border-0 shadow-sm rounded-3 tom-plant" required>
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
                    <button type="submit" class="btn px-5 py-2 rounded-3 fw-semibold"
                        style="background-color: #3b82f6; border: 1px solid #3b82f6; color: white; transition: background-color 0.3s ease;">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('scripts')
    <x-sweetalert-confirm />
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const plantSelects = document.querySelectorAll('.tom-plant');

            plantSelects.forEach(select => {
                new TomSelect(select, {
                    create: false,
                    maxItems: 1,
                    allowEmptyOption: false,
                    placeholder: "Select Plant",
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });

            // ===================== AJAX LIVE SEARCH =====================
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
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#d33', 
                            cancelButtonColor: '#3085d6' // optional: warna biru untuk cancel
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            }

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
                    .then(res => res.text())
                    .then(html => {
                        const dom = new DOMParser().parseFromString(html, "text/html");
                        tableContainer.innerHTML = dom.querySelector("#tableContainer").innerHTML;

                        bindPagination();

                        if (window.feather) feather.replace();
                        bindDeleteConfirm();

                        // Tooltip
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

            // Search input
            searchInput.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    let q = searchInput.value;
                    let url = `{{ route('master.models.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }, delay);
            });

            // Enter key
            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    let q = searchInput.value;
                    let url = `{{ route('master.models.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }
            });

            // Clear Search
            clearBtn.addEventListener("click", function() {
                searchInput.value = "";
                fetchData(`{{ route('master.models.index') }}`);
            });

            // ===================== AJAX PAGINATION =====================
            function bindPagination() {
                document.querySelectorAll("#tableContainer .pagination a").forEach(a => {
                    a.addEventListener("click", function(e) {
                        e.preventDefault();
                        fetchData(this.href);
                    });
                });
            }

            bindPagination();

            // Reset form ketika modal ditutup (Add)
            const addModelModal = document.getElementById("addModelModal");
            const formAdd = addModelModal.querySelector("form");

            if (addModelModal && formAdd) {
                addModelModal.addEventListener('hidden.bs.modal', function() {
                    formAdd.reset();
                });
            }

            // Reset Edit Modals
            const editModals = document.querySelectorAll('[id^="editModelModal-"]');
            editModals.forEach(modal => {
                const form = modal.querySelector("form");
                if (form) {
                    modal.addEventListener('hidden.bs.modal', function() {
                        form.reset();
                    });
                }
            });

        });

        // Tooltip
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
        tooltipTriggerList.map(function(el) {
            return new bootstrap.Tooltip(el, {
                title: el.getAttribute('data-bs-title'),
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addModelModal")).show();
            });
        </script>
    @endif

    @if ($errors->any() && session('edit_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let modalId = "editModelModal-{{ session('edit_modal') }}";
                let modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();
            });
        </script>
    @endif
@endpush
