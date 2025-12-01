@extends('layouts.app')
@section('title', 'FTPP')

@php
    $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
@endphp
@section('content')
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
            <li class="text-gray-700 font-medium">Create Finding</li>
        </ol>
    </nav>

    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm space-y-6 mt-2">

        <h4>Create New Finding</h4>

        {{-- Show create-audit-finding for: super admin, admin, auditor --}}
        @if (in_array($role, ['super admin', 'admin', 'auditor']))
            @include('contents.ftpp2.audit-finding.partials.create-audit-finding')
        @endif
    </div>

@endsection
