@extends('layouts.app')

@section('title', 'Document Review Approval')
@section('subtitle', 
    ($isSupervisorQueue ?? false) ? 'Documents waiting for supervisor check' : 
    (($isDeptHeadQueue ?? false) ? 'Documents waiting for dept head approval' : 'Documents waiting for review approval')
)
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pr-8 pt-3 pb-1 shadow-sm w-fit" aria-label="Breadcrumb">
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
            <li class="text-gray-700 font-bold">Approval Queue</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="px-6 py-4">
        <x-flash-message />

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col lg:flex-row justify-between gap-4 mb-4">

                <form method="GET" id="searchForm" class="flex flex-wrap items-center gap-2">
                    <div class="relative w-full md:w-96">
                        <input type="text" name="search" value="{{ request('search') }}" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                            placeholder="Type to search...">
                        <label for="searchInput"
                            class="absolute left-4 transition-all duration-150 bg-white px-1 rounded text-gray-400 text-sm {{ request('search') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-blue-700 transition"
                            title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        @if (request('search'))
                            <button type="button" id="clearSearch"
                                class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition"
                                onclick="document.getElementById('searchInput').value=''; document.getElementById('searchForm').submit();"
                                title="Clear search">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto overflow-y-auto max-h-[520px]">
                <table class="min-w-full divide-y divide-gray-200 folder-table" style="solid #e5e7eb;">
                    <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                        <tr>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Document Number</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Part Number</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Product</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Model</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Process</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Notes</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Updated By</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Last Update</th>
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-x divide-gray-200">
                        @forelse ($mappings as $mapping)
                            <tr>
                                <td class="px-2 py-3 text-xs text-center">
                                    {{ ($mappings->currentPage() - 1) * $mappings->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-2 py-3 text-left text-xs font-medium text-gray-800 min-w-[210px]">
                                    <div class="flex flex-col gap-1">
                                        <div class="font-semibold">{{ $mapping->document_number ?? '-' }}</div>
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded w-max 
                                                @php
                                                    $statusLower = strtolower($mapping->status?->name ?? '');
                                                    $statusClass = match($statusLower) {
                                                        'need check by supervisor' => 'text-blue-800 bg-blue-100',
                                                        'need approval by dept head' => 'text-purple-800 bg-purple-100',
                                                        default => 'text-yellow-800 bg-yellow-100',
                                                    };
                                                    echo $statusClass;
                                                @endphp
                                            ">
                                            {{ $mapping->status?->name ?? '-' }}
                                        </span>
                                        <div class="text-xs text-gray-500">{{ optional($mapping->department)->name ?? 'Unknown' }}</div>
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center text-xs font-medium min-w-[100px]">
                                    @if ($mapping->partNumber->isNotEmpty())
                                        {{ $mapping->partNumber->pluck('part_number')->join(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-center text-xs">
                                    @php
                                        $products = $mapping->product->pluck('code')->filter();
                                        if ($products->isEmpty()) {
                                            $products = $mapping->partNumber
                                                ->map(fn($pn) => $pn->product?->code)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $products->isNotEmpty() ? $products->join(', ') : '-' }}
                                </td>
                                <td class="px-2 py-3 text-center text-xs">
                                    @php
                                        $models = $mapping->productModel->pluck('name')->filter();
                                        if ($models->isEmpty()) {
                                            $models = $mapping->partNumber
                                                ->map(fn($pn) => $pn->productModel?->name)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $models->isNotEmpty() ? $models->join(', ') : '-' }}
                                </td>
                                <td class="px-2 py-3 text-center text-xs capitalize">
                                    @php
                                        $processes = $mapping->process->pluck('code')->filter();
                                        if ($processes->isEmpty()) {
                                            $processes = $mapping->partNumber
                                                ->map(fn($pn) => $pn->process?->code)
                                                ->filter();
                                        }
                                    @endphp
                                    {{ $processes->isNotEmpty() ? $processes->join(', ') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs max-w-[250px]">
                                    <div class="max-h-20 overflow-y-auto text-gray-600 leading-snug">
                                        {!! $mapping->notes ?? '-' !!}
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center text-xs">{{ $mapping->user?->name ?? '-' }}</td>
                                <td class="px-2 py-3 text-center text-xs">{{ $mapping->updated_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-2 py-3 text-xs text-center">
                                    <div class="flex items-center gap-2 relative justify-center">
                                        @php
                                            $roleNames = auth()->user()->roles->pluck('name')->map(fn($r) => strtolower(trim((string) $r)))->toArray();
                                            $isSupervisorRowRole = in_array('supervisor', $roleNames, true);
                                            $isDeptHeadRowRole = in_array('dept head', $roleNames, true) || in_array('department head', $roleNames, true);
                                            $userDeptIds = auth()->user()->departments->pluck('id')->toArray();
                                            $docDeptId = $mapping->department_id ?? ($mapping->department->id ?? null);
                                            $sameDepartment = $docDeptId && in_array($docDeptId, $userDeptIds);
                                            $status = strtolower($mapping->status?->name ?? '');
                                            $shouldDirectOnlyOffice = $sameDepartment && (
                                                ($isSupervisorRowRole && $status === 'need check by supervisor') ||
                                                ($isDeptHeadRowRole && $status === 'need approval by dept head')
                                            );

                                            $visibleFiles = $mapping->files
                                                ->sortByDesc(fn($f) => !empty($f->replaced_by_id))
                                                ->values();

                                            $currentFiles = $visibleFiles
                                                ->filter(fn($f) => empty($f->replaced_by_id))
                                                ->sortByDesc('created_at')
                                                ->values();

                                            $files = $visibleFiles
                                                ->map(
                                                    fn($f) => [
                                                        'id' => $f->id,
                                                        'file_path' => $f->file_path,
                                                        'name' => $f->file_name ?? basename($f->file_path),
                                                        'url' => asset('storage/' . $f->file_path),
                                                        'replaced_by_id' => $f->replaced_by_id,
                                                    ],
                                                )
                                                ->toArray();
                                        @endphp

                                        {{-- File Button --}}
                                        @if (count($files) > 1)
                                            <button id="viewFilesBtn-{{ $mapping->id }}" type="button"
                                                title="View files"
                                                class="relative focus:outline-none text-gray-700 hover:text-blue-600 toggle-files-dropdown"
                                                data-onlyoffice-direct="{{ $shouldDirectOnlyOffice ? '1' : '0' }}"
                                                data-mapping-id="{{ $mapping->id }}">
                                                <i data-feather="file-text" class="w-6 h-6"></i>
                                                <span
                                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                                    {{ count($files) }}
                                                </span>
                                            </button>

                                            <div id="viewFilesDropdown-{{ $mapping->id }}"
                                                class="hidden absolute right-0 bottom-full mb-2 w-60 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] origin-bottom-right translate-x-2">
                                                <div class="py-1 text-xs max-h-80 overflow-y-auto">
                                                    @foreach ($files as $file)
                                                        <div
                                                            class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 gap-2">
                                                            <button type="button" title="View File"
                                                                class="flex-1 text-left view-file-btn truncate {{ !empty($file['replaced_by_id']) ? 'text-red-700' : '' }}"
                                                                data-file="{{ $file['url'] }}"
                                                                data-mapping-id="{{ $mapping->id }}"
                                                                data-file-id="{{ $file['id'] }}"
                                                                data-file-name="{{ $file['name'] }}"
                                                                data-file-path="{{ $file['file_path'] }}"
                                                                data-onlyoffice-direct="{{ $shouldDirectOnlyOffice ? '1' : '0' }}">
                                                                📄 {{ $file['name'] }}
                                                            </button>
                                                            @if (!empty($file['replaced_by_id']))
                                                                <span
                                                                    class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 whitespace-nowrap">
                                                                    Replaced
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif(count($files) === 1)
                                            @php
                                                $fileUrl = $files[0]['url'] ?? '#';
                                                $isReplacedFile = !empty($files[0]['replaced_by_id']);
                                            @endphp
                                            <button type="button" title="View File"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $isReplacedFile ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-gradient-to-tr from-cyan-400 to-blue-500 text-white' }} shadow hover:scale-110 transition-transform duration-200 view-file-btn"
                                                data-file="{{ $fileUrl }}"
                                                data-mapping-id="{{ $mapping->id }}"
                                                data-file-id="{{ $files[0]['id'] ?? '' }}"
                                                data-file-name="{{ $files[0]['name'] ?? '' }}"
                                                data-file-path="{{ $files[0]['file_path'] ?? '' }}"
                                                data-onlyoffice-direct="{{ $shouldDirectOnlyOffice ? '1' : '0' }}">
                                                <i class="bi bi-eye"></i>
                                                @if ($isReplacedFile)
                                                    <span class="ml-1 inline-block rounded-full bg-red-200 px-1 py-0.5 text-[10px] font-semibold text-red-800">
                                                        Replaced
                                                    </span>
                                                @endif
                                            </button>
                                        @endif

                                        {{-- Action Menu Button --}}
                                        <div class="relative inline-block text-left">
                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition action-menu-toggle"
                                                data-target="actionMenu-{{ $mapping->id }}"
                                                title="Actions">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>

                                            <div id="actionMenu-{{ $mapping->id }}"
                                                class="hidden absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] py-1 text-sm action-menu-dropdown">
                                                <button type="button"
                                                    class="flex items-center gap-2 w-full px-3 py-2 text-left hover:bg-gray-50 text-green-700 btn-approve"
                                                    data-id="{{ $mapping->id }}">
                                                    <i class="bi bi-check2-circle"></i>
                                                    Approve
                                                </button>

                                                <button type="button"
                                                    class="flex items-center gap-2 w-full px-3 py-2 text-left hover:bg-gray-50 text-red-700"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                    data-id="{{ $mapping->id }}">
                                                    <i class="bi bi-x-circle"></i>
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
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

            <div class="mt-4">
                {{ $mappings->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
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
                        <a id="viewFullBtn" href="#" target="_blank" class="btn btn-outline-info btn-sm d-none">
                            <i class="bi bi-arrows-fullscreen"></i> View Full
                        </a>
                        <select id="printOrientation" class="form-select form-select-sm d-none" style="width: 125px;">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                        <a id="printFileBtn" href="#" class="btn btn-outline-secondary btn-sm d-none">
                            <i class="bi bi-printer"></i> Print
                        </a>
                        <a id="downloadFileBtn" href="#" download
                            class="btn btn-outline-primary btn-sm d-none" style="display:none !important;" aria-hidden="true"
                            tabindex="-1">
                            <i class="bi bi-download"></i> Download PDF
                        </a>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <iframe id="filePreviewFrame" src="" style="width:100%; height:80vh;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-reject')
@endsection

@push('styles')
    <style>
        .folder-table {
            border-collapse: separate;
        }

        .folder-table th,
        .folder-table td {
            border-right: 1px solid #e5e7eb;
        }

        .folder-table th:last-child,
        .folder-table td:last-child {
            border-right: none;
        }

        .folder-table tbody tr td {
            border-bottom: 1px solid #f3f4f6;
        }

        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.8rem;
            border-radius: 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
            line-height: 1;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
        }

        .btn-modern:active {
            transform: translateY(0);
        }

        .btn-modern-primary {
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
        }

        .btn-modern-success {
            color: #14532d;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-color: #86efac;
        }

        .btn-modern-danger {
            color: #7f1d1d;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-color: #fca5a5;
        }

        .btn-modern-ghost {
            color: #334155;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .action-menu-dropdown {
            min-width: 11rem;
        }

        .action-fixed {
            position: fixed !important;
            top: 0;
            left: 0;
            margin-top: 0 !important;
            z-index: 10000 !important;
        }

        .toggle-files-dropdown {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .toggle-files-dropdown:hover {
            color: #2563eb;
        }

        .dropdown-fixed {
            position: fixed !important;
            z-index: 10000 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggles = document.querySelectorAll('.action-menu-toggle');
            const allMenus = document.querySelectorAll('.action-menu-dropdown');
            let currentDocId = null;
            let currentFileId = null;
            let currentFileType = null;
            let currentPreviewObjectUrl = null;

            function closeAllActionMenus() {
                allMenus.forEach(m => {
                    m.classList.add('hidden');
                    m.classList.remove('action-fixed');
                });
            }

            feather.replace();

            const previewModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
            const previewFrame = document.getElementById('filePreviewFrame');
            const viewFullBtn = document.getElementById('viewFullBtn');

            menuToggles.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetId = this.getAttribute('data-target');
                    const menu = document.getElementById(targetId);
                    const isHidden = menu.classList.contains('hidden');

                    closeAllActionMenus();
                    if (!isHidden) return;

                    // Move menu to body and position as fixed so table overflow won't clip it.
                    if (menu.parentElement !== document.body) {
                        document.body.appendChild(menu);
                    }

                    menu.classList.remove('hidden');
                    menu.classList.add('action-fixed');

                    const btnRect = this.getBoundingClientRect();
                    const menuRect = menu.getBoundingClientRect();
                    const viewportW = window.innerWidth;
                    const viewportH = window.innerHeight;

                    let left = btnRect.right - menuRect.width;
                    left = Math.max(8, Math.min(left, viewportW - menuRect.width - 8));

                    let top = btnRect.bottom + 6;
                    if (top + menuRect.height > viewportH - 8) {
                        top = Math.max(8, btnRect.top - menuRect.height - 6);
                    }

                    menu.style.left = `${left}px`;
                    menu.style.top = `${top}px`;
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.action-menu-toggle') && !e.target.closest('.action-menu-dropdown')) {
                    closeAllActionMenus();
                }
            });

            window.addEventListener('scroll', closeAllActionMenus, true);
            window.addEventListener('resize', closeAllActionMenus);

            // === File Dropdown Toggle ===
            document.querySelectorAll('.toggle-files-dropdown').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dropdown = document.getElementById(btn.id.replace('Btn', 'Dropdown'));

                    const isVisible = !dropdown.classList.contains('hidden');

                    // Close all other dropdowns
                    document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add('hidden'));

                    // If clicked dropdown is open, close it
                    if (isVisible) {
                        dropdown.classList.add('hidden');
                        return;
                    }

                    // Calculate position
                    const rect = btn.getBoundingClientRect();
                    const offsetX = -120;
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = `${rect.bottom + 6}px`;
                    dropdown.style.left = `${rect.left + offsetX}px`;
                    dropdown.classList.remove('hidden');
                    dropdown.classList.add('dropdown-fixed');
                });
            });

            // Close dropdown on scroll
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(d => d.classList.add('hidden'));
            });

            // Close dropdown on outside click
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="viewFilesDropdown"]').forEach(dropdown => {
                    const button = document.getElementById(dropdown.id.replace('Dropdown', 'Btn'));
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });

            // === File Preview ===
            const withCacheBuster = (url) => {
                if (!url) return url;
                const sep = url.includes('?') ? '&' : '?';
                return `${url}${sep}_=${Date.now()}`;
            };

            function getFileType(url) {
                if (!url) return 'unknown';
                const ext = url.split('.').pop().toLowerCase().split('?')[0];

                if (ext === 'pdf') return 'pdf';
                if (['xls', 'xlsx', 'xlsm'].includes(ext)) return 'excel';
                if (['doc', 'docx'].includes(ext)) return 'word';
                if (['ppt', 'pptx'].includes(ext)) return 'powerpoint';
                if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(ext)) return 'image';

                return 'other';
            }

            function withPdfViewerParams(url) {
                if (!url) return url;
                if (url.includes('#')) return url;
                return `${url}#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0&zoom=page-width`;
            }

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const docId = btn.dataset.mappingId;
                    let url = btn.dataset.file || '';
                    const clickedFileId = btn.dataset.fileId;
                    const clickedFilePath = btn.dataset.filePath;

                    if (docId && !clickedFilePath) {
                        try {
                            const res = await fetch(`/document-review/${docId}/files`);
                            const json = await res.json();
                            const first = json?.files?.[0]?.file_path || '';
                            if (first) {
                                url = withCacheBuster(`/storage/${first}`);
                            }
                            const list = json?.files || [];
                            let target =
                                list.find(f => String(f.id) === String(clickedFileId)) ||
                                list.find(f => f.file_path === clickedFilePath);

                            if (!target && url) {
                                const raw = url.replace(/^.*\/storage\//, '');
                                target = list.find(f => f.file_path === raw);
                            }

                            if (!target && list.length) {
                                target = list[0];
                            }

                            if (target?.file_path) {
                                url = withCacheBuster(`/storage/${target.file_path}`);
                            }
                            currentFileId = target?.id || clickedFileId || null;
                        } catch (err) {
                            console.error('Failed to load files', err);
                            currentFileId = clickedFileId || null;
                        }
                    } else {
                        url = withCacheBuster(url);
                        currentFileId = clickedFileId || null;
                    }

                    const sourceFileType = getFileType(url);
                    currentFileType = sourceFileType;

                    const shouldDirectOnlyOffice = (btn.dataset.onlyofficeDirect || '') === '1';
                    if (shouldDirectOnlyOffice && currentFileId) {
                        try {
                            const ooRes = await fetch(`/editor/${currentFileId}/onlyoffice-url`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });

                            if (ooRes.ok) {
                                const data = await ooRes.json();
                                if (data.url) {
                                    window.open(data.url, '_blank');
                                    return;
                                }
                            }

                            if (ooRes.status !== 403) {
                                console.warn('OnlyOffice preview unavailable, fallback to existing preview flow.');
                            }
                        } catch (err) {
                            console.warn('OnlyOffice request failed, fallback to existing preview flow.', err);
                        }
                    }

                    const printBtnToggle = document.getElementById('printFileBtn');
                    const downloadBtnToggle = document.getElementById('downloadFileBtn');
                    const viewFullBtnToggle = document.getElementById('viewFullBtn');
                    const docStatus = (btn.dataset.docStatus || '').toString().toLowerCase();
                    const isAdminFlag = (btn.dataset.isAdmin || '') === '1';
                    let previewUrl = url;

                    if (sourceFileType !== 'pdf' && docId) {
                        const convertUrl = new URL(`/document-review/${docId}/download-as-pdf`, window.location.origin);
                        convertUrl.searchParams.set('inline', '1');
                        if (currentFileId) {
                            convertUrl.searchParams.set('file_id', String(currentFileId));
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Converting file...',
                                text: 'Please wait while we prepare PDF preview.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => Swal.showLoading()
                            });
                        }

                        try {
                            const convertRes = await fetch(convertUrl.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/pdf, application/json'
                                }
                            });

                            const contentType = convertRes.headers.get('content-type') || '';
                            if (!convertRes.ok || !contentType.includes('application/pdf')) {
                                let errMessage = 'Failed to convert file for preview.';
                                try {
                                    const errJson = await convertRes.json();
                                    errMessage = errJson.error || errJson.message || errMessage;
                                } catch (_) {
                                }
                                throw new Error(errMessage);
                            }

                            const pdfBlob = await convertRes.blob();
                            if (currentPreviewObjectUrl) {
                                URL.revokeObjectURL(currentPreviewObjectUrl);
                            }
                            currentPreviewObjectUrl = URL.createObjectURL(pdfBlob);
                            previewUrl = currentPreviewObjectUrl;
                            currentFileType = 'pdf';
                        } catch (convertError) {
                            console.error('Preview conversion failed:', convertError);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Conversion failed',
                                    text: convertError.message || 'Unable to convert file to PDF preview.'
                                });
                            } else {
                                alert(convertError.message || 'Unable to convert file to PDF preview.');
                            }
                            return;
                        } finally {
                            if (typeof Swal !== 'undefined' && Swal.isLoading()) {
                                Swal.close();
                            }
                        }
                    }

                    const isPdf = currentFileType === 'pdf';
                    const canPrint = isPdf && docStatus === 'approved';
                    const canDownload = isPdf && (docStatus === 'approved' || isAdminFlag);
                    const printOrientationSelect = document.getElementById('printOrientation');

                    if (canPrint) {
                        printBtnToggle.classList.remove('d-none');
                        printOrientationSelect?.classList.remove('d-none');
                    } else {
                        printBtnToggle.classList.add('d-none');
                        printOrientationSelect?.classList.add('d-none');
                    }

                    if (canDownload) {
                        downloadBtnToggle.classList.remove('d-none');
                    } else {
                        downloadBtnToggle.classList.add('d-none');
                    }

                    if (isPdf) {
                        viewFullBtnToggle.classList.remove('d-none');
                    } else {
                        viewFullBtnToggle.classList.add('d-none');
                    }

                    const finalPreviewUrl = currentFileType === 'pdf'
                        ? withPdfViewerParams(previewUrl)
                        : previewUrl;
                    previewFrame.dataset.url = finalPreviewUrl;
                    viewFullBtn.href = finalPreviewUrl;
                    previewModal.show();
                });
            });

            document.getElementById('filePreviewModal')
                .addEventListener('shown.bs.modal', function() {
                    if (previewFrame.dataset.url) {
                        previewFrame.src = previewFrame.dataset.url;
                    }
                });

            document.getElementById('filePreviewModal')
                .addEventListener('hidden.bs.modal', () => {
                    previewFrame.src = '';
                    previewFrame.dataset.url = '';
                    viewFullBtn.href = '#';
                    if (currentPreviewObjectUrl) {
                        URL.revokeObjectURL(currentPreviewObjectUrl);
                        currentPreviewObjectUrl = null;
                    }
                    currentDocId = null;
                    currentFileId = null;
                    currentFileType = null;

                    const printBtnToggle = document.getElementById('printFileBtn');
                    const downloadBtnToggle = document.getElementById('downloadFileBtn');
                    const viewFullBtnToggle = document.getElementById('viewFullBtn');
                    const printOrientationSelect = document.getElementById('printOrientation');
                    printBtnToggle.classList.add('d-none');
                    downloadBtnToggle.classList.add('d-none');
                    viewFullBtnToggle.classList.add('d-none');
                    printOrientationSelect?.classList.add('d-none');
                });

            // Same wiring as folder page: open approve modal and set action dynamically.
            document.querySelectorAll('.btn-approve').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const docId = this.getAttribute('data-id');
                    const approveForm = document.getElementById('approveForm');
                    approveForm.action = `/document-review/${docId}/approve-with-dates`;

                    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
                    approveModal.show();
                });
            });

            const rejectModal = document.getElementById('rejectModal');
            const rejectForm = document.getElementById('rejectForm');

            rejectModal?.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const mappingId = btn?.getAttribute('data-id');
                rejectForm.action = `/document-review/${mappingId}/reject`;
            });
        });
    </script>
@endpush
