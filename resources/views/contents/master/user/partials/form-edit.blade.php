<form action="{{ route('master.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="_form" value="edit">

    <div class="modal-content rounded-lg shadow-lg">
        <div class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
            <h5 class="modal-title fw-semibold text-white" id="editUserModalLabel-{{ $user->id }}">
                <i class="bi bi-pencil-square me-2"></i> Edit User
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                        title="Please enter a valid email address (e.g. user@example.com)"
                        value="{{ old('email', $user->email ?: '') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="editPasswordInput_{{ $user->id }}" placeholder="Input the new password"
                        class="form-control border-0 shadow-sm rounded-3 @error('password') is-invalid @enderror"
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_.])[A-Za-z\d@$!%*?&#_.]{8,}$"
                        minlength="8"
                        title="Password must be at least 8 characters and contain uppercase, lowercase, number, and special character (@$!%*?&#_.)" value="">
                    <small class="text-muted fst-italic">Leave blank if not changing password</small>
                    <style>
                        .pwcheck-icon {
                            width: 18px;
                            height: 18px;
                            display: inline-block;
                            vertical-align: middle;
                        }
                    </style>
                    <div id="editPasswordChecklist_{{ $user->id }}" class="mb-2" style="margin-top: 6px; font-size: 0.93em; color: #444;">
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
                        function pwcheck_svg($type) {
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
                                checklist.len.querySelector('.icon-status').innerHTML = lenOk ? `{!! pwcheck_svg('pass') !!}` : `{!! pwcheck_svg('fail') !!}`;
                                checklist.len.querySelector('.pw-label').className = 'pw-label ' + (lenOk ? 'text-success' : 'text-danger');

                                // lowercase
                                const lowerOk = /[a-z]/.test(val);
                                checklist.lower.querySelector('.icon-status').innerHTML = lowerOk ? `{!! pwcheck_svg('pass') !!}` : `{!! pwcheck_svg('fail') !!}`;
                                checklist.lower.querySelector('.pw-label').className = 'pw-label ' + (lowerOk ? 'text-success' : 'text-danger');

                                // uppercase
                                const upperOk = /[A-Z]/.test(val);
                                checklist.upper.querySelector('.icon-status').innerHTML = upperOk ? `{!! pwcheck_svg('pass') !!}` : `{!! pwcheck_svg('fail') !!}`;
                                checklist.upper.querySelector('.pw-label').className = 'pw-label ' + (upperOk ? 'text-success' : 'text-danger');

                                // number
                                const numOk = /[0-9]/.test(val);
                                checklist.num.querySelector('.icon-status').innerHTML = numOk ? `{!! pwcheck_svg('pass') !!}` : `{!! pwcheck_svg('fail') !!}`;
                                checklist.num.querySelector('.pw-label').className = 'pw-label ' + (numOk ? 'text-success' : 'text-danger');

                                // special
                                const specialOk = /[@$!%*?&#_.]/.test(val);
                                checklist.special.querySelector('.icon-status').innerHTML = specialOk ? `{!! pwcheck_svg('pass') !!}` : `{!! pwcheck_svg('fail') !!}`;
                                checklist.special.querySelector('.pw-label').className = 'pw-label ' + (specialOk ? 'text-success' : 'text-danger');
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
                    <input type="password" name="password_confirmation" id="editConfirmPasswordInput_{{ $user->id }}" class="form-control rounded-3"
                        placeholder="Please retype the same password" minlength="6" value="">
                    <div id="editConfirmPasswordFeedback_{{ $user->id }}" class="invalid-feedback" style="display:none;">Confirm password doesn't match</div>
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
                    $hasAuditorRole =
                        $user->roles
                            ->pluck('name')
                            ->filter(function ($name) {
                                return stripos($name, 'auditor') !== false;
                            })
                            ->count() > 0;
                @endphp

                <div class="col-md-6" id="auditTypeContainerEdit_{{ $user->id }}"
                    style="display: {{ $hasAuditorRole ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">Audit Type<span class="text-danger">*</span></label>
                    <select name="audit_type_ids[]" id="audit_type_select_edit_{{ $user->id }}"
                        class="form-select border-0 shadow-sm rounded-3 @error('audit_type_ids') is-invalid @enderror"
                        multiple {{ $hasAuditorRole ? 'required' : '' }}>
                        @foreach ($auditTypes as $a)
                            <option value="{{ $a->id }}"
                                {{ $user->auditTypes->contains('id', $a->id) ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('audit_type_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <script>
                (function() {
                    // Delay untuk memastikan TomSelect sudah diinisialisasi
                    setTimeout(function() {
                        const roleSelect = document.getElementById('role_select_edit_{{ $user->id }}');
                        const auditTypeContainer = document.getElementById('auditTypeContainerEdit_{{ $user->id }}');
                        const auditTypeSelect = document.getElementById('audit_type_select_edit_{{ $user->id }}');
                        const emailContainer = document.getElementById('emailContainerEdit_{{ $user->id }}');
                        const form = roleSelect.closest('form');
                        const emailInput = emailContainer ? emailContainer.querySelector('input[name="email"]') : null;

                        // Password confirmation validation
                        const passwordInput = document.getElementById('editPasswordInput_{{ $user->id }}');
                        const confirmInput = document.getElementById('editConfirmPasswordInput_{{ $user->id }}');
                        const confirmFeedback = document.getElementById('editConfirmPasswordFeedback_{{ $user->id }}');

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

                        if (!roleSelect || !auditTypeContainer || !emailContainer) return;

                        function updateFieldVisibility() {
                            let hasAuditor = false;
                            let hasDeptHead = false;
                            const tomSelectInstance = roleSelect.tomselect;

                            function checkRoles(selectedValues) {
                                let foundAuditor = false;
                                let foundDeptHead = false;
                                if (Array.isArray(selectedValues)) {
                                    selectedValues.forEach(val => {
                                        const option = roleSelect.querySelector(`option[value="${val}"]`);
                                        if (option) {
                                            const roleName = (option.textContent || '').toLowerCase();
                                            if (roleName.includes('auditor')) foundAuditor = true;
                                            if (roleName.includes('dept head')) foundDeptHead = true;
                                        }
                                    });
                                } else if (selectedValues) {
                                    const option = roleSelect.querySelector(`option[value="${selectedValues}"]`);
                                    if (option) {
                                        const roleName = (option.textContent || '').toLowerCase();
                                        if (roleName.includes('auditor')) foundAuditor = true;
                                        if (roleName.includes('dept head')) foundDeptHead = true;
                                    }
                                }
                                return {
                                    foundAuditor,
                                    foundDeptHead
                                };
                            }

                            if (tomSelectInstance) {
                                const selectedValues = tomSelectInstance.getValue();
                                const {
                                    foundAuditor,
                                    foundDeptHead
                                } = checkRoles(selectedValues);
                                hasAuditor = foundAuditor;
                                hasDeptHead = foundDeptHead;
                            } else {
                                const selectedOptions = Array.from(roleSelect.selectedOptions);
                                hasAuditor = selectedOptions.some(opt => (opt.text || '').toLowerCase().includes('auditor'));
                                hasDeptHead = selectedOptions.some(opt => (opt.text || '').toLowerCase().includes('dept head'));
                            }

                            // Audit Type visibility
                            if (hasAuditor) {
                                auditTypeContainer.style.display = 'block';
                                if (auditTypeSelect) {
                                    auditTypeSelect.setAttribute('required', 'required');
                                }
                            } else {
                                auditTypeContainer.style.display = 'none';
                                if (auditTypeSelect) {
                                    const auditTomSelect = auditTypeSelect.tomselect;
                                    if (auditTomSelect) {
                                        auditTomSelect.clear();
                                    }
                                    auditTypeSelect.removeAttribute('required');
                                }
                            }

                            // Email: always visible and required
                            emailContainer.style.display = 'block';
                            if (emailInput) emailInput.setAttribute('required', 'required');
                        }

                        // Listen to change event
                        if (roleSelect.tomselect) {
                            roleSelect.tomselect.on('change', updateFieldVisibility);
                        }
                        roleSelect.addEventListener('change', updateFieldVisibility);

                        // Initial state
                        updateFieldVisibility();

                        // Client-side validation: require email if dept head
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                let hasDeptHead = false;
                                const tomSelectInstance = roleSelect.tomselect;
                                let selectedValues = [];
                                if (tomSelectInstance) {
                                    selectedValues = tomSelectInstance.getValue();
                                } else {
                                    selectedValues = Array.from(roleSelect.selectedOptions).map(opt => opt.value);
                                }
                                if (Array.isArray(selectedValues)) {
                                    hasDeptHead = selectedValues.some(val => {
                                        const option = roleSelect.querySelector(`option[value="${val}"]`);
                                        return option && (option.textContent || '').toLowerCase().includes('dept head');
                                    });
                                }
                                if (emailContainer && emailInput) {
                                    if (!emailInput.value.trim()) {
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
                                }
                                // Confirm password match validation on submit
                                if (confirmInput && passwordInput && confirmInput.value !== passwordInput.value) {
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

        <div class="modal-footer border-t p-4 justify-content-between bg-white rounded-b-lg">
            <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded">
                Save Changes
            </button>
        </div>
    </div>
</form>
