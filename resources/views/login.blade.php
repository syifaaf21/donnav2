<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    {{-- ✅ Include Tailwind via Vite --}}
    @vite('resources/css/app.css')

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Custom Style  -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- ✅ Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- ✅ Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


    @stack('styles')
</head>

<body>
    <div class="container min-vh-100 overflow-hidden">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-card text-center">

                    <!-- Logo -->
                    <div class="flex justify-center mb-6">
                        <img src="{{ asset('images/donna.png') }}" alt="Logo" class="w-60 h-auto">
                    </div>

                    <!-- Title -->
                    <h2 class="text-center text-2xl font-semibold text-gray-700 mb-6">
                        Sign in to your account
                    </h2>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- NPK -->
                        <div>
                            <label for="npk" class="block text-sm font-medium text-gray-700 mb-1">NPK</label>
                            <input type="number" name="npk" id="npk" maxlength="6"
                                oninput="this.value = this.value.slice(0, 6);" value="{{ old('npk') }}" required placeholder="Enter your 6-digit NPK"
                                autofocus
                                class="w-full px-4 py-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('npk') border-red-500 @enderror">
                            @error('npk')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="password" required placeholder="Enter password (min. 6 characters)"
                                class="w-full px-4 py-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Forgot password and create account links -->
                        <div class="text-center mt-4 space-y-2">
                            <p class="text-sm">
                                Forgot your password?
                                <a href="https://wa.me/082328591267" class="text-blue-600 hover:underline">Contact
                                    ITD</a>
                            </p>
                            <p class="text-sm">
                                Don't have an account?
                                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Create</a>
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit"
                                class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition duration-200 shadow">
                                Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
