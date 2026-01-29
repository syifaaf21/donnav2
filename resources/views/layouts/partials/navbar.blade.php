<header id="minimalNavbar"
    class="fixed top-8 right-14 z-50 flex justify-between items-center px-2 py-1 bg-white/50 backdrop-blur-sm border border-white rounded-2xl shadow-md">
    <!-- RIGHT: Notification + Profile (pojok kanan) -->
    <div class="flex items-center px-2 py-1">
        <!-- Notification -->
        <div class="relative p-1 mt-2 mr-2">
            <button id="notificationBtn" type="button"
                class="relative focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" aria-haspopup="true"
                aria-controls="notificationDropdown" aria-expanded="false" title="Notifications">
                <span class="sr-only">Open notifications</span>
                <i data-feather="bell" class="w-5 h-5 text-gray-700"></i>

                @php
                    $unreadCount = auth()->user()->unreadNotifications->count();
                @endphp

                @if ($unreadCount > 0)
                    <span
                        class="absolute -top-1 -right-1 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full animate-pulse"
                        aria-hidden="false" aria-live="polite" aria-atomic="true">
                        <span class="sr-only">{{ $unreadCount }} unread notifications</span>
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <div id="notificationDropdown"
                class="hidden absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-xl shadow-xl ring-1 ring-black/5 z-50 flex flex-col overflow-hidden"
                role="menu" aria-labelledby="notificationBtn">

                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50/80">
                    <div class="flex items-center gap-2">
                        <i data-feather="bell" class="w-3 h-3 text-gray-700"></i>
                        <span class="text-xs font-medium text-gray-700">Notifications</span>
                    </div>
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 transition">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Notification list -->
                <div class="overflow-y-auto max-h-64 divide-y divide-gray-100">
                    @forelse(auth()->user()->notifications->take(8) as $notification)
                        @php
                            $isRedNotif =
                                in_array($notification->type, [
                                    \App\Notifications\DocumentCreatedNotification::class,
                                    \App\Notifications\FindingDueNotification::class,
                                ]) ||
                                ($notification->type === \App\Notifications\DocumentActionNotification::class &&
                                    (($notification->data['action'] ?? null) === 'rejected' ||
                                        str_contains(strtolower($notification->data['message'] ?? ''), 'rejected')));
                        @endphp
                        <a href="{{ $notification->data['url'] ?? '#' }}"
                            class="notification-item no-underline hover:no-underline flex items-start gap-3 px-4 py-3 {{ $isRedNotif ? 'bg-red-100 hover:bg-red-200' : '' }} hover:bg-gray-50 transition-colors"
                            role="menuitem" data-id="{{ $notification->id }}"
                            data-unread="{{ !$notification->read_at ? 'true' : 'false' }}">

                            <!-- Status dot -->
                            <span class="flex-none mt-1">
                                @if (is_null($notification->read_at))
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"
                                        aria-hidden="true"></span>
                                @elseif ($isRedNotif)
                                    <span class="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"
                                        aria-hidden="true"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-200 mt-2 flex-shrink-0"
                                        aria-hidden="true"></span>
                                @endif
                            </span>

                            <!-- Content -->
                            <div class="min-w-0 flex-auto">
                                <div
                                    class="text-xs leading-snug {{ $isRedNotif ? 'text-red-500' : 'text-gray-800' }}">
                                    {{ Str::limit($notification->data['message'] ?? 'No message', 120) }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <!-- NEW Badge -->
                            @if (!$notification->read_at)
                                <span
                                    class="flex-shrink-0 ml-2 px-1 py-0.5 text-[9px] font-semibold
                                text-blue-600 bg-blue-50 border border-blue-200 rounded-full">
                                    NEW
                                </span>
                            @endif
                        </a>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-500">
                                <i data-feather="inbox" class="w-5 h-5 mx-auto mb-2 text-gray-300"></i>
                                No notifications
                            </div>
                    @endforelse
                </div>

                <!-- Footer button -->
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    <a href="{{ route('notifications.index') }}"
                        class="block w-full text-center text-xs font-medium text-gray-700 bg-white
                   border border-gray-200 rounded-lg py-1.5
                   shadow-sm hover:bg-gray-100 hover:border-gray-300
                   transition-all duration-150 decoration-none">
                        View all notifications
                    </a>
                </div>
            </div>

        </div>

        <!-- Profile -->
        <div class="relative">
                    <button id="profileDropdownBtn" type="button"
                class="flex items-center gap-3 px-2 py-1 text-gray-700 transition-all duration-150" aria-haspopup="true"
                aria-controls="profileDropdownMenu" aria-expanded="false" title="Profile">
                    <div class="flex items-center gap-2">
                    <div
                        class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-blue-100 to-blue-500 text-gray-700 flex items-center justify-center font-bold uppercase text-sm shadow-inner">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <i data-feather="chevron-down"
                        class="w-3 h-3 text-gray-500 transform transition-transform duration-200 flex-shrink-0"></i>
                </div>
            </button>

            <div id="profileDropdownMenu"
                class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-xl py-2 z-50">

                <div class="px-4 border-b border-gray-100 py-2">
                    <p class="text-base font-semibold text-gray-800 truncate">
                        {{ Auth::user()->name ?? 'User' }}
                    </p>

                    <div class="space-y-1">
                        @if (Auth::user()->departments->count() > 0)
                            <div>
                                @foreach (Auth::user()->departments as $department)
                                    <span class="block text-xs text-gray-600">{{ $department->name }}</span>
                                @endforeach
                                <p class="text-[11px] text-gray-400 font-semibold">Departments</p>
                            </div>
                        @else
                            <span class="block text-xs text-gray-500">No departments</span>
                        @endif

                        @if (Auth::user()->roles->count() > 0)
                            <div>
                                @foreach (Auth::user()->roles as $role)
                                    <span class="block text-xs text-gray-600">{{ $role->name }}</span>
                                @endforeach
                                <p class="text-[11px] text-gray-400 font-semibold">Roles</p>

                            </div>
                        @else
                            <span class="block text-xs text-gray-500">No roles</span>
                        @endif
                    </div>
                </div>

                {{-- Edit Profile --}}
                <div class="px-2 pt-1">
                    <a href="{{ route('profile.index') }}"
                        class="w-full flex items-center gap-2 px-4 py-2.5
                   text-sm text-gray-700 rounded-lg
                   hover:bg-gray-50 transition
                   no-underline">
                        <i data-feather="user" class="w-4 h-4"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>

                {{-- Logout --}}
                <form action="{{ route('logout') }}" method="POST" role="none" class="px-2 pb-1">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition-colors">
                        <i data-feather="log-out" class="w-3 h-3"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebarBtn');
        const collapseBtn = document.getElementById('toggleSidebar');

        // Dropdowns
        const dropdownBtn = document.getElementById('profileDropdownBtn');
        const dropdownMenu = document.getElementById('profileDropdownMenu');
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

        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !dropdownMenu.classList.contains('hidden');
                if (isOpen) closeDropdown(dropdownBtn, dropdownMenu);
                else {
                    openDropdown(dropdownBtn, dropdownMenu);
                    closeDropdown(notificationBtn, notificationDropdown);
                }
            });
        }

        if (notificationBtn) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !notificationDropdown.classList.contains('hidden');
                if (isOpen) closeDropdown(notificationBtn, notificationDropdown);
                else {
                    openDropdown(notificationBtn, notificationDropdown);
                    closeDropdown(dropdownBtn, dropdownMenu);
                }
            });
        }

        // Sidebar open/collapse behavior
        function setSidebarState(expanded) {
            if (!sidebar) return;
            if (expanded) {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                if (openBtn) openBtn.classList.add('hidden');
                if (collapseBtn) collapseBtn.classList.remove('hidden');
                collapseBtn && collapseBtn.setAttribute('aria-expanded', 'true');
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                if (openBtn) openBtn.classList.remove('hidden');
                if (collapseBtn) collapseBtn.classList.add('hidden');
                collapseBtn && collapseBtn.setAttribute('aria-expanded', 'false');
            }
        }

        // initial state: assume collapsed if sidebar has w-20
        const initiallyExpanded = sidebar && sidebar.classList.contains('w-64');
        setSidebarState(initiallyExpanded);

        openBtn && openBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setSidebarState(true);
        });

        collapseBtn && collapseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setSidebarState(false);
        });

        // Close dropdowns on outside click or escape
        document.addEventListener('click', (e) => {
            if (dropdownMenu && !dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
                closeDropdown(dropdownBtn, dropdownMenu);
            }
            if (notificationDropdown && !notificationDropdown.contains(e.target) && !notificationBtn
                .contains(e.target)) {
                closeDropdown(notificationBtn, notificationDropdown);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown(dropdownBtn, dropdownMenu);
                closeDropdown(notificationBtn, notificationDropdown);
                // collapse sidebar with escape for convenience
                setSidebarState(false);
            }
        });

        // ✅ Mark as read on notification click
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', async function(e) {
                const notifId = this.dataset.id;
                const isUnread = this.dataset.unread === 'true';

                if (!isUnread) return; // Skip jika sudah read

                e.preventDefault();
                const url = this.href;
                const token = document.querySelector('meta[name="csrf-token"]')
                    .getAttribute('content');

                try {
                    await fetch(`/notifications/${notifId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    });

                    // Update badge counter
                    const badge = document.querySelector('span[aria-atomic="true"]');
                    if (badge) {
                        let count = parseInt(badge.textContent);
                        if (count > 1) {
                            badge.textContent = (count - 1) + '+';
                        } else {
                            badge.remove();
                        }
                    }

                    // Redirect ke URL
                    window.location.href = url;
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                    // Redirect tetap dilakukan meski error
                    window.location.href = url;
                }
            });
        });
    });
</script>
