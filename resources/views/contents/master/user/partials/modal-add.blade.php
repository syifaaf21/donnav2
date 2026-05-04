<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('master.users.store') }}" method="POST" class="modal-content rounded-lg shadow-lg">
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
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-3">
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
                    <div class="col-md-6" id="emailContainerAdd">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="emailInputAdd" placeholder="Input user Email"
                            class="form-control border-0 shadow-sm rounded-3 @error('email') is-invalid @enderror"
                            title="Please enter a valid email address (e.g. user@example.com)"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="emailAddInvalidFeedback" class="invalid-feedback" style="display:none;">Email is
                            required for Dept Head.</div>
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
                            .pwcheck-icon {
                                width: 18px;
                                height: 18px;
                                display: inline-block;
                                vertical-align: middle;
                            }
                        </style>
                        <div id="addPasswordChecklist" class="mb-2"
                            style="margin-top: 6px; font-size: 0.93em; color: #444;">
                            <div id="addpwlen" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20"
                                        fill="none">
                                        <circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2"
                                            fill="#fff" />
                                        <path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg></span>
                                <span class="pw-label">At least 8 characters</span>
                            </div>
                            <div id="addpwlower" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20"
                                        fill="none">
                                        <circle cx="10" cy="10" r="9" stroke="#ef4444"
                                            stroke-width="2" fill="#fff" />
                                        <path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg></span>
                                <span class="pw-label">Lowercase letter</span>
                            </div>
                            <div id="addpwupper" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20"
                                        fill="none">
                                        <circle cx="10" cy="10" r="9" stroke="#ef4444"
                                            stroke-width="2" fill="#fff" />
                                        <path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg></span>
                                <span class="pw-label">Uppercase letter</span>
                            </div>
                            <div id="addpwnum" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20"
                                        fill="none">
                                        <circle cx="10" cy="10" r="9" stroke="#ef4444"
                                            stroke-width="2" fill="#fff" />
                                        <path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg></span>
                                <span class="pw-label">Number</span>
                            </div>
                            <div id="addpwspecial" class="d-flex align-items-center mb-1">
                                <span class="icon-status me-2"><svg class="pwcheck-icon" viewBox="0 0 20 20"
                                        fill="none">
                                        <circle cx="10" cy="10" r="9" stroke="#ef4444"
                                            stroke-width="2" fill="#fff" />
                                        <path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg></span>
                                <span class="pw-label">Special character (@$!%*?&#_.)</span>
                            </div>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span
                                class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="addConfirmPasswordInput"
                            placeholder="Input confirm password" class="form-control border-0 shadow-sm rounded-3"
                            minlength="8" autocomplete="new-password" required
                            title="Please retype the same password">
                        <small class="text-muted fst-italic d-block mt-1">Please retype the same password for
                            confirmation.</small>
                        <div id="addConfirmPasswordFeedback" class="invalid-feedback" style="display:none;">Confirm
                            password doesn't match</div>
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

                    <!-- Audit Type per Role (shown only when Auditor / Lead Auditor role is selected) -->
                    @php
                        $auditTypes = \App\Models\Audit::select('id', 'name')->get();
                        $auditorRolesList = $roles->filter(fn($r) => stripos($r->name, 'auditor') !== false)->values();
                    @endphp

                    @foreach ($auditorRolesList as $role)
                        <div class="col-12 audit-type-role-section-add" id="auditTypeSection_add_{{ $role->id }}"
                            data-role-id="{{ $role->id }}" style="display: none;">
                            <div class="card border shadow-sm rounded-3" style="overflow:visible;">
                                <div class="card-header d-flex align-items-center gap-2 py-2 px-3"
                                    style="background: linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%); border-bottom:1px solid #bfdbfe;">
                                    <i class="bi bi-clipboard-check text-primary"></i>
                                    <span class="fw-semibold text-dark" style="font-size:.9rem;">
                                        Audit Type &mdash; <span class="text-primary">{{ $role->name }}</span>
                                    </span>
                                    <span class="ms-auto badge text-bg-danger"
                                        style="font-size:.7rem;">Required</span>
                                </div>
                                <div class="card-body p-3">
                                    <select name="audit_type_ids_by_role[{{ $role->id }}][]"
                                        id="audit_type_select_add_{{ $role->id }}"
                                        class="form-select rounded-3 @error('audit_type_ids_by_role.' . $role->id) is-invalid @enderror"
                                        multiple>
                                        @foreach ($auditTypes as $a)
                                            <option value="{{ $a->id }}"
                                                {{ in_array($a->id, (array) old("audit_type_ids_by_role.{$role->id}", [])) ? 'selected' : '' }}>
                                                {{ $a->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('audit_type_ids_by_role.' . $role->id)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <script>
                (function() {
                    const roleSelect = document.getElementById('role_select');
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
                        checklist.len.querySelector('.icon-status').innerHTML = lenOk ?
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.len.querySelector('.pw-label').className = 'pw-label ' + (lenOk ? 'text-success' :
                            'text-danger');
                        // lowercase
                        const lowerOk = /[a-z]/.test(val);
                        checklist.lower.querySelector('.icon-status').innerHTML = lowerOk ?
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.lower.querySelector('.pw-label').className = 'pw-label ' + (lowerOk ? 'text-success' :
                            'text-danger');
                        // uppercase
                        const upperOk = /[A-Z]/.test(val);
                        checklist.upper.querySelector('.icon-status').innerHTML = upperOk ?
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.upper.querySelector('.pw-label').className = 'pw-label ' + (upperOk ? 'text-success' :
                            'text-danger');
                        // number
                        const numOk = /[0-9]/.test(val);
                        checklist.num.querySelector('.icon-status').innerHTML = numOk ?
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.num.querySelector('.pw-label').className = 'pw-label ' + (numOk ? 'text-success' :
                            'text-danger');
                        // special
                        const specialOk = /[@$!%*?&#_.]/.test(val);
                        checklist.special.querySelector('.icon-status').innerHTML = specialOk ?
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                            '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                        checklist.special.querySelector('.pw-label').className = 'pw-label ' + (specialOk ? 'text-success' :
                            'text-danger');
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
                    if (!roleSelect) return;

                    function getSelectedRoleIdsAdd() {
                        if (roleSelect.tomselect) {
                            const val = roleSelect.tomselect.getValue();
                            return Array.isArray(val) ? val.map(String) : [String(val)].filter(Boolean);
                        }
                        return Array.from(roleSelect.selectedOptions).map(o => String(o.value));
                    }

                    function toggleAuditTypeAndEmail() {
                        const selectedIds = getSelectedRoleIdsAdd();

                        // Toggle per-role audit type sections
                        document.querySelectorAll('.audit-type-role-section-add').forEach(function(section) {
                            const roleId = String(section.dataset.roleId);
                            const isSelected = selectedIds.includes(roleId);
                            section.style.display = isSelected ? 'block' : 'none';
                            const select = section.querySelector('select');
                            if (select) {
                                if (isSelected) {
                                    select.setAttribute('required', 'required');
                                    // Init TomSelect lazily only when section is visible
                                    if (!select.tomselect) {
                                        new TomSelect(select, {
                                            create: false,
                                            maxItems: null,
                                            plugins: ['remove_button'],
                                            placeholder: 'Select audit types'
                                        });
                                    }
                                } else {
                                    select.removeAttribute('required');
                                    if (select.tomselect) select.tomselect.clear();
                                }
                            }
                        });

                        // Email: always visible and required
                        if (emailContainer) {
                            emailContainer.style.display = 'block';
                            if (emailInput) emailInput.setAttribute('required', 'required');
                        }
                    }

                    roleSelect.addEventListener('change', toggleAuditTypeAndEmail);
                    // Re-run after TomSelect is fully initialised (DOMContentLoaded)
                    document.addEventListener('DOMContentLoaded', function() {
                        toggleAuditTypeAndEmail();
                        if (roleSelect.tomselect) {
                            roleSelect.tomselect.on('change', toggleAuditTypeAndEmail);
                        }
                    });
                    // Initial run (before TomSelect)
                    toggleAuditTypeAndEmail();

                    // Client-side validation: require email if dept head, and confirm password match
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            // Email required for all users
                            if (emailContainer && emailInput) {
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
                {{-- Permission: akses menu utama --}}
                {{-- Permission: akses menu utama --}}
                <div class="w-100 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <label class="form-label fw-semibold mb-0">Menu Permissions</label>
                        <span class="badge text-bg-danger" style="font-size: .7rem;">Required</span>
                    </div>
                    <p class="text-muted" style="font-size: .8rem; margin-bottom: .75rem;">
                        Select which main menus this user can access.
                    </p>

                    <div class="d-grid gap-2" style="grid-template-columns: repeat(3, 1fr); display: grid;">

                        {{-- Document Control --}}
                        <label class="perm-card d-flex flex-column gap-2 p-3 rounded-3 border"
                            style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                            <input class="d-none perm-checkbox" type="checkbox" name="permissions[]"
                                value="document_control"
                                {{ in_array('document_control', (array) old('permissions', [])) ? 'checked' : '' }}>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width:32px;height:32px;background:#eff6ff;">
                                    <i class="bi bi-file-earmark-text text-primary" style="font-size:15px;"></i>
                                </div>
                                <span
                                    class="perm-check-badge rounded-circle border d-flex align-items-center justify-content-center"
                                    style="width:18px;height:18px;border-color:#dee2e6 !important;transition:all .15s;"></span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0" style="font-size:.85rem;">Document Control</p>
                                <p class="text-muted mb-0" style="font-size:.75rem;">Manage Non-manufacturing docs</p>
                            </div>
                        </label>

                        {{-- Document Review --}}
                        <label class="perm-card d-flex flex-column gap-2 p-3 rounded-3 border"
                            style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                            <input class="d-none perm-checkbox" type="checkbox" name="permissions[]"
                                value="document_review"
                                {{ in_array('document_review', (array) old('permissions', [])) ? 'checked' : '' }}>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width:32px;height:32px;background:#f0fdf4;">
                                    <i class="bi bi-patch-check text-success" style="font-size:15px;"></i>
                                </div>
                                <span
                                    class="perm-check-badge rounded-circle border d-flex align-items-center justify-content-center"
                                    style="width:18px;height:18px;border-color:#dee2e6 !important;transition:all .15s;"></span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0" style="font-size:.85rem;">Document Review</p>
                                <p class="text-muted mb-0" style="font-size:.75rem;">Manage Manufacturing docs</p>
                            </div>
                        </label>

                        {{-- FTPP --}}
                        <label class="perm-card d-flex flex-column gap-2 p-3 rounded-3 border"
                            style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                            <input class="d-none perm-checkbox" type="checkbox" name="permissions[]" value="ftpp"
                                {{ in_array('ftpp', (array) old('permissions', [])) ? 'checked' : '' }}>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width:32px;height:32px;background:#fffbeb;">
                                    <i class="bi bi-star text-warning" style="font-size:15px;"></i>
                                </div>
                                <span
                                    class="perm-check-badge rounded-circle border d-flex align-items-center justify-content-center"
                                    style="width:18px;height:18px;border-color:#dee2e6 !important;transition:all .15s;"></span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0" style="font-size:.85rem;">FTPP</p>
                                <p class="text-muted mb-0" style="font-size:.75rem;">Managae Audit Findings</p>
                            </div>
                        </label>

                    </div>
                </div>
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit"
                    class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
<style>
    .perm-card.active {
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
    }

    .perm-card.active .perm-check-badge {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }
</style>
<script>
    document.querySelectorAll('.perm-card').forEach(function(card) {
        const cb = card.querySelector('.perm-checkbox');
        const badge = card.querySelector('.perm-check-badge');

        function syncState() {
            if (cb.checked) {
                card.classList.add('active');
                badge.innerHTML =
                    '<svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 5l2.5 2.5 3.5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            } else {
                card.classList.remove('active');
                badge.innerHTML = '';
            }
        }

        cb.addEventListener('change', syncState);
        syncState(); // initial state for old() values
    });
</script>
