@extends('layouts.app')

@section('content')
    <div class="container py-2">
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
                        <a href="#" class="text-decoration-none text-secondary">Process</a>
                    </li>
                </ol>
            </nav>
            {{-- Tombol Add Process --}}
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProcessModal">
                <i class="bi bi-plus-circle"></i> Add Process
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="flex justify-content-end mb-3">
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
                    <div class="table-responsive">
                        <table class="min-w-full table-auto text-sm text-left text-gray-600">
                            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Plant</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($processes as $process)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ ($processes->currentPage() - 1) * $processes->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-3">{{ ucwords($process->name) }}</td>
                                        <td class="px-4 py-3">{{ $process->code }}</td>
                                        <td class="px-4 py-3">{{ $process->plant }}</td>
                                        <td class="px-4 py-3">
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editProcessModal-{{ $process->id }}"
                                                data-bs-title="Edit Process">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('master.processes.destroy', $process->id) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-bs-title="Delete Process">
                                                    <i class="bi bi-trash3"></i>
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
                        {{ $processes->withQueryString()->links() }}
                    </div>
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
                                <i class="bi bi-person-lines-fill me-2"></i>Edit Process
                            </h5>
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
                            <i class="bi bi-person-plus-fill me-2"></i>Create New Process
                        </h5>
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
                    <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary px-4">
                            <i class="bi bi-save2 me-1"></i>Save Process
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

@endpush
