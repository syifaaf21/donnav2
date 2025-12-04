@extends('layouts.app')
@section('title', 'Document Control')
@section('subtitle', 'Manage and organize documents efficiently')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pr-8 pt-3 pb-1 shadow-sm w-fit" aria-label="Breadcrumb">
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
    {{-- <div class="flex justify-between items-center my-2 pt-4">
        <div class="mb-2 pt-8">
            <div class="p-6 text-white">
                <h3 class="fw-bold">
                    Document Control
                </h3>
                <p class="text-base mt-1">Manage and organize your documents efficiently</p>
            </div>
        </div>
        <nav class="text-sm text-gray-500 bg-white rounded-full pr-8 pt-3 pb-1 shadow-sm w-fit" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Document Control</li>
            </ol>
        </nav>
    </div> --}}
    <div class="mx-auto px-6 py-2">

        {{-- 🔹 Header + Breadcrumb --}}
        <div class="flex flex-col lg:flex-row justify-between items-center mb-6 space-y-4 lg:space-y-0">

            {{-- Filter --}}
            @if (auth()->user()->roles->pluck('name')->contains('Admin') ||
                    auth()->user()->roles->pluck('name')->contains('Super Admin'))
                <form method="GET" id="filterForm" class="w-full lg:w-auto">
                    <div class="flex items-center space-x-4">
                        <div class="w-full lg:w-96 rounded-lg bg-white border border-gray-200 p-3 shadow-xl">
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
        </div>

        {{-- 🔹 Accordion --}}
        @php
            $countDept = count($groupedDocuments);
        @endphp

        <div id="docList"
            class="
                grid gap-6 grid-cols-1
                @if ($countDept > 1) md:grid-cols-2 lg:grid-cols-3 @endif
            ">
            @forelse ($groupedDocuments as $department => $mappings)
                <a href="{{ route('document-control.department', ['department' => $department]) }}"
                    class="block p-6 rounded-xl border border-gray-200 shadow-xl hover:shadow-2xl hover:border-sky-300
                   bg-white transition-all duration-200 group">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 rounded-lg bg-sky-100 text-sky-600 group-hover:bg-sky-200 transition">
                            <i class="bi bi-folder2-open text-lg"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-gray-800 group-hover:text-sky-700 transition">
                                {{ $department }}
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
                <p class="text-sm text-gray-500 text-center py-10 col-span-3">No documents found.</p>
            @endforelse
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
