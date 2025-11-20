@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen space-y-6">

        {{-- Flash Message --}}
        <x-flash-message />

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="list-reset flex items-center space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-700 font-semibold">Document Review</li>
            </ol>
        </nav>

        {{-- Plant Tabs --}}
        <div class="overflow-x-auto mb-4">
            <ul class="flex space-x-2 border-b border-gray-200" role="tablist">
                @php $lastTab = old('last_selected_plant') ?? null; @endphp
                @foreach ($groupedByPlant as $plant => $documentsByCode)
                    @php
                        $slug = \Illuminate\Support\Str::slug($plant);
                        $isActive = ($loop->first && !$lastTab) || ($lastTab && $lastTab === $slug);
                    @endphp
                    <li role="presentation">
                        <button
                            class="nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200
                        {{ $isActive ? 'text-blue-600 after:absolute after:-bottom-1 after:left-0 after:w-full after:h-0.5 after:bg-blue-600' : 'text-gray-600 hover:text-blue-600' }}"
                            id="tab-{{ $slug }}" data-bs-toggle="tab"
                            data-bs-target="#tab-content-{{ $slug }}" type="button" role="tab"
                            aria-controls="tab-content-{{ $slug }}"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}">
                            {{ ucfirst($plant) }}
                            {{-- Optionally: jumlah dokumen per tab --}}
                            <span class="ml-1 text-xs text-gray-400">
                                ({{ $documentsByCode->count() }})
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content bg-white border rounded-b-xl shadow-sm p-6">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                @php
                    $slug = \Illuminate\Support\Str::slug($plant);
                    $isActive = ($loop->first && !$lastTab) || ($lastTab && $lastTab === $slug);

                    $plantRoots = $documents
                        ->where('parent_id', null)
                        ->filter(fn($doc) => $documentsByCode->has($doc->code));
                @endphp
                <div id="tab-content-{{ $slug }}" role="tabpanel" aria-labelledby="tab-{{ $slug }}"
                    class="tab-pane fade {{ $isActive ? 'show active' : '' }}">
                    <ul class="space-y-2">
                        @foreach ($plantRoots as $document)
                            @include('contents.document-review.partials.tree-node', [
                                'document' => $document,
                                'plant' => $plant,
                            ])
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Expand/collapse tree node
            document.querySelectorAll(".tree-toggle").forEach(btn => {
                btn.addEventListener("click", function() {
                    const childList = this.closest('li').querySelector("ul");
                    if (childList) childList.classList.toggle("hidden");
                    this.querySelector("i").classList.toggle("rotate-90");
                });
            });

            // Tab localStorage
            const tabButtons = document.querySelectorAll(".nav-link");
            const tabPanes = document.querySelectorAll(".tab-pane");

            // Restore last selected tab
            const lastTab = localStorage.getItem("last_selected_plant");
            if (lastTab) {
                const lastPlantButton = document.getElementById("tab-" + lastTab);
                const lastPlantContent = document.getElementById("tab-content-" + lastTab);
                if (lastPlantButton && lastPlantContent) {
                    tabButtons.forEach(btn => {
                        btn.classList.remove("active", "bg-white", "text-blue-600", "border-t", "border-l",
                            "border-r", "-mb-px", "font-semibold");
                        btn.setAttribute("aria-selected", "false");
                    });
                    tabPanes.forEach(pane => {
                        pane.classList.remove("show", "active");
                    });
                    lastPlantButton.classList.add("active", "bg-white", "text-blue-600", "border-t", "border-l",
                        "border-r", "-mb-px", "font-semibold");
                    lastPlantButton.setAttribute("aria-selected", "true");
                    lastPlantContent.classList.add("show", "active");
                }
            }

            // Update localStorage on tab change
            tabButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    const plantSlug = this.id.replace("tab-", "");
                    localStorage.setItem("last_selected_plant", plantSlug);

                    // Update active classes manually
                    tabButtons.forEach(b => {
                        b.classList.remove("active", "bg-white", "text-blue-600",
                            "border-t", "border-l", "border-r", "-mb-px",
                            "font-semibold");
                        b.setAttribute("aria-selected", "false");
                    });
                    tabPanes.forEach(p => p.classList.remove("show", "active"));

                    this.classList.add("active", "bg-white", "text-blue-600", "border-t",
                        "border-l", "border-r", "-mb-px", "font-semibold");
                    this.setAttribute("aria-selected", "true");
                    const content = document.querySelector(this.dataset.bsTarget);
                    if (content) content.classList.add("show", "active");
                });
            });
        });
    </script>
@endpush
