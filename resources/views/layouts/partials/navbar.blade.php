<!-- Navbar -->
<header class="flex items-center justify-between px-16 py-3">
    <div class="flex items-center gap-3">
        <button id="openSidebarBtn" class="md:hidden">
            <i data-feather="menu"></i>
        </button>
        <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
    </div>

    <!-- Profile dropdown -->
    <div class="relative bg-white shadow rounded-xl border border-gray-100">
        <button id="profileDropdownBtn"
            class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm focus:outline-none">
            <!-- Inisial nama -->
            <div
                class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold uppercase">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>

            <span class="hidden md:inline">{{ Auth::user()->name ?? 'User' }}</span>
            <i data-feather="chevron-down" class="w-4 h-4"></i>
        </button>

        <!-- Dropdown menu -->
        <div id="profileDropdownMenu"
            class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-lg shadow-lg py-1 z-50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 text-sm">
                    <i data-feather="log-out" class="w-4 h-4"></i> Logout
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('profileDropdownBtn');
        const dropdownMenu = document.getElementById('profileDropdownMenu');

        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Klik di luar dropdown → otomatis tutup
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    });
</script>
