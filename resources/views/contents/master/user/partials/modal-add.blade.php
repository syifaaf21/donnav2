<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('master.users.store') }}" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            <input type="hidden" name="_form" value="add">

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="addUserModalLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Create New User
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-4">
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" placeholder="Input user name"
                            class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NPK --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NPK <span class="text-danger">*</span></label>
                        <input type="number" name="npk" placeholder="Input user NPK"
                            class="form-control border-0 shadow-sm rounded-3 @error('npk') is-invalid @enderror"
                            value="{{ old('npk') }}" pattern="\d{6}" maxlength="6"
                            oninput="this.value = this.value.slice(0, 6);"
                            title="NPK must be exactly 6 digits of number" inputmode="numeric" required>
                        @error('npk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select id="department_select" name="department_ids[]"
                            class="form-select border-0 shadow-sm rounded-3 @error('department_ids') is-invalid @enderror"
                            multiple required>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ in_array($department->id, (array) old('department_ids', [])) ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email (shown only when Role = Dept Head) --}}
                    <div class="col-md-6" id="emailContainerAdd" style="display: none;">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="emailInputAdd" placeholder="Input user Email"
                            class="form-control border-0 shadow-sm rounded-3 @error('email') is-invalid @enderror"
                            pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                            title="Please enter a valid email address (e.g. user@example.com)"
                            value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="emailAddInvalidFeedback" class="invalid-feedback" style="display:none;">Email is required for Dept Head.</div>
                    </div>

                    {{-- Password --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="addPasswordInput" placeholder="Input user password"
                            class="form-control border-0 shadow-sm rounded-3 @error('password') is-invalid @enderror"
                            minlength="8"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_.])[A-Za-z\d@$!%*?&#_.]{8,}$"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            value="{{ old('password') }}" required>
                        <style>
                            .pwcheck-icon { width: 18px; height: 18px; display: inline-block; vertical-align: middle; }
                        </style>
                        <div id="addPasswordChecklist" class="mb-2" style="margin-top: 6px; font-size: 0.93em; color: #444;">
                            <div id="addpwlen" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></span>
                                <span class="pw-label">At least 8 characters</span>
                            </div>
                            <div id="addpwlower" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></span>
                                <span class="pw-label">Lowercase letter</span>
                            </div>
                            <div id="addpwupper" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></span>
                                <span class="pw-label">Uppercase letter</span>
                            </div>
                            <div id="addpwnum" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></span>
                                <span class="pw-label">Number</span>
                            </div>
                            <div id="addpwspecial" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></span>
                                <span class="pw-label">Special character (@$!%*?&#_.)</span>
                            </div>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="addConfirmPasswordInput" placeholder="Input confirm password"
                            class="form-control border-0 shadow-sm rounded-3" minlength="8" autocomplete="new-password"
                            required title="Please retype the same password">
                        <small class="text-muted fst-italic d-block mt-1">Please retype the same password for confirmation.</small>
                        <div id="addConfirmPasswordFeedback" class="invalid-feedback" style="display:none;">Confirm password doesn't match</div>
                    </div>

                    {{-- Role --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select id="role_select" name="role_ids[]"
                            class="form-select border-0 shadow-sm rounded-3 @error('role_ids') is-invalid @enderror"
                            multiple required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ in_array($role->id, (array) old('role_ids', [])) ? 'selected' : '' }}>
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
                        // helper to check old role on initial render
                        $oldRoleName = null;
                        if (old('role_id')) {
                            $r = collect($roles)->firstWhere('id', (int) old('role_id'));
                            $oldRoleName = $r->name ?? null;
                        }
                    @endphp

                    <div class="col-md-6" id="auditTypeContainer" style="display: none;">
                        <label class="form-label fw-medium">Audit Type <span class="text-danger">*</span></label>
                        <select id="audit_type_select" name="audit_type_ids[]"
                            class="form-select rounded-3 @error('audit_type_ids') is-invalid @enderror"
                            multiple>
                            @foreach ($auditTypes as $a)
                                <option value="{{ $a->id }}"
                                    {{ in_array($a->id, (array) old('audit_type_ids', [])) ? 'selected' : '' }}>{{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('audit_type_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <script>
                (function() {
                    const roleSelect = document.getElementById('role_select');
                    const auditTypeContainer = document.getElementById('auditTypeContainer');
                    const auditTypeSelect = document.getElementById('audit_type_select');
                    const emailContainer = document.getElementById('emailContainerAdd');
                    const emailInput = document.getElementById('emailInputAdd');
                    const emailInvalidFeedback = document.getElementById('emailAddInvalidFeedback');
                    const form = roleSelect ? roleSelect.closest('form') : null;

                    // Password validation
                    const passwordInput = document.getElementById('addPasswordInput');
                    const confirmInput = document.getElementById('addConfirmPasswordInput');
                    const confirmFeedback = document.getElementById('addConfirmPasswordFeedback');
                    const checklist = {
                        len: document.getElementById('addpwlen'),
                        lower: document.getElementById('addpwlower'),
                        upper: document.getElementById('addpwupper'),
                        num: document.getElementById('addpwnum'),
                        special: document.getElementById('addpwspecial'),
                    };
                    function updateChecklist(val) {
                        // length
                        const lenOk = val.length >= 8;
                        checklist.len.querySelector('.icon-status').innerHTML = lenOk ? '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.len.querySelector('.pw-label').className = 'pw-label ' + (lenOk ? 'text-success' : 'text-danger');
                        // lowercase
                        const lowerOk = /[a-z]/.test(val);
                        checklist.lower.querySelector('.icon-status').innerHTML = lowerOk ? '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.lower.querySelector('.pw-label').className = 'pw-label ' + (lowerOk ? 'text-success' : 'text-danger');
                        // uppercase
                        const upperOk = /[A-Z]/.test(val);
                        checklist.upper.querySelector('.icon-status').innerHTML = upperOk ? '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.upper.querySelector('.pw-label').className = 'pw-label ' + (upperOk ? 'text-success' : 'text-danger');
                        // number
                        const numOk = /[0-9]/.test(val);
                        checklist.num.querySelector('.icon-status').innerHTML = numOk ? '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.num.querySelector('.pw-label').className = 'pw-label ' + (numOk ? 'text-success' : 'text-danger');
                        // special
                        const specialOk = /[@$!%*?&#_.]/.test(val);
                        checklist.special.querySelector('.icon-status').innerHTML = specialOk ? '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.special.querySelector('.pw-label').className = 'pw-label ' + (specialOk ? 'text-success' : 'text-danger');
                    }
                    if (passwordInput) {
                        passwordInput.addEventListener('input', function(e) {
                            updateChecklist(e.target.value);
                        });
                        // Initial state
                        updateChecklist(passwordInput.value || '');
                    }
                    function validateConfirmPassword() {
                        if (!confirmInput) return;
                        if (confirmInput.value.length === 0) {
                            confirmInput.classList.remove('is-invalid');
                            if (confirmFeedback) confirmFeedback.style.display = 'none';
                            return;
                        }
                        if (passwordInput && confirmInput.value !== passwordInput.value) {
                            confirmInput.classList.add('is-invalid');
                            if (confirmFeedback) confirmFeedback.style.display = 'block';
                        } else {
                            confirmInput.classList.remove('is-invalid');
                            if (confirmFeedback) confirmFeedback.style.display = 'none';
                        }
                    }
                    if (confirmInput && passwordInput) {
                        confirmInput.addEventListener('input', validateConfirmPassword);
                        passwordInput.addEventListener('input', validateConfirmPassword);
                    }

                    // Audit type & email logic
                    if (!roleSelect || !auditTypeContainer) return;

                    function toggleAuditTypeAndEmail() {
                        const selected = Array.from(roleSelect.selectedOptions).map(o => (o.text || '').toLowerCase());
                        const hasAuditor = selected.some(t => t.includes('auditor') || t.includes('lead auditor'));
                        const hasDeptHead = selected.some(t => t.includes('dept head'));
                        // Audit type
                        if (hasAuditor) {
                            auditTypeContainer.style.display = 'block';
                            if (auditTypeSelect) auditTypeSelect.setAttribute('required', 'required');
                        } else {
                            auditTypeContainer.style.display = 'none';
                            if (auditTypeSelect) auditTypeSelect.removeAttribute('required');
                        }
                        // Email
                        if (emailContainer) {
                            if (hasDeptHead) {
                                emailContainer.style.display = 'block';
                                if (emailInput) emailInput.setAttribute('required', 'required');
                            } else {
                                emailContainer.style.display = 'none';
                                if (emailInput) {
                                    emailInput.removeAttribute('required');
                                    emailInput.value = '';
                                    emailInput.classList.remove('is-invalid');
                                    if (emailInvalidFeedback) emailInvalidFeedback.style.display = 'none';
                                }
                            }
                        }
                    }
                    roleSelect.addEventListener('change', toggleAuditTypeAndEmail);
                    // initial state
                    toggleAuditTypeAndEmail();

                    // Client-side validation: require email if dept head, and confirm password match
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            // Email required for dept head
                            const selected = Array.from(roleSelect.selectedOptions).map(o => (o.text || '').toLowerCase());
                            const hasDeptHead = selected.some(t => t.includes('dept head'));
                            if (hasDeptHead && emailContainer && emailInput) {
                                if (!emailInput.value.trim()) {
                                    e.preventDefault();
                                    emailInput.classList.add('is-invalid');
                                    if (emailInvalidFeedback) emailInvalidFeedback.style.display = 'block';
                                    emailInput.focus();
                                }
                            }
                            // Confirm password match
                            if (confirmInput && passwordInput && confirmInput.value !== passwordInput.value) {
                                e.preventDefault();
                                confirmInput.classList.add('is-invalid');
                                if (confirmFeedback) confirmFeedback.style.display = 'block';
                                confirmInput.focus();
                            }
                        });
                    }
                })();
            </script>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                    data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
