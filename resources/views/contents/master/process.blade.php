@extends('layouts.app')
@section('title', 'Process')

@section('content')
    <div class="container mx-auto px-4 py-2 ">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-3">
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
                    <li class="text-gray-700">Process</li>
                </ol>
            </nav>
            {{-- Tombol Add Process --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addProcessModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Process</span>
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
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

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">No</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Code</th>
                            <th class="px-4 py-2">Plant</th>
                            <th class="px-4 py-2">Action</th>
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
                                <td class="px-4 py-2">
                                    <button data-bs-toggle="modal"
                                        data-bs-target="#editProcessModal-{{ $process->id }}" data-bs-title="Edit Process"
                                        class="bg-blue-600 text-white hover:bg-blue-700 p-2 rounded">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('master.processes.destroy', $process->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            data-bs-title="Delete Process" class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-4">No process found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-3">
                {{ $processes->withQueryString()->links('vendor.pagination.tailwind') }}
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
                                <i class="bi bi-pencil-square"></i>
                                Edit Process
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Name</label>
                                    <input type="text" name="name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ ucwords($process->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Code -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Code</label>
                                    <input type="text" name="code"
                                        class="form-control rounded-3 @error('code') is-invalid @enderror"
                                        value="{{ $process->code }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Plant --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Plant</label>
                                    <select name="plant" class="form-select" required>
                                        @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                            <option value="{{ $plant }}" @selected($process->plant === $plant)>
                                                {{ $plant }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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
                            <i class="bi bi-plus-circle me-2"></i>Create New Process
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Name</label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Code -->
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

                    <!-- Footer (dikeluarkan dari row g-3!) -->
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
                </div> <!-- modal-content -->
            </form>
        </div> <!-- modal-dialog -->
    </div> <!-- modal -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // TomSelect untuk modal Add - Role
            new TomSelect('#role_select', {
                create: true,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or create a role',
                load: function(query, callback) {
                    let url = '/api/roles?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Add - Department
            new TomSelect('#department_select', {
                create: true,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or create a department',
                load: function(query, callback) {
                    let url = '/api/departments?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Edit (semua modal edit role select)
            document.querySelectorAll('select[id^="role_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false, // TIDAK boleh create baru
                    maxItems: 1,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select a role',
                    load: function(query, callback) {
                        let url = '/api/roles?q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    }
                });
            });

            // TomSelect untuk modal Edit (semua modal edit department select)
            document.querySelectorAll('select[id^="department_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false, // TIDAK boleh create baru
                    maxItems: 1,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select a department',
                    load: function(query, callback) {
                        let url = '/api/departments?q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    }
                });
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
