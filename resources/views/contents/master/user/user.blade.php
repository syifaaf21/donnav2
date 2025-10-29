@extends('layouts.app')
@section('title', 'User')

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
                    <li class="text-gray-700 font-medium">User</li>
                </ol>
            </nav>

            {{-- Add User Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addUserModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add User</span>
            </button>
        </div>

        {{-- Card --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
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

            {{-- Table --}}
            <div class="overflow-x-auto overflow-y-auto max-h-96">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2">No</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">NPK</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Department</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2">{{ $user->npk }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                <td class="px-4 py-2">{{ $user->role->name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $user->department->name ?? '-' }}</td>
                                <td class="px-4 py-2 flex gap-2">
                                    {{-- Edit Button --}}
                                    <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#editUserModal-{{ $user->id }}" data-bs-title="Edit User"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    {{-- Delete Button --}}
                                    @if ($user->role->name != 'Admin')
                                        <form action="{{ route('master.users.destroy', $user->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-600 text-white hover:bg-red-700 p-2 rounded"
                                                data-bs-title="Delete User">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $users->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>


    {{-- Edit User Modals --}}
    @foreach ($users as $user)
        <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1"
            aria-labelledby="editUserModalLabel-{{ $user->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('master.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="editUserModalLabel-{{ $user->id }}">
                                <i class="bi bi-pencil-square text-primary"> </i>Edit User
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
                                        value="{{ $user->name }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- NPK -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">NPK</label>
                                    <input type="text" name="npk"
                                        class="form-control rounded-3 @error('npk') is-invalid @enderror"
                                        value="{{ $user->npk }}" required pattern="\d{6}" maxlength="6"
                                        title="NPK must be exactly 6 digits of number" inputmode="numeric">
                                    @error('npk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Email</label>
                                    <input type="email" name="email"
                                        class="form-control rounded-3 @error('email') is-invalid @enderror"
                                        pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                        title="Please enter a valid email address (e.g. user@example.com)"
                                        value="{{ $user->email }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Password</label>
                                    <input type="password" name="password"
                                        class="form-control rounded-3 @error('password') is-invalid @enderror"
                                        pattern=".{6,}" minlength="6" title="Password must be at least 6 characters"
                                        value="">
                                    <small class="text-muted fst-italic">Leave blank if not changing password</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3"
                                        value="">
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Role</label>
                                    <select name="role_id" id="role_select_edit_{{ $user->id }}"
                                        class="form-select rounded-3 @error('role_id') is-invalid @enderror" required>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Department -->
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Department</label>
                                    <select name="department_id" id="department_select_edit_{{ $user->id }}"
                                        class="form-select rounded-3 @error('department_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ $user->department_id == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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


    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('master.users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_form" value="add">

                <div class="modal-content border-0 shadow-lg rounded-4">
                    <!-- Header -->
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="addUserModalLabel">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Create New User
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NPK -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">NPK <span class="text-danger">*</span></label>
                                <input type="number" name="npk"
                                    class="form-control rounded-3 @error('npk') is-invalid @enderror"
                                    value="{{ old('npk') }}" pattern="\d{6}" maxlength="6"
                                    oninput="this.value = this.value.slice(0, 6);"
                                    title="NPK must be exactly 6 digits of number" inputmode="numeric" required>
                                @error('npk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control rounded-3 @error('email') is-invalid @enderror"
                                    pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                    title="Please enter a valid email address (e.g. user@example.com)"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password"
                                    class="form-control rounded-3 @error('password') is-invalid @enderror" pattern=".{6,}"
                                    minlength="6" title="Password must be at least 6 characters"
                                    value="{{ old('password') }}" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Confirm Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control rounded-3" minlength="6" autocomplete="new-password" required
                                    title="Please retype the same password">
                            </div>

                            <!-- Role -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                <select id="role_select" name="role_id"
                                    class="form-select rounded-3 @error('role_id') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>-- Select Role
                                        --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                                <select id="department_select" name="department_id"
                                    class="form-select rounded-3 @error('department_id') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>
                                        -- Select Department --
                                    </option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
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
                            Submit
                        </button>
                    </div>
                </div>
            </form>
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

            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault(); // Stop form submit
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated'); // Tambahkan class validasi Bootstrap
                }, false);
            });

            // TomSelect untuk modal Add - Role
            new TomSelect('#role_select', {
                create: false,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search a role',
                load: function(query, callback) {
                    let url = '/api/roles?q=' + encodeURIComponent(query);
                    fetch(url)
                        .ten(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            new TomSelect('#department_select', {
                create: false,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search a department',
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
                new bootstrap.Modal(document.getElementById("editUserModal-{{ session('edit_modal') }}")).show();
            });
        </script>
    @endif

    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addUserModal")).show();
            });
        </script>
    @endif
@endpush
