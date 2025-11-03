@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class="container mx-auto my-2 px-4">
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left: list/filter (col 1-4) --}}
            <div class="lg:col-span-4 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 h-[85vh] overflow-auto">
                {{-- Filter + Search --}}
                <div class="flex flex-col gap-3 mb-4">
                    {{-- Filter Department --}}
                    <form method="GET" class="w-full">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Filter Department</label>
                        <select id="departmentSelect" name="department_id" onchange="this.form.submit()"
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

                    {{-- Search Bar --}}
                    <form method="GET" id="searchForm" class="flex items-center w-full relative">
                        <input type="text" name="search" id="searchInput"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Search..." value="{{ request('search') }}">
                        {{-- Tombol submit --}}
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500">
                            <i class="bi bi-search"></i>
                        </button>
                        {{-- Tombol clear --}}
                        <button type="button" id="clearSearch"
                            class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </form>
                </div>
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
                                            <div class="flex flex-col gap-3">
                                                {{-- Top: Document title & status --}}
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h6 class="text-sm font-semibold text-gray-800">
                                                            {{ $mapping->document->name }}
                                                        </h6>
                                                    </div>
                                                    {{-- Status badge --}}
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                                            @if ($mapping->status->name == 'Active') bg-green-100 text-green-700
                                                            @elseif($mapping->status->name == 'Need Review') bg-yellow-100 text-yellow-800
                                                            @elseif($mapping->status->name == 'Rejected') bg-red-100 text-red-700
                                                            @else bg-gray-100 text-gray-700 @endif">
                                                        {{ $mapping->status->name ?? '-' }}
                                                    </span>
                                                </div>


                                                {{-- View button di bawah status --}}
                                                <div class="mt-1 flex justify-center">
                                                    @if (count($mapping->files_for_modal) > 1)
                                                        <div class="relative inline-block text-left">
                                                            <button type="button"
                                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md border border-gray-200 bg-white hover:bg-gray-50 focus:outline-none"
                                                                id="viewDropdownButton-{{ $mapping->id }}"
                                                                data-dropdown-toggle="viewDropdown-{{ $mapping->id }}">
                                                                View
                                                                <svg class="w-3 h-3 ml-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </button>

                                                            <div id="viewDropdown-{{ $mapping->id }}"
                                                                class="hidden absolute mt-1 w-64 left-1/2 -translate-x-1/2 origin-top bg-white border border-gray-100 rounded-md shadow-lg z-10 break-words whitespace-normal">
                                                                <div class="py-1">
                                                                    @foreach ($mapping->files_for_modal as $file)
                                                                        <button type="button"
                                                                            class="w-full text-left text-xs px-3 py-1.5 hover:bg-gray-50 view-file-btn"
                                                                            data-file="{{ $file['url'] }}"
                                                                            data-docid="{{ $mapping->id }}"
                                                                            data-doc-title="{{ $mapping->document->name }}"
                                                                            data-status="{{ $mapping->status->name }}"
                                                                            data-files='@json($mapping->files_for_modal)'>
                                                                            📄 {{ $file['name'] }}
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif(count($mapping->files_for_modal) === 1)
                                                        @php $file = $mapping->files_for_modal[0]; @endphp
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md border border-gray-200 hover:bg-gray-50 view-file-btn"
                                                            data-file="{{ $file['url'] }}"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-status="{{ $mapping->status->name }}"
                                                            data-files='@json($mapping->files_for_modal)'>
                                                            View
                                                        </button>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="text-xs text-gray-500 space-y-1 mt-2">
                                                    <p><span class="font-semibold">Updated by:</span>
                                                        {{ $mapping->user->name ?? '-' }}</p>
                                                    <p><span class="font-semibold">Last Update:</span>
                                                        {{ $mapping->updated_at ? $mapping->updated_at->format('d M Y') : '-' }}
                                                    </p>
                                                    <p><span class="font-semibold">Note:</span> {!! $mapping->notes ?? '-' !!}</p>
                                                    <p><span class="font-semibold">Valid until:</span>
                                                        {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                                                    </p>
                                                </div>

                                                {{-- Revise / Approve / Reject buttons --}}
                                                <div id="actionButtons" class="flex flex-wrap gap-2 mt-2">
                                                    {{-- Upload --}}
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-md bg-yellow-500 text-white hover:bg-yellow-600 btn-revise"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-status="{{ $mapping->status->name }}"
                                                        data-files='@json($mapping->files_for_modal)'
                                                        onclick="openReviseModal(this)">
                                                        Upload
                                                    </button>

                                                    {{-- Approve --}}
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-md bg-green-500 text-white hover:bg-green-600 btn-approve"
                                                        data-bs-toggle="modal" data-bs-target="#approveModal"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-status="{{ $mapping->status->name }}">
                                                        Approve
                                                    </button>

                                                    {{-- Reject --}}
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-md bg-red-500 text-white hover:bg-red-600 btn-reject"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-notes="{!! $mapping->notes ?? '' !!}"
                                                        data-status="{{ $mapping->status->name }}">
                                                        Reject
                                                    </button>
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
            {{-- Right: Preview (col 5-12) --}}
            <div class="lg:col-span-8 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <svg class="w-5 h-5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A2 2 0 0122 9.618V16a2 2 0 01-2 2H6a2 2 0 01-2-2V6.382a2 2 0 00.447-1.894L9 10m6 0V4">
                        </path>
                    </svg>
                    <div class="text-sm font-semibold text-gray-800" id="previewTitle">File Preview</div>
                </div>

                {{-- Preview Container --}}
                <div id="previewContainer" class="flex-1 flex items-center justify-center bg-gray-50 p-4 min-h-[70vh]">
                    <p class="text-gray-400 text-center">Select a file to preview</p>
                </div>

                {{-- Hidden field untuk tracking doc id --}}
                <input type="hidden" id="currentDocId">
            </div>
            @include('contents.document-control.partials.modal-revise')
            @include('contents.document-control.partials.modal-approve')
            @include('contents.document-control.partials.modal-reject')

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

                    // =======================
                    // Dropdown toggle
                    // =======================
                    document.addEventListener('click', function(e) {
                        const toggleBtn = e.target.closest('[data-dropdown-toggle]');
                        if (toggleBtn) {
                            const targetId = toggleBtn.getAttribute('data-dropdown-toggle');
                            const dropdown = document.getElementById(targetId);
                            if (dropdown) dropdown.classList.toggle('hidden');
                        } else {
                            document.querySelectorAll('[id^="viewDropdown-"]').forEach(d => d.classList.add(
                                'hidden'));
                        }
                    });

                    document.getElementById('clearSearch')?.addEventListener('click', function() {
                        const input = document.getElementById('searchInput');
                        if (input) {
                            input.value = '';
                            document.getElementById('searchForm').submit(); // reload tanpa filter
                        }
                    });

                    // =======================
                    // Approve Modal
                    // =======================
                    const approveModal = document.getElementById('approveModal');
                    const approveForm = document.getElementById('approveForm');
                    if (approveModal) {
                        approveModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            const mappingId = button.getAttribute('data-docid');
                            if (approveForm) approveForm.action = `${baseUrl}/${mappingId}/approve`;
                            const approveDocIdInput = document.getElementById('approveDocId');
                            if (approveDocIdInput) approveDocIdInput.value = mappingId;
                        });
                    }

                    // =======================
                    // Add / Remove File Inputs
                    // =======================
                    const addFileBtn = document.getElementById('add-file');
                    if (addFileBtn) {
                        addFileBtn.addEventListener('click', function() {
                            const wrapper = document.getElementById('new-files-container');
                            if (!wrapper) return;

                            const group = document.createElement('div');
                            group.className = 'flex items-center gap-2 mb-2';
                            group.innerHTML = `
                                <input type="file" name="revision_files[]" class="form-control" required>
                                <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded remove-file">✕</button>
                            `;
                            wrapper.appendChild(group);

                            const removeBtn = group.querySelector('.remove-file');
                            if (removeBtn) removeBtn.addEventListener('click', () => group.remove());
                        });
                    }

                    // =======================
                    // Accordion Toggle
                    // =======================
                    document.querySelectorAll('.doc-accordion-toggle').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const targetId = btn.dataset.target;
                            const panel = document.getElementById(targetId);
                            const rotateIcon = document.querySelector(`[data-rotate-for="${targetId}"]`);
                            if (!panel) return;

                            const isHidden = panel.classList.contains('hidden');
                            panel.classList.toggle('hidden', !isHidden);
                            panel.classList.toggle('block', isHidden);
                            if (rotateIcon) rotateIcon.classList.toggle('rotate-180', isHidden);
                        });
                    });

                    // =======================
                    // Preview / View File (tanpa tombol right preview)
                    // =======================
                    document.addEventListener('click', function(e) {
                        const btn = e.target.closest('.view-file-btn');
                        if (!btn) return;

                        const fileUrl = btn.dataset.file;
                        const docId = btn.dataset.docid;
                        const docTitle = btn.dataset.docTitle || 'File Preview';
                        const notes = btn.dataset.notes || '';

                        window.currentDocId = docId;
                        window.currentDocNotes = notes;

                        const previewContainer = document.getElementById('previewContainer');
                        const previewTitle = document.getElementById('previewTitle');

                        if (!previewContainer) return;

                        document.getElementById('currentDocId').value = docId;
                        if (previewTitle) previewTitle.textContent = docTitle;

                        previewContainer.innerHTML = '';
                        const iframe = document.createElement('iframe');
                        iframe.src = fileUrl;
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.border = 'none';
                        previewContainer.appendChild(iframe);

                        // Simpan current mapping files
                        try {
                            const files = JSON.parse(btn.getAttribute('data-files') || '[]');
                            btn.dataset.currentMappingFiles = JSON.stringify(files);
                            window.currentMappingFiles = files;
                        } catch {
                            window.currentMappingFiles = [];
                        }
                    });

                    // =======================
                    // Approve Buttons (Left side)
                    // =======================
                    document.querySelectorAll('.btn-approve').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const docId = btn.dataset.docid;
                            const docTitle = btn.dataset.docTitle;
                            const approveDocIdInput = document.getElementById('approveDocId');
                            const approveModalLabel = document.getElementById('approveModalLabel');
                            const approveForm = document.getElementById('approveForm');

                            if (approveDocIdInput) approveDocIdInput.value = docId;
                            if (approveModalLabel) approveModalLabel.textContent = "Approve " + docTitle;
                            if (approveForm) approveForm.action = `${baseUrl}/${docId}/approve`;
                        });
                    });

                    // =======================
                    // Revise Modal
                    // =======================
                    function populateReviseModal(btn) {
                        const files = JSON.parse(btn.dataset.files || '[]');
                        const container = document.getElementById('reviseFilesContainer');
                        if (!container) return;
                        container.innerHTML = '';

                        if (!files.length) {
                            container.innerHTML = '<p class="text-gray-500">No file available</p>';
                            return;
                        }

                        files.forEach(f => {
                            const div = document.createElement('div');
                            div.className = 'mb-4 border rounded p-3 bg-gray-50';
                            div.innerHTML = `
                                <div class="flex justify-between items-center mb-2">
                                    <strong>${f.document_name}</strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <a href="${f.url}" target="_blank" class="text-sm px-3 py-1.5 rounded border border-gray-200">${f.name}</a>
                                    <button type="button" class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded replace-file">Replace</button>
                                </div>
                                <input type="hidden" name="revision_file_ids[]" value="${f.id}">
                                <div class="mt-2 hidden file-input-wrapper">
                                    <input type="file" name="revision_files[]">
                                </div>
                            `;
                            container.appendChild(div);

                            const replaceBtn = div.querySelector('.replace-file');
                            if (replaceBtn) {
                                replaceBtn.addEventListener('click', () => {
                                    const wrapper = div.querySelector('.file-input-wrapper');
                                    if (wrapper) wrapper.classList.remove('hidden');
                                    const input = wrapper.querySelector('input[type="file"]');
                                    if (input) input.required = true;
                                });
                            }
                        });
                    }

                    window.openReviseModal = function(btn) {
                        const modal = document.getElementById('modal-revise');
                        const form = document.getElementById('reviseFormDynamic');
                        if (!modal || !form) return;

                        const mappingId = btn.dataset.docid;
                        const files = JSON.parse(btn.dataset.files || '[]');

                        form.action = `${baseUrl}/${mappingId}/revise`;
                        modal.classList.remove('hidden');

                        populateReviseModal(btn);
                    };

                    window.closeReviseModal = function() {
                        const modal = document.getElementById('modal-revise');
                        const container = document.getElementById('reviseFilesContainer');
                        const form = document.getElementById('reviseFormDynamic');
                        if (modal) modal.classList.add('hidden');
                        if (container) container.innerHTML = '';
                        if (form) form.reset();
                    };

                    // =======================
                    // Quill Reject Editor
                    // =======================
                    let rejectQuill;
                    const rejectEditorEl = document.getElementById('quillRejectEditor');
                    if (rejectEditorEl) {
                        rejectQuill = new Quill('#quillRejectEditor', {
                            theme: 'snow',
                            placeholder: 'Write rejection notes here...'
                        });
                    }

                    const rejectModalEl = document.getElementById('rejectModal');
                    const rejectModal = rejectModalEl ? new bootstrap.Modal(rejectModalEl) : null;

                    // =======================
                    // Buka modal Reject
                    // =======================
                    document.body.addEventListener('click', function(e) {
                        const btn = e.target.closest('.btn-reject');
                        if (!btn || btn.disabled) return;

                        const docId = btn.dataset.docid;
                        const notesHTML = btn.dataset.notes || ''; // HTML tersimpan

                        const rejectDocInput = document.getElementById('rejectDocumentId');
                        if (rejectDocInput) rejectDocInput.value = docId;

                        // Masukkan HTML ke Quill
                        if (rejectQuill) rejectQuill.clipboard.dangerouslyPasteHTML(notesHTML);

                        const rejectForm = document.getElementById('rejectForm');
                        if (rejectForm) rejectForm.action = `${baseUrl}/${docId}/reject`;

                        if (rejectModal) rejectModal.show();
                    });

                    // =======================
                    // Submit form Reject
                    // =======================
                    document.getElementById('rejectForm')?.addEventListener('submit', function() {
                        const notesInput = document.getElementById('rejectNotes');
                        if (rejectQuill && notesInput) {
                            // Ambil HTML dari Quill, simpan ke hidden input
                            notesInput.value = rejectQuill.root.innerHTML.trim();
                        }
                    });

                    // =======================
                    // Update left-side buttons by status
                    // =======================
                    function updateActionButtonsByStatus(container) {
                        container.querySelectorAll('.btn-revise, .btn-approve, .btn-reject').forEach(btn => {
                            const status = btn.dataset.status;
                            const type = btn.classList.contains('btn-revise') ? 'revise' :
                                btn.classList.contains('btn-approve') ? 'approve' : 'reject';

                            let enabled = false;
                            if (['Active', 'Rejected', 'Obsolete'].includes(status) && type === 'revise') enabled =
                                true;
                            else if (status === 'Need Review' && (type === 'approve' || type === 'reject'))
                                enabled = true;

                            if (enabled) {
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                            } else {
                                btn.disabled = true;
                                btn.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        });
                    }

                    document.querySelectorAll('.space-y-3').forEach(section => {
                        updateActionButtonsByStatus(section);
                    });
                });
            </script>
        @endpush
