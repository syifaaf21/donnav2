<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite('resources/css/app.css')

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ url('images/favicon.ico') }}">
</head>

<body class="min-h-screen flex items-center justify-center bg-primaryLight font-inter">

    <!-- Login Card -->
    <div
        class="w-full max-w-sm p-4 bg-white/50 backdrop-blur-sm border border-white rounded-xl shadow-lg overflow-hidden transition-colors duration-200 focus-within:bg-white">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-4">
            <div class="p-3 bg-gradient-to-br from-primaryLight/10 to-primaryDark/10 rounded-full shadow-md">
                <div class="relative w-48 h-16 group">
                    <img src="{{ asset('images/madonna.png') }}" alt="Madonna logo"
                        class="absolute inset-0 w-full h-full object-contain transition-opacity duration-200 opacity-100 group-hover:opacity-0">
                    <img src="{{ asset('images/madonna-logo.png') }}" alt="Madonna hover"
                        class="absolute inset-0 w-full h-full object-contain transition-opacity duration-200 opacity-0 group-hover:opacity-100">
                </div>
            </div>
            <h2 class="mt-3 text-2xl font-semibold text-gray-800">Sign in</h2>
            {{-- <p class="mt-1 text-sm text-gray-500 text-center">Enter your 6-digit NPK and password to access your account</p> --}}

            <div class="w-16 h-px bg-gray-400 rounded mt-4"></div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Employee ID (NPK)</label>
                <input type="number" name="npk" id="npk" maxlength="6"
                    oninput="this.value = this.value.slice(0, 6);" value="{{ old('npk') }}" required
                    placeholder="Enter your NPK"
                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                @error('npk')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter your password"
                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-blue-600">
                <a href="https://wa.me/081399949961" class="hover:text-blue-700 transition font-medium">
                    Forgot password?
                </a>
                <a href="{{ route('register') }}" class="hover:text-blue-700 transition font-medium">
                    Create account
                </a>
            </div>

            <button type="submit"
                class="w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all duration-150">
                Login
            </button>
        </form>
    </div>

</body>


</html>
