{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>

    <link rel="icon" href="{{ url('images/favicon.ico') }}">

    {{-- ✅ Include Tailwind via Vite --}}
    @vite('resources/css/app.css')

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Custom Style  -->
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">

    {{-- ✅ Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="font-['Segoe_UI'] bg-primaryDark text-gray-800">

    @include('layouts.partials.sidebar')
    @include('components.flash-message')

    <!-- ✅ Main Content -->
    <div id="mainWrapper" class="flex flex-col min-h-screen transition-all duration-300 ml-64 my-2 px-2">
        {{-- @include('layouts.partials.navbar') --}}
        <div class="my-3 mr-8 ml-8 pt-2 bg-gradient-to-r from-gray-400/30 to-gray-200/60 rounded-3xl">
            @include('layouts.partials.navbar')
            <div class="flex items-center justify-between w-full px-8">
                <div class="text-white flex flex-col justify-center">
                    <h3 class="font-bold leading-tight">@yield('title', 'Dashboard')</h3>
                    <p class="max-w-[550px] text-white/80 text-sm">@yield('subtitle', '')</p>
                </div>
                <div class="flex items-center space-x-2 mr-48 mb-2">
                    @yield('breadcrumbs')
                </div>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-1 p-2 mx-8 my-2">
            @yield('content')
        </main>

        <!-- Footer Inside Main Wrapper -->
        @include('layouts.partials.footer')
    </div>

    {{-- <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script> --}}

    <!-- ✅ jQuery (load first, even if not required by Bootstrap 5) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ✅ Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ✅ AlpineJS -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- ✅ Custom & Plugins -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    @stack('scripts')

</body>

</html>
