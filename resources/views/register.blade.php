<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

</head>

<body class="bg-gray-100">

    <div class="container min-vh-100 d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card shadow-lg rounded-xl p-4 overflow-auto" style="max-height: 95vh;">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <img src="{{ asset('images/donna.png') }}" alt="Logo" class="w-32 h-auto mx-auto">
                </div>

                <!-- Title -->
                <h2 class="text-center text-xl font-semibold text-gray-700 mb-4">Create Your Account</h2>

                <form action="{{ route('register') }}" method="POST" class="space-y-3">
                    @csrf

                    <!-- NPK -->
                    <div>
                        <label for="npk" class="form-label fw-semibold small">NPK</label>
                        <input type="number" name="npk" id="npk" maxlength="6"
                            oninput="this.value = this.value.slice(0,6);" value="{{ old('npk') }}" required
                            placeholder="Enter your 6-digit NPK"
                            class="form-control form-control-sm @error('npk') is-invalid @enderror">
                        @error('npk')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="form-label fw-semibold small">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="Enter your full name"
                            class="form-control form-control-sm @error('name') is-invalid @enderror">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            placeholder="Enter your email"
                            class="form-control form-control-sm @error('email') is-invalid @enderror">
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="position-relative mb-3">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <input type="password" name="password" id="password" required placeholder="Enter your password"
                            class="form-control form-control-sm @error('password') is-invalid @enderror"
                            style="padding-right: 3rem;" />
                        <span class="position-absolute end-0 pe-3"
                            style="cursor:pointer; top: 45%; transform: translateY(-50%);"
                            onclick="togglePassword('password', 'togglePassword')">
                            <i id="togglePassword" class="bi bi-eye-fill"></i>
                        </span>

                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                            Must be at least 8 characters, include uppercase, lowercase, a number, and a special
                            character (e.g., @#$%).
                        </small>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="position-relative mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold small">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="Confirm your password" class="form-control form-control-sm"
                            style="padding-right: 3rem;" />
                        <span class="position-absolute end-0 pe-3"
                            style="cursor:pointer; top: 75%; transform: translateY(-50%);"
                            onclick="togglePassword('password_confirmation', 'toggleConfirmPassword')">
                            <i id="toggleConfirmPassword" class="bi bi-eye-fill"></i>
                        </span>

                    </div>



                    <!-- Department -->
                    <div>
                        <label for="department" class="form-label fw-semibold small">Department</label>
                        <select name="department" id="department" required
                            class="form-control form-control-sm @error('department') is-invalid @enderror">
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
                    <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">Register</button>
                    </div>

                    <p class="text-center text-sm mt-3" style="font-size: 0.8rem;">
                        Already have an account? <a href="{{ route('login') }}"
                            class="text-blue-600 hover:underline">Sign in</a>
                    </p>
                </form>
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

</body>

</html>
