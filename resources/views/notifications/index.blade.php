@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="max-w-3xl mx-auto mt-10">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">All Notifications</h1>

            @if (auth()->user()->unreadNotifications->count() > 0)
                <button id="markAllReadBtn" class="text-sm text-blue-600 hover:underline">
                    Mark all as read
                </button>
            @endif
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
            <h2 class="text-lg font-semibold mb-2 mt-6">{{ $period }}</h2>

            @foreach ($notifications as $notification)
                <div id="notif-{{ $notification->id }}"
                    class="flex items-start justify-between p-4 mb-2 border rounded transition-colors
            @if (is_null($notification->read_at)) bg-blue-50 @else bg-white @endif">

                    <div class="flex-1">
                        <a href="{{ route('notifications.read', $notification->id) }}"
                            class="notif-item block text-gray-800 hover:text-blue-600">
                            {{ $notification->data['message'] ?? 'No message' }}
                        </a>
                        <div class="text-xs text-gray-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if (is_null($notification->read_at))
                        <button type="button"
                            class="mark-read-btn text-xs text-blue-600 hover:underline whitespace-nowrap ml-3"
                            data-id="{{ $notification->id }}"> Mark as read </button>
                    @endif
                </div>
            @endforeach
        @empty
            <p class="text-gray-500">You have no notifications.</p>
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
