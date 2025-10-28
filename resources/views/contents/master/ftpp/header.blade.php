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
            <div class="flex gap-2">
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="audit">Audit Type</button>
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="finding_category">Finding Category</button>
                <button class="btn-tab px-4 py-2 bg-gray-200 rounded-md hover:bg-blue-500 hover:text-white"
                    data-section="klausul">Klausul</button>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4">
                <div id="ftpp-content" class="p-2 min-h-[200px] text-gray-700">
                    {{-- default content --}}
                    <p class="text-gray-500 italic">Choose menu.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-tab');
            const contentDiv = document.getElementById('ftpp-content');

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const section = btn.dataset.section;

                    // Ubah warna tombol aktif
                    buttons.forEach(b => b.classList.remove('bg-blue-500', 'text-white'));
                    btn.classList.add('bg-blue-500', 'text-white');

                    contentDiv.innerHTML =
                        '<div class="text-center text-gray-400 py-6">Loading...</div>';

                    fetch(`{{ route('master.ftpp.load', '') }}/${section}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Gagal memuat konten');
                            return response.text();
                        })
                        .then(html => contentDiv.innerHTML = html)
                        .catch(() => contentDiv.innerHTML =
                            '<div class="text-red-500 text-center py-6">Terjadi kesalahan.</div>');
                });
            });
        });
    </script>
@endsection
