<form action="{{ route('master.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="_form" value="edit">

    <div class="modal-content rounded-lg shadow-lg">
        <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
            style="background-color: #f5f5f7;">
            <h5 class="modal-title fw-semibold text-dark" id="editUserModalLabel-{{ $user->id }}"
                style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                <i class="bi bi-pencil-square me-2 text-primary"></i> Edit User
            </h5>
            <button type="button"
                class="btn btn-light position-absolute top-0 end-0 m-3 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                data-bs-dismiss="modal" aria-label="Close" style="width: 36px; height: 36px; border: 1px solid #ddd;">
                <i class="bi bi-x-lg"></i>
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

                <!-- Email (shown only when Role = Dept Head, or validation error, or old input) -->
                {{-- @php
                    $showEmail = false;
                    // Show if validation error or old input exists
                    if (old('email') !== null || $errors->has('email')) {
                        $showEmail = true;
                    } else {
                        // Cek role dari old input
                        $oldRoles = old('role_ids', $user->roles->pluck('id')->toArray());
                        foreach ($roles as $role) {
                            if (in_array($role->id, (array) $oldRoles) && stripos($role->name, 'dept head') !== false) {
                                $showEmail = true;
                                break;
                            }
                        }
                    }
                @endphp --}}
                <div class="col-md-6" id="emailContainerEdit_{{ $user->id }}">
                    <label class="form-label fw-semibold">Email<span class="text-danger">*</span></label>
                    <input type="email" name="email"
                        class="form-control border-0 shadow-sm rounded-3 @error('email') is-invalid @enderror"
                        title="Please enter a valid email address (e.g. user@example.com)"
                        value="{{ old('email', $user->email ?: '') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="editPasswordInput_{{ $user->id }}"
                        placeholder="Input the new password"
                        class="form-control border-0 shadow-sm rounded-3 @error('password') is-invalid @enderror"
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_.])[A-Za-z\d@$!%*?&#_.]{8,}$"
                        minlength="8"
                        title="Password must be at least 8 characters and contain uppercase, lowercase, number, and special character (@$!%*?&#_.)"
                        value="">
                    <small class="text-muted fst-italic">Leave blank if not changing password</small>
                    <style>
                        .pwcheck-icon {
                            width: 18px;
                            height: 18px;
                            display: inline-block;
                            vertical-align: middle;
                        }
                    </style>
                    <div id="editPasswordChecklist_{{ $user->id }}" class="mb-2"
                        style="margin-top: 6px; font-size: 0.93em; color: #444;">
                        <div id="editpwlen_{{ $user->id }}" class="d-flex align-items-center mb-1">
                            <span class="icon-status me-2">{!! pwcheck_svg('fail') !!}</span>
                            <span class="pw-label">At least 8 characters</span>
                        </div>
                        <div id="editpwlower_{{ $user->id }}" class="d-flex align-items-center mb-1">
                            <span class="icon-status me-2">{!! pwcheck_svg('fail') !!}</span>
                            <span class="pw-label">Lowercase letter</span>
                        </div>
                        <div id="editpwupper_{{ $user->id }}" class="d-flex align-items-center mb-1">
                            <span class="icon-status me-2">{!! pwcheck_svg('fail') !!}</span>
                            <span class="pw-label">Uppercase letter</span>
                        </div>
                        <div id="editpwnum_{{ $user->id }}" class="d-flex align-items-center mb-1">
                            <span class="icon-status me-2">{!! pwcheck_svg('fail') !!}</span>
                            <span class="pw-label">Number</span>
                        </div>
                        <div id="editpwspecial_{{ $user->id }}" class="d-flex align-items-center mb-1">
                            <span class="icon-status me-2">{!! pwcheck_svg('fail') !!}</span>
                            <span class="pw-label">Special character (@$!%*?&#_.)</span>
                        </div>
                    </div>
                    @php
                        function pwcheck_svg($type)
                        {
                            if ($type === 'pass') {
                                return '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#22c55e" stroke-width="2" fill="#fff"/><path d="M6 10.5l3 3 5-5" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                            } else {
                                return '<svg class="pwcheck-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#ef4444" stroke-width="2" fill="#fff"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>';
                            }
                        }
                    @endphp
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <script>
                        (function() {
                            const input = document.getElementById('editPasswordInput_{{ $user->id }}');
                            const checklist = {
                                len: document.getElementById('editpwlen_{{ $user->id }}'),
                                lower: document.getElementById('editpwlower_{{ $user->id }}'),
                                upper: document.getElementById('editpwupper_{{ $user->id }}'),
                                num: document.getElementById('editpwnum_{{ $user->id }}'),
                                special: document.getElementById('editpwspecial_{{ $user->id }}'),
                            };

                            function updateChecklist(val) {
                                // length
                                const lenOk = val.length >= 8;
                                checklist.len.querySelector('.icon-status').innerHTML = lenOk ? `{!! pwcheck_svg('pass') !!}` :
                                    `{!! pwcheck_svg('fail') !!}`;
                                checklist.len.querySelector('.pw-label').className = 'pw-label ' + (lenOk ? 'text-success' :
                                    'text-danger');

                                // lowercase
                                const lowerOk = /[a-z]/.test(val);
                                checklist.lower.querySelector('.icon-status').innerHTML = lowerOk ? `{!! pwcheck_svg('pass') !!}` :
                                    `{!! pwcheck_svg('fail') !!}`;
                                checklist.lower.querySelector('.pw-label').className = 'pw-label ' + (lowerOk ? 'text-success' :
                                    'text-danger');

                                // uppercase
                                const upperOk = /[A-Z]/.test(val);
                                checklist.upper.querySelector('.icon-status').innerHTML = upperOk ? `{!! pwcheck_svg('pass') !!}` :
                                    `{!! pwcheck_svg('fail') !!}`;
                                checklist.upper.querySelector('.pw-label').className = 'pw-label ' + (upperOk ? 'text-success' :
                                    'text-danger');

                                // number
                                const numOk = /[0-9]/.test(val);
                                checklist.num.querySelector('.icon-status').innerHTML = numOk ? `{!! pwcheck_svg('pass') !!}` :
                                    `{!! pwcheck_svg('fail') !!}`;
                                checklist.num.querySelector('.pw-label').className = 'pw-label ' + (numOk ? 'text-success' :
                                    'text-danger');

                                // special
                                const specialOk = /[@$!%*?&#_.]/.test(val);
                                checklist.special.querySelector('.icon-status').innerHTML = specialOk ? `{!! pwcheck_svg('pass') !!}` :
                                    `{!! pwcheck_svg('fail') !!}`;
                                checklist.special.querySelector('.pw-label').className = 'pw-label ' + (specialOk ? 'text-success' :
                                    'text-danger');
                            }
                            input && input.addEventListener('input', function(e) {
                                updateChecklist(e.target.value);
                            });
                            // Initial state
                            updateChecklist(input ? input.value : '');
                        })();
                    </script>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        id="editConfirmPasswordInput_{{ $user->id }}" class="form-control rounded-3"
                        placeholder="Please retype the same password" minlength="6" value="">
                    <div id="editConfirmPasswordFeedback_{{ $user->id }}" class="invalid-feedback"
                        style="display:none;">Confirm password doesn't match</div>
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
                <!-- Audit Type per Role (shown only when Auditor / Lead Auditor role is selected) -->
                @php
                    $auditTypes = \App\Models\Audit::select('id', 'name')->get();
                    $auditorRolesList = $roles->filter(fn($r) => stripos($r->name, 'auditor') !== false)->values();
                @endphp

                @foreach ($auditorRolesList as $role)
                    @php
                        $isRoleActive = $user->roles->contains('id', $role->id);
                        $selectedAuditIds = old(
                            "audit_type_ids_by_role.{$role->id}",
                            $auditTypeIdsByRole[$role->id] ?? [],
                        );
                    @endphp
                    <div class="col-12 audit-type-role-section-edit"
                        id="auditTypeSection_edit_{{ $user->id }}_{{ $role->id }}"
                        data-role-id="{{ $role->id }}" style="display: {{ $isRoleActive ? 'block' : 'none' }};">
                        <div class="card border shadow-sm rounded-3" style="overflow:visible;">
                            <div class="card-header d-flex align-items-center gap-2 py-2 px-3"
                                style="background: linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%); border-bottom:1px solid #bfdbfe;">
                                <i class="bi bi-clipboard-check text-primary"></i>
                                <span class="fw-semibold text-dark" style="font-size:.9rem;">
                                    Audit Type &mdash; <span class="text-primary">{{ $role->name }}</span>
                                </span>
                                <span class="ms-auto badge text-bg-danger" style="font-size:.7rem;">Required</span>
                            </div>
                            <div class="card-body p-3">
                                <select name="audit_type_ids_by_role[{{ $role->id }}][]"
                                    id="audit_type_select_edit_{{ $user->id }}_{{ $role->id }}"
                                    class="form-select rounded-3 @error('audit_type_ids_by_role.' . $role->id) is-invalid @enderror"
                                    multiple {{ $isRoleActive ? 'required' : '' }}>
                                    @foreach ($auditTypes as $a)
                                        <option value="{{ $a->id }}"
                                            {{ in_array($a->id, (array) $selectedAuditIds) ? 'selected' : '' }}>
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
            <script>
                (function() {
                    // Delay to ensure TomSelect is fully initialised
                    setTimeout(function() {
                        const roleSelect = document.getElementById('role_select_edit_{{ $user->id }}');
                        const emailContainer = document.getElementById('emailContainerEdit_{{ $user->id }}');
                        const form = roleSelect ? roleSelect.closest('form') : null;
                        const emailInput = emailContainer ? emailContainer.querySelector('input[name="email"]') : null;

                        // Password confirmation validation
                        const passwordInput = document.getElementById('editPasswordInput_{{ $user->id }}');
                        const confirmInput = document.getElementById('editConfirmPasswordInput_{{ $user->id }}');
                        const confirmFeedback = document.getElementById(
                            'editConfirmPasswordFeedback_{{ $user->id }}');

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

                        if (!roleSelect || !emailContainer) return;

                        function getSelectedRoleIdsEdit() {
                            if (roleSelect.tomselect) {
                                const val = roleSelect.tomselect.getValue();
                                return Array.isArray(val) ? val.map(String) : [String(val)].filter(Boolean);
                            }
                            return Array.from(roleSelect.selectedOptions).map(o => String(o.value));
                        }

                        function updateFieldVisibility() {
                            const selectedIds = getSelectedRoleIdsEdit();

                            // Toggle per-role audit type sections for this user
                            document.querySelectorAll(
                                '[id^="auditTypeSection_edit_{{ $user->id }}_"]'
                            ).forEach(function(section) {
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
                            emailContainer.style.display = 'block';
                            if (emailInput) emailInput.setAttribute('required', 'required');
                        }

                        // Listen to change event (both TomSelect and native)
                        if (roleSelect.tomselect) {
                            roleSelect.tomselect.on('change', updateFieldVisibility);
                        }
                        roleSelect.addEventListener('change', updateFieldVisibility);

                        // Initial state
                        updateFieldVisibility();

                        // Client-side submit validation
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                if (emailContainer && emailInput && !emailInput.value.trim()) {
                                    e.preventDefault();
                                    emailInput.classList.add('is-invalid');
                                    let feedback = emailContainer.querySelector('.invalid-feedback');
                                    if (!feedback) {
                                        feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        emailInput.parentNode.appendChild(feedback);
                                    }
                                    feedback.textContent = 'Email is required.';
                                    emailInput.focus();
                                }
                                if (confirmInput && passwordInput && confirmInput.value !== passwordInput
                                    .value) {
                                    e.preventDefault();
                                    confirmInput.classList.add('is-invalid');
                                    if (confirmFeedback) confirmFeedback.style.display = 'block';
                                    confirmInput.focus();
                                }
                            });
                        }
                    }, 800);
                })();
            </script>
        </div>

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
                    <label class="perm-card-edit-{{ $user->id }} d-flex flex-column gap-2 p-3 rounded-3 border"
                        style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                        <input class="d-none perm-checkbox-edit-{{ $user->id }}" type="checkbox"
                            name="permissions[]" value="document_control"
                            {{ in_array('document_control', (array) old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
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
                    <label class="perm-card-edit-{{ $user->id }} d-flex flex-column gap-2 p-3 rounded-3 border"
                        style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                        <input class="d-none perm-checkbox-edit-{{ $user->id }}" type="checkbox"
                            name="permissions[]" value="document_review"
                            {{ in_array('document_review', (array) old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
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
                    <label class="perm-card-edit-{{ $user->id }} d-flex flex-column gap-2 p-3 rounded-3 border"
                        style="cursor: pointer; transition: all .15s; border-color: #dee2e6 !important;">
                        <input class="d-none perm-checkbox-edit-{{ $user->id }}" type="checkbox"
                            name="permissions[]" value="ftpp"
                            {{ in_array('ftpp', (array) old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
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


            <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit"
                class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded">
                Save Changes
            </button>
        </div>
    </div>
</form>
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
    (function() {
        const uid = '{{ $user->id }}';
        const cards = document.querySelectorAll('.perm-card-edit-' + uid);

        cards.forEach(function(card) {
            const cb = card.querySelector('.perm-checkbox-edit-' + uid);
            const badge = card.querySelector('.perm-check-badge');

            function syncState() {
                if (cb.checked) {
                    card.style.backgroundColor = '#eff6ff';
                    card.style.borderColor = '#3b82f6';
                    badge.style.backgroundColor = '#3b82f6';
                    badge.style.borderColor = '#3b82f6';
                    badge.innerHTML =
                        '<svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 5l2.5 2.5 3.5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                } else {
                    card.style.backgroundColor = '';
                    card.style.borderColor = '#dee2e6';
                    badge.style.backgroundColor = '';
                    badge.style.borderColor = '#dee2e6';
                    badge.innerHTML = '';
                }
            }

            cb.addEventListener('change', syncState);
            syncState(); // sync state awal dari checked/unchecked
        });
    })();
</script>
