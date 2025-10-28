@extends('layouts.app')
@section('title', 'FTPP')

@section('content')
    <div class="container mx-auto px-4 py-2">
        <div>
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Breadcrumbs --}}
                <nav class="text-sm text-gray-500">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li>/</li>
                        <li>Master</li>
                        <li>/</li>
                        <li class="text-gray-700 font-medium">FTPP</li>
                    </ol>
                </nav>
            </div>

            {{-- main content --}}
            <div class="flex">
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="audit">Audit Type</button>
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="finding_category">Finding Category</button>
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="klausul">Klausul</button>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4">
                <div id="ftpp-content" class="p-2 min-h-[200px] text-gray-700">

                    {{-- Section Audit --}}
                    <div id="section-audit" class="hidden">
                        @include('contents.master.ftpp.partials.audit')
                    </div>

                    {{-- Section Finding Category --}}
                    <div id="section-finding-category" class="hidden">
                        @include('contents.master.ftpp.partials.finding_category')
                    </div>

                    {{-- Section Klausul --}}
                    <div id="section-klausul" class="hidden">
                        @include('contents.master.ftpp.partials.klausul')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".btn-tab");
            const sections = {
                audit: document.getElementById("section-audit"),
                finding_category: document.getElementById("section-finding-category"),
                klausul: document.getElementById("section-klausul")
            };

            // sembunyikan semua section dulu
            Object.values(sections).forEach(section => section.classList.add("hidden"));

            // event klik
            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const target = tab.getAttribute("data-section");

                    // hilangkan highlight tab lain
                    tabs.forEach(t => t.classList.remove("bg-blue-500", "text-white"));
                    tabs.forEach(t => t.classList.add("bg-gray-200", "text-gray-700"));

                    // aktifkan tab ini
                    tab.classList.remove("bg-gray-200", "text-gray-700");
                    tab.classList.add("bg-blue-500", "text-white");

                    // sembunyikan semua, tampilkan target
                    Object.values(sections).forEach(section => section.classList.add("hidden"));
                    sections[target].classList.remove("hidden");
                });
            });
        });
    </script>
@endpush
