<header class="flex items-center justify-between mx-12 py-3">
    <div class="item-start bg-white border border-gray-100 px-3 py-1 rounded-xl shadow">
        <h5 class="text-choco font-semibold">@yield('title', 'Dashboard')</h5>
    </div>

    <div class="flex items-center gap-4 ml-auto ">
        <!-- Notification Dropdown -->
        <div class="relative bg-white shadow rounded-full border border-gray-100 p-2">
            <button id="notificationBtn" type="button" class="relative focus:outline-none" aria-haspopup="true"
                aria-controls="notificationDropdown" aria-expanded="false" title="Notifications">
                <span class="sr-only">Open notifications</span>
                <i data-feather="bell" class="w-6 h-6 text-choco"></i>

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
                        <div class="p-2 border-b border-gray-200
                    {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}"
                            role="none">
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

        <!-- Profile Dropdown -->
        <div class="relative">
            <button id="profileDropdownBtn" type="button"
                class="flex items-center gap-3 px-3 py-2 bg-white border border-gray-200 hover:bg-gray-50
               rounded-xl shadow-sm text-choco transition-all duration-150"
                aria-haspopup="true" aria-controls="profileDropdownMenu" aria-expanded="false" title="Profile">

                <!-- Profile Initial -->
                <div
                    class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-choco
                   flex items-center justify-center font-bold uppercase text-base shadow-inner">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>

                <!-- Username -->
                <h6 class="truncate text-gray-800 max-w-[100px]">
                    {{ Auth::user()->name ?? 'User' }}
                </h6>

                <!-- Chevron Icon -->
                <i data-feather="chevron-down"
                    class="w-4 h-4 text-gray-500 transform transition-transform duration-200"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="profileDropdownMenu"
                class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-xl py-2 z-50">

                <!-- Name / Department / Role -->
                <div class="px-4 py-2 border-b border-gray-100">

                    <p class="text-xs text-gray-500 truncate">
                        {{ optional(Auth::user()->departments()->first())->name ?? 'Department' }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ optional(Auth::user()->roles()->first())->name ?? 'Role' }}
                    </p>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" role="none">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <i data-feather="log-out" class="w-4 h-4"></i>
                        <span>Logout</span>
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
