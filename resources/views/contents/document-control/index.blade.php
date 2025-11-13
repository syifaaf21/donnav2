@extends('layouts.app')

@section('title', 'Document Control')

@section('content')
    <div class="container mx-auto my-4 px-4">
        {{-- 🔹 Header + Breadcrumb --}}
        <div class="flex justify-between items-center mb-6">
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-700 font-semibold">Document Control</li>
                </ol>
            </nav>
        </div>

        {{-- 🔹 Main Card --}}
        <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
            {{-- Filter & Search --}}
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @if (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'Super Admin')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select id="departmentSelect" name="department_id"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-sky-400 focus:border-sky-400"
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

                <div class="md:col-span-2 relative">
                    <label for="searchInput" class="block text-sm font-semibold text-gray-700 mb-2 tracking-wide">
                        Search Documents
                    </label>

                    <div class="relative">
                        <input type="text" name="search" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
                   placeholder-transparent focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Type to search..." value="{{ request('search') }}">

                        <!-- Placeholder floating label -->
                        <label for="searchInput"
                            class="absolute left-4 top-2.5 text-gray-400 text-sm pointer-events-none transition-all duration-150
                   peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm
                   peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600 bg-white px-1 rounded">
                            Type to search...
                        </label>

                        <!-- Buttons (Search + Clear) -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-3">
                            <button type="submit" class="text-gray-400 hover:text-sky-600 transition-colors duration-150">
                                <i class="bi bi-search text-lg"></i>
                            </button>
                            <button type="button" id="clearSearch"
                                onclick="document.getElementById('searchInput').value=''; document.getElementById('filterForm').submit();"
                                class="text-gray-400 hover:text-red-500 transition-colors duration-150">
                                <i class="bi bi-x-circle text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- 🔹 Accordion --}}
            <div id="docList" class="space-y-4">
                @forelse ($groupedDocuments as $department => $mappings)
                    <div
                        class="border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        {{-- Header Accordion --}}
                        <button type="button"
                            class="doc-accordion-toggle w-full flex items-center justify-between px-5 py-3 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-sky-50 focus:outline-none"
                            data-target="panel-{{ $loop->index }}">
                            <div class="flex items-center gap-3">
                                <div class="bg-sky-100 text-sky-600 p-2 rounded-md">
                                    <i class="bi bi-folder2-open text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $department }}</h3>
                                    <p class="text-xs text-gray-500">{{ count($mappings) }} documents</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-down text-gray-500 transition-transform transform"
                                data-rotate-for="panel-{{ $loop->index }}"></i>
                        </button>

                        {{-- Content --}}
                        <div id="panel-{{ $loop->index }}"
                            class="accordion-panel hidden px-4 py-3 bg-white transition-all duration-300 ease-in-out">
                            <div class="space-y-4">
                                @foreach ($mappings as $mapping)
                                    <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition group">
                                        <div class="flex flex-col gap-2">
                                            {{-- Header --}}
                                            <div class="flex justify-between items-start">
                                                <h6
                                                    class="text-sm font-semibold text-gray-800 leading-snug break-words max-w-[75%] group-hover:text-sky-600">
                                                    {{ $mapping->document->name }}
                                                </h6>
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if ($mapping->status->name == 'Active') bg-green-100 text-green-700
                                                @elseif($mapping->status->name == 'Need Review') bg-yellow-100 text-yellow-800
                                                @elseif($mapping->status->name == 'Rejected') bg-red-100 text-red-700
                                                @elseif ($mapping->status->name == 'Obsolete') bg-gray-100 text-gray-600
                                                @elseif ($mapping->status->name == 'Uncomplete') bg-orange-100 text-orange-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                    {{ $mapping->status->name ?? '-' }}
                                                </span>
                                            </div>

                                            {{-- Details Section --}}
                                            <div
                                                class="mt-2 text-sm text-gray-700 bg-gray-50/70 rounded-lg border border-gray-100 p-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                                                {{-- Updated By --}}
                                                <div class="flex items-start">
                                                    <span class="font-medium text-gray-600 w-28 shrink-0">Updated by:</span>
                                                    <span
                                                        class="text-gray-800 truncate">{{ $mapping->user->name ?? '-' }}</span>
                                                </div>

                                                {{-- Last Update --}}
                                                <div class="flex items-start">
                                                    <span class="font-medium text-gray-600 w-28 shrink-0">Last
                                                        Update:</span>
                                                    <span
                                                        class="text-gray-800">{{ $mapping->updated_at ? $mapping->updated_at->format('d M Y') : '-' }}</span>
                                                </div>

                                                {{-- Valid Until --}}
                                                <div class="flex items-start">
                                                    <span class="font-medium text-gray-600 w-28 shrink-0">Valid
                                                        until:</span>
                                                    <span class="text-gray-800">
                                                        {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                                                    </span>
                                                </div>

                                                {{-- Note --}}
                                                <div class="flex items-start sm:col-span-2">
                                                    <span
                                                        class="font-medium text-gray-600 w-28 shrink-0 mt-0.5">Note:</span>
                                                    <span
                                                        class="text-gray-800 bg-white border border-gray-100 rounded-md px-3 py-2 leading-relaxed max-h-32 overflow-y-auto shadow-inner text-justify whitespace-pre-wrap break-words w-full">
                                                        {!! $mapping->notes ?? '-' !!}
                                                    </span>
                                                </div>
                                            </div>
                                            {{-- Actions --}}
                                            <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100">
                                                <button type="button"
                                                    class="btn-revise inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-yellow-500 text-white hover:bg-yellow-600"
                                                    data-docid="{{ $mapping->id }}"
                                                    data-doc-title="{{ $mapping->document->name }}"
                                                    data-status="{{ $mapping->status->name }}"
                                                    data-files='@json($mapping->files_for_modal)'
                                                    onclick="openReviseModal(this)">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>

                                                @if (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'Super Admin')
                                                    <button type="button"
                                                        class="btn-approve inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-green-500 text-white hover:bg-green-600"
                                                        data-bs-toggle="modal" data-bs-target="#approveModal"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-status="{{ $mapping->status->name }}">
                                                        <i class="bi bi-check2-circle"></i> Approve
                                                    </button>


                                                    <button type="button"
                                                        class="btn-reject inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-red-500 text-white hover:bg-red-600"
                                                        data-docid="{{ $mapping->id }}"
                                                        data-doc-title="{{ $mapping->document->name }}"
                                                        data-notes="{{ str_replace('"', '&quot;', $mapping->notes ?? '') }}"
                                                        data-status="{{ $mapping->status->name }}">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                @endif

                                                {{-- View --}}
                                                <div class="relative inline-block">
                                                    @if (count($mapping->files_for_modal) > 1)
                                                        <button id="viewFilesBtn-{{ $mapping->id }}" type="button"
                                                            class="relative focus:outline-none text-gray-700 hover:text-sky-600">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                            <span
                                                                class="absolute -top-1 -right-2 w-4 h-4 flex items-center justify-center bg-sky-500 text-white text-[10px] rounded-full">
                                                                {{ count($mapping->files_for_modal) }}
                                                            </span>
                                                        </button>

                                                        <div id="viewFilesDropdown-{{ $mapping->id }}"
                                                            class="hidden absolute right-0 bottom-full mb-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                                            <div class="py-1 text-sm max-h-48 overflow-y-auto">
                                                                @foreach ($mapping->files_for_modal as $file)
                                                                    <button type="button"
                                                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 truncate view-file-btn"
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
                                                        @php $file = $mapping->files_for_modal[0]; @endphp
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 bg-white hover:bg-gray-50 view-file-btn"
                                                            data-file="{{ $file['url'] }}"
                                                            data-docid="{{ $mapping->id }}"
                                                            data-doc-title="{{ $mapping->document->name }}"
                                                            data-status="{{ $mapping->status->name }}"
                                                            data-files='@json($mapping->files_for_modal)'>
                                                            <i class="bi bi-eye"></i> View
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
                    <p class="text-sm text-gray-500 text-center py-10">No documents found.</p>
                @endforelse
            </div>
        </div>

        {{-- 🔹 Modals --}}
        @include('contents.document-control.partials.modal-revise')
        @include('contents.document-control.partials.modal-approve')
        @include('contents.document-control.partials.modal-reject')
    </div>

    {{-- 🔹 File Preview Modal --}}
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content rounded-2xl">
                <div class="modal-header bg-white-600 text-white">
                    <h5 class="modal-title font-semibold" id="previewTitle">File Preview</h5>
                    <a href="#" id="viewFullBtn"
                        class="ml-auto text-xs bg-sky-600 hover:bg-sky/30 px-3 py-1.5 rounded-md transition pointer-events-none opacity-60">
                        View Full
                    </a>
                    <button type="button" class="btn-close btn-close-sky" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height:80vh">
                    <iframe id="previewIframe" src="" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>
    </div>
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
                btn.addEventListener('click', (e) => {
                    // Cegah konflik dengan tombol dropdown di dalam panel
                    if (e.target.closest('[id^="viewFilesBtn-"]')) return;

                    const targetId = btn.dataset.target;
                    const panel = document.getElementById(targetId);
                    const rotateIcon = document.querySelector(`[data-rotate-for="${targetId}"]`);
                    if (!panel) return;

                    const isHidden = panel.classList.contains('hidden');

                    // Tutup panel lain
                    document.querySelectorAll('.accordion-panel').forEach(p => {
                        if (p !== panel) {
                            p.classList.add('hidden');
                            const otherIcon = document.querySelector(
                                `[data-rotate-for="${p.id}"]`);
                            if (otherIcon) otherIcon.classList.remove('rotate-180');
                        }
                    });

                    // Toggle panel aktif
                    panel.classList.toggle('hidden', !isHidden);
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

                // Bersihkan isi modal
                container.innerHTML = '';
                newFilesContainer.innerHTML = '';

                if (files.length === 0) {
                    // Upload awal
                    container.innerHTML = `
                        <div class="p-3 border rounded bg-gray-50">
                            <label class="block text-sm font-medium mb-2 text-gray-700">Upload initial files</label>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm" required>
                            </div>
                        </div>
                    `;
                } else {
                    // Revisi
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
                                                                                <strong>File ${i + 1}:</strong> ${f.name || f.original_name || 'Unnamed File'}
                                                                            </p>
                                                                            <a href="${f.url}" target="_blank" class="text-blue-600 text-xs hover:underline">View File</a>
                                                                            <div class="mt-2 flex items-center gap-2">
                                                                                <label class="text-xs text-gray-600">Replace:</label>
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

                // ✅ tampilkan modal
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
                    const status = btn.dataset.status?.trim();
                    const type =
                        btn.classList.contains('btn-revise') ? 'revise' :
                        btn.classList.contains('btn-approve') ? 'approve' :
                        'reject';

                    let enabled = false;

                    // 🔹 Upload active hanya untuk: Active, Rejected, Obsolete, Uncomplete
                    if (type === 'revise' && ['Active', 'Rejected', 'Obsolete', 'Uncomplete'].includes(
                            status)) {
                        enabled = true;
                    }

                    // 🔹 Approve/Reject hanya aktif saat Need Review
                    if (['approve', 'reject'].includes(type) && status === 'Need Review') {
                        enabled = true;
                    }

                    // 🔹 Terapkan perubahan
                    btn.disabled = !enabled;
                    btn.classList.toggle('opacity-50', !enabled);
                    btn.classList.toggle('cursor-not-allowed', !enabled);
                });
            }
            document.querySelectorAll('#docList .p-4.border').forEach(section => {
                updateActionButtonsByStatus(section);
            });
        });
    </script>
@endpush
