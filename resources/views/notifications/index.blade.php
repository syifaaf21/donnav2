@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="max-w-3xl mx-auto mt-10 px-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-white">All Notifications</h1>

            <div class="flex items-center space-x-3">
                @php
                    $unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                @endphp

                @if ($unread > 0)
                    <div class="flex items-center">
                        <div class="relative inline-flex">
                            <button id="markAllReadBtn"
                                    class="text-sm bg-white text-blue-600 hover:underline px-3 py-1 rounded hover:bg-blue-4 00 transition">
                                Mark all as read
                            </button>

                            <!-- Badge melayang di atas kanan button -->
                            <span id="notifCountBadge"
                                  class="absolute -top-2 right-0 transform translate-x-1/2 inline-flex items-center justify-center bg-red-600 text-white text-xs font-semibold w-6 h-6 rounded-full shadow"
                                  aria-live="polite"
                                  aria-label="{{ $unread }} unread notifications">
                                {{ $unread > 99 ? '99+' : $unread }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @php
            use Carbon\Carbon;

            $groupedNotifications = auth()
                ->user()
                ->notifications->groupBy(function ($notification) {
                    $created = Carbon::parse($notification->created_at);

                    if ($created->isToday()) {
                        return 'Today';
                    } elseif ($created->isCurrentWeek()) {
                        return 'This Week';
                    } elseif ($created->isCurrentMonth()) {
                        return 'This Month';
                    } else {
                        return $created->format('F Y');
                    }
                });
        @endphp

        @forelse($groupedNotifications as $period => $notifications)
            <h2 class="text-lg font-semibold mb-3 mt-6 text-white">{{ $period }}</h2>

            @foreach ($notifications as $notification)
                <div id="notif-{{ $notification->id }}"
                     class="flex items-start justify-between p-4 mb-3 border rounded-lg transition-transform transform
                        hover:shadow-sm
                        @if (is_null($notification->read_at)) bg-blue-50 border-blue-100 @else bg-white border-gray-100 @endif">

                    <div class="flex items-start gap-3 flex-1">
                        {{-- visual indicator --}}
                        @if (is_null($notification->read_at))
                            <span class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0" aria-hidden="true"></span>
                        @else
                            <span class="w-3 h-3 rounded-full bg-gray-200 mt-2 flex-shrink-0" aria-hidden="true"></span>
                        @endif

                        <div class="min-w-0">
                            <a href="{{ route('notifications.read', $notification->id) }}"
                               class="notif-item block text-sm font-medium text-gray-900 hover:text-blue-600 break-words">
                                {{ $notification->data['message'] ?? 'No message' }}
                            </a>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center ml-4">
                        @if (is_null($notification->read_at))
                            <button type="button"
                                    class="mark-read-btn text-xs text-blue-600 hover:underline whitespace-nowrap ml-3 px-2 py-1 border border-blue-100 rounded hover:bg-blue-50 transition"
                                    data-id="{{ $notification->id }}">
                                Mark as read
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @empty
            <div class="py-10 text-center">
                <p class="text-gray-500">You have no notifications.</p>
            </div>
        @endforelse
    </div>

    {{-- AJAX Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // ✅ Mark single notification
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    try {
                        const res = await fetch(`/notifications/${id}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                        });

                        if (res.ok) {
                            // ubah tampilan tanpa reload
                            const notifCard = document.getElementById(`notif-${id}`);
                            notifCard.classList.remove('bg-blue-50');
                            notifCard.classList.add('bg-white');
                            this.remove();

                            // update counter (jika kamu punya badge notifikasi)
                            const badge = document.getElementById('notifCountBadge');
                            if (badge) {
                                let count = parseInt(badge.textContent);
                                if (count > 1) badge.textContent = count - 1;
                                else badge.remove();
                            }
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                });
            });

            // ✅ Mark all read
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', async function() {
                    try {
                        const res = await fetch(`/notifications/mark-all-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                        });

                        if (res.ok) {
                            document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                            document.querySelectorAll('[id^="notif-"]').forEach(div => {
                                div.classList.remove('bg-blue-50');
                                div.classList.add('bg-white');
                            });

                            const badge = document.getElementById('notifCountBadge');
                            if (badge) badge.remove();
                        }
                    } catch (error) {
                        console.error('Error marking all as read:', error);
                    }
                });
            }
        });
    </script>
@endsection
<style>
    /* Hilangkan underline pada semua link menu-sidebar */
    .notif-item {
        text-decoration: none !important;
    }
</style>
<style>
    /* Hilangkan underline pada semua link menu-sidebar */
    .notif-item {
        text-decoration: none !important;
    }
</style>
