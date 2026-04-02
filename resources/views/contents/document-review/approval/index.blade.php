@extends('layouts.app')

@section('title', 'Document Review Approval')
@section('subtitle', 
    ($isSupervisorQueue ?? false) ? 'Documents waiting for supervisor check' : 
    (($isDeptHeadQueue ?? false) ? 'Documents waiting for dept head approval' : 'Documents waiting for review approval')
)
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pr-8 pt-3 pb-1 shadow-sm w-fit" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('document-review.index') }}" class="text-blue-600 hover:underline">Document Review</a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Approval Queue</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="px-6 py-4">
        <x-flash-message />

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col lg:flex-row justify-between gap-4 mb-4">

                <form method="GET" id="searchForm" class="flex flex-wrap items-center gap-2">
                    <div class="relative w-full md:w-96">
                        <input type="text" name="search" value="{{ request('search') }}" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Type to search...">
                        <label for="searchInput"
                            class="absolute left-4 transition-all duration-150 bg-white px-1 rounded text-gray-400 text-sm {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-blue-700 transition"
                            title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        @if (request('search'))
                            <button type="button" id="clearSearch"
                                class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition"
                                onclick="document.getElementById('searchInput').value=''; document.getElementById('searchForm').submit();"
                                title="Clear search">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto overflow-y-auto max-h-[520px]">
                <table class="min-w-full divide-y divide-gray-200 folder-table" style="solid #e5e7eb;">
                    <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                        <tr>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Document Number</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Part Number</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Product</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Model</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Process</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Notes</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Updated By</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Last Update</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-x divide-gray-200">
                        @forelse ($mappings as $mapping)
                            <tr>
                                <td class="px-2 py-3 text-xs text-center">
                                    {{ ($mappings->currentPage() - 1) * $mappings->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-2 py-3 text-left text-xs font-medium text-gray-800 min-w-[210px]">
                                    <div class="flex flex-col gap-1">
                                        <div class="font-semibold">{{ $mapping->document_number ?? '-' }}</div>
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded w-max 
                                                @php
                                                    $statusLower = strtolower($mapping->status?->name ?? '');
                                                    $statusClass = match($statusLower) {
                                                        'need check by supervisor' => 'text-blue-800 bg-blue-100',
                                                        'need approval by dept head' => 'text-purple-800 bg-purple-100',
                                                        default => 'text-yellow-800 bg-yellow-100',
                                                    };
                                                    echo $statusClass;
                                                @endphp
                                            ">
                                            {{ $mapping->status?->name ?? '-' }}
                                        </span>
                                        <div class="text-xs text-gray-500">{{ optional($mapping->department)->name ?? 'Unknown' }}</div>
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center text-xs font-medium min-w-[100px]">
                                    @if ($mapping->partNumber->isNotEmpty())
                                        {{ $mapping->partNumber->pluck('part_number')->join(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-center text-xs">
                                    @php
                                        $products = $mapping->product->pluck('code')->filter();
                                        if ($products->isEmpty()) {
                                            $products = $mapping->partNumber
                                                ->map(fn($pn) => $pn->product?->code)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $products->isNotEmpty() ? $products->join(', ') : '-' }}
                                </td>
                                <td class="px-2 py-3 text-center text-xs">
                                    @php
                                        $models = $mapping->productModel->pluck('name')->filter();
                                        if ($models->isEmpty()) {
                                            $models = $mapping->partNumber
                                                ->map(fn($pn) => $pn->productModel?->name)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $models->isNotEmpty() ? $models->join(', ') : '-' }}
                                </td>
                                <td class="px-2 py-3 text-center text-xs capitalize">
                                    @php
                                        $processes = $mapping->process->pluck('code')->filter();
                                        if ($processes->isEmpty()) {
                                            $processes = $mapping->partNumber
                                                ->map(fn($pn) => $pn->process?->code)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $processes->isNotEmpty() ? $processes->join(', ') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs max-w-[250px]">
                                    <div class="max-h-20 overflow-y-auto text-gray-600 leading-snug">
                                        {!! $mapping->notes ?? '-' !!}
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center text-xs">{{ $mapping->user?->name ?? '-' }}</td>
                                <td class="px-2 py-3 text-center text-xs">{{ $mapping->updated_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-2 py-3 text-xs text-center">
                                    <div class="relative inline-block text-left">
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition action-menu-toggle"
                                            data-target="actionMenu-{{ $mapping->id }}"
                                            title="Actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <div id="actionMenu-{{ $mapping->id }}"
                                            class="hidden absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] py-1 text-sm action-menu-dropdown">
                                            <a href="{{ route('document-review.showFolder', [$mapping->approval_plant, $mapping->approval_doc_code]) }}"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-left hover:bg-gray-50 text-slate-700"
                                                title="Open in folder view">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                                Open
                                            </a>

                                            <button type="button"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-left hover:bg-gray-50 text-green-700 btn-approve"
                                                data-id="{{ $mapping->id }}">
                                                <i class="bi bi-check2-circle"></i>
                                                Approve
                                            </button>

                                            <button type="button"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-left hover:bg-gray-50 text-red-700"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                data-id="{{ $mapping->id }}">
                                                <i class="bi bi-x-circle"></i>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div
                                        class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                                        <i class="bi bi-inbox text-4xl"></i>
                                        <span>No Documents found</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $mappings->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-reject')
@endsection

@push('styles')
    <style>
        .folder-table {
            border-collapse: separate;
        }

        .folder-table th,
        .folder-table td {
            border-right: 1px solid #e5e7eb;
        }

        .folder-table th:last-child,
        .folder-table td:last-child {
            border-right: none;
        }

        .folder-table tbody tr td {
            border-bottom: 1px solid #f3f4f6;
        }

        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.8rem;
            border-radius: 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
            line-height: 1;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
        }

        .btn-modern:active {
            transform: translateY(0);
        }

        .btn-modern-primary {
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
        }

        .btn-modern-success {
            color: #14532d;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-color: #86efac;
        }

        .btn-modern-danger {
            color: #7f1d1d;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-color: #fca5a5;
        }

        .btn-modern-ghost {
            color: #334155;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .action-menu-dropdown {
            min-width: 11rem;
        }

        .action-fixed {
            position: fixed !important;
            top: 0;
            left: 0;
            margin-top: 0 !important;
            z-index: 10000 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggles = document.querySelectorAll('.action-menu-toggle');
            const allMenus = document.querySelectorAll('.action-menu-dropdown');

            function closeAllActionMenus() {
                allMenus.forEach(m => {
                    m.classList.add('hidden');
                    m.classList.remove('action-fixed');
                });
            }

            menuToggles.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetId = this.getAttribute('data-target');
                    const menu = document.getElementById(targetId);
                    const isHidden = menu.classList.contains('hidden');

                    closeAllActionMenus();
                    if (!isHidden) return;

                    // Move menu to body and position as fixed so table overflow won't clip it.
                    if (menu.parentElement !== document.body) {
                        document.body.appendChild(menu);
                    }

                    menu.classList.remove('hidden');
                    menu.classList.add('action-fixed');

                    const btnRect = this.getBoundingClientRect();
                    const menuRect = menu.getBoundingClientRect();
                    const viewportW = window.innerWidth;
                    const viewportH = window.innerHeight;

                    let left = btnRect.right - menuRect.width;
                    left = Math.max(8, Math.min(left, viewportW - menuRect.width - 8));

                    let top = btnRect.bottom + 6;
                    if (top + menuRect.height > viewportH - 8) {
                        top = Math.max(8, btnRect.top - menuRect.height - 6);
                    }

                    menu.style.left = `${left}px`;
                    menu.style.top = `${top}px`;
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.action-menu-toggle') && !e.target.closest('.action-menu-dropdown')) {
                    closeAllActionMenus();
                }
            });

            window.addEventListener('scroll', closeAllActionMenus, true);
            window.addEventListener('resize', closeAllActionMenus);

            // Same wiring as folder page: open approve modal and set action dynamically.
            document.querySelectorAll('.btn-approve').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const docId = this.getAttribute('data-id');
                    const approveForm = document.getElementById('approveForm');
                    approveForm.action = `/document-review/${docId}/approve-with-dates`;

                    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
                    approveModal.show();
                });
            });

            const rejectModal = document.getElementById('rejectModal');
            const rejectForm = document.getElementById('rejectForm');

            rejectModal?.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const mappingId = btn?.getAttribute('data-id');
                rejectForm.action = `/document-review/${mappingId}/reject`;
            });
        });
    </script>
@endpush
