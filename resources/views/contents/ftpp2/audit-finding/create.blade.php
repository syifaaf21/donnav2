@extends('layouts.app')
@section('title', 'FTPP2')

@php
    $role = strtolower(auth()->user()->role->name);
@endphp
@section('content')

    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm space-y-6 mt-2">
        {{-- Back button --}}
        <div class="mb-3">
            <a href="{{ route('ftpp.index') }}"
                class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded hover:bg-gray-200 text-sm text-gray-700">
                <i data-feather="arrow-left" class="w-4 h-4"></i>
                <span class="ml-2">Back</span>
            </a>
        </div>

        {{-- Show create-audit-finding for: super admin, admin, auditor --}}
        @if (in_array($role, ['super admin', 'admin', 'auditor']))
            @include('contents.ftpp2.audit-finding.partials.create-audit-finding')
        @endif
    </div>

@endsection
