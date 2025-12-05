@extends('layouts.app')
@section('title', 'Department Master')
@section('subtitle', 'Manage Departments')
@section('breadcrumbs')
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
            <li class="text-gray-700 font-bold">Department</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="mx-auto px-4 py-2 bg-white rounded-lg shadow">
        {{-- Header --}}
        {{-- <div class="flex justify-end items-center my-4 pt-4">
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
                    <li class="text-gray-700 font-bold">Department</li>
                </ol>
            </nav>
        </div>
        <div class="py-6 mt-4 text-white">
            <div class="mb-4 text-white">
                <h1 class="fw-bold ">Department Master</h1>
                <p style="font-size: 0.9rem;">
                    Manage department records. Use the "Add Department" button to create new entries and the actions column to edit or delete existing departments.
                </p>
            </div>
        </div> --}}

        <div class="overflow-hidden">
            <div class="flex items-center justify-between my-2">
                {{-- Search Bar (left) --}}
                <form id="searchForm" method="GET" class="flex items-end w-full md:w-96">
                    <div class="relative w-full">
                        <input type="text" name="search" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
                            focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Type to search..." value="{{ request('search') }}">

                        <label for="searchInput"
                            class="absolute left-4 transition-all duration-150 bg-white px-1 rounded  text-sky-600
                            text-gray-400 text-sm
                            {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>
                    </div>
                </form>

                {{-- Add Button (right) --}}
                <div class="ms-4 flex-shrink-0">
                    {{-- Add Button --}}
                    <button type="button" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"
                        class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Department</span>
                    </button>
                </div>
            </div>

            <div id="tableContainer">
                {{-- Table --}}
                <div
                    class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
                    <table class="min-w-full divide-y divide-gray-200 text-gray-700">
                        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                            <tr>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Name</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Code</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Plant</th>
                                <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-x divide-gray-200">
                            @forelse ($departments as $department)
                                <tr class="hover:bg-gray-50 transition-all duration-150">
                                    <td class="px-4 py-3 border-r border-gray-200">
                                        {{ ($departments->currentPage() - 1) * $departments->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold border-r border-gray-200">
                                        {{ ucwords($department->name) }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ $department->code }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ $department->plant }}</td>
                                    <td class="px-4 py-3 text-sm text-center border-r border-gray-200">
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#editDepartmentModal-{{ $department->id }}"
                                            title="Edit Department"
                                            class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                            <i data-feather="edit" class="w-4 h-4"></i>
                                        </button>

                                        <form action="{{ route('master.departments.destroy', $department->id) }}"
                                            method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Department"
                                                class=" w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-4">No departments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $departments->withQueryString()->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Modals -->
    @foreach ($departments as $department)
        <div class="modal fade" id="editDepartmentModal-{{ $department->id }}" tabindex="-1"
            aria-labelledby="editDepartmentModalLabel-{{ $department->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.departments.update', $department->id) }}" method="POST"
                    id="editForm-{{ $department->id }}" class="modal-content rounded-4 shadow-lg">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    {{-- Header --}}
                    <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                        style="background-color: #f5f5f7;">
                        <h5 class="modal-title fw-semibold text-dark" id="editDepartmentModalLabel-{{ $department->id }}"
                            style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Department
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
                                <input type="text" name="name" placeholder="Enter department name"
                                    class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name', $department->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Code --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                <select name="code" id="edit-code-select-{{ $department->id }}"
                                    class="form-select border-0 shadow-sm rounded-3 @error('code') is-invalid @enderror"
                                    required>
                                    @foreach ($codes as $code)
                                        <option value="{{ $code }}"
                                            {{ old('code', $department->code) == $code ? 'selected' : '' }}>
                                            {{ $code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                                <select name="plant" id="edit-plant-select-{{ $department->id }}"
                                    class="form-select border-0 shadow-sm rounded-3 @error('plant') is-invalid @enderror"
                                    required>
                                    <option value="Unit"
                                        {{ old('plant', $department->plant) == 'Unit' ? 'selected' : '' }}>Unit</option>
                                    <option value="Body"
                                        {{ old('plant', $department->plant) == 'Body' ? 'selected' : '' }}>Body</option>
                                    <option value="Electric"
                                        {{ old('plant', $department->plant) == 'Electric' ? 'selected' : '' }}>Electric
                                    </option>
                                    <option value="ALL"
                                        {{ old('plant', $department->plant) == 'ALL' ? 'selected' : '' }}>ALL</option>
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
                        <button type="submit"
                            class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Add Modal --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.departments.store') }}" method="POST"
                class="modal-content rounded-4 shadow-lg">
                @csrf
                <input type="hidden" name="_form" value="add">

                {{-- Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold text-dark" id="addDepartmentModalLabel"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Department
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
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" placeholder="Enter department name"
                                style="border-color: #d1d5db; padding: 10px; font-size: 0.95rem;"
                                class="form-control border-0 shadow-sm rounded-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Code --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                            <select id="code-select" name="code"
                                class="form-select border-0 shadow-sm rounded-3 @error('code') is-invalid @enderror"
                                required>
                                <option value="" disabled selected>-- Select or create code --</option>

                                @foreach ($codes as $code)
                                    <option value="{{ $code }}" {{ old('code') == $code ? 'selected' : '' }}>
                                        {{ $code }}
                                    </option>
                                @endforeach

                                @if (old('code') && !$codes->contains(old('code')))
                                    <option value="{{ old('code') }}" selected>{{ old('code') }}</option>
                                @endif
                            </select>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Plant --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Plant <span class="text-danger">*</span></label>
                            <select id="plant-select" name="plant"
                                class="form-select border-0 shadow-sm rounded-3 @error('plant') is-invalid @enderror"
                                required>
                                <option value="" disabled selected>-- Select Plant --</option>

                                <option value="Unit" {{ old('plant') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                <option value="Body" {{ old('plant') == 'Body' ? 'selected' : '' }}>Body</option>
                                <option value="Electric" {{ old('plant') == 'Electric' ? 'selected' : '' }}>Electric
                                </option>
                                <option value="ALL" {{ old('plant') == 'ALL' ? 'selected' : '' }}>ALL</option>

                                @if (old('plant') && !in_array(old('plant'), ['Unit', 'Body', 'Electric', 'ALL']))
                                    <option value="{{ old('plant') }}" selected>{{ old('plant') }}</option>
                                @endif
                            </select>
                            @error('plant')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                    <button type="submit"
                        class="btn px-3 py-1 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
<x-sweetalert-confirm />
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            bindDeleteSweetAlert();

            // TomSelect for CODE (creatable)
            new TomSelect("#code-select", {
                create: true,
                persist: false,
                maxItems: 1,
                placeholder: "Choose or add code...",
                allowEmptyOption: true,
            });

            // TomSelect for PLANT (creatable + predefined options)
            new TomSelect("#plant-select", {
                create: false,
                persist: false,
                maxItems: 1,
                placeholder: "Choose plant...",
                allowEmptyOption: false, // ← penting
            });

            // TomSelect for EDIT modal (NO CREATE)
            document.querySelectorAll('[id^="editDepartmentModal-"]').forEach(modal => {
                const id = modal.getAttribute("id").replace("editDepartmentModal-", "");

                const codeSelect = modal.querySelector(`#edit-code-select-${id}`);
                const plantSelect = modal.querySelector(`#edit-plant-select-${id}`);

                if (codeSelect) {
                    new TomSelect(codeSelect, {
                        create: true,
                        maxItems: 1,
                        placeholder: "Select code...",
                        allowEmptyOption: false
                    });
                }

                if (plantSelect) {
                    new TomSelect(plantSelect, {
                        create: true,
                        maxItems: 1,
                        placeholder: "Select plant...",
                        allowEmptyOption: false
                    });
                }
            });

            function bindDeleteSweetAlert() {
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.removeEventListener('submit', form._swalHandler); // hapus handler lama jika ada

                    const handler = e => {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!'
                        }).then(result => {
                            if (result.isConfirmed) form.submit();
                        });
                    };

                    form.addEventListener('submit', handler);
                    form._swalHandler = handler; // simpan reference handler untuk bisa di-remove nanti
                });
            }

            const searchInput = document.getElementById("searchInput");
            const clearBtn = document.getElementById("clearSearch");
            const tableContainer = document.getElementById("tableContainer");

            let typingTimer;
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

                        // Update table + pagination
                        tableContainer.innerHTML = dom.querySelector("#tableContainer").innerHTML;

                        // Re-bind pagination after replacing DOM
                        bindPagination();
                        bindDeleteSweetAlert();

                        // Re-init Feather Icons
                        if (window.feather) {
                            feather.replace();
                        }

                        // Re-init Bootstrap tooltip (jika ada)
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

            // Live search
            searchInput.addEventListener("keyup", function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    let query = searchInput.value;
                    let url =
                        `{{ route('master.departments.index') }}?search=${encodeURIComponent(query)}`;
                    fetchData(url);
                }, delay);
            });

            // Enter key search
            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    let query = searchInput.value;
                    let url =
                        `{{ route('master.departments.index') }}?search=${encodeURIComponent(query)}`;
                    fetchData(url);
                }
            });

            // Clear search
            clearBtn.addEventListener("click", function() {
                searchInput.value = "";
                fetchData(`{{ route('master.departments.index') }}`);
            });

            // AJAX pagination
            function bindPagination() {
                document.querySelectorAll("#tableContainer .pagination a").forEach(link => {
                    link.addEventListener("click", function(e) {
                        e.preventDefault();
                        fetchData(this.href);
                    });
                });
            }

            bindPagination();
            //Cancel button modal
            const addDepartmentModal = document.getElementById("addDepartmentModal");
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

            const editModals = document.querySelectorAll('[id^="editDepartmentModal-"]');
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
    </script>
    @if ($errors->any() && session('edit_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("editDepartmentModal-{{ session('edit_modal') }}")).show();
            });
        </script>
    @endif

    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addDepartmentModal")).show();
            });
        </script>
    @endif
@endpush
