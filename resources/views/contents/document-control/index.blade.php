@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class=" mx-auto my-4 px-4">
        {{-- 🔹 Header + Breadcrumb --}}
        <div class="flex justify-between items-center mb-6">
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-700 font-semibold">Document Control</li>
                </ol>
            </nav>
        </div>

        {{-- 🔹 Main Card --}}
        <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
            {{-- Filter --}}
            <form method="GET" id="filterForm" class="mb-6">
                @if (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'Super Admin')
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
            <div id="docList" class="space-y-4">
                @forelse ($groupedDocuments as $department => $mappings)
                    <div
                        class="border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        {{-- Header Accordion --}}
                        <a href="{{ route('document-control.department', ['department' => $department]) }}"
                            class="w-full flex items-center justify-between px-5 py-3 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-sky-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-sky-100 text-sky-600 p-2 rounded-md">
                                    <i class="bi bi-folder2-open text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $department }}</h3>
                                    <p class="text-xs text-gray-500">{{ count($mappings) }} documents</p>
                                </div>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-gray-500"></i>
                        </a>
                        {{-- Content --}}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-10">No documents found.</p>
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
