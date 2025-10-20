@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="max-w-3xl mx-auto mt-10">
        <h1 class="text-xl font-semibold mb-4">All Notifications</h1>

        @php
            use Carbon\Carbon;

            $groupedNotifications = auth()->user()->notifications->groupBy(function ($notification) {
                $created = Carbon::parse($notification->created_at);

                if ($created->isToday()) {
                    return 'Today';
                } elseif ($created->isCurrentWeek()) {
                    return 'This Week';
                } elseif ($created->isCurrentMonth()) {
                    return 'This Month';
                } else {
                    return $created->format('F Y'); // misal "March 2025"
                }
            });
        @endphp

        @forelse($groupedNotifications as $period => $notifications)
            <h2 class="text-lg font-semibold mb-2 mt-6">{{ $period }}</h2>
            @foreach ($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}"
                   class="block p-4 mb-2 border rounded @if (is_null($notification->read_at)) bg-blue-50 @else bg-white @endif">
                    <div class="text-gray-800">{{ $notification->data['message'] ?? 'No message' }}</div>
                    <div class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
                </a>
            @endforeach
        @empty
            <p class="text-gray-500">You have no notifications.</p>
        @endforelse
    </div>
@endsection
