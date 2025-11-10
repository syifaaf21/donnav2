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
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email"
                                class="form-control rounded-3 @error('email') is-invalid @enderror"
                                pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                title="Please enter a valid email address (e.g. user@example.com)"
                                value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password"
                                class="form-control rounded-3 @error('password') is-invalid @enderror" minlength="8"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                                value="{{ old('password') }}" required>
                            <small class="text-muted fst-italic d-block mt-1">
                                Must be at least 8 characters, include uppercase, lowercase, a number, and a special
                                character (e.g., @#$%).
                            </small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control rounded-3" minlength="8" autocomplete="new-password" required
                                title="Please retype the same password">
                            <small class="text-muted fst-italic d-block mt-1">
                                Please retype the same password for confirmation.
                            </small>
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
