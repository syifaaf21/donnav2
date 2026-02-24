@extends('layouts.app')
@section('title')
    {{ $department?->name ?? 'Unknown' }}
@endsection
@section('subtitle', 'Manage Obsolete Docuemnt Records')
@php
    /**
     * approvalMode = false → halaman department biasa
     * approvalMode = true  → halaman approval queue
     */
    $approvalMode = $approvalMode ?? false;
@endphp
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
                <a href="{{ route('document-control.index') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-calendar-range me-1"></i> Document Control
                </a>
            </li>

            <li>/</li>

            <li class="text-gray-700 font-bold">{{ $approvalMode ? 'Approval' : $department?->name ?? 'Unknown' }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="mx-auto px-4 space-y-4">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">
                        Document Control – {{ $department->name }}
                    </h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Manage and organize documents efficiently
                    </p>
                </div> --}}
        {{-- </div> --}}

        {{-- Breadcrumbs --}}
        {{-- <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1"
                aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">

                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>

                    <li>/</li>

                    <li>
                        <a href="{{ route('document-control.index') }}"
                            class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-gear me-1"></i> Document Control
                        </a>
                    </li>

                    <li>/</li>

                    <li class="text-gray-500 font-medium">Documents</li>

                    <li>/</li>

                    <li class="text-gray-700 font-bold">{{ $department->name }}</li>
                </ol>
            </nav>
        </div> --}}

        <!-- Search & Filter Form -->
        <div class="flex justify-end w-full mb-2 gap-2 items-start">
            <!-- Filter Dropdown Button -->
            @if (!$approvalMode)
                <div class="relative">
                    <button id="filterStatusBtn" type="button"
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 shadow hover:bg-blue-50 transition-colors"
                        title="Filter by Status">
                        <i class="bi bi-funnel-fill text-xl text-sky-600"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="filterStatusDropdown"
                        class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg z-[9999]">
                        <div class="py-2 text-sm">
                            <div class="px-3 pb-2">
                                <input type="text" id="statusSearchInput"
                                    class="w-full rounded border border-gray-200 px-2 py-1 text-sm"
                                    placeholder="Type to filter status...">
                            </div>
                            <form id="statusFilterForm" method="GET"
                                action="{{ $approvalMode ? route('document-control.approval') : route('document-control.department', $department?->name ?? 'Unknown') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <ul id="statusList" class="flex flex-col gap-1 max-h-64 overflow-y-auto px-2">
                                    <!-- Status list dari controller -->
                                    <li>
                                        <label
                                            class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                            <input type="checkbox" name="status[]" value="all" class="status-checkbox"
                                                id="statusAllCheckbox"
                                                {{ empty($selectedStatuses) || in_array('all', $selectedStatuses) ? 'checked' : '' }}>
                                            <i class="bi bi-list-check text-gray-700 text-lg"></i>
                                            <span class="flex-1 text-sm">All</span>
                                            <span
                                                class="text-xs text-gray-500 font-semibold">{{ array_sum($statusCounts ?? []) }}</span>
                                        </label>
                                    </li>
                                    @php
                                        $icons = [
                                            'active' => 'bi bi-check-circle-fill',
                                            'need_review' => 'bi bi-exclamation-circle-fill',
                                            'rejected' => 'bi bi-x-circle-fill',
                                            'obsolete' => 'bi bi-archive-fill',
                                            'uncomplete' => 'bi bi-slash-circle-fill',
                                        ];
                                        $colors = [
                                            'active' => 'text-green-700',
                                            'need_review' => 'text-yellow-700',
                                            'rejected' => 'text-red-700',
                                            'obsolete' => 'text-gray-700',
                                            'uncomplete' => 'text-orange-700',
                                        ];
                                    @endphp
                                    @foreach ($statuses as $key => $label)
                                        <li>
                                            <label
                                                class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                                <input type="checkbox" name="status[]" value="{{ $key }}"
                                                    class="status-checkbox"
                                                    {{ !empty($selectedStatuses) && in_array($key, $selectedStatuses) ? 'checked' : '' }}>
                                                <i class="{{ $icons[$key] ?? '' }} {{ $colors[$key] ?? '' }} text-lg"></i>
                                                <span class="flex-1 text-sm">{{ $label }}</span>
                                                <span
                                                    class="text-xs text-gray-500 font-semibold">{{ $statusCounts[$key] ?? 0 }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                <!-- Live filter: no apply button -->
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Search Form -->
            <form id="filterForm" method="GET"
                action="{{ $approvalMode ? route('document-control.approval') : route('document-control.department', $department?->name ?? 'Unknown') }}"
                class="flex flex-col items-end w-auto space-y-1">
                <div class="relative w-96">
                    <input type="text" name="search" id="searchInput"
                        class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
             focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                        placeholder="Type to search..." value="{{ request('search') }}">

                    <label for="searchInput"
                        class="absolute left-4 transition-all duration-150 bg-white px-1 rounded
             text-gray-400 text-sm
             {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                        Type to search...
                    </label>
                </div>
            </form>
        </div>
        <div id="liveTableWrapper">
            <!-- Table -->
            <div class="flex-1">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto overflow-y-auto max-h-[520px]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                                <tr>
                                    <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">No
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Document
                                        Name
                                    </th>
                                    {{-- <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Status
                                    </th> --}}
                                    <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Obsolete
                                        Date
                                    </th>
                                    @if ($approvalMode)
                                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                            style="color: #1e2b50; letter-spacing: 0.5px;">
                                            Department
                                        </th>
                                    @endif
                                    <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Updated By
                                    </th>
                                    {{-- <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Last
                                        Update
                                    </th> --}}
                                    <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Notes</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-x divide-gray-200">
                                @if ($mappings->isEmpty())
                                    <tr colspan="12">
                                        <td colspan="12">
                                            <div
                                                class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                                                <i class="bi bi-inbox text-4xl"></i>
                                                <span>No Documents found</span>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($mappings as $mapping)
                                        <tr class="hover:bg-gray-50 transition-all duration-150">
                                            <td class="px-1 py-3 text-center text-xs border-r border-gray-200"
                                                style="width: 40px; min-width: 32px;">
                                                {{ ($mappings->currentPage() - 1) * $mappings->perPage() + $loop->iteration }}
                                            </td>
                                            <td class="px-2 py-2 text-xs max-w-xs border-r border-gray-200">
                                                <div class="flex flex-col gap-1">
                                                    <div class="font-semibold truncate"
                                                        title="{{ $mapping->document->name }}">
                                                        {{ $mapping->document->name }}
                                                    </div>

                                                    @php
                                                        $statusColor = match ($mapping->status->name) {
                                                            'Active' => 'bg-green-100 text-green-800',
                                                            'Need Review' => 'bg-yellow-100 text-yellow-800',
                                                            'Rejected' => 'bg-red-100 text-red-800',
                                                            'Obsolete' => 'bg-gray-200 text-gray-800',
                                                            'Uncomplete' => 'bg-orange-100 text-orange-800',
                                                            default => 'bg-blue-100 text-blue-800',
                                                        };
                                                    @endphp

                                                    <!-- Tambahkan self-start supaya tidak stretch -->
                                                    <span
                                                        class="self-start inline-flex px-2 py-1 text-xs font-semibold rounded {{ $statusColor }}">
                                                        {{ $mapping->status->name }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-1 py-3 text-center text-xs font-semibold border-r border-gray-200"
                                                style="width: 90px; min-width: 70px;">
                                                {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                                            </td>
                                            @if ($approvalMode)
                                                <td
                                                    class="px-2 py-3 text-center text-xs truncate border-r border-gray-200">
                                                    {{ $mapping->department?->name ?? '-' }}
                                                </td>
                                            @endif
                                            <td class="px-2 py-3 text-center text-xs truncate border-r border-gray-200">
                                                {{ ucwords(strtolower($mapping->user->name ?? '-')) }}
                                            </td>
                                            {{-- <td class="px-2 py-3 text-xs border-r border-gray-200">
                                                {{ $mapping->updated_at?->format('d M Y') ?? '-' }}
                                            </td> --}}
                                            <td class="px-4 py-3 text-left text-xs max-w-xs border-r border-gray-200">
                                                <div class="overflow-y-auto max-h-16 text-xs note-tooltip"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-container="body"
                                                    title="{{ $mapping->notes ? e(strip_tags($mapping->notes)) : '-' }}">
                                                    {!! $mapping->notes ?? '-' !!}
                                                </div>

                                            </td>
                                            {{-- Actions --}}
                                            <td class="px-1 py-2 text-center"
                                                style="@if ($approvalMode) width:140px; min-width:120px; @else width:120px; min-width:90px; @endif">
                                                <div class="flex justify-center items-center gap-1 flex-wrap">

                                                    {{-- VIEW FILES --}}
                                                    @php
                                                        $filesToShow = collect($mapping->files_for_modal_all)->filter(
                                                            fn($file) => ($file['is_active'] ?? 0) == 1 ||
                                                                ($file['pending_approval'] ?? 0) == 2,
                                                        );
                                                    @endphp

                                                    @if ($filesToShow->count() > 1)
                                                        <div class="relative">
                                                            <!-- Dropdown toggle button -->
                                                            <button id="viewFilesBtn-{{ $mapping->id }}" type="button"
                                                                class="text-gray-700 hover:text-blue-600 toggle-files-dropdown"
                                                                data-files='@json($filesToShow->values()->all())'>
                                                                <i class="bi bi-file-earmark-text text-2xl"></i>
                                                                <span
                                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                                    {{ $filesToShow->count() }}
                                                                </span>
                                                            </button>

                                                            <!-- Dropdown menu (vertical list) -->
                                                            <div id="viewFilesDropdown-{{ $mapping->id }}"
                                                                class="hidden absolute right-0 bottom-full mb-2 w-64 bg-white border border-gray-200 rounded-md shadow-lg z-[9999]">
                                                                <div class="py-1 text-sm max-h-80 overflow-y-auto">
                                                                    @foreach ($filesToShow as $file)
                                                                        <button type="button"
                                                                            class="w-full flex justify-between items-center px-3 py-2 rounded-md text-sm truncate view-file-btn
                                                                                {{ ($file['pending_approval'] ?? 0) == 2 ? 'bg-red-50 border border-red-300' : (!empty($file['replaced_by_id']) ? 'bg-red-100 border border-red-400' : '') }}"
                                                                            data-file="{{ $file['url'] }}"
                                                                            data-doc-title="{{ $file['name'] }}">

                                                                            <div class="flex items-center gap-2" style="min-width:0;">
                                                                                <i class="bi bi-file-earmark-text text-lg text-gray-600"></i>
                                                                                <span class="truncate" style="flex:1 1 auto;min-width:0;">{{ $file['name'] }}</span>
                                                                            </div>
                                                                            <div class="flex items-center gap-2">
                                                                                @if (($file['pending_approval'] ?? 0) == 2)
                                                                                    <span
                                                                                        class="inline-block bg-red-500 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap pointer-events-none">
                                                                                        Rejected
                                                                                    </span>
                                                                                @elseif (!empty($file['replaced_by_id']))
                                                                                    <span
                                                                                        class="inline-block bg-red-300 text-red-900 text-xs font-semibold px-2 py-0.5 rounded-full whitespace-nowrap pointer-events-none">
                                                                                        Replaced
                                                                                    </span>
                                                                                @else
                                                                                    @if(!empty($file['size']))
                                                                                        <span class="text-xs text-gray-500">{{ $file['size'] }}</span>
                                                                                    @endif
                                                                                @endif
                                                                            </div>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif($filesToShow->count() === 1)
                                                        @php $file = $filesToShow->first(); @endphp
                                                        <button type="button"
                                                            class="action-btn inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-500 text-white shadow hover:scale-110 transition-transform duration-200 view-file-btn
                                                                {{ !empty($file['replaced_by_id']) ? 'bg-red-100 text-red-900' : 'bg-cyan-500' }}"
                                                            data-file="{{ $file['url'] }}"
                                                            data-doc-title="{{ $file['name'] }}" title="View File">
                                                            <i class="bi bi-eye"></i>
                                                            @if (!empty($file['replaced_by_id']))
                                                                <span
                                                                    class="ml-1 inline-block bg-red-300 text-red-900 text-xs font-semibold px-1 py-0.5 rounded-full">
                                                                    Replaced
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif

                                                    {{-- UPLOAD ALWAYS APPEARS LEFT WITH OTHER ACTIONS --}}
                                                    @if (!$approvalMode)
                                                        <button type="button"
                                                            class="action-btn btn-revise inline-flex items-center w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-status="{{ $mapping->status->name }}"
                                                            data-reminder="{{ $mapping->reminder_date ?? $mapping->reminder ?? '' }}"
                                                            data-files='@json($mapping->files_for_modal_all)'
                                                            onclick="openReviseModal(this)" title="Upload">
                                                            <i class="bi bi-upload"></i>
                                                        </button>
                                                    @endif

                                                    {{-- ADMIN ACTIONS --}}
                                                    @if ($approvalMode && in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']))
                                                        <form
                                                            action="{{ route('document-control.approve', ['mapping' => $mapping->id]) }}"
                                                            method="POST" class="inline-flex items-center m-0 p-0">
                                                            @csrf
                                                            <button type="button"
                                                                class="action-btn inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-600 text-white hover:bg-green-700 transition-colors btn-approve"
                                                                data-status="{{ $mapping->status->name }}"
                                                                data-obsolete="{{ $mapping->obsolete_date }}"
                                                                data-period="{{ $mapping->period_years }}"
                                                                onclick="confirmApprove(this)" title="Approve" aria-label="Approve document">
                                                                <i class="bi bi-check2-circle"></i>
                                                            </button>
                                                        </form>

                                                        <button type="button"
                                                            class="action-btn inline-flex items-center justify-center w-9 h-9 rounded-full bg-red-600 text-white hover:bg-red-700 transition-colors btn-reject"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-notes="{{ str_replace('"', '&quot;', $mapping->notes ?? '') }}"
                                                            data-status="{{ $mapping->status->name }}"
                                                            data-reject-url="{{ route('document-control.reject', $mapping) }}"
                                                            title="Reject" aria-label="Reject document">
                                                            <i class="bi bi-x-circle-fill"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 px-4 py-2">
                        {{ $mappings->withQueryString()->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content rounded-2xl">
                <div class="modal-header bg-white border-b flex items-center justify-between">
                    <!-- Container teks agar overflow disembunyikan -->
                    <h5 class="modal-title font-semibold text-gray-800 truncate max-w-[70%]" id="previewTitle"
                        title="">
                        File Preview
                    </h5>

                    <div class="flex items-center gap-2">
                        <a href="#" id="viewFullBtn" class="btn btn-info btn-sm">
                            <i class="bi bi-arrows-fullscreen"></i> View Full
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="height:80vh">
                    <iframe id="previewIframe" src="" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('contents.document-control.partials.modal-revise')
    @include('contents.document-control.partials.modal-approve')
    @include('contents.document-control.partials.modal-reject')
@endsection
<style>
    /* --- Dropdown fix style --- */
    .dropdown-fixed {
        position: fixed !important;
        z-index: 999999 !important;
        background-color: #ffffff !important;
        /* warna putih solid */
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        border-radius: 8px !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Tambahan: untuk isi dropdown agar tidak transparan juga */
    .dropdown-fixed .py-1 {
        background-color: #fff;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        border-radius: 6px;
    }

    .action-btn i {
        font-size: 16px;
        line-height: 1;
    }
</style>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // current user admin flag (Admin or Super Admin)
            const currentUserIsAdmin = @json(in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']));
            // Filter Status Dropdown
            const filterStatusBtn = document.getElementById('filterStatusBtn');
            const filterStatusDropdown = document.getElementById('filterStatusDropdown');
            const statusListEl = document.getElementById('statusList');
            const statusSearchInput = document.getElementById('statusSearchInput');
            // Search status in dropdown (Blade only, no JS counting)
            if (statusSearchInput) {
                statusSearchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    document.querySelectorAll('#statusList li').forEach(li => {
                        const label = li.querySelector('span.flex-1');
                        if (label && label.textContent.toLowerCase().includes(filter)) {
                            li.style.display = '';
                        } else {
                            li.style.display = 'none';
                        }
                    });
                });
            }

            // Checkbox logic (multi-select, all) + live submit
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('status-checkbox')) {
                    const allCheckbox = document.getElementById('statusAllCheckbox');
                    const statusCheckboxes = Array.from(document.querySelectorAll('.status-checkbox'))
                        .filter(cb => cb !== allCheckbox);
                    if (e.target === allCheckbox) {
                        // Jika 'all' dicentang, centang semua, jika uncheck, uncheck semua
                        statusCheckboxes.forEach(cb => cb.checked = allCheckbox.checked);
                    } else {
                        // Jika status lain dicentang, uncheck 'all'
                        if (allCheckbox) allCheckbox.checked = false;
                    }
                    setTimeout(function() {
                        document.getElementById('statusFilterForm').submit();
                    }, 10);
                }
            });

            // Show/hide dropdown
            if (filterStatusBtn && filterStatusDropdown) {
                filterStatusBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isVisible = !filterStatusDropdown.classList.contains('hidden');
                    document.querySelectorAll('#filterStatusDropdown').forEach(d => d.classList.add(
                        'hidden'));
                    if (isVisible) {
                        filterStatusDropdown.classList.add('hidden');
                        return;
                    }
                    // Position dropdown (clamped to viewport to avoid overflow)
                    const rect = filterStatusBtn.getBoundingClientRect();
                    filterStatusDropdown.style.position = 'fixed';
                    filterStatusDropdown.style.top = `${rect.bottom + 6}px`;
                    // Estimate dropdown width and clamp left to viewport
                    const preferredLeft = rect.left - 220;
                    const estimatedWidth = Math.max(240, Math.min(360, filterStatusDropdown.offsetWidth ||
                        280));
                    const maxLeft = window.innerWidth - estimatedWidth - 12;
                    const clampedLeft = Math.min(Math.max(8, preferredLeft), maxLeft);
                    filterStatusDropdown.style.left = `${clampedLeft}px`;
                    filterStatusDropdown.classList.remove('hidden');
                    filterStatusDropdown.classList.add('dropdown-fixed');
                });
                // Close dropdown on outside click
                document.addEventListener('click', function(e) {
                    if (!filterStatusDropdown.contains(e.target) && !filterStatusBtn.contains(e.target)) {
                        filterStatusDropdown.classList.add('hidden');
                    }
                });
            }
            // ...existing code...
            let typingTimer = null;
            const searchInputEl = document.getElementById("searchInput");

            // Init tooltips for notes column
            function initNoteTooltips(container = document) {
                const noteTooltips = Array.from(container.querySelectorAll(
                    '.note-tooltip[data-bs-toggle="tooltip"]'));
                noteTooltips.forEach(el => new bootstrap.Tooltip(el, {
                    boundary: 'window'
                }));
            }

            searchInputEl.addEventListener("keyup", function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    liveSearch();
                }, 300);
            });

            const searchInput = document.getElementById('searchInput');
            const searchLabel = document.querySelector('label[for="searchInput"]');

            function updateSearchLabelState() {
                if (searchInput && searchLabel) {
                    if (searchInput.value.trim() !== '') {
                        searchLabel.classList.add('-top-3', 'text-xs', 'text-sky-600');
                        searchLabel.classList.remove('top-2.5', 'text-sm');
                    } else {
                        searchLabel.classList.remove('-top-3', 'text-xs', 'text-sky-600');
                        searchLabel.classList.add('top-2.5', 'text-sm');
                    }
                }
            }

            // Jalankan saat halaman load
            updateSearchLabelState();

            // Jalankan setiap AJAX selesai update search bar
            document.addEventListener("ajaxSearchUpdate", updateSearchLabelState);

            function liveSearch(url = null) {
                const search = searchInputEl.value;
                const requestUrl = url ?? (`${filterForm.action}?search=${encodeURIComponent(search)}`);

                fetch(requestUrl, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, "text/html");

                        const newTable = doc.querySelector("#liveTableWrapper");
                        const current = document.querySelector("#liveTableWrapper");

                        if (newTable && current) {
                            current.innerHTML = newTable.innerHTML;
                            document.dispatchEvent(new Event("ajaxSearchUpdate"));
                            rebindTableEvents();
                            initNoteTooltips(current);
                        }
                    });
            }

            function rebindTableEvents() {

                // =========================
                // Rebind Reject button
                // =========================
                document.querySelectorAll('.btn-reject').forEach(btn => {
                    btn.onclick = function() {
                        const rejectUrl = this.dataset.rejectUrl;
                        const rejectDocInput = document.getElementById('rejectDocumentId');
                        const rejectNotesInput = document.getElementById('rejectNotes');
                        const rejectForm = document.getElementById('rejectForm');

                        rejectDocInput.value = this.dataset.docid;
                        rejectQuill.setText('');
                        rejectForm.action = rejectUrl;

                        new bootstrap.Modal(document.getElementById('rejectModal')).show();
                    };
                });

                // =========================
                // Rebind dropdown file (build popup from data-files)
                // =========================
                document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();

                        // close any existing popup
                        document.querySelectorAll('.file-popup').forEach(p => p.remove());

                        // toggle state
                        if (btn.dataset.popupOpen === '1') {
                            btn.dataset.popupOpen = '0';
                            return;
                        }

                        const files = JSON.parse(btn.dataset.files || '[]');
                        const rect = btn.getBoundingClientRect();

                        const popup = document.createElement('div');
                        popup.className = 'file-popup dropdown-fixed';
                        popup.style.position = 'fixed';
                        popup.style.top = `${rect.bottom + 6}px`;
                        popup.style.left = `${Math.min(Math.max(8, rect.left - 120), window.innerWidth - 260)}px`;
                        popup.style.backgroundColor = '#fff';
                        popup.style.opacity = '1';
                        popup.style.visibility = 'visible';
                        popup.style.zIndex = '999999';
                        popup.style.border = '1px solid rgba(0,0,0,0.08)';
                        popup.style.borderRadius = '8px';
                        popup.style.boxShadow = '0 8px 24px rgba(16,24,40,0.12)';
                        popup.style.padding = '6px';
                        popup.style.maxHeight = '320px';
                        popup.style.overflow = 'auto';
                        popup.style.minWidth = '220px';

                        // build list
                        files.forEach(f => {
                            const row = document.createElement('button');
                            row.type = 'button';
                            row.className = 'w-full flex justify-between items-center px-3 py-2 rounded-md text-sm text-left view-file-btn';
                            row.style.background = 'transparent';
                            row.style.border = 'none';
                            row.style.display = 'flex';
                            row.style.alignItems = 'center';
                            row.style.gap = '8px';
                            row.dataset.file = f.url || '';
                            row.dataset.docTitle = f.name || '';

                            const left = document.createElement('div');
                            left.style.display = 'flex';
                            left.style.alignItems = 'center';
                            left.style.gap = '8px';
                            const ico = document.createElement('i');
                            ico.className = 'bi bi-file-earmark-text text-gray-600';
                            ico.style.minWidth = '18px';
                            left.appendChild(ico);
                            const name = document.createElement('span');
                            name.className = 'truncate';
                            name.style.maxWidth = '160px';
                            name.textContent = f.name || '—';
                            left.appendChild(name);

                            const right = document.createElement('div');
                            right.style.display = 'flex';
                            right.style.alignItems = 'center';
                            right.style.gap = '6px';
                            if ((f.pending_approval || 0) == 2) {
                                const badge = document.createElement('span');
                                badge.className = 'inline-block bg-red-600 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full';
                                badge.textContent = 'Rejected';
                                right.appendChild(badge);
                            } else if (f.replaced_by_id) {
                                const badge = document.createElement('span');
                                badge.className = 'inline-block bg-red-200 text-red-900 text-xs font-semibold px-2 py-0.5 rounded-full';
                                badge.textContent = 'Replaced';
                                right.appendChild(badge);
                            } else if (f.size) {
                                const size = document.createElement('span');
                                size.className = 'text-xs text-gray-500';
                                size.textContent = f.size;
                                right.appendChild(size);
                            }

                            row.appendChild(left);
                            row.appendChild(right);

                            popup.appendChild(row);
                        });

                        document.body.appendChild(popup);
                        btn.dataset.popupOpen = '1';

                        // bind preview events
                        popup.querySelectorAll('.view-file-btn').forEach(b => {
                            b.addEventListener('click', function(ev) {
                                ev.stopPropagation();
                                document.getElementById('previewTitle').textContent = this.dataset.docTitle || 'File Preview';
                                document.getElementById('previewIframe').src = this.dataset.file || '';
                                new bootstrap.Modal(document.getElementById('viewFileModal')).show();
                                popup.remove();
                                btn.dataset.popupOpen = '0';
                            });
                        });

                        // close on outside click
                        const closeHandler = (ev) => {
                            if (!popup.contains(ev.target) && ev.target !== btn) {
                                popup.remove();
                                btn.dataset.popupOpen = '0';
                                document.removeEventListener('click', closeHandler);
                            }
                        };
                        setTimeout(() => document.addEventListener('click', closeHandler), 10);
                    };
                });

                // =========================
                // Rebind open file preview
                // =========================
                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.onclick = function() {
                        document.getElementById('previewTitle').textContent = this.dataset.docTitle;
                        document.getElementById('previewIframe').src = this.dataset.file;

                        new bootstrap.Modal(document.getElementById('viewFileModal')).show();
                    };
                });

                // =========================
                // Rebind Revise modal button
                // =========================
                document.querySelectorAll('.btn-revise').forEach(btn => {
                    btn.onclick = function() {
                        openReviseModal(this);
                    };
                });

                // =========================
                // Rebind Approve button
                // =========================
                document.querySelectorAll('.btn-approve').forEach(btn => {
                    btn.onclick = function() {
                        confirmApprove(this);
                    };
                });

                // =========================
                // Re-apply status button disable/enable
                // =========================
                updateActionButtonsByStatus(document);
                initNoteTooltips(document);
            }


            document.addEventListener("click", function(e) {
                if (e.target.closest(".pagination a")) {
                    e.preventDefault();
                    const url = e.target.closest("a").href;
                    liveSearch(url);
                }
            });


            const baseUrl = window.location.origin; // gunakan sesuai routing kamu

            const filterForm = document.getElementById('filterForm');
            const clearBtn = document.getElementById('clearSearch');

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';

                    // Jika ada parameter lain (misalnya department), jangan dihapus
                    filterForm.submit();
                });
            }

            function updateActionButtonsByStatus(container) {
                container.querySelectorAll('.btn-revise, .btn-approve, .btn-reject').forEach(btn => {
                    // Determine status (prefer button dataset, otherwise fallback to row)
                    let status = btn.dataset.status?.trim();
                    const row = btn.closest('tr');
                    if ((!status || status === '') && row) {
                        const dsEl = row.querySelector('[data-status]');
                        if (dsEl && dsEl.dataset.status) {
                            status = dsEl.dataset.status.trim();
                        } else {
                            const statusEl = row.querySelector('.self-start.inline-flex');
                            if (statusEl) status = statusEl.textContent.trim();
                        }
                    }

                    const type = btn.classList.contains('btn-revise') ? 'revise' :
                        btn.classList.contains('btn-approve') ? 'approve' :
                        'reject';

                    let enabled = false;

                    // Revise: admins always see it; others see for Rejected/Obsolete/Uncomplete,
                    // and for Active only if reminder is today
                    if (type === 'revise') {
                        if (currentUserIsAdmin) {
                            enabled = true;
                        } else if (['Rejected', 'Obsolete', 'Uncomplete'].includes(status)) {
                            enabled = true;
                        } else if (status === 'Active') {
                            // check reminder date on button or row
                            let reminderStr = btn.dataset.reminder;
                            if (!reminderStr && row) {
                                const remEl = row.querySelector('.reminder-date');
                                reminderStr = remEl ? (remEl.dataset.reminder || remEl.textContent.trim()) : null;
                            }
                            if (isToday(reminderStr)) enabled = true;
                        }
                    }

                    // Approve/Reject hanya aktif saat Need Review
                    if ((type === 'approve' || type === 'reject') && status === 'Need Review') {
                        enabled = true;
                    }

                    // HILANGKAN tombol kalau tidak memenuhi syarat
                    if (!enabled) {
                        btn.style.display = 'none'; // Hide tombol
                    } else {
                        btn.style.display = ''; // Tampilkan
                    }
                });
            }

            // Helper: returns true when dateStr represents today's date (YYYY-MM-DD or other parseable)
            function isToday(dateStr) {
                if (!dateStr) return false;
                const cleaned = dateStr.trim();
                const dateOnly = cleaned.split(' ')[0];
                // Normalize separators
                const normalized = dateOnly.replace(/\//g, '-');
                // If looks like YYYY-MM-DD or YYYY-M-D
                const parts = normalized.split('-');
                let d;
                if (parts.length === 3 && parts[0].length === 4) {
                    d = new Date(normalized + 'T00:00:00');
                } else {
                    d = new Date(cleaned);
                }
                if (isNaN(d.getTime())) return false;
                const now = new Date();
                return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate();
            }


            // Terapkan ke semua section mapping
            updateActionButtonsByStatus(document);

            // =========================
            // Modal Revise
            // =========================
            const reviseModal = document.getElementById('modal-revise');
            const reviseFilesContainer = document.getElementById('reviseFilesContainer');
            const newFilesContainer = document.getElementById('new-files-container');
            const addFileBtn = document.getElementById('add-file');
            // ===============================
            // VALIDASI: Cegah submit jika tidak ada perubahan file
            // ===============================
            const reviseFormDynamic = document.getElementById('reviseFormDynamic');

            if (reviseFormDynamic) {
                reviseFormDynamic.addEventListener('submit', function(e) {

                    // ambil file replace lama
                    const existingFileInputs = reviseFilesContainer.querySelectorAll('input[type="file"]');

                    // ambil file baru
                    const newFileInputs = newFilesContainer.querySelectorAll('input[type="file"]');

                    // ambil file yang dihapus
                    const deletedFileInputs = reviseFormDynamic.querySelectorAll(
                        'input[name="deleted_file_ids[]"]');

                    let hasChange = false;

                    // cek apakah ada file lama yang diganti
                    existingFileInputs.forEach(input => {
                        if (input.files.length > 0) {
                            hasChange = true;
                        }
                    });

                    // cek file baru
                    newFileInputs.forEach(input => {
                        if (input.files.length > 0) {
                            hasChange = true;
                        }
                    });

                    // cek file yang dihapus
                    if (deletedFileInputs.length > 0) {
                        hasChange = true;
                    }

                    if (!hasChange) {
                        e.preventDefault();
                        showReviseError("Please modify at least one file before submitting.");
                        return;
                    }

                    // ===============================
                    // VALIDASI: Total file size <= 10MB
                    // ===============================
                    let totalSize = 0;

                    // Hitung ukuran file lama yang diganti
                    existingFileInputs.forEach(input => {
                        if (input.files.length > 0) {
                            totalSize += input.files[0].size;
                        }
                    });

                    // Hitung ukuran file baru
                    newFileInputs.forEach(input => {
                        if (input.files.length > 0) {
                            totalSize += input.files[0].size;
                        }
                    });

                    // Convert bytes ke MB (2 decimal)

                    // Use float for comparison, allow up to and including 20.00 MB
                    let totalSizeMBFloat = totalSize / (1024 * 1024);
                    const maxSize = 20; // dalam MB

                    if (totalSizeMBFloat > maxSize) {
                        e.preventDefault();
                        showReviseError(`
    <div class="flex items-start">
        <i data-feather="alert-circle" class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
        <div class="text-xs text-red-700">
            <p class="font-semibold mb-1">Total file size exceeds 20MB</p>
            <p>Current total size: <strong>${totalSizeMBFloat.toFixed(2)} MB</strong></p>
            <p>
                Please compress your PDF files and reupload it.
            </p>
        </div>
    </div>
`);
                        return;
                    }

                });
            }


            window.openReviseModal = function(btn) {
                const mappingId = btn.dataset.docid;
                const files = JSON.parse(btn.dataset.files || '[]');
                const status = btn.dataset.status?.trim(); // Ambil status dari button

                // Kosongkan container
                reviseFilesContainer.innerHTML = '';
                newFilesContainer.innerHTML = '';

                // --- Render file baru dulu (kalau mau user tambah file awal) ---
                newFilesContainer.innerHTML = `
        <div class="mb-2">
            <label class="block text-sm font-medium mb-1">Add new file(s)</label>
        </div>
    `;

                // --- Render file lama aktif di bawah ---
                if (files.length > 0) {
                    // Exclude files that have been replaced (replaced_by_id set)
                    const activeFiles = files.filter(f => f.is_active == 1 && !f.replaced_by_id);
                    // Hitung total size file aktif
                    const totalSize = activeFiles.reduce((sum, f) => {
                        if (typeof f.size !== 'undefined' && f.size !== null && f.size !== '' && !isNaN(
                                Number(f.size))) {
                            return sum + Number(f.size);
                        }
                        return sum;
                    }, 0);
                    // Tampilkan total size di atas daftar file
                    let totalSizeHtml =
                        `<div class="mb-2 text-xs text-gray-700 font-semibold">Total size: <span class="${totalSize > 20*1024*1024 ? 'text-red-600' : ''}">${window.formatFileSize(totalSize)} / 20 MB</span></div>`;
                    // Debug: cek isi file lama
                    console.log('Active files for modal:', activeFiles);
                    reviseFilesContainer.innerHTML = totalSizeHtml + activeFiles.map((f, i) => `
            <div class="p-3 border rounded bg-gray-50 mb-2">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-sm mb-1"><strong>File ${i+1}:</strong> ${f.name || 'Unnamed'}
                            ${f.replaced_by_id ? `<span class="inline-block bg-red-200 text-red-900 text-xs font-semibold px-2 py-0.5 rounded-full ml-2">Replaced</span>` : ''}
                        </p>
                        <p class="text-xs text-gray-500 mb-1">
                            Size: ${
                                (typeof f.size !== 'undefined' && f.size !== null && f.size !== '' && !isNaN(Number(f.size)))
                                    ? window.formatFileSize(Number(f.size))
                                    : 'Unknown'
                            }
                        </p>
                    </div>
                    ${status === 'Active' && activeFiles.length > 1 ? `
                                                                <button type="button" class="text-red-600 hover:text-red-800 hover:bg-red-100 p-1 rounded transition-colors btn-delete-file" data-file-id="${f.id}" title="Delete file">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                                ` : ''}
                </div>
                <a href="${f.url}" target="_blank" class="text-blue-600 text-xs hover:underline">View File</a>
                ${!f.replaced_by_id ? `
                <div class="mt-2 flex items-center gap-2">
                    <label class="text-xs text-gray-600"><strong>Replace:</strong></label>
                    <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm">
                </div>
                ` : ''}
                <input type="hidden" name="revision_file_ids[]" value="${f.id}">
            </div>
        `).join('');

                    // Bind delete button events
                    reviseFilesContainer.querySelectorAll('.btn-delete-file').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const fileId = this.dataset.fileId;
                            const fileContainer = this.closest('.p-3');

                            Swal.fire({
                                title: 'Delete File?',
                                text: 'Are you sure you want to delete this file?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete it',
                                cancelButtonText: 'Cancel',
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-semibold px-3 py-2 mx-2',
                                    cancelButton: 'btn btn-outline-secondary fw-semibold px-3 py-2 mx-2',
                                    popup: 'swal-on-top'
                                },
                                didOpen: function(popup) {
                                    popup.style.zIndex = '999999';
                                    const backdrop = document.querySelector(
                                        '.swal2-container');
                                    if (backdrop) backdrop.style.zIndex = '999998';
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Remove dari DOM langsung
                                    fileContainer.remove();

                                    // Add hidden input untuk menandai file sebagai deleted
                                    const deleteInput = document.createElement('input');
                                    deleteInput.type = 'hidden';
                                    deleteInput.name = 'deleted_file_ids[]';
                                    deleteInput.value = fileId;
                                    reviseFormDynamic.appendChild(deleteInput);

                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'File removed',
                                        text: 'This file has been removed from revision.',
                                        timer: 2000,
                                        timerProgressBar: true,
                                        buttonsStyling: false,
                                        customClass: {
                                            popup: 'swal-on-top'
                                        },
                                        didOpen: function(popup) {
                                            popup.style.zIndex = '999999';
                                            const backdrop = document
                                                .querySelector(
                                                    '.swal2-container');
                                            if (backdrop) backdrop.style
                                                .zIndex = '999998';
                                        }
                                    });
                                }
                            });
                        });
                    });
                }

                // Set form action
                const form = document.getElementById('reviseFormDynamic');
                form.action = `${window.location.origin}/document-control/${mappingId}/revise`;

                // Tampilkan modal
                reviseModal.classList.remove('hidden');
            };


            window.closeReviseModal = function() {
                reviseModal.classList.add('hidden');
            };

            // Tambah file baru di modal revise
            if (addFileBtn) {
                addFileBtn.addEventListener('click', function() {
                    const index = newFilesContainer.children.length + 1;
                    const div = document.createElement('div');
                    div.className = 'mb-2 flex items-center gap-2';
                    div.innerHTML = `
                <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm" required>
                <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="this.parentElement.remove()">✕</button>
            `;
                    newFilesContainer.appendChild(div);
                });
            }

            // =========================
            // Modal Approve
            // =========================
            const approveModalEl = document.getElementById('approveModal');
            const approveDocInput = document.getElementById('approveDocId');
            const obsoleteInput = document.getElementById('obsolete_date');
            const reminderInput = document.getElementById('reminder_date');
            const approveForm = document.getElementById('approveForm');

            // Saat tombol approve diklik → set ID nya (punyamu tetap dipakai)
            document.querySelectorAll('.btn-approve').forEach(btn => {
                btn.addEventListener('click', function() {
                    const docId = this.dataset.docid;
                    if (approveDocInput) approveDocInput.value = docId;
                });
            });

            // ================================
            // 🔥 ADD THIS — Set action form approve
            // ================================
            if (approveModalEl) {
                approveModalEl.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    // Ambil URL approve dari tombol
                    const approveUrl = button.dataset.approveUrl;
                    if (approveForm && approveUrl) {
                        approveForm.action = approveUrl;
                    }
                });
            }

            // simple validation: reminder <= obsolete
            if (approveForm) {
                approveForm.addEventListener('submit', function(e) {
                    const obsolete = new Date(obsoleteInput.value);
                    const reminder = new Date(reminderInput.value);
                    let valid = true;

                    if (reminder > obsolete) {
                        document.getElementById('reminderError').style.display = 'block';
                        valid = false;
                    } else {
                        document.getElementById('reminderError').style.display = 'none';
                    }

                    if (!valid) e.preventDefault();
                });
            }

            // =========================
            // Modal Reject + Quill
            // =========================
            const rejectQuill = new Quill('#quillRejectEditor', {
                theme: 'snow',
                placeholder: 'Write rejection notes...'
            });

            const rejectModalEl = document.getElementById('rejectModal');
            const rejectDocInput = document.getElementById('rejectDocumentId');
            const rejectNotesInput = document.getElementById('rejectNotes');
            const rejectForm = document.getElementById('rejectForm');

            document.querySelectorAll('.btn-reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const docId = this.dataset.docid;
                    const notes = this.dataset.notes || '';
                    const rejectUrl = this.dataset.rejectUrl; // URL POST dari Blade

                    // Set doc id
                    rejectDocInput.value = docId;

                    // Set Quill content
                    rejectQuill.setText('');

                    // Set form action dinamis
                    rejectForm.action = rejectUrl;

                    // Tampilkan modal
                    const modal = new bootstrap.Modal(rejectModalEl);
                    modal.show();
                });
            });

            // Saat submit form, ambil konten Quill dan simpan ke input hidden
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    const content = rejectQuill.root.innerHTML.trim();

                    // Cek apakah Quill kosong
                    if (content === "<p><br></p>" || content === "") {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Notes Required',
                            text: 'Please write rejection notes before submitting.'
                        });
                        return;
                    }

                    // Simpan isi Quill
                    rejectNotesInput.value = content;
                });
            }


            // =========================
            // File preview dropdown / view
            // =========================
            document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();

                    // close existing popups
                    document.querySelectorAll('.file-popup').forEach(p => p.remove());

                    if (btn.dataset.popupOpen === '1') { btn.dataset.popupOpen = '0'; return; }

                    const files = JSON.parse(btn.dataset.files || '[]');
                    const rect = btn.getBoundingClientRect();
                    const popup = document.createElement('div');
                    popup.className = 'file-popup dropdown-fixed';
                    popup.style.position = 'fixed';
                    popup.style.top = `${rect.bottom + 6}px`;
                    popup.style.left = `${Math.min(Math.max(8, rect.left - 120), window.innerWidth - 260)}px`;
                    popup.style.backgroundColor = '#fff';
                    popup.style.opacity = '1';
                    popup.style.visibility = 'visible';
                    popup.style.zIndex = '999999';
                    popup.style.border = '1px solid rgba(0,0,0,0.08)';
                    popup.style.borderRadius = '8px';
                    popup.style.boxShadow = '0 8px 24px rgba(16,24,40,0.12)';
                    popup.style.padding = '6px';
                    popup.style.maxHeight = '320px';
                    popup.style.overflow = 'auto';
                    popup.style.minWidth = '220px';

                    files.forEach(f => {
                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'w-full flex justify-between items-center px-3 py-2 rounded-md text-sm text-left view-file-btn';
                        row.style.background = 'transparent';
                        row.style.border = 'none';
                        row.style.display = 'flex';
                        row.style.alignItems = 'center';
                        row.style.gap = '8px';
                        row.dataset.file = f.url || '';
                        row.dataset.docTitle = f.name || '';

                        const left = document.createElement('div');
                        left.style.display = 'flex';
                        left.style.alignItems = 'center';
                        left.style.gap = '8px';
                        const ico = document.createElement('i');
                        ico.className = 'bi bi-file-earmark-text text-gray-600';
                        ico.style.minWidth = '18px';
                        left.appendChild(ico);
                        const name = document.createElement('span');
                        name.className = 'truncate';
                        name.style.maxWidth = '160px';
                        name.textContent = f.name || '—';
                        left.appendChild(name);

                        const right = document.createElement('div');
                        right.style.display = 'flex';
                        right.style.alignItems = 'center';
                        right.style.gap = '6px';
                        if ((f.pending_approval || 0) == 2) {
                            const badge = document.createElement('span');
                            badge.className = 'inline-block bg-red-600 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full';
                            badge.textContent = 'Rejected';
                            right.appendChild(badge);
                        } else if (f.replaced_by_id) {
                            const badge = document.createElement('span');
                            badge.className = 'inline-block bg-red-200 text-red-900 text-xs font-semibold px-2 py-0.5 rounded-full';
                            badge.textContent = 'Replaced';
                            right.appendChild(badge);
                        } else if (f.size) {
                            const size = document.createElement('span');
                            size.className = 'text-xs text-gray-500';
                            size.textContent = f.size;
                            right.appendChild(size);
                        }

                        row.appendChild(left);
                        row.appendChild(right);
                        popup.appendChild(row);
                    });

                    document.body.appendChild(popup);
                    btn.dataset.popupOpen = '1';

                    popup.querySelectorAll('.view-file-btn').forEach(b => {
                        b.addEventListener('click', function(ev) {
                            ev.stopPropagation();
                            document.getElementById('previewTitle').textContent = this.dataset.docTitle || 'File Preview';
                            document.getElementById('previewIframe').src = this.dataset.file || '';
                            new bootstrap.Modal(document.getElementById('viewFileModal')).show();
                            popup.remove();
                            btn.dataset.popupOpen = '0';
                        });
                    });

                    const closeHandler = (ev) => {
                        if (!popup.contains(ev.target) && ev.target !== btn) {
                            popup.remove();
                            btn.dataset.popupOpen = '0';
                            document.removeEventListener('click', closeHandler);
                        }
                    };
                    setTimeout(() => document.addEventListener('click', closeHandler), 10);
                });
            });

            // Tutup dropdown saat scroll
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add(
                    'hidden'));
                // also remove dynamic file popups
                document.querySelectorAll('.file-popup').forEach(p => p.remove());
            });

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(dropdown => {
                    const button = document.getElementById(dropdown.id.replace('Dropdown', 'Btn'));
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.view-file-btn');
                if (!btn) return;
                // Tutup semua dropdown file
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add(
                    'hidden'));

                const fileUrl = btn.dataset.file;
                const docTitle = btn.dataset.docTitle || 'File Preview';
                const previewTitle = document.getElementById('previewTitle');
                const previewIframe = document.getElementById('previewIframe');
                if (previewTitle) previewTitle.textContent = docTitle;
                if (previewIframe) previewIframe.src = fileUrl;

                const viewFileModalEl = document.getElementById('viewFileModal');
                if (viewFileModalEl) new bootstrap.Modal(viewFileModalEl).show();

                const viewFullBtn = document.getElementById('viewFullBtn');
                if (viewFullBtn) {
                    viewFullBtn.href = fileUrl;
                    viewFullBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            });
            document.addEventListener('hidden.bs.modal', function() {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            });

            // initial tooltip binding
            initNoteTooltips(document);

        });

        function confirmApprove(btn) {
            const form = btn.closest('form');
            const periodYears = btn.dataset.period || 0;

            Swal.fire({
                title: `
            <span style="
                font-size: 1.35rem;
                font-weight: 700;
                color: #1f2937;
                font-family: 'Inter', sans-serif;
            ">
                Approve Document?
            </span>
        `,
                html: `
            <div style="
                font-size: 1rem;
                color: #4b5563;
                font-family: 'Inter', sans-serif;
                margin-top: 6px;
                line-height: 1.55;
            ">
                Are you sure you want to approve this document?<br><br>
                <strong style="color:#111827;">This document will be obsolete in:</strong><br>

                <span style="
                    font-size: 1.35rem;
                    color: #0d6efd;
                    font-weight: 700;
                    display: inline-block;
                    margin-top: 4px;
                ">
                    ${periodYears} year(s)
                </span>
            </div>
        `,
                icon: "warning",

                showCancelButton: true,
                confirmButtonText: "Yes, approve it",
                cancelButtonText: "Cancel",

                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg',
                    confirmButton: 'btn btn-success fw-semibold px-4 py-2 mx-2',
                    cancelButton: 'btn btn-outline-secondary fw-semibold px-4 py-2 mx-2'
                },

                padding: "1.8rem 2rem",

                // **Made wider**
                width: "480px",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }


        function showReviseError(message) {
            let oldAlert = document.getElementById('revise-alert');
            if (oldAlert) oldAlert.remove();

            const alertDiv = document.createElement('div');
            alertDiv.id = 'revise-alert';
            alertDiv.className = "alert alert-danger mt-3";
            alertDiv.innerHTML = message; // ← wajib pakai innerHTML

            const form = document.getElementById('reviseFormDynamic');
            form.prepend(alertDiv);
            // Jika sebelumnya tombol submit di-disable saat submit, kembalikan ke state awal
            try {
                const submitBtn = document.getElementById('reviseSubmitBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.pointerEvents = '';
                    submitBtn.style.opacity = '';
                    submitBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Submit';
                }
            } catch (e) {
                console.warn('Failed to re-enable revise submit button', e);
            }
            if (window.feather) {
                feather.replace();
            }
        }

        window.formatFileSize = function(bytes) {
            if (typeof bytes !== 'number' || isNaN(bytes)) return 'Unknown';
            if (bytes >= 1024 * 1024) {
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            }
            return bytes + ' bytes';
        }
    </script>
@endpush
