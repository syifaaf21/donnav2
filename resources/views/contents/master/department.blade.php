@extends('layouts.app')
@section('title', 'Department')

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
                    <li class="text-gray-700 font-medium">Department</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Department</span>
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
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
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
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    {{ ($departments->currentPage() - 1) * $departments->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-2">{{ $department->name }}</td>
                                <td class="px-4 py-2">{{ $department->code }}</td>
                                <td class="px-4 py-2">{{ $department->plant }}</td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#editDepartmentModal-{{ $department->id }}"
                                        data-bs-title="Edit Department"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>

                                    <form action="{{ route('master.departments.destroy', $department->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-bs-title="Delete Department"
                                            class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
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
    <!-- Edit Modals -->
    @foreach ($departments as $department)
        <div class="modal fade" id="editDepartmentModal-{{ $department->id }}" tabindex="-1"
            aria-labelledby="editDepartmentModalLabel-{{ $department->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.departments.update', $department->id) }}" method="POST"
                    id="editForm-{{ $department->id }}" class="modal-content rounded-xl">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    <!-- Header -->
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="editDepartmentModalLabel-{{ $department->id }}">
                            <i class="bi bi-person-lines-fill me-2"></i>Edit Department
                        </h5>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-5">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name', $department->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code"
                                    class="form-control rounded-3 @error('code') is-invalid @enderror"
                                    value="{{ old('code', $department->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Plant -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-select rounded-3 @error('plant') is-invalid @enderror"
                                    required>
                                    <option value="" disabled
                                        {{ old('plant', $department->plant) ? '' : 'selected' }}>-- Select Plant --
                                    </option>
                                    <option value="Unit"
                                        {{ old('plant', $department->plant) == 'Unit' ? 'selected' : '' }}>Unit</option>
                                    <option value="Body"
                                        {{ old('plant', $department->plant) == 'Body' ? 'selected' : '' }}>Body</option>
                                    <option value="Electric"
                                        {{ old('plant', $department->plant) == 'Electric' ? 'selected' : '' }}>Electric
                                    </option>
                                    <option value="All"
                                        {{ old('plant', $department->plant) == 'All' ? 'selected' : '' }}>ALL</option>
                                </select>
                                @error('plant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
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
            <form action="{{ route('master.departments.store') }}" method="POST" class="modal-content rounded-xl">
                @csrf <input type="hidden" name="_form" value="add">
                <!-- Header -->
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="addDepartmentModalLabel"> <i
                            class="bi bi-plus-circle me-2 text-primary"></i>Create New Department </h5>
                </div> <!-- Body -->
                <div class="modal-body p-5">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6"> <label class="form-label fw-medium">Name <span class="text-danger">*</span></label> <input type="text"
                                name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required> @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Code -->
                        <div class="col-md-6"> <label class="form-label fw-medium">Code <span class="text-danger">*</span></label> <input type="text"
                                name="code" class="form-control rounded-3 @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" required> @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Plant -->
                        <div class="col-md-6"> <label class="form-label fw-medium">Plant <span class="text-danger">*</span></label> <select name="plant"
                                class="form-select rounded-3 @error('plant') is-invalid @enderror" required>
                                <option value="" disabled {{ old('plant') ? '' : 'selected' }}>-- Select Plant --
                                </option>
                                <option value="Unit" {{ old('plant') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                <option value="Body" {{ old('plant') == 'Body' ? 'selected' : '' }}>Body</option>
                                <option value="Electric" {{ old('plant') == 'Electric' ? 'selected' : '' }}>Electric
                                </option>
                                <option value="All" {{ old('plant') == 'All' ? 'selected' : '' }}>ALL</option>
                            </select> @error('plant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal"> Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // SweetAlert for delete confirmation
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
                    if (result.isConfirmed) this.submit();
                });
            });
        });

        // Clear search
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
            //Tooltip
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
                new bootstrap.Modal(document.getElementById("editDeparmentModal-{{ session('edit_modal') }}")).show();
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
