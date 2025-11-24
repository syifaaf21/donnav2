@extends('layouts.app')
@section('title', 'Process')

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
                    <li class="text-gray-700 font-medium">Process</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addProcessModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Process</span>
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <form method="GET" id="searchForm" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                </form>
            </div>

            <div id="tableContainer">
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
                            @forelse ($processes as $process)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        {{ ($processes->currentPage() - 1) * $processes->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-2">{{ ucwords($process->name) }}</td>
                                    <td class="px-4 py-2">{{ $process->code }}</td>
                                    <td class="px-4 py-2">{{ $process->plant }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#editProcessModal-{{ $process->id }}"
                                            data-bs-title="Edit Process"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                            <i data-feather="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.processes.destroy', $process->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" data-bs-title="Delete Process"
                                                class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-4">No processes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $processes->withQueryString()->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>
    </div>


    {{-- Edit Process Modals --}}
    @foreach ($processes as $process)
        <div class="modal fade" id="editProcessModal-{{ $process->id }}" tabindex="-1"
            aria-labelledby="editProcessModalLabel-{{ $process->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.processes.update', $process->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="editProcessModalLabel-{{ $process->id }}">
                                <i class="bi bi-pencil-square text-primary"></i>
                                Edit Process
                            </h5>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" placeholder="Enter process name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ ucwords($process->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Code -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" placeholder="Enter product code"
                                        class="form-control rounded-3 @error('code') is-invalid @enderror"
                                        value="{{ $process->code }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Plant --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-medium">Plant <span class="text-danger">*</span></label>
                                    <select name="plant" class="form-select tom-plant" required>
                                        @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                            <option value="{{ $plant }}" @selected($process->plant === $plant)>
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
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Add Process Modal -->
    <div class="modal fade" id="addProcessModal" tabindex="-1" aria-labelledby="addProcessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.processes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_form" value="add">

                <div class="modal-content border-0 shadow-lg rounded-4">
                    <!-- Header -->
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="addProcessModalLabel">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Process
                        </h5>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="Enter process name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" placeholder="Enter product code"
                                    class="form-control rounded-3 @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-12">
                                <label class="form-label">Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-select tom-plant" required>
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

                    <!-- Footer (dikeluarkan dari row g-3!) -->
                    <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                            Submit
                        </button>
                    </div>
                </div> <!-- modal-content -->
            </form>
        </div> <!-- modal-dialog -->
    </div> <!-- modal -->
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
            // ===================== LIVE SEARCH & AJAX =====================
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

            // Ketika mengetik
            searchInput.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const q = searchInput.value;
                    const url =
                        `{{ route('master.processes.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }, delay);
            });

            // Enter key
            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const q = searchInput.value;
                    const url = `{{ route('master.processes.index') }}?search=${encodeURIComponent(q)}`;
                    fetchData(url);
                }
            });

            // Clear search
            clearBtn.addEventListener("click", function() {
                searchInput.value = "";
                fetchData(`{{ route('master.processes.index') }}`);
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

            //Cancel button modal
            const addDepartmentModal = document.getElementById("addProcessModal");
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

            const editModals = document.querySelectorAll('[id^="editProcessModal-"]');
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
                new bootstrap.Modal(document.getElementById("editProcessModal-{{ session('edit_modal') }}")).show();
            });
        </script>
    @endif

    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addProcessModal")).show();
            });
        </script>
    @endif
@endpush
