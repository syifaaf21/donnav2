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

    <!-- ✅ Sidebar CSS (bs-brain optional) -->
    <link href="https://unpkg.com/bs-brain@2.0.4/components/sidebars/sidebar-1/assets/css/sidebar-1.css" rel="stylesheet">

    <!-- ✅ Custom Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <!-- ✅ Custom Styles (optional from children views) -->
    @stack('styles')
</head>

<body>
  <div class="d-flex">
  <!-- Sidebar -->
  <x-sidebar />

  <!-- Page Content -->
  <div id="mainContent" class="flex-grow-1 p-3">
    @yield('content')
  </div>
</div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/sidebar.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @stack('scripts')
</body>

</html>
