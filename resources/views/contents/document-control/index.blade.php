@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class="container mx-auto my-2 px-4">
        {{-- Stats --}}
        @php
            // safe counts using collection filters
            $totalDocuments = $documentsMapping->count();
            $activeDocuments = $documentsMapping->filter(fn($d) => optional($d->status)->name === 'Active')->count();
            $obsoleteDocuments = $documentsMapping
                ->filter(fn($d) => optional($d->status)->name === 'Obsolete')
                ->count();

            $stats = [
                ['title' => 'Total Documents', 'count' => $totalDocuments, 'color' => 'sky'],
                ['title' => 'Active', 'count' => $activeDocuments, 'color' => 'green'],
                ['title' => 'Obsolete', 'count' => $obsoleteDocuments, 'color' => 'gray'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            @foreach ($stats as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex flex-col items-center">
                    <p class="text-sm font-semibold text-gray-700 mb-2">{{ $stat['title'] }}</p>
                    <span class="text-xl font-extrabold text-{{ $stat['color'] }}-600">
                        {{ $stat['count'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left: list/filter (col 1-4) --}}
            <div class="lg:col-span-4 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 h-[85vh] overflow-auto">
                {{-- Filter --}}
                <form method="GET" class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Filter Department</label>
                    <select name="department_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-200">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Accordion per Department (Tailwind simple) --}}
                <div id="docList" class="space-y-3">
                    @forelse ($groupedDocuments as $department => $mappings)
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <button type="button"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 focus:outline-none doc-accordion-toggle"
                                data-target="panel-{{ $loop->index }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <div class="text-left">
                                        <div class="text-sm font-semibold text-gray-800">{{ $department }}</div>
                                        <div class="text-xs text-gray-500">{{ count($mappings) }} documents</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 transition-transform transform rotate-0"
                                    data-rotate-for="panel-{{ $loop->index }}" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="panel-{{ $loop->index }}" class="hidden px-4 py-3 bg-white">
                                <div class="space-y-3">
                                    @foreach ($mappings as $mapping)
                                        <div class="rounded-lg border border-gray-100 p-3 hover:shadow-sm transition">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h6 class="text-sm font-semibold text-gray-800">
                                                            {{ $mapping->document->name }}
                                                        </h6>
                                                        <span class="text-xs text-gray-500">
                                                            {{ $mapping->document_number ?? '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="mt-1 text-xs text-gray-500 space-y-0.5">
                                                        <p>Updated by: {{ $mapping->user->name ?? '-' }}</p>
                                                        <p>Last Update: {{ $mapping->updated_at ?? '-' }}</p>
                                                        <p>Note: @if ($mapping->notes)
                                                                {!! $mapping->notes !!}
                                                            @else
                                                                -
                                                            @endif
                                                        </p>
                                                        <p>Valid until:
                                                            {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="ml-3 flex flex-col items-end gap-2">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                                    @if ($mapping->status->name == 'Active') bg-green-100 text-green-700
                                                    @elseif($mapping->status->name == 'Need Review') bg-yellow-100 text-yellow-800
                                                    @elseif($mapping->status->name == 'Rejected') bg-red-100 text-red-700
                                                    @else bg-gray-100 text-gray-700 @endif">
                                                        {{ $mapping->status->name ?? '-' }}
                                                    </span>

                                                    {{-- file buttons (stacked) --}}
                                                    <div class="flex flex-col items-end">
                                                        @foreach ($mapping->files as $file)
                                                            <button type="button"
                                                                class="mt-2 inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 view-file-btn"
                                                                data-file="{{ asset('storage/' . $file->file_path) }}"
                                                                data-docid="{{ $mapping->id }}"
                                                                data-doc-title="{{ $mapping->document->title }}"
                                                                data-status="{{ $mapping->status->name }}"
                                                                data-files='@json($mapping->files->map(fn($f) => ['id' => $f->id, 'name' => basename($f->file_path), 'url' => asset('storage/' . $f->file_path)]))'>
                                                                <svg class="w-4 h-4" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7l-4-4H4z">
                                                                    </path>
                                                                </svg>
                                                                View {{ $loop->iteration }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No documents found.</p>
                    @endforelse
                </div>
            </div>

            {{-- Right: preview (col 5-12) --}}
            <div class="lg:col-span-8 bg-white border border-gray-100 rounded-2xl shadow-sm p-0 flex flex-col">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A2 2 0 0122 9.618V16a2 2 0 01-2 2H6a2 2 0 01-2-2V6.382a2 2 0 01.447-1.894L9 10m6 0V4">
                            </path>
                        </svg>
                        <div class="text-sm font-semibold text-gray-800" id="previewTitle">File Preview</div>
                    </div>

                    {{-- Action buttons --}}
                    <div id="actionButtons" class="hidden flex items-center gap-2">
                        <button
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-yellow-200 text-yellow-700 hover:bg-yellow-50"
                            id="reviseBtn" data-bs-toggle="modal" data-bs-target="#reviseModalDynamic">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6"></path>
                            </svg>
                            Revise
                        </button>

                        <form id="approveForm" method="POST" class="inline" action="#">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-green-200 text-green-700 hover:bg-green-50">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                        </form>

                        <form id="rejectForm" method="POST" class="inline" action="#">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>

                <div id="previewContainer" class="flex-1 p-4 flex items-center justify-center"
                    style="min-height: calc(85vh - 60px);">
                    <p class="text-gray-400">Select a file to preview</p>
                </div>
            </div>
        </div>

        {{-- Hidden to track selected doc --}}
        <input type="hidden" id="currentDocId">
    </div>

    @include('contents.document-control.partials.modal-revise')

    @push('scripts')
        <script>
            (function() {
                const baseUrl = "{{ url('document-control') }}";

                // Accordion toggle
                document.querySelectorAll('.doc-accordion-toggle').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const targetId = btn.dataset.target;
                        const panel = document.getElementById(targetId);
                        const rotateIcon = document.querySelector(`[data-rotate-for="${targetId}"]`);

                        if (!panel) return;

                        const isHidden = panel.classList.contains('hidden');
                        if (isHidden) {
                            panel.classList.remove('hidden');
                            panel.classList.add('block');
                            if (rotateIcon) rotateIcon.classList.add('rotate-180');
                        } else {
                            panel.classList.add('hidden');
                            panel.classList.remove('block');
                            if (rotateIcon) rotateIcon.classList.remove('rotate-180');
                        }
                    });
                });

                // View file buttons
                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const fileUrl = btn.dataset.file;
                        const docId = btn.dataset.docid;
                        const docTitle = btn.dataset.docTitle || 'File Preview';
                        const previewContainer = document.getElementById('previewContainer');
                        const actionButtons = document.getElementById('actionButtons');

                        // Save current doc id
                        document.getElementById('currentDocId').value = docId;

                        // Update form actions
                        const approveForm = document.getElementById('approveForm');
                        const rejectForm = document.getElementById('rejectForm');
                        if (approveForm) approveForm.action = `${baseUrl}/${docId}/approve`;
                        if (rejectForm) rejectForm.action = `${baseUrl}/${docId}/reject`;

                        // Show action buttons
                        if (actionButtons) actionButtons.classList.remove('hidden');

                        // Update preview title
                        const previewTitle = document.getElementById('previewTitle');
                        if (previewTitle) previewTitle.textContent = docTitle;

                        // Render iframe safely
                        previewContainer.innerHTML = '';
                        const iframe = document.createElement('iframe');
                        iframe.src = fileUrl;
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.border = 'none';
                        previewContainer.appendChild(iframe);

                        // Save current mapping files for revise modal
                        try {
                            window.currentMappingFiles = JSON.parse(btn.getAttribute('data-files') || '[]');
                        } catch (err) {
                            window.currentMappingFiles = [];
                        }
                    });
                });

                // Populate revise modal when it is shown (works if you're using Bootstrap modal)
                const reviseModalEl = document.getElementById('reviseModalDynamic');
                if (reviseModalEl) {
                    // If Bootstrap is present, listen to its show event, otherwise fallback to custom event
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        reviseModalEl.addEventListener('show.bs.modal', (event) => {
                            populateReviseModal();
                        });
                    } else {
                        // If no bootstrap, catch clicks on revise button and populate before showing custom modal
                        const reviseBtn = document.getElementById('reviseBtn');
                        if (reviseBtn) {
                            reviseBtn.addEventListener('click', () => {
                                populateReviseModal();
                            });
                        }
                    }
                }

                function populateReviseModal() {
                    const docId = document.getElementById('currentDocId').value;
                    if (!docId) {
                        alert('Please choose a document to revise.');
                        return;
                    }

                    let files = window.currentMappingFiles || [];

                    // Fallback: find a sample button by doc id
                    if ((!files || files.length === 0) && document.querySelector(`.view-file-btn[data-docid="${docId}"]`)) {
                        const sampleBtn = document.querySelector(`.view-file-btn[data-docid="${docId}"]`);
                        try {
                            files = JSON.parse(sampleBtn.getAttribute('data-files') || '[]');
                        } catch (e) {
                            files = [];
                        }
                    }

                    const container = document.getElementById('reviseFilesContainer');
                    if (!container) return;

                    let html = '';
                    if (!files || files.length === 0) {
                        html = '<p class="text-gray-500">No file available</p>';
                    } else {
                        files.forEach(f => {
                            html += `
                        <div class="mb-4 border rounded p-3 bg-gray-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current File:</label>
                            <div class="flex items-center justify-between">
                                <a href="${f.url}" target="_blank" class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded border border-gray-200">
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7l-4-4H4z"></path></svg>
                                    ${f.name}
                                </a>
                                <span class="text-xs text-gray-500">ID: ${f.id}</span>
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Revised File</label>
                                <input type="file" name="files[${f.id}]" class="block w-full text-sm text-gray-600 border border-gray-200 rounded-lg px-3 py-2">
                            </div>
                        </div>
                        `;
                        });
                    }

                    container.innerHTML = html;

                    // Set action for revise form
                    const reviseForm = document.getElementById('reviseFormDynamic');
                    if (reviseForm) reviseForm.action = `${baseUrl}/${docId}/revise`;
                }
            })();
        </script>
    @endpush

@endsection
