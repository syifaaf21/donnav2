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
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 flex items-center justify-center">

    <!-- Login Card -->
    <div class="w-full max-w-md p-6 bg-white rounded-2xl shadow-2xl overflow-auto">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/donna.png') }}" alt="Logo"
                class="w-32 h-auto mx-auto transform hover:scale-105 transition-transform duration-300">
        </div>

        <!-- Title -->
        <h2 class="text-center text-3xl font-bold text-gray-700 mb-6">Sign in to your account</h2>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- NPK -->
            <div>
                <label for="npk" class="block text-sm font-medium text-gray-600 mb-1">NPK</label>
                <input type="number" name="npk" id="npk" maxlength="6"
                    oninput="this.value = this.value.slice(0, 6);" value="{{ old('npk') }}" required
                    placeholder="Enter your 6-digit NPK"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                @error('npk')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    placeholder="Enter password (min. 8 characters with uppercase, lowercase, number & special)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Forgot password & create account -->
            <div class="flex flex-col items-center space-y-2 text-sm text-gray-600">
                <a href="https://wa.me/081399949961" class="hover:text-blue-600 transition">Forgot your password? Contact ITD</a>
                <a href="{{ route('register') }}" class="hover:text-blue-600 transition">Don't have an account? Create</a>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-300">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
