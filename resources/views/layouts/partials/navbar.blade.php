<header class="flex items-center justify-between mx-12 py-3">
    <div class="item-start bg-white border border-gray-100 px-3 py-1 rounded-lg shadow">
        <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="flex items-center gap-4 ml-auto ">
        <!-- Notification Dropdown -->
        <div class="relative bg-white shadow rounded-3xl border border-gray-100 px-3 py-2">
            <button id="notificationBtn" type="button" class="relative focus:outline-none"
                aria-haspopup="true" aria-controls="notificationDropdown" aria-expanded="false" title="Notifications">
                <span class="sr-only">Open notifications</span>
                <i data-feather="bell" class="w-6 h-6"></i>

                @php
                    $unreadCount = auth()->user()->unreadNotifications->count();
                @endphp

                @if ($unreadCount > 0)
                    <span
                        class="absolute -top-1 -right-1 inline-flex items-center justify-center
                       w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-full"
                        aria-hidden="false" aria-live="polite" aria-atomic="true">
                        <span class="sr-only">{{ $unreadCount }} unread notifications</span>
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <div id="notificationDropdown"
                class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-md z-50 flex flex-col max-h-64 overflow-hidden"
                role="menu" aria-labelledby="notificationBtn">
                <div class="overflow-y-auto flex-grow">
                    @forelse(auth()->user()->notifications->take(5) as $notification)
                        <div
                            class="p-2 border-b border-gray-200
                    {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}" role="none">
                            <div class="text-sm text-gray-800" role="menuitem">
                                {{ $notification->data['message'] ?? 'No message' }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="p-2 text-gray-500">No notifications</div>
                    @endforelse
                </div>

                <div class="text-center p-2 border-t border-gray-200">
                    <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:underline text-sm">View
                        All</a>
                </div>
            </div>
        </div>

        <!-- Profile dropdown -->
        <div class="relative shadow rounded-xl border border-gray-100">
            <button id="profileDropdownBtn" type="button"
                class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm focus:outline-none"
                aria-haspopup="true" aria-controls="profileDropdownMenu" aria-expanded="false" title="Profile">
                <span class="sr-only">Open profile menu</span>
                <div
                    class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold uppercase">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>

                <span class="hidden md:inline">{{ Auth::user()->name ?? 'User' }}</span>
                <i data-feather="chevron-down" class="w-4 h-4 transform transition-transform duration-200"></i>
            </button>

            <div id="profileDropdownMenu"
                class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-lg shadow-lg py-1 z-50"
                role="menu" aria-labelledby="profileDropdownBtn">
                <form action="{{ route('logout') }}" method="POST" role="none">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 text-sm"
                        role="menuitem">
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

        function closeDropdown(elBtn, elMenu) {
            if (!elBtn || !elMenu) return;
            elMenu.classList.add('hidden');
            elBtn.setAttribute('aria-expanded', 'false');
            const icon = elBtn.querySelector('i[data-feather]');
            if (icon) icon.classList.remove('rotate-180');
        }

        function openDropdown(elBtn, elMenu) {
            if (!elBtn || !elMenu) return;
            elMenu.classList.remove('hidden');
            elBtn.setAttribute('aria-expanded', 'true');
            const icon = elBtn.querySelector('i[data-feather]');
            if (icon) icon.classList.add('rotate-180');
        }

        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownMenu.classList.contains('hidden') === false;
            if (isOpen) closeDropdown(dropdownBtn, dropdownMenu);
            else {
                openDropdown(dropdownBtn, dropdownMenu);
                closeDropdown(notificationBtn, notificationDropdown);
            }
        });

        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = notificationDropdown.classList.contains('hidden') === false;
            if (isOpen) closeDropdown(notificationBtn, notificationDropdown);
            else {
                openDropdown(notificationBtn, notificationDropdown);
                closeDropdown(dropdownBtn, dropdownMenu);
            }
        });

        // keyboard support: Enter / Space to toggle, Escape to close
        [dropdownBtn, notificationBtn].forEach(btn => {
            btn.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter' || ev.key === ' ') {
                    ev.preventDefault();
                    btn.click();
                } else if (ev.key === 'Escape') {
                    // close its menu
                    const menuId = btn.getAttribute('aria-controls');
                    const menuEl = document.getElementById(menuId);
                    if (menuEl && !menuEl.classList.contains('hidden')) {
                        closeDropdown(btn, menuEl);
                        btn.focus();
                    }
                }
            });
        });

        // Klik di luar dropdown → otomatis tutup keduanya
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
                closeDropdown(dropdownBtn, dropdownMenu);
            }
            if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                closeDropdown(notificationBtn, notificationDropdown);
            }
        });

        // close on Escape globally
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown(dropdownBtn, dropdownMenu);
                closeDropdown(notificationBtn, notificationDropdown);
            }
        });
    });
</script>
