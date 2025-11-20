@extends('layouts.app')

@section('title', "Folder $docCode - " . ucwords($plant))

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
        <!-- Search & Filter Bar -->
        <form action="{{ route('document-review.showFolder', [$plant, base64_encode($docCode)]) }}" method="GET"
            class="flex justify-end items-center gap-3 mb-4 w-full flex-wrap">

            <!-- Search Input (bentuk asli) -->
            <div class="flex items-center w-full sm:w-96 relative">
                <input type="text" name="q" value="{{ request('q') }}" id="searchInput"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Search...">

                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500">
                    <i class="bi bi-search"></i>
                </button>

                <button type="button" id="clearSearch"
                    class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sky-500"
                    onclick="document.getElementById('searchInput').value=''; this.form.submit();">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
            <!-- Filter Button -->
            <button type="button"
                class="flex items-center gap-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 text-sm"
                data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i>
            </button>
        </form>


        <!-- Modal Filter -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-2xl">

                    <form action="{{ route('document-review.showFolder', [$plant, base64_encode($docCode)]) }}"
                        method="GET">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filterModalLabel">Filter Documents</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body space-y-4">

                            <!-- Part Number -->
                            <div class="flex flex-col">
                                <label class="form-label font-semibold text-gray-700 mb-1">Part Number</label>
                                <select name="part_number" id="modalPart" class="form-select rounded-lg py-2">
                                    <option value="">All Part Numbers</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part }}" @selected(request('part_number') == $part)>{{ $part }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Model -->
                            <div class="flex flex-col">
                                <label class="form-label font-semibold text-gray-700 mb-1">Model</label>
                                <select name="model" id="modalModel" class="form-select rounded-lg py-2">
                                    <option value="">All Models</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model }}" @selected(request('model') == $model)>
                                            {{ $model }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Process -->
                            <div class="flex flex-col">
                                <label class="form-label font-semibold text-gray-700 mb-1">Process</label>
                                <select name="process" id="modalProcess" class="form-select rounded-lg py-2">
                                    <option value="">All Processes</option>
                                    @foreach ($processes as $process)
                                        <option value="{{ $process }}" @selected(request('process') == $process)>
                                            {{ ucwords($process) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Product -->
                            <div class="flex flex-col">
                                <label class="form-label font-semibold text-gray-700 mb-1">Product</label>
                                <select name="product" id="modalProduct" class="form-select rounded-lg py-2">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product }}" @selected(request('product') == $product)>
                                            {{ $product }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer flex justify-between">
                            <button type="button" id="clearFilterBtn"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                                Clear
                            </button>

                            <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow p-4">
            <table class="min-w-full table-auto text-sm text-left text-gray-700 border border-gray-200">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Document Number</th>
                        <th class="px-4 py-2">Part Number</th>
                        <th class="px-4 py-2">Product</th>
                        <th class="px-4 py-2">Model</th>
                        <th class="px-4 py-2">Process</th>
                        <th class="px-4 py-2">Notes</th>
                        <th class="px-4 py-2">Deadline</th>
                        <th class="px-4 py-2">Updated By</th>
                        <th class="px-4 py-2">Last Update</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $index => $doc)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $doc->document_number ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $doc->partNumber->part_number ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $doc->product->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $doc->productModel->name ?? '-' }}</td>
                            <td class="px-4 py-2 capitalize">{{ $doc->process->code ?? '-' }}</td>
                            <td class="max-w-[250px]">
                                <div class="max-h-24 overflow-y-auto">
                                    {!! $doc->notes ?? '-' !!}
                                </div>
                            </td>
                            <td class="px-4 py-2">{{ $doc->deadline?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $doc->user?->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $doc->updated_at?->format('Y-m-d') ?? '-' }}</td>
                            @php
                                $statusName = strtolower($doc->status?->name ?? '');
                                $statusClass = match ($statusName) {
                                    'approved'
                                        => 'inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded',
                                    'rejected'
                                        => 'inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded',
                                    'need review'
                                        => 'inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded',
                                    default
                                        => 'inline-block px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded',
                                };
                            @endphp
                            <td class="px-4 py-2">
                                <span class="{{ $statusClass }}">{{ ucfirst($statusName ?: '-') }}</span>
                            </td>
                            <td class="px-4 py-2 text-center">

                                <div class="flex justify-center items-center gap-2 relative">

                                    {{-- ================= FILE BUTTON (TETAP DI LUAR) ================= --}}
                                    <div class="relative inline-block overflow-visible">
                                        @php
                                            $files = $doc->files
                                                ->map(
                                                    fn($f) => [
                                                        'name' => $f->file_name ?? basename($f->file_path),
                                                        'url' => asset('storage/' . $f->file_path),
                                                    ],
                                                )
                                                ->toArray();
                                        @endphp

                                        @if (count($files) > 1)
                                            <button id="viewFilesBtn-{{ $doc->id }}" type="button"
                                                title="View File"
                                                class="relative focus:outline-none text-gray-700 hover:text-blue-600 toggle-files-dropdown">
                                                <i data-feather="file-text" class="w-6 h-6"></i>
                                                <span
                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                    {{ count($files) }}
                                                </span>
                                            </button>

                                            <div id="viewFilesDropdown-{{ $doc->id }}"
                                                class="hidden absolute right-0 bottom-full mb-2 w-60 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] origin-bottom-right translate-x-2">
                                                <div class="py-1 text-sm max-h-80 overflow-y-auto">
                                                    @foreach ($files as $file)
                                                        <button type="button" title="View File"
                                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 view-file-btn truncate"
                                                            data-file="{{ $file['url'] }}">
                                                            📄 {{ $file['name'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif(count($files) === 1)
                                            <button type="button" title="View File"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 text-white bg-cyan-500 hover:bg-cyan-600 view-file-btn"
                                                data-file="{{ $files[0]['url'] }}">
                                                <i data-feather="file-text" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- ================== TITIK 3 (ALL OTHER ACTIONS) ================== --}}
                                    <div class="relative inline-block text-left">
                                        <button type="button"
                                            onclick="document.getElementById('actionMenu-{{ $doc->id }}').classList.toggle('hidden')"
                                            class="w-8 h-8 flex justify-center items-center rounded-full hover:bg-gray-200">
                                            <i class="bi bi-three-dots-vertical text-lg"></i>
                                        </button>

                                        <div id="actionMenu-{{ $doc->id }}"
                                            class="hidden absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] py-1 text-sm">

                                            {{-- Edit --}}
                                            @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']) ||
                                                    auth()->user()->department_id === $doc->department_id)
                                                <button type="button"
                                                    class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-yellow-600
        disabled:text-yellow-300 disabled:hover:bg-white"
                                                    data-doc-id="{{ $doc->id }}" title="Edit Document"
                                                    @if ($statusName === 'need review') disabled @endif>
                                                    <i class="bi bi-pencil mr-2"></i> Edit
                                                </button>
                                            @endif

                                            {{-- Approve --}}
                                            @if (in_array(strtolower(Auth::user()->role->name), ['admin', 'super admin']))
                                                <button type="button"
                                                    class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-green-600
    disabled:text-green-300 disabled:hover:bg-white btn-approve"
                                                    data-id="{{ $doc->id }}"
                                                    @if ($statusName !== 'need review') disabled @endif>
                                                    <i class="bi bi-check2-circle mr-2"></i> Approve
                                                </button>

                                                {{-- Reject --}}
                                                <button type="button"
                                                    class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-red-600
    disabled:text-red-300 disabled:hover:bg-white"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                    data-id="{{ $doc->id }}"
                                                    @if ($statusName !== 'need review') disabled @endif>
                                                    <i class="bi bi-x-circle mr-2"></i> Reject
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-gray-400">
                                <p class="text-sm">No data found. Apply filters to see results.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $documents->withQueryString()->links('vendor.pagination.tailwind') }}
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
                    <iframe id="filePreviewFrame" src="" style="width:100%; height:80vh;"
                        frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')
    @include('contents.document-review.partials.modal-reject')
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

        .action-fixed {
            position: fixed !important;
            z-index: 999999 !important;
            background: white !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            border-radius: 8px !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
    </style>
    @push('scripts')
        <script>
            const currentPlant = "{{ $plant }}";
            document.addEventListener('DOMContentLoaded', function() {
                const originalModelOptions = @json($models);
                const originalProcessOptions = @json($processes);
                const originalProductOptions = @json($products);

                // === Inisialisasi TomSelect ===
                let tsPart = new TomSelect("#modalPart", {
                    allowEmptyOption: true,
                    create: false,
                    placeholder: "Select Part Number",
                    onChange(value) {
                        updateModalFilters(value);
                    }
                });

                let tsModel = new TomSelect("#modalModel", {
                    allowEmptyOption: true,
                    create: false,
                    placeholder: "Select Model"
                });

                let tsProcess = new TomSelect("#modalProcess", {
                    allowEmptyOption: true,
                    create: false,
                    placeholder: "Select Process"
                });

                let tsProduct = new TomSelect("#modalProduct", {
                    allowEmptyOption: true,
                    create: false,
                    placeholder: "Select Product"
                });

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

                // === APPROVE MODAL ===
                document.querySelectorAll('.btn-approve').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const docId = this.getAttribute('data-id');

                        // Atur action form
                        const approveForm = document.getElementById('approveForm');
                        approveForm.action = `/document-review/${docId}/approve-with-dates`;

                        // Tampilkan modal
                        const approveModal = new bootstrap.Modal(document.getElementById(
                            'approveModal'));
                        approveModal.show();
                    });
                });
                // === REJECT MODAL ===
                const rejectModal = document.getElementById('rejectModal');
                rejectModal.addEventListener('show.bs.modal', function(event) {
                    let button = event.relatedTarget;
                    let docId = button.getAttribute('data-id');

                    // Set action ke form modal
                    document.getElementById('rejectForm').action =
                        `/document-review/${docId}/reject`;
                });

                let quillReject = new Quill('#quillRejectEditor', {
                    theme: 'snow'
                });

                document.getElementById('rejectForm').addEventListener('submit', function() {
                    let html = quillReject.root.innerHTML;
                    document.getElementById('rejectNotes').value = html;
                });

                function openRejectModal(docId, plant, docCode, notes) {
                    document.getElementById('rejectDocumentId').value = docId;
                    document.getElementById('rejectPlant').value = plant;
                    document.getElementById('rejectDocCode').value = btoa(docCode); // encode base64
                    document.getElementById('rejectNotes').value = notes || '';

                    // Jika pakai Quill
                    if (window.quillReject) {
                        quillReject.root.innerHTML = notes || '';
                    }

                    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                    modal.show();
                }

                const modalPart = document.getElementById('modalPart');
                const modalModel = document.getElementById('modalModel');
                const modalProcess = document.getElementById('modalProcess');
                const modalProduct = document.getElementById('modalProduct');

                if (modalPart) {
                    modalPart.addEventListener('change', updateModalFilters);
                }

                const currentPlant = "{{ $plant }}";

                function updateModalFilters(partNumber) {

                    if (typeof partNumber !== "string") {
                        partNumber = tsPart.getValue();
                    }

                    fetch(`/document-review/filters?part_number=${partNumber}&plant=${currentPlant}`)
                        .then(res => res.json())
                        .then(data => {
                            resetTomSelect(tsModel, data.models);
                            resetTomSelect(tsProcess, data.processes);
                            resetTomSelect(tsProduct, data.products);
                        });
                }

                function refreshTomSelect(ts, list) {
                    ts.clearOptions();
                    ts.addOption({
                        value: "",
                        text: "All"
                    });

                    list.forEach(item => {
                        ts.addOption({
                            value: item,
                            text: item.replace(/(^|\s)\S/g, (t) => t.toUpperCase())
                        });
                    });

                    ts.refreshOptions(false);
                    ts.setValue("");
                }


                function resetTomSelect(ts, list) {
                    ts.clearOptions();

                    ts.addOption({
                        value: "",
                        text: "All"
                    });

                    list.forEach(item => {
                        ts.addOption({
                            value: item,
                            text: item.replace(/(^|\s)\S/g, (t) => t.toUpperCase())
                        });
                    });

                    ts.refreshOptions(false);
                }

                function updateSelect(select, options) {
                    select.querySelectorAll('option').forEach(o => {
                        o.hidden = o.value && !options.includes(o.value);
                    });
                }

                const filterModal = document.getElementById('filterModal');
                if (filterModal) {
                    filterModal.addEventListener('shown.bs.modal', function() {
                        updateModalFilters(tsPart.getValue());
                    });
                }
                // === CLEAR FILTER BUTTON ===
                const clearFilterBtn = document.getElementById("clearFilterBtn");

                if (clearFilterBtn) {
                    clearFilterBtn.addEventListener("click", () => {

                        // Clear DOM select
                        tsPart.setValue("");
                        tsModel.setValue("");
                        tsProcess.setValue("");
                        tsProduct.setValue("");

                        // Reset options model/process/product
                        resetTomSelect(tsModel, originalModelOptions);
                        resetTomSelect(tsProcess, originalProcessOptions);
                        resetTomSelect(tsProduct, originalProductOptions);

                        // Hapus query string filter
                        const url = new URL(window.location.href);
                        url.searchParams.delete('part_number');
                        url.searchParams.delete('model');
                        url.searchParams.delete('process');
                        url.searchParams.delete('product');

                        window.location.href = url.toString();
                    });
                }

                document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));

                document.querySelectorAll('button[onclick*="actionMenu"]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();

                        const id = btn.getAttribute('onclick').match(/actionMenu-(\d+)/)[1];
                        const menu = document.getElementById(`actionMenu-${id}`);

                        // Tutup semua action menu lain
                        document.querySelectorAll('[id^="actionMenu-"]').forEach(m => m.classList.add(
                            'hidden'));

                        const isVisible = !menu.classList.contains('hidden');
                        if (isVisible) {
                            menu.classList.add('hidden');
                            return;
                        }

                        // Ambil posisi tombol
                        const rect = btn.getBoundingClientRect();

                        // Posisi fixed
                        menu.style.position = 'fixed';
                        menu.style.top = `${rect.bottom + 5}px`;
                        menu.style.left = `${rect.left - 140}px`; // offset sedikit ke kiri
                        menu.style.zIndex = 999999;
                        menu.classList.remove('hidden');
                    });
                });

                // Klik di luar → tutup
                document.addEventListener('click', () => {
                    document.querySelectorAll('[id^="actionMenu-"]').forEach(m => m.classList.add('hidden'));
                });
            });
        </script>
    @endpush
@endsection
