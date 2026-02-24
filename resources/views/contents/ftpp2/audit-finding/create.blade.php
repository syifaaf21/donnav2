@extends('layouts.app')
@section('title', 'Create New FTPP Finding')
@section('subtitle', 'Please enter the details below to create a new FTPP finding.')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
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
    $roles = auth()->user()->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
@endphp
@section('content')
    <div class="px-6 space-y-4">
        {{-- Show create-audit-finding for: super admin, admin, auditor --}}
        @if (count(array_intersect($roles, ['super admin', 'admin', 'lead auditor', 'auditor'])) > 0)
            @include('contents.ftpp2.audit-finding.partials.create-audit-finding')
        @endif
    </div>
@endsection
