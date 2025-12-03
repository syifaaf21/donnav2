@extends('layouts.app')

@section('title', "Folder $docCode - " . ucwords($plant))

@section('content')
    <div class="p-6 min-h-screen space-y-6">
        <div class="py-6 mt-4 text-white">
            <div class="mb-4 text-white">
                <h1 class="fw-bold ">
                    Document Review - {{ ucfirst($plant) }}
                </h1>
                <p style="font-size: 0.9rem;">
                    Review and manage documents across different plants. Select a plant tab to view its document hierarchy.
                </p>
            </div>
        </div>

        <x-flash-message />


        <!-- Breadcrumb (left) -->
        <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li>
                    <a href="{{ route('document-review.index') }}" class="text-blue-600 hover:underline">Document
                        Review</a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">{{ ucfirst($plant) }}</li>
                <li>/</li>
                <li class="text-gray-700 font-medium">{{ $docCode }}</li>
            </ol>
        </nav>

        <!-- Search & Filter Bar (right) -->
        <form id="searchForm" action="{{ route('document-review.showFolder', [$plant, base64_encode($docCode)]) }}"
            method="GET" class="flex items-center gap-3 w-full md:w-auto justify-end">

            <!-- Search Input -->
            <div class="relative w-full md:w-96">
                <input type="text" name="q" value="{{ request('q') }}" id="searchInput"
                    class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
                 focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                    placeholder="Type to search...">

                <label for="searchInput"
                    class="absolute left-4 transition-all duration-150 bg-white px-1 rounded
                            text-gray-400 text-sm
                            {{ request('q') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                    Type to search...
                </label>

                <button type="submit"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5
                            rounded-lg text-gray-400 hover:text-blue-700 transition">
                    <i data-feather="search" class="w-5 h-5"></i>
                </button>

                @if (request('q'))
                    <button type="button" id="clearSearch"
                        class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition"
                        onclick="document.getElementById('searchInput').value=''; this.form.submit();">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                @endif
            </div>

            <!-- Filter Button -->
            <button type="button"
                class="flex items-center gap-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 text-sm"
                data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i>
            </button>
        </form>


        <!-- Modal Filter -->
        <div class="modal fade " id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
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
                                        <option value="{{ $part }}" @selected(request('part_number') == $part)>
                                            {{ $part }}
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
        <!-- New Modern Dribbble-Style Table -->
        <div class="flex-1">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto max-h-[520px]">
                    <table class="min-w-full divide-y divide-gray-200 folder-table" style="solid #e5e7eb;">
                        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Document
                                    Number
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Part Number
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Product</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Model</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Process</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Notes</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Deadline</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Updated By
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Last Update
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-x divide-gray-200">
                            @forelse ($documents as $doc)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                        {{ $doc->document_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm font-medium">
                                        @if ($doc->partNumber->isNotEmpty())
                                            {{ $doc->partNumber->pluck('part_number')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($doc->product->isNotEmpty())
                                            {{ $doc->product->pluck('name')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($doc->productModel->isNotEmpty())
                                            {{ $doc->productModel->pluck('name')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 capitalize">
                                        @if ($doc->process->isNotEmpty())
                                            {{ $doc->process->pluck('code')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 max-w-[250px]">
                                        <div class="max-h-20 overflow-y-auto text-gray-600 leading-snug">
                                            {!! $doc->notes ?? '-' !!}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="text-gray-800">{{ $doc->deadline?->format('Y-m-d') ?? '-' }}</span>
                                    </td>

                                    <td class="px-4 py-3">{{ $doc->user?->name ?? '-' }}</td>

                                    <td class="px-4 py-3">{{ $doc->updated_at?->format('Y-m-d') ?? '-' }}</td>

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
                                    <td class="px-4 py-3">
                                        <span class="{{ $statusClass }}">{{ ucwords($statusName ?: '-') }}</span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center items-center gap-2 relative">
                                            {{-- ================= FILE BUTTON ================= --}}
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
                                                    @php
                                                        $fileUrl = $files[0]['url'] ?? '#';
                                                    @endphp
                                                    <button type="button" title="View File"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-500 text-white shadow hover:scale-110 transition-transform duration-200 view-file-btn"
                                                        data-file="{{ $fileUrl }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @endif

                                            </div>

                                            {{-- ==================  (ALL OTHER ACTIONS) ================== --}}
                                            <div class="relative inline-block text-left">
                                                <button type="button"
                                                    onclick="document.getElementById('actionMenu-{{ $doc->id }}').classList.toggle('hidden')"
                                                    class="w-8 h-8 flex justify-center items-center rounded-full hover:bg-gray-200">
                                                    <i class="bi bi-three-dots-vertical text-lg"></i>
                                                </button>

                                                <div id="actionMenu-{{ $doc->id }}"
                                                    class="hidden absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] py-1 text-sm">

                                                    {{-- Edit --}}
                                                    @php
                                                        // Roles
                                                        $roles = auth()
                                                            ->user()
                                                            ->roles->pluck('name')
                                                            ->map(fn($r) => strtolower($r))
                                                            ->toArray();
                                                        $isAdminOrSuper =
                                                            in_array('admin', $roles) ||
                                                            in_array('super admin', $roles);

                                                        // Ambil semua department user (many-to-many)
                                                        $userDeptIds = auth()
                                                            ->user()
                                                            ->departments->pluck('id')
                                                            ->toArray();

                                                        // Ambil department dokumen
                                                        $docDeptId =
                                                            $doc->department_id ?? ($doc->department->id ?? null);

                                                        // Cek apakah user punya department yg sama dengan dokumen
                                                        $sameDepartment =
                                                            $docDeptId && in_array($docDeptId, $userDeptIds);
                                                    @endphp

                                                    @if ($isAdminOrSuper || $sameDepartment)
                                                        <button type="button"
                                                            class="open-revise-modal flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-yellow-600
                                                        disabled:text-yellow-300 disabled:hover:bg-white"
                                                            data-doc-id="{{ $doc->id }}" title="Edit Document"
                                                            @if ($statusName === 'need review') disabled @endif>
                                                            <i class="bi bi-pencil mr-2"></i> Edit
                                                        </button>
                                                    @endif

                                                    {{-- Approve --}}
                                                    @if (in_array(strtolower(auth()->user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin']))
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
                                    <td colspan="12">
                                        <div
                                            class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                                            <i class="bi bi-inbox text-4xl"></i>
                                            <span>No Documents found</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="mt-4 text-dark">
                {{ $documents->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>

        <!-- Modal Preview File -->
        <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewLabel"
            aria-hidden="true">
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

                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
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

            /* Per-column borders for folder table (visual only) */
            .folder-table {
                border-collapse: separate;
                /* keep sticky header rendering stable */
            }

            .folder-table th,
            .folder-table td {
                border-right: 1px solid #e5e7eb;
            }

            /* Remove right border on the last column */
            .folder-table th:last-child,
            .folder-table td:last-child {
                border-right: none;
            }

            /* Slightly soften the horizontal divider to match borders */
            .folder-table tbody tr td {
                border-bottom: 1px solid #f3f4f6;
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
                    // === MODAL REVISE (Document Review) ===

                    const reviseModal = document.getElementById('modal-revise');
                    const reviseForm = document.getElementById('reviseFormDynamic');
                    const filesContainer = document.getElementById('reviseFilesContainer');
                    const newFilesContainer = document.getElementById('new-files-container');
                    const addFileBtn = document.getElementById('add-file');

                    /**
                     * OPEN MODAL
                     * Triggered by any button: <button data-doc-id="123">
                     */
                    document.querySelectorAll('.open-revise-modal').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const docId = btn.getAttribute('data-doc-id');

                            // Set form action
                            reviseForm.action = `/document-review/${docId}/revise`;

                            // Reset dynamic fields
                            filesContainer.innerHTML =
                                "<p class='text-sm text-gray-500'>Loading files...</p>";
                            newFilesContainer.innerHTML = "";

                            // Load existing files from backend
                            fetch(`/document-review/${docId}/files`)
                                .then(res => res.json())
                                .then(data => {
                                    if (!data.success || data.files.length === 0) {
                                        filesContainer.innerHTML =
                                            `<p class="text-sm text-gray-500">No existing files.</p>`;
                                        return;
                                    }

                                    // Render file list
                                    filesContainer.innerHTML = `
                        <h4 class="font-semibold text-gray-700 mb-2">Existing Files</h4>
                        <div class="space-y-2">
                        ${data.files.map(file => `
                                                                                                                                                                                                                                                        <div class="flex items-center justify-between border rounded p-2 bg-gray-50">
                                                                                                                                                                                                                                                            <span class="text-sm">📄 ${file.original_name}</span>

                                                                                                                                                                                                                                                            <div class="flex gap-2">
                                                                                                                                                                                                                                                                <a href="/storage/${file.file_path}"
                                                                                                                                                                                                                                                                   target="_blank"
                                                                                                                                                                                                                                                                   class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                                                                                                                                                                                                                                                   View
                                                                                                                                                                                                                                                                </a>

                                                                                                                                                                                                                                                                <button type="button"
                                                                                                                                                                                                                                                                    class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded replace-btn"
                                                                                                                                                                                                                                                                    data-file-id="${file.id}">
                                                                                                                                                                                                                                                                    Replace
                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                    `).join('')}
                        </div>
                    `;
                                })
                                .catch(() => {
                                    filesContainer.innerHTML =
                                        `<p class="text-sm text-red-500">Failed to load file list.</p>`;
                                });

                            // Show modal
                            reviseModal.classList.remove('hidden');
                        });
                    });


                    /**
                     * CLOSE MODAL
                     */
                    window.closeReviseModal = function() {
                        reviseModal.classList.add('hidden');
                        newFilesContainer.innerHTML = "";
                    };


                    /**
                     * ADD FILE BUTTON (Manual Add – Not tied to replacing existing file)
                     */
                    addFileBtn.addEventListener('click', () => {
                        newFilesContainer.insertAdjacentHTML('beforeend', renderNewFileInput());
                    });


                    /**
                     * REPLACE BUTTON (Linked to existing file)
                     */
                    filesContainer.addEventListener('click', function(e) {
                        if (!e.target.classList.contains('replace-btn')) return;

                        const fileId = e.target.getAttribute('data-file-id');

                        newFilesContainer.insertAdjacentHTML(
                            'beforeend',
                            renderNewFileInput(fileId)
                        );
                    });


                    /**
                     * TEMPLATE: Input File Baru
                     */
                    function renderNewFileInput(oldFileId = null) {
                        return `
                        <div class="border rounded p-3 bg-white shadow-sm relative mt-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                New File ${oldFileId ? "(Replacing existing file)" : ""}
                            </label>

                            <input type="file"
                                name="revision_files[]"
                                required
                                class="block w-full border border-gray-300 rounded p-1 text-sm">

                            ${oldFileId ? `
                                                                                                                                                                                                                                                            <input type="hidden" name="revision_file_ids[]" value="${oldFileId}">
                                                                                                                                                                                                                                                        ` : `
                                                                                                                                                                                                                                                            <input type="hidden" name="revision_file_ids[]" value="">
                                                                                                                                                                                                                                                        `}

                                    <button type="button"
                                            class="absolute top-1 right-1 text-red-500 text-xs remove-file-btn">
                                        ✕
                                    </button>
                                </div>
                            `;
                    }

                    /**
                     * REMOVE DYNAMIC FILE INPUT
                     */
                    newFilesContainer.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-file-btn')) {
                            e.target.parentElement.remove();
                        }
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
                    // === VALIDASI REVISE FORM ===
                    // Fungsi helper untuk menampilkan error
                    function showReviseError(message) {
                        let oldAlert = document.getElementById('revise-alert');
                        if (oldAlert) oldAlert.remove();
                        const alertDiv = document.createElement('div');
                        alertDiv.id = 'revise-alert';
                        alertDiv.className = "alert alert-danger mt-3";
                        alertDiv.innerText = message;
                        reviseForm.prepend(alertDiv);
                    }
                });
            </script>
        @endpush
    @endsection
