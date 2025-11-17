@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen space-y-6">
        <x-flash-message />

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Document Review</li>
            </ol>
        </nav>

        <!-- Tabs per Plant -->
        <ul class="nav nav-tabs flex border-b border-gray-300 mb-4" role="tablist">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif px-4 py-2 rounded-t-lg"
                        id="tab-{{ $plant }}" data-bs-toggle="tab" data-bs-target="#tab-content-{{ $plant }}"
                        type="button" role="tab" aria-controls="tab-content-{{ $plant }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ ucfirst($plant) }}
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content border border-t-0 rounded-b-lg p-4 bg-white">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                <div class="tab-pane fade @if ($loop->first) show active @endif"
                    id="tab-content-{{ $plant }}" role="tabpanel" aria-labelledby="tab-{{ $plant }}">

                    <!-- Folder Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($documentsByCode as $docCode => $documentMappings)
                            <a href="{{ route('document-review.showFolder', [
                                'plant' => $plant,
                                'docCode' => base64_encode($docCode),
                            ]) }}"
                                class="flex flex-col items-center justify-center border rounded-lg p-6 bg-yellow-50 hover:bg-yellow-100 hover:shadow-lg transition-all duration-200">
                                <i class="bi bi-folder-fill text-yellow-400 text-6xl mb-4"></i>
                                <h3 class="text-lg font-semibold text-dark-800">{{ $docCode }}</h3>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
