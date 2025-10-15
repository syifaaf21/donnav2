@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto mt-10">
    <h1 class="text-xl font-semibold mb-4">All Notifications</h1>

    @forelse(auth()->user()->notifications as $notification)
        <div class="p-4 mb-2 border rounded @if(is_null($notification->read_at)) bg-blue-50 @else bg-white @endif">
            <div class="text-gray-800">{{ $notification->data['message'] ?? 'No message' }}</div>
            <div class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
        </div>
    @empty
        <p class="text-gray-500">You have no notifications.</p>
    @endforelse
</div>
@endsection
