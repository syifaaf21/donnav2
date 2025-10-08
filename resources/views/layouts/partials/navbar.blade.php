<!-- Navbar -->
<header class="flex items-center justify-between bg-white shadow px-4 py-3">
    <div class="flex items-center gap-3">
        <button id="openSidebarBtn" class="md:hidden">
            <i data-feather="menu"></i>
        </button>
        <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
            class="flex items-center gap-2 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm">
            <i data-feather="log-out" class="w-4 h-4"></i> Logout
        </button>
    </form>
</header>
