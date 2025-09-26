<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Document')</title>

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- ✅ Sidebar CSS -->
    <link href="https://unpkg.com/bs-brain@2.0.4/components/sidebars/sidebar-1/assets/css/sidebar-1.css" rel="stylesheet">

    <!-- ✅ Custom Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <!-- ✅ Custom Style  -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Tambahan style dari child view -->
    @stack('styles')
</head>

<body>

    @if (!request()->is('login') && !request()->is('register') && !request()->is('password/*'))
        <div class="d-flex">
            <!-- Sidebar -->
            <x-sidebar />

            <!-- Page Content -->
            <div id="mainContent" class="flex-grow-1 p-3">
                <!-- Navbar -->
                <x-navbar />

                <x-flash-message/>

                <!-- Content -->
                @yield('content')
            </div>
        </div>
    @else
        {{-- Layout khusus login/register tanpa sidebar & navbar --}}
        <main class="auth-wrapper d-flex align-items-center justify-content-center" style="min-height: 100vh;">
            @yield('content')
        </main>
    @endif


    <!-- Bootstrap JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    <!-- ✅ Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ Custom Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    <!-- ✅ SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ jQuery + DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <!-- ✅ Script DataTables -->
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                paging: true, // Pagination
                searching: true, // Search box
                ordering: true, // Sorting
                lengthChange: true, // Pilihan jumlah data per halaman
                pageLength: 10, // Default 10 baris
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
