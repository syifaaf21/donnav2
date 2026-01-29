@extends('layouts.app')
@section('title', 'Document Control')
@section('subtitle', 'Manage and organize documents efficiently')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pr-8 pt-3 pb-1 shadow-sm w-fit" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Document Control</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="mx-auto px-6 py-2">

        {{-- 🔹 Header + Breadcrumb --}}
        @php
            // count documents with 'Need Review' status for badge
            $approvalCount = 0;
            foreach ($groupedDocuments ?? [] as $deptDocs) {
                foreach ($deptDocs as $doc) {
                    if (optional($doc->status)->name === 'Need Review') {
                        $approvalCount++;
                    }
                }
            }
        @endphp

        {{-- 🔹 WRAPPER PUTIH (Filter + Grid jadi 1) --}}
        <div class="bg-white rounded-xl p-4 shadow-sm mb-6">

            {{-- Header: Filter + Button --}}
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">

                {{-- Filter --}}
                @if (auth()->user()->roles->pluck('name')->contains('Admin') ||
                        auth()->user()->roles->pluck('name')->contains('Super Admin'))
                    <form method="GET" id="filterForm" class="w-full lg:w-auto">
                        <div class="flex items-center space-x-4">
                            <div class="w-full lg:w-96 rounded-lg border border-gray-200 p-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-filter"></i> Department
                                </label>
                                <select id="departmentSelect" name="department_id"
                                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 bg-white shadow-sm focus:ring-sky-400 focus:border-sky-400"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                @endif

                {{-- Approval Queue Button --}}
                @if (auth()->user()->roles->pluck('name')->contains(fn($r) => in_array($r, ['Admin', 'Super Admin'])))
                    <div class="relative inline-block">
                        <a href="{{ route('document-control.approval') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-blue-700 text-sm font-medium
               shadow-sm hover:bg-blue-200 hover:shadow-md transition-all duration-150">
                            <i class="bi bi-check2-square mr-2"></i>
                            Approval
                        </a>

                        <span
                            class="absolute -top-2 -right-2 inline-flex items-center justify-center w-6 h-6 text-xs font-semibold bg-red-500 text-white rounded-full">
                            {{ $approvalCount }}
                        </span>
                    </div>
                @endif

            </div>

            {{-- 🔹 Accordion / Grid --}}
            @php
                $countDept = count($groupedDocuments);
            @endphp

            <div id="docList"
                class="grid gap-6 grid-cols-1 @if ($countDept > 1) md:grid-cols-2 lg:grid-cols-3 @endif">

                @forelse ($groupedDocuments as $department => $mappings)
                    <a href="{{ route('document-control.department', ['department' => $department]) }}"
                        class="block p-6 rounded-xl border border-gray-200 shadow-xl hover:shadow-2xl hover:border-sky-300
                       bg-white transition-all duration-200 group department-link">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 rounded-lg bg-sky-100 text-sky-600 group-hover:bg-sky-200 transition">
                                <i class="bi bi-folder2-open text-lg"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-800 group-hover:text-sky-700 transition">
                                    {{ $department ? $department : 'Unknown' }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    {{ count($mappings) }} Documents
                                </p>
                            </div>
                        </div>

                        {{-- List of documents (Max 3 preview) --}}
                        <div class="space-y-2 mb-4">
                            @foreach ($mappings->take(3) as $doc)
                                <div class="flex items-center gap-2 text-xs text-gray-600 truncate">
                                    <i class="bi bi-file-earmark text-sky-500"></i>
                                    <span class="truncate">{{ $doc->document->name }}</span>
                                </div>
                            @endforeach

                            @if (count($mappings) > 3)
                                <p class="text-xs text-gray-400 mt-1">
                                    +{{ count($mappings) - 3 }} more…
                                </p>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-500">
                                Click to open
                            </span>

                            <i
                                class="bi bi-arrow-right-circle-fill text-sky-500 text-lg group-hover:translate-x-1 transition"></i>
                        </div>

                    </a>

                @empty
                    <div
                        class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-inner border border-dashed border-gray-200">
                        <div class="flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-16 h-16 mb-4 text-sky-300">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h4l2 2h6a2 2 0 012 2v14a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-xl text-gray-600 font-semibold">No Departments Found</span>
                            <span class="text-sm text-gray-400 mt-2 text-center">There are no departments or documents to
                                display.<br>Please contact the administrator if you believe this is an error.</span>
                        </div>
                    </div>
                @endforelse

            </div>

        </div>
    </div>

    <!-- Scroll Up Button -->
    <button id="scrollUpBtn"
        class="fixed bottom-6 right-6 w-12 h-12 bg-sky-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-sky-600 transition-opacity"
        title="Scroll to top">
        <i class="bi bi-chevron-up text-lg"></i>
    </button>
@endsection


@push('scripts')
    <style>
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(17, 24, 39, 0.72);
            /* dark bg with opacity */
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
            display: none;
        }

        .custom-loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .loader-gradient {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: conic-gradient(#0ea5e9 10%,
                    #38bdf8 30%,
                    #818cf8 60%,
                    #0ea5e9 100%);
            animation: spin 1.1s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 32px 0 #0ea5e955;
        }

        .loader-dot {
            width: 28px;
            height: 28px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 4px #0ea5e9, 0 0 16px 0 #38bdf8aa;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            background: linear-gradient(90deg, #0ea5e9, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-shadow: 0 4px 24px #0ea5e955;
            filter: drop-shadow(0 2px 8px #818cf855);
            text-align: center;
        }
    </style>
    <div id="loadingOverlay">
        <div class="custom-loader">
            <div class="loader-gradient">
                <div class="loader-dot"></div>
            </div>
            <div class="loader-text">Loading...</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = "{{ url('document-control') }}";

            // =======================
            // TomSelect
            // =======================
            const deptSelect = document.getElementById("departmentSelect");
            if (deptSelect) {
                new TomSelect(deptSelect, {
                    placeholder: "Select a department...",
                    allowEmptyOption: true,
                    maxItems: 1,
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                });
            }

            // Loading overlay saat klik department
            document.querySelectorAll('.department-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Tampilkan overlay loading
                    const overlay = document.getElementById('loadingOverlay');
                    if (overlay) overlay.style.display = 'flex';
                });
            });
        });
        const scrollBtn = document.getElementById('scrollUpBtn');

        // Tampilkan tombol saat scroll lebih dari 100px
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                scrollBtn.classList.remove('hidden');
            } else {
                scrollBtn.classList.add('hidden');
            }
        });

        // Scroll ke atas saat tombol diklik
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Sembunyikan default
        scrollBtn.classList.add('hidden');
    </script>
@endpush
