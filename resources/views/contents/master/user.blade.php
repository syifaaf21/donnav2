@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Search Form -->
            <form method="GET" class="w-50 d-flex align-items-center" id="searchForm">
                <div class="input-group">
                    <input type="text" name="search" id="searchInput" class="form-control"
                        placeholder="Search by Name or NPK" value="{{ request('search') }}">

                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="clearSearch">
                        Clear
                    </button>
                </div>
            </form>

            <button class="btn btn-outline-primary ms-auto btn-sm shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#addUserModal" data-bs-title="Add New User">
                <i class="bi bi-plus-circle"></i>
                <span>Add User</span>
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-wrapper mb-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table modern-table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>NPK</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->npk }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role->name ?? '-' }}</td>
                                        <td>{{ $user->department->name ?? '-' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal-{{ $user->id }}"
                                                data-bs-title="Edit User">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            @if ($user->role_id !== 1)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        data-bs-title="Delete User">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit User Modals --}}
    @foreach ($users as $user)
        <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1"
            aria-labelledby="editUserModalLabel-{{ $user->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="editUserModalLabel-{{ $user->id }}">
                                <i class="bi bi-person-lines-fill me-2"></i>Edit User
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
                                    <select name="role_id"
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
                                    <select name="department_id"
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


    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_form" value="add">

            <div class="modal-content border-0 shadow-lg rounded-4">
                <!-- Header -->
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="addUserModalLabel">
                        <i class="bi bi-person-plus-fill me-2"></i>Create New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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

                        <!-- NPK -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">NPK</label>
                            <input type="text" name="npk"
                                class="form-control rounded-3 @error('npk') is-invalid @enderror"
                                value="{{ old('npk') }}" required pattern="\d{6}" maxlength="6"
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
                                value="{{ old('email') }}" required>
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
                                value="{{ old('password') }}" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                id="password_confirmation" class="form-control rounded-3"
                                minlength="6" autocomplete="new-password" required
                                title="Please retype the same password">
                        </div>

                        <!-- Role -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Role</label>
                            <select name="role_id"
                                class="form-select rounded-3 @error('role_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>
                                    -- Select Role --
                                </option>
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
                            <label class="form-label fw-medium">Department</label>
                            <select name="department_id"
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
                    <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary px-4">
                        <i class="bi bi-save2 me-1"></i>Save User
                    </button>
                </div>
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
