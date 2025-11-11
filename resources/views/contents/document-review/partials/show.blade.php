@extends('layouts.app')

@section('title', "Folder: $docCode")

@section('content')
<div class="p-6 bg-gray-50 min-h-screen space-y-6">
    <x-flash-message />

    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2 items-center">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('document-review.index') }}" class="text-blue-600 hover:underline">
                    Document Review
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-medium">{{ $docCode }} ({{ ucfirst($plant) }})</li>
        </ol>
    </nav>

</div>
@endsection
