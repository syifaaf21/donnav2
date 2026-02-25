<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

</head>

<body
    class="min-h-screen bg-primaryLight flex items-center justify-center">

    <div class="container d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card shadow-lg rounded-3">
                <div class="text-grey-700 rounded-top mx-auto p-3">
                    <div class="d-flex align-items-center">
                        {{-- <img src="{{ asset('images/madonna-logo.png') }}" alt="Logo" class="me-3"
                            style="height:40px;"> --}}
                        <div>
                            <h5 class="mb-0">Create Your Account</h5>
                            <small class="opacity-75">Join and access your dashboard</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('register') }}" method="POST" class="row g-3">
                        @csrf

                        <!-- NPK -->
                        <div class="col-12">
                            <label for="npk" class="form-label fw-semibold small">
                                <i class="bi bi-card-heading me-1"></i>NPK
                                <span class="text-muted small ms-1">(6 digits)</span>
                            </label>
                            <input type="number" name="npk" id="npk" maxlength="6" inputmode="numeric"
                                oninput="this.value = this.value.slice(0,6);" value="{{ old('npk') }}" required
                                placeholder="Enter your 6-digit NPK"
                                class="form-control form-control-sm @error('npk') is-invalid @enderror"
                                aria-describedby="npkHelp" />
                            <div id="npkHelp" class="form-text small">Only numeric characters, max 6 digits.</div>
                            @error('npk')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Full Name -->
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small">
                                <i class="bi bi-person-fill me-1"></i>Full Name
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                placeholder="Enter your full name"
                                class="form-control form-control-sm @error('name') is-invalid @enderror" />
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold small">
                                <i class="bi bi-envelope-fill me-1"></i>Email
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                placeholder="Enter your email"
                                class="form-control form-control-sm @error('email') is-invalid @enderror" required/>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="col-12">
                            <label for="password" class="form-label fw-semibold small">
                                <i class="bi bi-lock-fill me-1"></i>Password
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="password" id="password" required
                                    placeholder="Enter your password"
                                    class="form-control form-control-sm @error('password') is-invalid @enderror"
                                    oninput="validatePassword()" />
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('password', 'togglePassword')"
                                    aria-label="Toggle password visibility">
                                    <i id="togglePassword" class="bi bi-eye-fill"></i>
                                </button>
                            </div>

                            <small id="passwordValidationMessage" class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                                <ul id="passwordValidationList" style="list-style:none;padding-left:0;margin-bottom:0;">
                                    <li id="pw-length" class="d-flex align-items-center mb-1"><span class="me-2"><i class="bi bi-x-circle text-danger"></i></span>At least 8 characters</li>
                                    <li id="pw-lower" class="d-flex align-items-center mb-1"><span class="me-2"><i class="bi bi-x-circle text-danger"></i></span>Lowercase letter</li>
                                    <li id="pw-upper" class="d-flex align-items-center mb-1"><span class="me-2"><i class="bi bi-x-circle text-danger"></i></span>Uppercase letter</li>
                                    <li id="pw-number" class="d-flex align-items-center mb-1"><span class="me-2"><i class="bi bi-x-circle text-danger"></i></span>Number</li>
                                    <li id="pw-special" class="d-flex align-items-center mb-1"><span class="me-2"><i class="bi bi-x-circle text-danger"></i></span>Special character (@#$%^&+=!)</li>
                                </ul>
                            </small>
                            @error('password')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label fw-semibold small">
                                <i class="bi bi-lock-fill me-1"></i>Confirm Password
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    placeholder="Confirm your password" class="form-control form-control-sm"
                                    oninput="validateConfirmPassword()" />
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('password_confirmation', 'toggleConfirmPassword')"
                                    aria-label="Toggle confirm password visibility">
                                    <i id="toggleConfirmPassword" class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <small id="confirmPasswordValidationMessage" class="text-danger d-block mt-2"
                                style="font-size: 0.78rem;"></small>
                        </div>

                        <!-- Department -->
                        <div class="col-12">
                            <label for="department" class="form-label fw-semibold small">
                                <i class="bi bi-building me-1"></i>Department
                            </label>
                            <select name="department" id="department" required
                                class="form-select form-select-sm @error('department') is-invalid @enderror">
                                <option value="" disabled selected>Select your department</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="col-12 d-grid mt-2">
                            <button type="submit"
                                class="w-full py-2 text-sm bg-gradient-to-r from-primaryLight to-primaryDark text-white font-medium rounded-lg shadow-sm hover:from-primaryDark hover:to-primaryLight transition-all duration-200">Register</button>
                        </div>

                        <div class="col-12 text-center">
                            <p class="text-muted small mb-0">
                                Already have an account?
                                <a href="{{ route('login') }}"
                                    class="text-blue-500 fw-semibold hover:text-blue-700">Sign in</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect('#department', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: "Select your department"
        });

        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                field.type = "password";
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }
    </script>
    @if (session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                timer: 2000, // muncul 2 detik
                showConfirmButton: false
            });
        </script>
    @endif

    <script>
        function validatePassword() {
            const password = document.getElementById('password').value;
            // Checklist elements
            const lengthEl = document.getElementById('pw-length');
            const lowerEl = document.getElementById('pw-lower');
            const upperEl = document.getElementById('pw-upper');
            const numberEl = document.getElementById('pw-number');
            const specialEl = document.getElementById('pw-special');

            // At least 8 characters
            if (password.length >= 8) {
                lengthEl.querySelector('i').className = 'bi bi-check-circle-fill text-success';
                lengthEl.classList.add('text-success');
            } else {
                lengthEl.querySelector('i').className = 'bi bi-x-circle text-danger';
                lengthEl.classList.remove('text-success');
            }
            // Lowercase
            if (/[a-z]/.test(password)) {
                lowerEl.querySelector('i').className = 'bi bi-check-circle-fill text-success';
                lowerEl.classList.add('text-success');
            } else {
                lowerEl.querySelector('i').className = 'bi bi-x-circle text-danger';
                lowerEl.classList.remove('text-success');
            }
            // Uppercase
            if (/[A-Z]/.test(password)) {
                upperEl.querySelector('i').className = 'bi bi-check-circle-fill text-success';
                upperEl.classList.add('text-success');
            } else {
                upperEl.querySelector('i').className = 'bi bi-x-circle text-danger';
                upperEl.classList.remove('text-success');
            }
            // Number
            if (/\d/.test(password)) {
                numberEl.querySelector('i').className = 'bi bi-check-circle-fill text-success';
                numberEl.classList.add('text-success');
            } else {
                numberEl.querySelector('i').className = 'bi bi-x-circle text-danger';
                numberEl.classList.remove('text-success');
            }
            // Special character
            if (/[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(password)) {
                specialEl.querySelector('i').className = 'bi bi-check-circle-fill text-success';
                specialEl.classList.add('text-success');
            } else {
                specialEl.querySelector('i').className = 'bi bi-x-circle text-danger';
                specialEl.classList.remove('text-success');
            }

            validateConfirmPassword(); // Revalidate confirm password
        }

        function validateConfirmPassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const validationMessage = document.getElementById('confirmPasswordValidationMessage');

            if (confirmPassword && password !== confirmPassword) {
                validationMessage.textContent = "Passwords do not match.";
            } else {
                validationMessage.textContent = "";
            }
        }
    </script>

</body>

</html>
<style>
    .ts-dropdown {
        top: auto !important;
        bottom: 100% !important;
        margin-bottom: 4px !important;
        animation: dropUp 0.15s ease-out;
    }

    @keyframes dropUp {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
