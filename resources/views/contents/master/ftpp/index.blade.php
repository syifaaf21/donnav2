@extends('layouts.app')
@section('title', 'Master FTPP')
@section('subtitle', 'Manage FTPP')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-500 font-medium">Master</li>
            <li>/</li>
            <li class="text-gray-700 font-bold">FTPP</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="mx-auto px-4 py-2 space-y-4">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2 text-white">
                    <h3 class="fw-bold">Master FTPP</h3>
                    <p class="text-sm" style="font-size: 0.85rem;">
                        Use this page to manage FTPP master data, including Audit Types, Finding Categories, and Klausul.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500 font-medium">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">FTPP</li>
                </ol>
            </nav>
        </div> --}}

        {{-- main content --}}
        <div class="">
            <div class="flex ml-4">
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg font-semibold text-white transition transform duration-150 ease-in-out hover:-translate-y-0.5 hover:scale-105 hover:bg-gradient-to-b hover:from-blue-50 hover:to-white hover:text-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-200"
                    data-section="audit">Audit
                    Type</button>
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg font-semibold text-white transition transform duration-150 ease-in-out hover:-translate-y-0.5 hover:scale-105 hover:bg-gradient-to-b hover:from-blue-50 hover:to-white hover:text-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-200"
                    data-section="finding_category">Finding Category</button>
                <button
                    class="btn-tab px-4 py-2 rounded-t-lg font-semibold text-white transition transform duration-150 ease-in-out hover:-translate-y-0.5 hover:scale-105 hover:bg-gradient-to-b hover:from-blue-50 hover:to-white hover:text-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-200"
                    data-section="klausul">Klausul</button>
            </div>

            <div id="ftpp-content" class="p-4 min-h-[200px] text-gray-700 bg-white rounded-xl shadow-lg">

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
            const STORAGE_KEY = 'ftpp-active-tab';

            function activateTab(target) {
                // guard
                if (!sections[target]) return;

                // reset tabs
                tabs.forEach(t => {
                    t.classList.remove("bg-gradient-to-b", "from-blue-200", "to-white", "text-gray-700", "shadow-top");
                    t.classList.add("text-white");
                });

                // hide all sections
                Object.values(sections).forEach(section => section.classList.add("hidden"));

                // activate selected tab
                const activeBtn = document.querySelector(`.btn-tab[data-section="${target}"]`);
                if (activeBtn) {
                    activeBtn.classList.remove("text-white");
                    activeBtn.classList.add("bg-gradient-to-b", "from-blue-200", "to-white", "text-gray-700", "shadow-top");
                }
                sections[target].classList.remove("hidden");

                // persist
                localStorage.setItem(STORAGE_KEY, target);
            }

            // default hide
            Object.values(sections).forEach(section => section.classList.add("hidden"));

            // initial tab (from storage or first)
            const stored = localStorage.getItem(STORAGE_KEY);
            const initial = (stored && sections[stored]) ? stored : tabs[0]?.getAttribute("data-section");
            if (initial) activateTab(initial);

            // click handlers
            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const target = tab.getAttribute("data-section");
                    activateTab(target);
                });
            });
        });
    </script>
@endpush
