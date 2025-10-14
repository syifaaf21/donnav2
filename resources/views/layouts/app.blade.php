{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    {{-- ✅ Include Tailwind via Vite --}}
    @vite('resources/css/app.css')

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Custom Style  -->
    <link href="{{ asset('css/style2.css') }}" rel="stylesheet">

    {{-- ✅ Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- ✅ Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    {{-- Data Tables --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800">

    @include('layouts.partials.sidebar')

    <!-- ✅ Main Content -->
    <div id="mainWrapper" class="flex flex-col min-h-screen transition-all duration-300 ml-64">
        @include('layouts.partials.navbar')

        <!-- Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script> --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- ✅ Custom Script  -->
    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>



</html>
