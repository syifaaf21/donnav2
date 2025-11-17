@extends('layouts.app')

@section('title', 'Documents - ' . $department->name)

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li><a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center"><i
                            class="bi bi-house-door me-1"></i> Dashboard</a></li>
                <li>/</li>
                <li><a href="{{ route('document-control.index') }}" class="text-blue-600 hover:underline">Document Control</a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Documents</li>
                <li>/</li>
                <li class="text-gray-700 font-medium">{{ $department->name }}</li>
            </ol>
        </nav>

        <!-- Search Form -->
        <div class="flex justify-end w-full mb-4">
            <form id="filterForm" method="GET" action="{{ route('document-control.department', $department->name) }}"
                class="flex flex-col items-end w-auto space-y-1">
                <div class="relative w-96">
                    <input type="text" name="search" id="searchInput"
                        class="peer w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-700
                  placeholder-transparent focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                        placeholder="Type to search..." value="{{ request('search') }}">

                    <label for="searchInput"
                        class="absolute left-4 transition-all duration-150 bg-white px-1 rounded
                  text-gray-400 text-sm
                  {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                        Type to search...
                    </label>

                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
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
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow p-4">
            <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Document Name</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Obsolete Date</th>
                        <th class="px-4 py-2">Updated By</th>
                        <th class="px-4 py-2">Last Update</th>
                        <th class="px-4 py-2">Notes</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mappings as $mapping)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-gray-600 text-sm">
                                {{ ($mappings->currentPage() - 1) * $mappings->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 truncate text-sm max-w-xs"
                                title="{{ $mapping->document->name }}">
                                {{ $mapping->document->name }}
                            </td>
                            {{-- Status badge --}}
                            <td class="px-4 py-2">
                                @php
                                    $statusColor = match ($mapping->status->name) {
                                        'Active' => 'bg-green-100 text-green-800',
                                        'Need Review' => 'bg-yellow-100 text-yellow-800',
                                        'Rejected' => 'bg-red-100 text-red-800',
                                        'Obsolete' => 'bg-gray-200 text-gray-800',
                                        'Uncomplete' => 'bg-orange-100 text-orange-800',
                                        default => 'bg-blue-100 text-blue-800',
                                    };
                                @endphp
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $statusColor }}">
                                    {{ $mapping->status->name }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm">
                                {{ $mapping->obsolete_date ? \Carbon\Carbon::parse($mapping->obsolete_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm truncate">
                                {{ ucwords(strtolower($mapping->user->name ?? '-')) }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm">{{ $mapping->updated_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-sm max-w-xs">
                                <div class="overflow-y-auto max-h-16 text-sm">
                                    {!! $mapping->notes ?? '-' !!}
                                </div>
                            </td>
                            {{-- Actions --}}
                            <td class="px-4 py-2 text-center">
                                <div class="flex justify-center gap-2 flex-wrap">
                                    <!-- View Files -->
                                    <div class="relative inline-block overflow-visible">
                                        @if (count($mapping->files_for_modal) > 1)
                                            <button id="viewFilesBtn-{{ $mapping->id }}" type="button"
                                                class="relative focus:outline-none text-gray-700 hover:text-blue-600 toggle-files-dropdown">
                                                <i class="bi bi-file-earmark-text text-lg"></i>
                                                <span
                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                    {{ count($mapping->files_for_modal) }}
                                                </span>
                                            </button>
                                            <div id="viewFilesDropdown-{{ $mapping->id }}"
                                                class="hidden absolute right-0 bottom-full mb-2 w-60 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] origin-bottom-right translate-x-2">
                                                <div class="py-1 text-sm max-h-80 overflow-y-auto">
                                                    @foreach ($mapping->files_for_modal as $file)
                                                        <button type="button"
                                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate rounded-md text-sm"
                                                            data-file="{{ $file['url'] }}"
                                                            data-doc-title="{{ $file['name'] }}">
                                                            📄 {{ $file['name'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif(count($mapping->files_for_modal) === 1)
                                            @php $file = $mapping->files_for_modal[0]; @endphp
                                            <button type="button"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 bg-cyan-500 hover:bg-cyan-600 text-white view-file-btn"
                                                data-file="{{ $file['url'] }}"
                                                title="View File">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Upload -->
                                    <button type="button"
                                        class="btn-revise inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-yellow-500 text-white hover:bg-yellow-600"
                                        data-docid="{{ $mapping->id }}" data-doc-title="{{ $mapping->document->name }}"
                                        data-status="{{ $mapping->status->name }}"
                                        title="Upload"
                                        data-files='@json($mapping->files_for_modal)' onclick="openReviseModal(this)">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                    <!-- Approve / Reject -->
                                    @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
                                        <button type="button"
                                            class="btn-approve inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-green-500 text-white hover:bg-green-600"
                                            data-bs-toggle="modal" data-bs-target="#approveModal"
                                            data-docid="{{ $mapping->id }}"
                                            data-doc-title="{{ $mapping->document->name }}"
                                            data-status="{{ $mapping->status->name }}"
                                            title="Approve"
                                            data-approve-url="{{ route('document-control.approve', ['mapping' => $mapping->id]) }}">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                        <button type="button"
                                            class="btn-reject inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-red-500 text-white hover:bg-red-600"
                                            data-docid="{{ $mapping->id }}"
                                            data-doc-title="{{ $mapping->document->name }}"
                                            data-notes="{{ str_replace('"', '&quot;', $mapping->notes ?? '') }}"
                                            data-status="{{ $mapping->status->name }}"
                                            title="Reject"
                                            data-reject-url="{{ route('document-control.reject', $mapping) }}">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $mappings->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content rounded-2xl">
                <div class="modal-header bg-white border-b flex items-center justify-between">
                    <!-- Container teks agar overflow disembunyikan -->
                    <h5 class="modal-title font-semibold text-gray-800 truncate max-w-[70%]" id="previewTitle"
                        title="">
                        File Preview
                    </h5>

                    <div class="flex items-center gap-2">
                        <a href="#" id="viewFullBtn" class="btn btn-info btn-sm">
                            <i class="bi bi-arrows-fullscreen"></i> View Full
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="height:80vh">
                    <iframe id="previewIframe" src="" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('contents.document-control.partials.modal-revise')
    @include('contents.document-control.partials.modal-approve')
    @include('contents.document-control.partials.modal-reject')
@endsection
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
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = window.location.origin; // gunakan sesuai routing kamu

            const searchInput = document.getElementById('searchInput');
            const filterForm = document.getElementById('filterForm');
            const clearBtn = document.getElementById('clearSearch');

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';

                    // Jika ada parameter lain (misalnya department), jangan dihapus
                    filterForm.submit();
                });
            }

            function updateActionButtonsByStatus(container) {
                container.querySelectorAll('.btn-revise, .btn-approve, .btn-reject').forEach(btn => {
                    const status = btn.dataset.status?.trim();
                    const type = btn.classList.contains('btn-revise') ? 'revise' :
                        btn.classList.contains('btn-approve') ? 'approve' :
                        'reject';

                    let enabled = false;

                    // Revise aktif untuk Active, Rejected, Obsolete, Uncomplete
                    if (type === 'revise' && ['Active', 'Rejected', 'Obsolete', 'Uncomplete'].includes(
                            status)) {
                        enabled = true;
                    }

                    // Approve/Reject hanya aktif saat Need Review
                    if (['approve', 'reject'].includes(type) && status === 'Need Review') {
                        enabled = true;
                    }

                    // Terapkan style dan disable
                    btn.disabled = !enabled;
                    btn.classList.toggle('opacity-50', !enabled);
                    btn.classList.toggle('cursor-not-allowed', !enabled);
                });
            }

            // Terapkan ke semua section mapping
            updateActionButtonsByStatus(document);

            // =========================
            // Modal Revise
            // =========================
            const reviseModal = document.getElementById('modal-revise');
            const reviseFilesContainer = document.getElementById('reviseFilesContainer');
            const newFilesContainer = document.getElementById('new-files-container');
            const addFileBtn = document.getElementById('add-file');

            window.openReviseModal = function(btn) {
                const mappingId = btn.dataset.docid;
                const files = JSON.parse(btn.dataset.files || '[]');
                reviseFilesContainer.innerHTML = '';
                newFilesContainer.innerHTML = '';

                if (files.length === 0) {
                    reviseFilesContainer.innerHTML = `
                <div class="p-3 border rounded bg-gray-50">
                    <label class="block text-sm font-medium mb-2">Upload initial files</label>
                    <input type="file" name="revision_files[]" class="form-control" required>
                </div>
            `;
                } else {
                    const activeFiles = files.filter(f => f.is_active == 1);
                    reviseFilesContainer.innerHTML = activeFiles.map((f, i) => `
                <div class="p-3 border rounded bg-gray-50 mb-2">
                    <p class="text-sm mb-1"><strong>File ${i+1}:</strong> ${f.name || 'Unnamed'}</p>
                    <a href="${f.url}" target="_blank" class="text-blue-600 text-xs hover:underline">View File</a>
                    <div class="mt-2 flex items-center gap-2">
                        <label class="text-xs text-gray-600">Replace:</label>
                        <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm">
                        <input type="hidden" name="revision_file_ids[]" value="${f.id}">
                    </div>
                </div>
            `).join('');
                }

                // set form action
                const form = document.getElementById('reviseFormDynamic');
                form.action = `${window.location.origin}/document-control/${mappingId}/revise`;

                // tampilkan modal
                reviseModal.classList.remove('hidden');
            };

            window.closeReviseModal = function() {
                reviseModal.classList.add('hidden');
            };

            // Tambah file baru di modal revise
            if (addFileBtn) {
                addFileBtn.addEventListener('click', function() {
                    const index = newFilesContainer.children.length + 1;
                    const div = document.createElement('div');
                    div.className = 'mb-2 flex items-center gap-2';
                    div.innerHTML = `
                <input type="file" name="revision_files[]" class="form-control border-gray-300 rounded p-1 text-sm" required>
                <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="this.parentElement.remove()">✕</button>
            `;
                    newFilesContainer.appendChild(div);
                });
            }

            // =========================
            // Modal Approve
            // =========================
            const approveModalEl = document.getElementById('approveModal');
            const approveDocInput = document.getElementById('approveDocId');
            const obsoleteInput = document.getElementById('obsolete_date');
            const reminderInput = document.getElementById('reminder_date');
            const approveForm = document.getElementById('approveForm');

            // Saat tombol approve diklik → set ID nya (punyamu tetap dipakai)
            document.querySelectorAll('.btn-approve').forEach(btn => {
                btn.addEventListener('click', function() {
                    const docId = this.dataset.docid;
                    if (approveDocInput) approveDocInput.value = docId;
                });
            });

            // ================================
            // 🔥 ADD THIS — Set action form approve
            // ================================
            if (approveModalEl) {
                approveModalEl.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    // Ambil URL approve dari tombol
                    const approveUrl = button.dataset.approveUrl;
                    if (approveForm && approveUrl) {
                        approveForm.action = approveUrl;
                    }
                });
            }

            // simple validation: reminder <= obsolete
            if (approveForm) {
                approveForm.addEventListener('submit', function(e) {
                    const obsolete = new Date(obsoleteInput.value);
                    const reminder = new Date(reminderInput.value);
                    let valid = true;

                    if (reminder > obsolete) {
                        document.getElementById('reminderError').style.display = 'block';
                        valid = false;
                    } else {
                        document.getElementById('reminderError').style.display = 'none';
                    }

                    if (!valid) e.preventDefault();
                });
            }

            // =========================
            // Modal Reject + Quill
            // =========================
            const rejectQuill = new Quill('#quillRejectEditor', {
                theme: 'snow',
                placeholder: 'Write rejection notes...'
            });

            const rejectModalEl = document.getElementById('rejectModal');
            const rejectDocInput = document.getElementById('rejectDocumentId');
            const rejectNotesInput = document.getElementById('rejectNotes');
            const rejectForm = document.getElementById('rejectForm');

            document.querySelectorAll('.btn-reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const docId = this.dataset.docid;
                    const notes = this.dataset.notes || '';
                    const rejectUrl = this.dataset.rejectUrl; // URL POST dari Blade

                    // Set doc id
                    rejectDocInput.value = docId;

                    // Set Quill content
                    rejectQuill.clipboard.dangerouslyPasteHTML(notes);

                    // Set form action dinamis
                    rejectForm.action = rejectUrl;

                    // Tampilkan modal
                    const modal = new bootstrap.Modal(rejectModalEl);
                    modal.show();
                });
            });

            // Saat submit form, ambil konten Quill dan simpan ke input hidden
            if (rejectForm) {
                rejectForm.addEventListener('submit', function() {
                    if (rejectQuill && rejectNotesInput) {
                        rejectNotesInput.value = rejectQuill.root.innerHTML.trim();
                    }
                });
            }

            // =========================
            // File preview dropdown / view
            // =========================
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
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.view-file-btn');
                if (!btn) return;

                const fileUrl = btn.dataset.file;
                const docTitle = btn.dataset.docTitle || 'File Preview';
                const previewTitle = document.getElementById('previewTitle');
                const previewIframe = document.getElementById('previewIframe');
                if (previewTitle) previewTitle.textContent = docTitle;
                if (previewIframe) previewIframe.src = fileUrl;

                const viewFileModalEl = document.getElementById('viewFileModal');
                if (viewFileModalEl) new bootstrap.Modal(viewFileModalEl).show();

                const viewFullBtn = document.getElementById('viewFullBtn');
                if (viewFullBtn) {
                    viewFullBtn.href = fileUrl;
                    viewFullBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            });
        });
    </script>
@endpush
