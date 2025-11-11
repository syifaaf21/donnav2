@extends('layouts.app')

@section('title', "Folder $docCode - $plant")

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen space-y-6">
        <x-flash-message />

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li>
                    <a href="{{ route('document-review.index') }}" class="text-blue-600 hover:underline">Document Review</a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">{{ ucfirst($plant) }}</li>
                <li>/</li>
                <li class="text-gray-700 font-medium">{{ $docCode }}</li>
            </ol>
        </nav>

        <!-- Search bar -->
        <form id="filterForm" method="GET" action="{{ route('document-review.showFolder', [$plant, $docCode]) }}"
            class="flex justify-end mb-6 w-full">
            <div class="flex items-center w-full sm:w-96 relative">
                <input type="text" name="q" value="{{ request('q') }}" id="searchInput"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Search documents...">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500">
                    <i class="bi bi-search"></i>
                </button>
                <button type="button" id="clearSearch"
                    class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500"
                    onclick="document.getElementById('searchInput').value=''; document.getElementById('filterForm').submit();">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </form>

        <!-- Documents Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($documents as $doc)
                @php
                    $statusName = strtolower($doc->status?->name ?? '');
                    $statusClass = match ($statusName) {
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'need review' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                    $files = $doc->files
                        ->map(
                            fn($f) => [
                                'name' => $f->file_name ?? basename($f->file_path),
                                'url' => asset('storage/' . $f->file_path),
                            ],
                        )
                        ->toArray();
                @endphp
                <div class="bg-white shadow rounded-lg p-4 flex flex-col justify-between hover:shadow-lg transition">
                    <div class="space-y-2">
                        <h3 class="font-semibold text-gray-700 truncate">{{ $doc->document_number ?? '-' }}</h3>
                        <p class="text-gray-500 text-sm line-clamp-3">{!! $doc->notes ?? '-' !!}</p>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                            <span>Reminder: {{ $doc->reminder_date?->format('Y-m-d') ?? '-' }}</span>
                            <span>Deadline: {{ $doc->deadline?->format('Y-m-d') ?? '-' }}</span>
                            <span>Updated: {{ $doc->updated_at?->format('Y-m-d') ?? '-' }} by
                                {{ $doc->user?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <!-- Status Badge -->
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded {{ $statusClass }}">{{ ucfirst($statusName ?: '-') }}</span>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            {{-- File dropdown / view --}}
                            @if (count($files) > 1)
                                <div class="relative inline-block">
                                    <button type="button" class="text-gray-700 hover:text-blue-600 toggle-files-dropdown">
                                        <i data-feather="file-text" class="w-5 h-5"></i>
                                        <span
                                            class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">{{ count($files) }}</span>
                                    </button>
                                    <div
                                        class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                                        <div class="py-1 max-h-60 overflow-y-auto">
                                            @foreach ($files as $file)
                                                <button type="button"
                                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate"
                                                    data-file="{{ $file['url'] }}">
                                                    📄 {{ $file['name'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @elseif(count($files) === 1)
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 bg-white hover:bg-gray-50 view-file-btn"
                                    data-file="{{ $files[0]['url'] }}">
                                    <i data-feather="file-text" class="w-4 h-4"></i> View
                                </button>
                            @endif

                            {{-- Edit button --}}
                            <button type="button" class="btn btn-outline-warning btn-sm px-2 py-1 rounded text-xs"
                                data-doc-id="{{ $doc->id }}" title="Edit Document"
                                @if (!in_array($statusName, ['approved', 'rejected'])) disabled @endif>
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-400">
                    <p class="text-sm">No documents found. Apply filters to see results.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Preview File -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="filePreviewLabel">File Preview</h5>
                    <div class="d-flex gap-2">
                        <a id="viewFullBtn" href="#" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-arrows-fullscreen"></i> View Full
                        </a>
                        <a id="printFileBtn" href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer"></i> Print / Save as PDF
                        </a>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <iframe id="filePreviewFrame" src="" style="width:100%; height:80vh;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')

    <style>
        /* --- Dropdown fix style --- */
        .dropdown-fixed {
            position: fixed !important;
            z-index: 999999 !important;
            background-color: #ffffff !important;
            /* warna putih solid */
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            border-radius: 8px !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Tambahan: untuk isi dropdown agar tidak transparan juga */
        .dropdown-fixed .py-1 {
            background-color: #fff;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            const previewModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
            const previewFrame = document.getElementById('filePreviewFrame');
            const viewFullBtn = document.getElementById('viewFullBtn');

            // === Dropdown logic ===
            document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dropdown = document.getElementById(btn.id.replace('Btn', 'Dropdown'));

                    const isVisible = !dropdown.classList.contains('hidden');

                    // Tutup semua dropdown lain
                    document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList
                        .add('hidden'));

                    // Kalau yang diklik sedang terbuka → tutup saja
                    if (isVisible) {
                        dropdown.classList.add('hidden');
                        return;
                    }

                    // Hitung posisi
                    const rect = btn.getBoundingClientRect();
                    const offsetX = -120;
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = `${rect.bottom + 6}px`;
                    dropdown.style.left = `${rect.left + offsetX}px`;
                    dropdown.classList.remove('hidden');
                    dropdown.classList.add('dropdown-fixed');
                });
            });

            // Tutup dropdown saat scroll
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add(
                    'hidden'));
            });

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(dropdown => {
                    const button = document.getElementById(dropdown.id.replace('Dropdown', 'Btn'));
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });


            // === File preview modal ===
            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const url = btn.dataset.file;
                    previewFrame.src = url;
                    viewFullBtn.href = url;
                    previewModal.show();
                    document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList
                        .add('hidden'));
                });
            });

            const printFileBtn = document.getElementById('printFileBtn');

            printFileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const frame = document.getElementById('filePreviewFrame');
                const fileUrl = frame.src;

                if (!fileUrl) {
                    alert('No file loaded.');
                    return;
                }

                // Pastikan iframe sudah memuat file
                frame.focus();

                // Panggil print dari iframe tanpa membuka tab baru
                try {
                    frame.contentWindow.print();
                } catch (err) {
                    console.error('Unable to auto-print:', err);
                    alert('Failed to print file.');
                }
            });


            // Reset modal
            document.getElementById('filePreviewModal').addEventListener('hidden.bs.modal', () => {
                previewFrame.src = '';
                viewFullBtn.href = '#';
            });

            // Klik di luar → tutup dropdown
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(dropdown => {
                    const button = document.getElementById(dropdown.id.replace('Dropdown', 'Btn'));
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
            // === Modal Revise / Edit Document ===
            document.querySelectorAll('button[data-doc-id]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const docId = btn.getAttribute('data-doc-id');
                    const reviseModal = new bootstrap.Modal(document.getElementById('reviseModal'));
                    const reviseForm = document.getElementById('reviseForm');
                    const filesContainer = document.querySelector('.existing-files-container');

                    // Ubah action form
                    reviseForm.action =
                        `/document-review/${docId}/revise`; // ubah sesuai route kamu

                    // Kosongkan isi dulu
                    filesContainer.innerHTML = '<p class="text-muted">Loading files...</p>';

                    // Ambil data file via AJAX
                    fetch(`/document-review/${docId}/files`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.files && data.files.length > 0) {
                                filesContainer.innerHTML = `
                        <label class="form-label fw-semibold">Existing Files</label>
                        <ul class="list-group">
                            ${data.files.map(f => `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span>📄 ${f.name}</span>
                                                        <a href="${f.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </li>
                                                `).join('')}
                        </ul>
                    `;
                            } else {
                                filesContainer.innerHTML =
                                    '<p class="text-muted">No files available for revision.</p>';
                            }
                        })
                        .catch(err => {
                            console.error('Error loading files:', err);
                            filesContainer.innerHTML =
                                '<p class="text-danger">Failed to load files.</p>';
                        });

                    reviseModal.show();
                });
            });

        });
    </script>
@endsection
