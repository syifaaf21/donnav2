<header class="flex items-center justify-between px-24 py-3">
    <div class="flex items-center gap-3">
        <button id="openSidebarBtn" class="md:hidden">
            <i data-feather="menu"></i>
        </button>
        <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="flex items-center gap-4">
        <!-- Notification Dropdown -->
        <div class="relative">
    <button id="notificationBtn" class="relative focus:outline-none">
        <i data-feather="bell"></i>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 inline-block w-3 h-3 bg-red-600 rounded-full"></span>
        @endif
    </button>

    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-md z-50 max-h-64 overflow-y-auto">
        @forelse(auth()->user()->notifications->take(5) as $notification)
            <div class="p-2 border-b border-gray-200">
                <div class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'No message' }}</div>
                <div class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <div class="p-2 text-gray-500">No notifications</div>
        @endforelse

        <div class="text-center p-2">
            <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:underline text-sm">View All</a>
        </div>
    </div>
</div>

        <!-- Profile dropdown -->
        <div class="relative bg-white shadow rounded-xl border border-gray-100">
            <button id="profileDropdownBtn"
                class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm focus:outline-none">
                <div
                    class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold uppercase">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>

                <span class="hidden md:inline">{{ Auth::user()->name ?? 'User' }}</span>
                <i data-feather="chevron-down" class="w-4 h-4"></i>
            </button>

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
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown
    const dropdownBtn = document.getElementById('profileDropdownBtn');
    const dropdownMenu = document.getElementById('profileDropdownMenu');

    // Notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    dropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle('hidden');
        notificationDropdown.classList.add('hidden'); // tutup notif kalau profile dibuka
    });

    notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationDropdown.classList.toggle('hidden');
        dropdownMenu.classList.add('hidden'); // tutup profile kalau notif dibuka
    });

    // Klik di luar dropdown → otomatis tutup keduanya
    document.addEventListener('click', (e) => {
        if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
        if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Kalau kamu mau update badge atau list notifikasi via JS,
    // bisa pakai fetch AJAX ke backend, tapi kalau sudah dari blade,
    // tidak perlu script ini.
});
</script>
