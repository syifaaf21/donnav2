@extends('layouts.app')
@section('title', 'FTPP')

@php
    $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
@endphp
@section('content')
    <div class="p-6 space-y-4 mt-6">
        <div class="py-6">
            <div class="text-white">
                <h1 class="fw-bold">
                    Create New FTPP Finding
                </h1>
                <p class="text-base mt-1">Please enter the details below to create a new FTPP finding.</p>
            </div>
        </div>
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-2" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li>
                    <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-folder me-1"></i>FTPP
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-bold">Create Finding</li>
            </ol>
        </nav>



        {{-- Show create-audit-finding for: super admin, admin, auditor --}}
        @if (in_array($role, ['super admin', 'admin', 'auditor']))
            @include('contents.ftpp2.audit-finding.partials.create-audit-finding')
        @endif
    </div>

@endsection
