@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class=" mx-auto p-4">
        {{-- 🔹 Header + Breadcrumb --}}
        <div class="flex justify-between items-center mb-6">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-2" aria-label="Breadcrumb">
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
        </div>

        {{-- 🔹 Main Card --}}
        <div class="bg-white shadow-2xl rounded-2xl border border-gray-100 p-6">
            {{-- Filter --}}
            <form method="GET" id="filterForm" class="mb-6">
                @if (auth()->user()->roles->pluck('name')->contains('Admin') ||
                        auth()->user()->roles->pluck('name')->contains('Super Admin'))
                    <div class="flex justify-end w-full">
                        <div class="w-96 rounded-2xl">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="departmentSelect" name="department_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-sky-400 focus:border-sky-400"
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
                @endif
            </form>


            {{-- 🔹 Accordion --}}
            @php
                $countDept = count($groupedDocuments);
            @endphp

            <div id="docList"
                class="
        grid gap-4
        @if ($countDept === 1) grid-cols-1
        @else
            grid-cols-1 md:grid-cols-2 lg:grid-cols-3 @endif
    ">
                @forelse ($groupedDocuments as $department => $mappings)
                    <a href="{{ route('document-control.department', ['department' => $department]) }}"
                        class="block p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-sky-300
                   bg-white transition-all duration-200 group">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-sky-100 text-sky-600 group-hover:bg-sky-200 transition">
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
                        <div class="space-y-1 mb-2">
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
                        <div class="flex justify-between items-center mt-auto pt-2 border-t border-gray-100">
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
    </div>
    <!-- Scroll Up Button -->
    <button id="scrollUpBtn"
        class="fixed bottom-5 right-5 w-12 h-12 bg-sky-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-sky-600 transition-opacity"
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
