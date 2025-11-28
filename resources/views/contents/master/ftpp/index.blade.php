@extends('layouts.app')
@section('title', 'FTPP')

@section('content')
    <div class="mx-auto px-4 py-2">
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
        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex mb-2">
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg hover:shadow-[0_-2px_4px_rgba(0,0,0,0.1)]
                        hover:border-x hover:border-gray-200"
                    data-section="audit">Audit
                    Type</button>
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg hover:shadow-[0_-2px_4px_rgba(0,0,0,0.1)]
                        hover:border-x hover:border-gray-200"
                    data-section="finding_category">Finding Category</button>
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg hover:shadow-[0_-2px_4px_rgba(0,0,0,0.1)]
                        hover:border-x hover:border-gray-200"
                    data-section="klausul">Klausul</button>
            </div>

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

            // aktifkan tab pertama secara default
            const firstTab = tabs[0];
            const firstTarget = firstTab.getAttribute("data-section");

            firstTab.classList.remove("text-gray-700");
            firstTab.classList.add("bg-gradient-to-b", "from-blue-200", "to-white", "text-gray-700", "shadow-top");
            sections[firstTarget].classList.remove("hidden");

            // event klik
            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const target = tab.getAttribute("data-section");

                    // hilangkan highlight tab lain
                    tabs.forEach(t => t.classList.remove("bg-gradient-to-b", "from-blue-200",
                        "to-white", "text-gray-700", "shadow-top"));
                    tabs.forEach(t => t.classList.add("text-gray-700"));

                    // aktifkan tab ini
                    tab.classList.remove("text-gray-700");
                    tab.classList.add("bg-gradient-to-b", "from-blue-200", "to-white",
                        "text-gray-700", "shadow-top");

                    // sembunyikan semua, tampilkan target
                    Object.values(sections).forEach(section => section.classList.add("hidden"));
                    sections[target].classList.remove("hidden");
                });
            });
        });
    </script>
@endpush
