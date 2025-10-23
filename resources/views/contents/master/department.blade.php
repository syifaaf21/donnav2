@extends('layouts.app')
@section('title', 'Department')

@section('content')
    <div class="container mx-auto px-4 py-2 ">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <!-- Breadcrumb -->
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
                    <li class="text-gray-700">Department</li>
                </ol>
            </nav>

            <!-- Add Button -->
            <button type="button" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Department</span>
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <!-- Search -->
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

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2 text-left">No</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Code</th>
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
                                <td class="px-4 py-2 text-center flex justify-center gap-2">
                                    <button data-bs-toggle="modal"
                                        data-bs-target="#editDepartmentModal-{{ $department->id }}"
                                        data-bs-title="Edit Department"
                                        class="bg-blue-600 text-white hover:bg-blue-700 p-2 rounded">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('master.departments.destroy', $department->id) }}" method="POST"
                                        class="delete-form inline">
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
                                <td colspan="4" class="text-center text-gray-500 py-4">No departments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t">
                {{ $departments->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.departments.store') }}" method="POST" class="modal-content rounded-xl">
                @csrf
                <input type="hidden" name="_form" value="add">

                <div class="modal-header bg-gray-100 rounded-t-xl">
                    <h5 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-plus-circle"></i>
                        Create New Department
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name</label>
                            <input type="text" name="name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Code</label>
                            <input type="text" name="code"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                value="{{ old('code') }}" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-gray-100 rounded-b-xl flex justify-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-700 rounded-lg text-gray-700 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach ($departments as $department)
        <div class="modal fade" id="editDepartmentModal-{{ $department->id }}" tabindex="-1"
            aria-labelledby="editDepartmentModalLabel-{{ $department->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.departments.update', $department->id) }}" method="POST"
                    class="modal-content rounded-xl">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    <div class="modal-header bg-gray-100 rounded-t-xl">
                        <h5 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-pencil-square"></i>
                            Edit Department
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Name</label>
                                <input type="text" name="name"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    value="{{ ucwords($department->name) }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Code</label>
                                <input type="text" name="code"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    value="{{ $department->code }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-gray-100 rounded-b-xl flex justify-between p-4">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
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
