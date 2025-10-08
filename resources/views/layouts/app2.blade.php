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
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- ✅ Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- ✅ Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">


    @stack('styles')
</head>


<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <!-- ✅ Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            @include('layouts.partials.navbar')

            <!-- Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace(); // biar ikon feather muncul
    });
</script>

<script>
    feather.replace();

    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');

    toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');
        sidebarTexts.forEach(t => t.classList.toggle('hidden'));
    });

    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        dropdown.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }
</script>

