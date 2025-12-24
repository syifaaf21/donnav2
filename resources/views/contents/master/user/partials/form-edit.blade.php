<form action="{{ route('master.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="_form" value="edit">

    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
            style="background-color: #f5f5f7;">
            <h5 class="modal-title fw-semibold text-dark" id="editUserModalLabel-{{ $user->id }}"
                style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                <i class="bi bi-pencil-square me-2 text-primary"></i> Edit User
            </h5>
            <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                data-bs-dismiss="modal" aria-label="Close" style="width: 36px; height: 36px; border: 1px solid #ddd;">
                <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
            </button>
        </div>

        <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            <div class="row g-3">
                <!-- Name -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name<span class="text-danger">*</span></label>
                    <input type="text" name="name"
                        class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                        value="{{ $user->name }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- NPK -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NPK<span class="text-danger">*</span></label>
                    <input type="text" name="npk"
                        class="form-control border-0 shadow-sm rounded-3 @error('npk') is-invalid @enderror"
                        value="{{ $user->npk }}" required pattern="\d{6}" maxlength="6"
                        title="NPK must be exactly 6 digits of number" inputmode="numeric">
                    @error('npk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                {{-- <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email"
                        class="form-control border-0 shadow-sm rounded-3 @error('email') is-invalid @enderror"
                        pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                        title="Please enter a valid email address (e.g. user@example.com)" value="{{ $user->email ?: '-' }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> --}}

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" placeholder="Input the new password"
                        class="form-control border-0 shadow-sm rounded-3 @error('password') is-invalid @enderror"
                        pattern=".{6,}" minlength="6" title="Password must be at least 6 characters" value="">
                    <small class="text-muted fst-italic">Leave blank if not changing password</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control rounded-3"
                        placeholder="Please retype the same password" minlength="6" value="">
                </div>

                <!-- Role -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role<span class="text-danger">*</span></label>
                    <select name="role_ids[]" id="role_select_edit_{{ $user->id }}"
                        class="form-select border-0 shadow-sm rounded-3 @error('role_ids') is-invalid @enderror"
                        multiple required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ $user->roles->contains('id', $role->id) ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Audit Type (shown only when Role = Auditor) -->
                @php
                    $auditTypes = \App\Models\Audit::select('id', 'name')->get();
                    $roleName = $user->roles->pluck('name')->first() ?? null;
                    if (old('role_id')) {
                        $r = collect($roles)->firstWhere('id', (int) old('role_id'));
                        $roleName = $r->name ?? $roleName;
                    }
                @endphp

                <div class="col-md-6" id="auditTypeContainerEdit_{{ $user->id }}"
                    style="display: {{ $user->roles->pluck('name')->map(fn($n) => strtolower($n))->contains('auditor') ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">Audit Type<span class="text-danger">*</span></label>
                    <select name="audit_type_id" id="audit_type_select_edit_{{ $user->id }}"
                        class="form-select border-0 shadow-sm rounded-3 @error('audit_type_id') is-invalid @enderror">
                        <option value="" disabled
                            {{ old('audit_type_id', $user->audit_type_id) ? '' : 'selected' }}>-- Select
                            Audit Type --</option>
                        @foreach ($auditTypes as $a)
                            <option value="{{ $a->id }}"
                                {{ (old('audit_type_id') ? old('audit_type_id') == $a->id : $user->audit_type_id == $a->id) ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('audit_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Department -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department<span class="text-danger">*</span></label>
                    <select name="department_ids[]" id="department_select_edit_{{ $user->id }}"
                        class="form-select border-0 shadow-sm rounded-3 @error('department_ids') is-invalid @enderror"
                        multiple required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ $user->departments->contains('id', $department->id) ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <script>
                (function() {
                    const roleSelect = document.getElementById('role_select_edit_{{ $user->id }}');
                    const auditTypeContainer = document.getElementById('auditTypeContainerEdit_{{ $user->id }}');
                    const auditTypeSelect = document.getElementById('audit_type_select_edit_{{ $user->id }}');

                    if (!roleSelect || !auditTypeContainer) return;

                    function toggleAuditType() {
                        const selected = Array.from(roleSelect.selectedOptions).map(o => (o.text || '').toLowerCase());
                        const hasAuditor = selected.some(t => t.includes('auditor'));
                        if (hasAuditor) {
                            auditTypeContainer.style.display = 'block';
                            if (auditTypeSelect) auditTypeSelect.setAttribute('required', 'required');
                        } else {
                            auditTypeContainer.style.display = 'none';
                            if (auditTypeSelect) auditTypeSelect.removeAttribute('required');
                        }
                    }

                    roleSelect.addEventListener('change', toggleAuditType);
                    // initial state
                    toggleAuditType();
                })();
            </script>
        </div>

        <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
            <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal"
                style="text-decoration: none; transition: background-color 0.3s ease;">
                Cancel
            </button>
            <button type="submit"
                class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                Save Changes
            </button>
        </div>
    </div>
</form>
