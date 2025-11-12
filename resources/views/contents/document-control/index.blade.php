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
            <div class="lg:col-span-12 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 h-[85vh] overflow-auto">
                {{-- Filter + Search --}}
                <div class="flex flex-col gap-3">
                    {{-- Filter Department --}}
                    <form method="GET" id="filterForm" class="flex flex-col gap-3 mb-4 w-full">
                        @if (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'Super Admin')
                            {{-- Filter Department --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Department</label>
                                <select id="departmentSelect" name="department_id"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-200"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Search Bar --}}
                        <div class="flex items-center w-full relative">
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
                                class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500"
                                onclick="document.getElementById('searchInput').value=''; document.getElementById('filterForm').submit();">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
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
                                <div class="space-y-5">
                                    @foreach ($mappings as $mapping)
                                        <div
                                            class="rounded-lg border border-gray-100 p-3 hover:shadow-sm transition flex flex-col">
                                            <div class="flex flex-col gap-2 overflow-hidden">
                                                {{-- 🔹 Header: Document title + status --}}
                                                <div class="flex items-start justify-between gap-3 ">
                                                    <h6
                                                        class="text-sm font-semibold text-gray-800 leading-snug break-words max-w-[80%]">
                                                        {{ $mapping->document->name }}
                                                    </h6>
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap
                                                            @if ($mapping->status->name == 'Active') bg-green-100 text-green-700
                                                            @elseif($mapping->status->name == 'Need Review') bg-yellow-100 text-yellow-800
                                                            @elseif($mapping->status->name == 'Rejected') bg-red-100 text-red-700
                                                            @elseif ($mapping->status->name == 'Obsolete') bg-gray-100 text-gray-700
                                                            @elseif ($mapping->status->name == 'Uncomplete') bg-orange-100 text-orange-700
                                                            @else bg-gray-100 text-gray-700 @endif">
                                                        {{ $mapping->status->name ?? '-' }}
                                                    </span>
                                                </div>

                                                {{-- 🔹 Detail Info --}}
                                                <div class="text-xs text-gray-600 space-y-1 mt-1">
                                                    <div class="flex">
                                                        <span class="font-semibold w-28">Updated by:</span>
                                                        <span
                                                            class="flex-1 truncate">{{ $mapping->user->name ?? '-' }}</span>
                                                    </div>
                                                    <div class="flex">
                                                        <span class="font-semibold w-28">Last Update:</span>
                                                        <span
                                                            class="flex-1">{{ $mapping->updated_at ? $mapping->updated_at->format('d M Y') : '-' }}</span>
                                                    </div>
                                                    <div class="flex">
                                                        <span class="font-semibold w-28">Valid until:</span>
                                                        <span
                                                            class="flex-1">{{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}</span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <span class="font-semibold w-28">Note:</span>
                                                        <span
                                                            class="max-h-32 overflow-auto whitespace-pre-wrap break-words text-justify">{!! $mapping->notes ?? '-' !!}</span>
                                                    </div>
                                                </div>

                                                {{-- 🔹 Action Buttons --}}
                                                <div
                                                    class="flex items-center justify-start gap-2 pt-2 border-t border-gray-100 mt-2 flex-wrap">

                                                    {{-- Upload --}}
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-yellow-500 text-white hover:bg-yellow-600 btn-revise"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-status="{{ $mapping->status->name }}"
                                                        data-files='@json($mapping->files_for_modal)'
                                                        onclick="openReviseModal(this)">
                                                        Upload
                                                    </button>

                                                    @if (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'Super Admin')
                                                        {{-- Approve --}}
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-green-500 text-white hover:bg-green-600 btn-approve"
                                                            data-bs-toggle="modal" data-bs-target="#approveModal"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-status="{{ $mapping->status->name }}">
                                                            Approve
                                                        </button>

                                                        {{-- Reject --}}
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-red-500 text-white hover:bg-red-600 btn-reject"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-notes="{{ str_replace('"', '&quot;', $mapping->notes ?? '') }}"
                                                            data-status="{{ $mapping->status->name }}">
                                                            Reject
                                                        </button>
                                                    @endif
                                                    {{-- View Button seperti notifikasi --}}
                                                    <div class="relative inline-block overflow-visible">
                                                        @if (count($mapping->files_for_modal) > 1)
                                                            {{-- Tombol dengan badge jumlah file --}}
                                                            <button id="viewFilesBtn-{{ $mapping->id }}" type="button"
                                                                class="relative focus:outline-none text-gray-700 hover:text-blue-600">
                                                                <i data-feather="file-text" class="w-5 h-5"></i>
                                                                <span
                                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4
                                                                    text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                                    {{ count($mapping->files_for_modal) }}
                                                                </span>
                                                            </button>

                                                            {{-- Dropdown --}}
                                                            <div id="viewFilesDropdown-{{ $mapping->id }}"
                                                                class="hidden absolute right-0 bottom-full mb-2 w-60 bg-white border border-gray-200
                                                                rounded-md shadow-lg z-50 origin-bottom-right translate-x-2">
                                                                <div class="py-1 text-sm max-h-48 overflow-y-auto">
                                                                    @foreach ($mapping->files_for_modal as $file)
                                                                        <button type="button"
                                                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate"
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
                                                        @elseif (count($mapping->files_for_modal) === 1)
                                                            {{-- Hanya 1 file → langsung tampil tombol View biasa --}}
                                                            @php $file = $mapping->files_for_modal[0]; @endphp
                                                            <button type="button"
                                                                class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md
                                                                border border-gray-200 bg-white hover:bg-gray-50 view-file-btn"
                                                                data-file="{{ $file['url'] }}"
                                                                data-docid="{{ $mapping->id }}"
                                                                data-doc-title="{{ $mapping->document->name }}"
                                                                data-status="{{ $mapping->status->name }}"
                                                                data-files='@json($mapping->files_for_modal)'>
                                                                <i data-feather="file-text" class="w-4 h-4"></i> View
                                                            </button>
                                                        @endif
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

            @include('contents.document-control.partials.modal-revise')
            @include('contents.document-control.partials.modal-approve')
            @include('contents.document-control.partials.modal-reject')
        </div>
    @endsection
    <!-- Modal Preview File -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewTitle">File Preview</h5>
                    <a href="#" id="viewFullBtn"
                        class="ml-auto text-xs text-white bg-sky-500 hover:bg-sky-600 px-3 py-1.5 rounded-md whitespace-nowrap opacity-50 cursor-not-allowed pointer-events-none">
                        View Full
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0" style="height:80vh">
                    <iframe id="previewIframe" src="" style="width:100%; height:100%; border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

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
            document.querySelectorAll('[id^="viewFilesBtn-"]').forEach(btn => {
                const id = btn.id.replace('viewFilesBtn-', '');
                const dropdown = document.getElementById(`viewFilesDropdown-${id}`);

                if (dropdown) {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });
                }

                document.addEventListener('click', (e) => {
                    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });

            // =======================
            // Clear Search
            // =======================
            document.getElementById('clearSearch')?.addEventListener('click', function() {
                const input = document.getElementById('searchInput');
                if (input) {
                    input.value = '';
                    document.getElementById('searchForm').submit();
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
                    console.log('File input added:', group.querySelector('input[type="file"]'));


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
            // Preview / View File (optional, tetap fungsional)
            // =======================
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.view-file-btn');
                if (!btn) return;

                const fileUrl = btn.dataset.file;
                const docId = btn.dataset.docid;
                const docTitle = btn.dataset.docTitle || 'File Preview';

                // Set data ke modal
                const previewTitle = document.getElementById('previewTitle');
                const previewIframe = document.getElementById('previewIframe');
                if (previewTitle) previewTitle.textContent = docTitle;
                if (previewIframe) previewIframe.src = fileUrl;

                // Tampilkan modal
                const viewFileModalEl = document.getElementById('viewFileModal');
                if (viewFileModalEl) {
                    const viewFileModal = new bootstrap.Modal(viewFileModalEl);
                    viewFileModal.show();
                }

                // Update View Full button (optional)
                const viewFullBtn = document.getElementById('viewFullBtn');
                if (viewFullBtn) {
                    viewFullBtn.href = fileUrl;
                    viewFullBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            });


            // =======================
            // View Full (Fullscreen Overlay)
            // =======================
            document.querySelectorAll('.view-full-btn, #viewFullBtn').forEach(btn => {
                const viewFileModal = document.getElementById('viewFileModal');
                if (viewFileModal) {
                    viewFileModal.addEventListener('hidden.bs.modal', () => {
                        window.currentFile = null;
                        const viewFullBtn = document.getElementById('viewFullBtn');
                        if (viewFullBtn) {
                            viewFullBtn.dataset.file = '';
                            viewFullBtn.classList.add('opacity-50', 'cursor-not-allowed',
                                'pointer-events-none');
                        }

                        // Reset iframe height
                        const iframe = document.getElementById('previewIframe');
                        if (iframe) iframe.style.height =
                            '500px'; // ganti sesuai default modal height

                        // Reset modal dialog maxWidth
                        const modalDialog = viewFileModal.querySelector('.modal-dialog');
                        if (modalDialog) modalDialog.style.maxWidth = '';
                    });
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
                // files berasal dari data-files, yang sudah berisi is_active
                const files = (JSON.parse(btn.dataset.files || '[]')); // these are only active ones per accessor

                const container = document.getElementById('reviseFilesContainer');
                if (!container) return;
                container.innerHTML = '';

                // Jika tidak ada file (inisialisasi), tampilkan area upload awal
                if (!files.length) {
                    container.innerHTML = `
            <div class="p-3 border rounded bg-gray-50">
                <label class="block text-sm font-medium mb-2">Upload initial files</label>
                <div id="new-files-container">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="file" name="revision_files[]" class="form-control" required>
                        <input type="hidden" name="revision_file_ids[]" value="">
                        <button type="button" id="add-file" class="px-2 py-1 bg-green-100 text-green-700 rounded">+</button>
                    </div>
                </div>
            </div>
        `;
                    // reattach add-file listener (you already have code for add-file globally but when injected dynamically, attach)
                    document.getElementById('add-file').addEventListener('click', function() {
                        const wrapper = document.getElementById('new-files-container');
                        const group = document.createElement('div');
                        group.className = 'flex items-center gap-2 mb-2';
                        group.innerHTML = `
                <input type="file" name="revision_files[]" class="form-control" required>
                <input type="hidden" name="revision_file_ids[]" value="">
                <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded remove-file">✕</button>
            `;
                        wrapper.appendChild(group);
                        group.querySelector('.remove-file').addEventListener('click', () => group.remove());
                    });
                    return;
                }

                // show existing active files to allow replace
                files.forEach((f, idx) => {
                    const div = document.createElement('div');
                    div.className = 'mb-4 border rounded p-3 bg-gray-50';
                    div.innerHTML = `
            <div class="flex justify-between items-center mb-2">
                <strong class="text-sm">${f.document_name}</strong>
            </div>
            <div class="flex items-center justify-between gap-2">
                <a href="${f.url}" target="_blank" class="text-sm px-3 py-1.5 rounded border border-gray-200">${f.name}</a>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded replace-file">Replace</button>
                    <span class="text-xs text-gray-500">or leave it to keep</span>
                </div>
            </div>
            <input type="hidden" name="revision_file_ids[]" value="${f.id}">
            <div class="mt-2 hidden file-input-wrapper">
                <input type="file" name="revision_files[]">
            </div>
        `;
                    container.appendChild(div);

                    const replaceBtn = div.querySelector('.replace-file');
                    replaceBtn.addEventListener('click', () => {
                        const wrapper = div.querySelector('.file-input-wrapper');
                        wrapper.classList.remove('hidden');
                        const input = wrapper.querySelector('input[type="file"]');
                        input.required = true;

                        // IMPORTANT: to keep arrays aligned, set a value for revision_files[] even if user doesn't pick,
                        // when they pick file it will submit. If they don't pick, server will ignore because file absent.
                    });
                });

                // Also allow adding a new file (not replacing existing) if needed
                const addNewHtml = document.createElement('div');
                addNewHtml.className = 'mt-3';
                addNewHtml.innerHTML = `
        <label class="block text-sm font-medium mb-2">Add new file (optional)</label>
        <div id="new-files-container">
            <div class="flex items-center gap-2 mb-2">
                <input type="file" name="revision_files[]" class="form-control">
                <input type="hidden" name="revision_file_ids[]" value="">
                <button type="button" id="add-file-dynamic" class="px-2 py-1 bg-green-100 text-green-700 rounded">+</button>
            </div>
        </div>
    `;
                container.appendChild(addNewHtml);

                document.getElementById('add-file-dynamic').addEventListener('click', function() {
                    const wrapper = document.getElementById('new-files-container');
                    const group = document.createElement('div');
                    group.className = 'flex items-center gap-2 mb-2';
                    group.innerHTML = `
            <input type="file" name="revision_files[]" class="form-control">
            <input type="hidden" name="revision_file_ids[]" value="">
            <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded remove-file">✕</button>
        `;
                    wrapper.appendChild(group);
                    group.querySelector('.remove-file').addEventListener('click', () => group.remove());
                });
            }

            window.openReviseModal = function(btn) {
                const modal = document.getElementById('modal-revise');
                const form = document.getElementById('reviseFormDynamic');
                const container = document.getElementById('reviseFilesContainer');
                const newFilesContainer = document.getElementById('new-files-container');
                if (!modal || !form || !container) return;

                // Ambil data
                const mappingId = btn.dataset.docid;
                const files = JSON.parse(btn.dataset.files || '[]');
                form.action = `${baseUrl}/${mappingId}/revise`;

                // Bersihkan dulu isi modal
                container.innerHTML = '';
                newFilesContainer.innerHTML = '';

                if (files.length === 0) {
                    // 🟢 Upload awal
                    container.innerHTML = `
            <div class="p-3 border rounded bg-gray-50">
                <label class="block text-sm font-medium mb-2 text-gray-700">Upload initial files</label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm" required>
                </div>
            </div>
        `;
                } else {
                    // 🟡 Revisi: tampilkan file lama aktif + input pengganti
                    const activeFiles = files.filter(f => f.is_active === 1);

                    if (activeFiles.length === 0) {
                        container.innerHTML =
                            `<p class="text-gray-500 text-sm italic">No active files found.</p>`;
                    } else {
                        let html = `
                <div class="space-y-3">
                    ${activeFiles.map((f, i) => `
                            <div class="p-3 border rounded bg-gray-50">
                                <p class="text-sm text-gray-700 mb-1">
                                    <strong>File ${i + 1}:</strong> ${f.original_name}
                                </p>
                                <a href="/storage/${f.file_path}" target="_blank"
                                    class="text-blue-600 text-xs hover:underline">
                                    View current file
                                </a>

                                <div class="mt-2 flex items-center gap-2">
                                    <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm">
                                    <input type="hidden" name="revision_file_ids[]" value="${f.id}">
                                </div>
                            </div>
                        `).join('')}
                </div>
            `;
                        container.innerHTML = html;
                    }
                }

                modal.classList.remove('hidden');
            };

            window.closeReviseModal = function() {
                document.getElementById('modal-revise').classList.add('hidden');
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

            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-reject');
                if (!btn || btn.disabled) return;

                const docId = btn.dataset.docid;
                const notesHTML = btn.dataset.notes || '';

                const rejectDocInput = document.getElementById('rejectDocumentId');
                if (rejectDocInput) rejectDocInput.value = docId;

                if (rejectQuill) rejectQuill.clipboard.dangerouslyPasteHTML(notesHTML);

                const rejectForm = document.getElementById('rejectForm');
                if (rejectForm) rejectForm.action = `${baseUrl}/${docId}/reject`;

                if (rejectModal) rejectModal.show();
            });

            document.getElementById('rejectForm')?.addEventListener('submit', function() {
                const notesInput = document.getElementById('rejectNotes');
                if (rejectQuill && notesInput) {
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
                    if (['Active', 'Rejected', 'Obsolete', 'Uncomplete'].includes(status) && type ===
                        'revise') enabled = true;
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
