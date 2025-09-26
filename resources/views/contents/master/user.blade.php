@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Search Form -->
            <form method="GET" class="w-50">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name or NPK"
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-auto btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <button class="btn btn-outline-primary ms-auto btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i> Add User
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
                                                data-bs-target="#editUserModal-{{ $user->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            @if ($user->role_id !== 1)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1"
                                        aria-labelledby="editUserModalLabel-{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editUserModalLabel-{{ $user->id }}">
                                                            Edit User
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Fields -->
                                                        <!-- Name -->
                                                        <div class="mb-3">
                                                            <label>Name</label>
                                                            <input type="text" name="name"
                                                                class="form-control @error('name') is-invalid @enderror"
                                                                value="{{ old('name', $user->name) }}" required>
                                                            @error('name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- NPK -->
                                                        <div class="mb-3">
                                                            <label>NPK</label>
                                                            <input type="text" name="npk"
                                                                class="form-control @error('npk') is-invalid @enderror"
                                                                value="{{ old('npk', $user->npk) }}" required>
                                                            @error('npk')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- Email -->
                                                        <div class="mb-3">
                                                            <label>Email</label>
                                                            <input type="email" name="email"
                                                                class="form-control @error('email') is-invalid @enderror"
                                                                value="{{ old('email', $user->email) }}" required>
                                                            @error('email')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- Password -->
                                                        <div class="mb-3">
                                                            <label>Password (optional)</label>
                                                            <input type="password" name="password"
                                                                class="form-control @error('password') is-invalid @enderror">
                                                            @error('password')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label>Confirm Password</label>
                                                            <input type="password" name="password_confirmation"
                                                                class="form-control">
                                                        </div>

                                                        <!-- Role -->
                                                        <div class="mb-3">
                                                            <label>Role</label>
                                                            <select name="role_id"
                                                                class="form-select @error('role_id') is-invalid @enderror"
                                                                required>
                                                                <option value="">Select Role</option>
                                                                @foreach ($roles as $role)
                                                                    <option value="{{ $role->id }}"
                                                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                                        {{ $role->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('role_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- Department -->
                                                        <div class="mb-3">
                                                            <label>Department</label>
                                                            <select name="department_id"
                                                                class="form-select @error('department_id') is-invalid @enderror"
                                                                required>
                                                                <option value="">Select Department</option>
                                                                @foreach ($departments as $department)
                                                                    <option value="{{ $department->id }}"
                                                                        {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                                        {{ $department->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('department_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>NPK</label>
                            <input type="text" name="npk" class="form-control @error('npk') is-invalid @enderror"
                                value="{{ old('npk') }}" required>
                            @error('npk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="text" name="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                value="{{ old('password') }}" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>-- Select Role --
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

                        <div class="mb-3">
                            <label>Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror"
                                required>
                                <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>-- Select
                                    Department --</option>
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


                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save User</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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
    </script>
@endpush
