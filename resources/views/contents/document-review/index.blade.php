@extends('layouts.app')

@section('title', 'Document Review')
<style>
    .nav-link.active {
        background-color: white !important;
        border: 1px solid #d1d5db !important;
        /* gray-300 */
        border-bottom: none !important;
        color: #2563eb !important;
        /* blue-600 */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen space-y-6">

        {{-- Flash Message --}}
        <x-flash-message />

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
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
        <div class="border-b border-gray-300">
            <ul class="flex space-x-2" role="tablist">
                @foreach ($groupedByPlant as $plant => $documentsByCode)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <li role="presentation">
                        <button
                            class="nav-link px-5 py-2.5 rounded-t-lg text-sm font-medium transition-all duration-200 text-gray-600 @if ($loop->first) active @endif"
                            id="tab-{{ $slug }}" data-bs-toggle="tab"
                            data-bs-target="#tab-content-{{ $slug }}" type="button" role="tab"
                            aria-controls="tab-content-{{ $slug }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ ucfirst($plant) }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content bg-white border rounded-b-xl shadow-sm p-6">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                <div id="tab-content-{{ $slug }}" role="tabpanel" aria-labelledby="tab-{{ $slug }}"
                    class="tab-pane fade @if ($loop->first) show active @endif">

                    {{-- Grid of Folders --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                        @foreach ($documentsByCode as $docCode => $documentMappings)
                            <a href="{{ route('document-review.showFolder', [
                                'plant' => $plant,
                                'docCode' => base64_encode($docCode),
                            ]) }}"
                                class="
                                group flex flex-col items-center justify-center
                                border border-gray-200 rounded-xl p-6
                                bg-white hover:bg-[#FFF7E5]
                                shadow-sm hover:shadow-md
                                transition-all duration-200
                            ">
                                <div class="relative">
                                    <i
                                        class="bi bi-folder-fill text-yellow-300 text-6xl transition-transform group-hover:scale-110"></i>
                                </div>

                                <h3 class="mt-4 text-lg font-semibold text-gray-800 group-hover:text-blue-600">
                                    {{ $docCode }}
                                </h3>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $documentMappings->count() }} documents
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // 1️⃣ Ambil tab terakhir dari localStorage
            const lastTab = localStorage.getItem("last_selected_plant");

            if (lastTab) {
                const lastPlantButton = document.getElementById("tab-" + lastTab);
                const lastPlantContent = document.getElementById("tab-content-" + lastTab);

                if (lastPlantButton && lastPlantContent) {
                    // Hilangkan active dari semua tab
                    document.querySelectorAll(".nav-link").forEach(btn => {
                        btn.classList.remove("active");
                        btn.setAttribute("aria-selected", "false");
                    });

                    // Hilangkan active dari semua tab content
                    document.querySelectorAll(".tab-pane").forEach(pane => {
                        pane.classList.remove("show", "active");
                    });

                    // Aktifkan tab yg disimpan
                    lastPlantButton.classList.add("active");
                    lastPlantButton.setAttribute("aria-selected", "true");

                    lastPlantContent.classList.add("show", "active");
                }
            }

            // 2️⃣ Simpan tab ketika diklik
            document.querySelectorAll(".nav-link").forEach(btn => {
                btn.addEventListener("click", function() {
                    const plantSlug = this.id.replace("tab-", "");
                    localStorage.setItem("last_selected_plant", plantSlug);
                });
            });

        });
    </script>
@endpush
