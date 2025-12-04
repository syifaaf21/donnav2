@extends('layouts.app')
@section('title', 'Create New FTPP Finding')
@section('subtitle', 'Please enter the details below to create a new FTPP finding.')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-folder me-1"></i> FTPP
                </a>
            </li>
            <li>/</li>

            <li class="text-gray-700 font-bold">Create Finding</li>
        </ol>
    </nav>
@endsection

@php
    $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
@endphp
@section('content')
    <div class="px-6 space-y-4">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Create New FTPP Finding</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Please enter the details below to create a new FTPP finding.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>
                        <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-folder me-1"></i> FTPP
                        </a>
                    </li>
                    <li>/</li>

                    <li class="text-gray-700 font-bold">Create Finding</li>
                </ol>
            </nav>
        </div> --}}

        {{-- Show create-audit-finding for: super admin, admin, auditor --}}
        @if (in_array($role, ['super admin', 'admin', 'auditor']))
            @include('contents.ftpp2.audit-finding.partials.create-audit-finding')
        @endif
    </div>

@endsection
