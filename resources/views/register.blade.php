<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="container min-vh-100 d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card shadow-lg rounded-lg p-4 overflow-auto" style="max-height: 95vh;">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <img src="{{ asset('images/donna.png') }}" alt="Logo" class="w-32 h-auto mx-auto">
                </div>

                <h2 class="text-center text-xl font-semibold text-gray-700 mb-4">Create Your Account</h2>

                <form action="{{ route('register') }}" method="POST" class="space-y-3">
                    @csrf

                    <!-- NPK -->
                    <div>
                        <label for="npk" class="form-label">NPK</label>
                        <input type="number" name="npk" id="npk" maxlength="6"
                            oninput="this.value = this.value.slice(0,6);" value="{{ old('npk') }}" required
                            placeholder="Enter your 6-digit NPK"
                            class="form-control @error('npk') is-invalid @enderror">
                        @error('npk')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="Enter your full name"
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="Enter your email"
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" required
                            placeholder="Password (min. 6 characters)"
                            class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="Confirm your password" class="form-control">
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department" class="form-label">Department</label>
                        <select name="department" id="department" required
                            class="form-control @error('department') is-invalid @enderror">
                            <option value="" disabled selected>Select your department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department') == $dept->id ? 'selected' : '' }}>
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
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>

                    <p class="text-center text-sm mt-3">
                        Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect('#department', {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: "Select your department"
        });
    </script>
</body>
</html>
