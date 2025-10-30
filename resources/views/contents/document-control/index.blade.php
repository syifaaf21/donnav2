@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class="container mx-auto my-2 px-4">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left: list/filter (col 1-4) --}}
            <div class="lg:col-span-4 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 h-[85vh] overflow-auto">
                {{-- Filter --}}
                <form method="GET" class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Filter Department</label>
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

                                                    <div class="mt-2 text-xs text-gray-500 space-y-1">
                                                        <p><span class="font-semibold">Updated by:</span>
                                                            {{ $mapping->user->name ?? '-' }}</p>
                                                        <p><span class="font-semibold">Last Update:</span>
                                                            {{ $mapping->updated_at ? $mapping->updated_at->format('d M Y') : '-' }}
                                                        </p>
                                                        <p><span class="font-semibold">Note:</span>
                                                            @if ($mapping->notes)
                                                                {!! $mapping->notes !!}
                                                            @else
                                                                -
                                                            @endif
                                                        </p>
                                                        <p><span class="font-semibold">Valid until:</span>
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
                                                        @foreach ($mapping->files_for_modal as $file)
                                                            <button type="button"
                                                                class="mt-2 inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 view-file-btn"
                                                                data-file="{{ $file['url'] }}"
                                                                data-docid="{{ $mapping->id }}"
                                                                data-doc-title="{{ $mapping->document->name }}"
                                                                data-status="{{ $mapping->status->name }}"
                                                                data-files='@json($mapping->files_for_modal)'>
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
                                d="M15 10l4.553-2.276A2 2 0 0122 9.618V16a2 2 0 01-2 2H6a2 2 0 01-2-2V6.382a2 2 0 00.447-1.894L9 10m6 0V4">
                            </path>
                        </svg>
                        <div class="text-sm font-semibold text-gray-800" id="previewTitle">File Preview</div>
                    </div>

                    {{-- Action buttons --}}
                    <div id="actionButtons" class="hidden flex items-center gap-2">
                        <button id="btnRevise"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-yellow-200 text-yellow-700 hover:bg-yellow-50"
                            onclick="openReviseModal({{ $mapping->id ?? 'null' }})" disabled>
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6"></path>
                            </svg>
                            Revise
                        </button>

                        <button id="btnApprove" type="button" data-docid="{{ $mapping->id }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-green-200 text-green-700 hover:bg-green-50 btn-approve"
                            data-bs-toggle="modal" data-bs-target="#approveModal" disabled>
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Approve
                        </button>

                        <form id="rejectForm" method="POST" class="inline" action="#">
                            @csrf
                            <button id="btnReject" type="submit"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                                disabled>
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ✅ Preview Container dipindah ke bawah header --}}
                <div id="previewContainer" class="flex-1 p-4 flex items-center justify-center"
                    style="min-height: calc(85vh - 60px);">
                    <p class="text-gray-400">Select a file to preview</p>
                </div>
            </div>


            {{-- Hidden to track selected doc --}}
            <input type="hidden" id="currentDocId">
        </div>

        @include('contents.document-control.partials.modal-revise')
        @include('contents.document-control.partials.modal-approve')
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
                        const container = document.getElementById('file-fields');
                        if (!container) return;

                        const group = document.createElement('div');
                        group.className = 'flex items-center mb-2 file-input-group';
                        group.innerHTML = `
                <input type="file" name="files[]" class="form-control border rounded px-2 py-1 text-sm" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                <button type="button" class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded remove-file">✕</button>
            `;
                        container.appendChild(group);

                        const removeBtn = group.querySelector('.remove-file');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', () => group.remove());
                        }
                    });
                }

                // Remove existing file buttons
                document.querySelectorAll('.remove-file').forEach(btn => {
                    btn.addEventListener('click', function() {
                        btn.parentElement.remove();
                    });
                });

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
                // Preview / View File
                // =======================
                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const fileUrl = btn.dataset.file;
                        const docId = btn.dataset.docid;
                        const docTitle = btn.dataset.docTitle || 'File Preview';

                        const previewContainer = document.getElementById('previewContainer');
                        const actionButtons = document.getElementById('actionButtons');
                        const previewTitle = document.getElementById('previewTitle');

                        if (!previewContainer) return;

                        document.getElementById('currentDocId').value = docId;
                        if (actionButtons) actionButtons.classList.remove('hidden');
                        if (previewTitle) previewTitle.textContent = docTitle;

                        previewContainer.innerHTML = '';
                        const iframe = document.createElement('iframe');
                        iframe.src = fileUrl;
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.border = 'none';
                        previewContainer.appendChild(iframe);

                        const status = btn.dataset.status;
                        const reviseBtn = document.getElementById('btnRevise');
                        const approveBtn = document.getElementById('btnApprove');
                        const rejectBtn = document.getElementById('btnReject');

                        // Reset semua dulu ke kondisi disabled
                        [reviseBtn, approveBtn, rejectBtn].forEach(b => {
                            if (!b) return;
                            b.disabled = true;
                            b.classList.add('opacity-50', 'cursor-not-allowed',
                                'hover:bg-transparent');
                        });

                        // Aktifkan tombol sesuai status
                        if (['Active', 'Rejected', 'Obsolete'].includes(status)) {
                            // ✅ Revise aktif
                            reviseBtn.disabled = false;
                            reviseBtn.classList.remove('opacity-50', 'cursor-not-allowed',
                                'hover:bg-transparent');
                        } else if (status === 'Need Review') {
                            // ✅ Approve & Reject aktif
                            [approveBtn, rejectBtn].forEach(b => {
                                b.disabled = false;
                                b.classList.remove('opacity-50', 'cursor-not-allowed',
                                    'hover:bg-transparent');
                            });
                        }

                        // Save current mapping files safely
                        try {
                            window.currentMappingFiles = JSON.parse(btn.getAttribute('data-files') ||
                                '[]');
                        } catch {
                            window.currentMappingFiles = [];
                        }

                        // Update approve/reject form actions
                        const approveForm = document.getElementById('approveForm');
                        const rejectForm = document.getElementById('rejectForm');
                        if (approveForm) approveForm.action = `${baseUrl}/${docId}/approve`;
                        if (rejectForm) rejectForm.action = `${baseUrl}/${docId}/reject`;
                    });
                });

                // =======================
                // Approve Buttons
                // =======================
                document.querySelectorAll('.btn-approve').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const docId = btn.dataset.docid;
                        const docTitle = btn.dataset.docTitle;

                        const approveDocIdInput = document.getElementById('approveDocId');
                        const approveModalLabel = document.getElementById('approveModalLabel');
                        const obsoleteError = document.getElementById('obsoleteError');
                        const reminderError = document.getElementById('reminderError');
                        const obsoleteDate = document.getElementById('obsolete_date');
                        const reminderDate = document.getElementById('reminder_date');

                        if (approveDocIdInput) approveDocIdInput.value = docId;
                        if (approveModalLabel) approveModalLabel.textContent = "Approve " + docTitle;
                        if (obsoleteError) obsoleteError.style.display = 'none';
                        if (reminderError) reminderError.style.display = 'none';
                        if (obsoleteDate) obsoleteDate.value = '';
                        if (reminderDate) reminderDate.value = '';
                    });
                });

                // =======================
                // Revise Modal
                // =======================
                function populateReviseModal() {
                    const docIdInput = document.getElementById('currentDocId');
                    if (!docIdInput) return;

                    const files = window.currentMappingFiles || [];
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
                                    <a href="${f.url}" target="_blank" class="text-sm px-3 py-1.5 rounded border border-gray-200">
                                        ${f.name}
                                    </a>
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
                            });
                        }
                    });

                }

                window.openReviseModal = function(mappingId) {
                    const modal = document.getElementById('modal-revise');
                    const form = document.getElementById('reviseFormDynamic');
                    if (modal) modal.classList.remove('hidden');
                    if (form) form.action = `${baseUrl}/${mappingId}/revise`;
                    populateReviseModal();
                }

                window.closeReviseModal = function() {
                    const modal = document.getElementById('modal-revise');
                    const container = document.getElementById('reviseFilesContainer');
                    const form = document.getElementById('reviseFormDynamic');
                    if (modal) modal.classList.add('hidden');
                    if (container) container.innerHTML = '';
                    if (form) form.reset();
                }
            });
        </script>
    @endpush
