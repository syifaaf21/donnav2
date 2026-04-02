@extends('layouts.app')

@section('title', "Folder $docCode - " . ucwords($plant))
@section('subtitle', 'Review and manage documents across different plants.')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('document-review.index') }}" class="text-blue-600 hover:underline">
                    <i class="bi bi-check-square me-1"></i> Document Review
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-medium">{{ ucfirst($plant) }}</li>
            <li>/</li>
            <li class="text-gray-700 font-medium">{{ $docCode }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="p-6 min-h-screen space-y-6">
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-6 mt-4 text-white">
                <div class="mb-4 text-white">
                    <h3 class="fw-bold ">
                        Document Review - {{ ucfirst($plant) }}
                    </h3>
                    <p style="font-size: 0.9rem;">
                        Review and manage documents across different plants. Select a plant tab to view its document
                        hierarchy.
                    </p>
                </div>
            </div> --}}
        <!-- Breadcrumb (left) -->
        {{-- <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit" aria-label="Breadcrumb">
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
        </div> --}}

        <x-flash-message />

        <!-- Search & Filter Bar (right) -->
        <form id="searchForm" action="{{ route('document-review.showFolder', [$plant, base64_encode($docCode)]) }}"
            method="GET" class="flex items-center gap-3 w-full md:w-auto justify-end">

            <!-- Search Input -->
            <div class="relative w-full md:w-96">
                <input type="text" name="q" value="{{ request('q') }}" id="searchInput"
                    class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
                    placeholder="Type to search...">
                <label for="searchInput"
                    class="absolute left-4 transition-all duration-150 bg-white px-1 rounded text-gray-400 text-sm {{ request('q') ? '-top-3 text-xs text-sky-600' : 'top-2.5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-placeholder-shown:top-2.5 peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                    Type to search...
                </label>
                <button type="submit"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-blue-700 transition">
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

            <!-- Status Filter Dropdown -->
            <div class="relative">
                <button id="filterStatusBtn" type="button"
                    class="modern-pill-btn flex items-center gap-2 px-4 h-10 rounded-xl bg-white border border-gray-200 shadow hover:bg-blue-50 transition-colors font-semibold text-gray-700 text-sm"
                    title="Filter by Status">
                    Status
                    <svg class="w-4 h-4 ml-1 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Dropdown menu -->
                <div id="filterStatusDropdown"
                    class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg z-[9999]">
                    <div class="py-2 text-sm">
                        <div class="px-3 pb-2">
                            <input type="text" id="statusSearchInput"
                                class="w-full rounded border border-gray-200 px-2 py-1 text-sm"
                                placeholder="Type to filter status...">
                        </div>
                        <ul id="statusList" class="flex flex-col gap-1 max-h-64 overflow-y-auto px-2">
                            <li>
                                <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="all" class="status-checkbox"
                                        id="statusAllCheckbox"
                                        {{ empty(request('status')) || (is_array(request('status')) && in_array('all', request('status'))) ? 'checked' : '' }}>
                                    <i class="bi bi-list-check text-gray-700 text-lg"></i>
                                    <span class="flex-1 text-sm">All</span>
                                    <span class="text-xs text-gray-500 font-semibold">0</span>
                                </label>
                            </li>
                            @php
                                $statusOptions = [
                                    [
                                        'key' => 'approved',
                                        'label' => 'Approved',
                                        'icon' => 'bi bi-check-circle-fill',
                                        'color' => 'text-green-700',
                                    ],
                                    [
                                        'key' => 'need_review',
                                        'label' => 'Need Review',
                                        'icon' => 'bi bi-exclamation-circle-fill',
                                        'color' => 'text-yellow-700',
                                    ],
                                    [
                                        'key' => 'rejected',
                                        'label' => 'Rejected',
                                        'icon' => 'bi bi-x-circle-fill',
                                        'color' => 'text-red-700',
                                    ],
                                    [
                                        'key' => 'uncomplete',
                                        'label' => 'Uncomplete',
                                        'icon' => 'bi bi-slash-circle-fill',
                                        'color' => 'text-orange-700',
                                    ],
                                ];
                                $selectedStatuses = request('status', []);
                                if (!is_array($selectedStatuses)) {
                                    $selectedStatuses = [$selectedStatuses];
                                }
                            @endphp
                            @foreach ($statusOptions as $opt)
                                <li>
                                    <label
                                        class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                        <input type="checkbox" name="status[]" value="{{ $opt['key'] }}"
                                            class="status-checkbox"
                                            {{ in_array($opt['key'], $selectedStatuses) ? 'checked' : '' }}>
                                        <i class="{{ $opt['icon'] }} {{ $opt['color'] }} text-lg"></i>
                                        <span class="flex-1 text-sm">{{ $opt['label'] }}</span>
                                        <span
                                            class="text-xs text-gray-500 font-semibold">{{ $statusCounts[$opt['key']] ?? 0 }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filter Button -->
            <button type="button"
                class="modern-square-btn bg-white border border-gray-200 rounded-xl shadow p-2.5 hover:bg-gray-100 transition"
                data-bs-toggle="modal" data-bs-target="#filterModal">
                <i data-feather="filter" class="w-5 h-5"></i>
            </button>
        </form>


        <!-- Modal Filter -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                    <form action="{{ route('document-review.showFolder', [$plant, base64_encode($docCode)]) }}"
                        method="GET">
                        {{-- Modal Header --}}
                        <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                            style="background-color: #f5f5f7;">
                            <h5 class="modal-title fw-semibold text-dark" id="filterModalLabel"
                                style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                                <i class="bi bi-funnel me-2 text-primary"></i> Filter Documents
                            </h5>

                            {{-- Close button --}}
                            <button type="button"
                                class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                                data-bs-dismiss="modal" aria-label="Close"
                                style="width: 36px; height: 36px; border: 1px solid #ddd;">
                                <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="modal-body p-5 bg-gray-50"
                            style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                            <div class="row g-4">

                                <!-- Part Number -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Part Number</label>
                                    <select name="part_number" id="modalPart"
                                        class="form-select border-0 shadow-sm rounded-3">
                                        <option value="">All Part Numbers</option>
                                        @foreach ($partNumbers as $part)
                                            <option value="{{ $part }}" @selected(request('part_number') == $part)>
                                                {{ $part }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Model -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Model</label>
                                    <select name="model" id="modalModel"
                                        class="form-select border-0 shadow-sm rounded-3">
                                        <option value="">All Models</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model }}" @selected(request('model') == $model)>
                                                {{ $model }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Process -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Process</label>
                                    <select name="process" id="modalProcess"
                                        class="form-select border-0 shadow-sm rounded-3">
                                        <option value="">All Processes</option>
                                        @foreach ($processes as $process)
                                            <option value="{{ $process }}" @selected(request('process') == $process)>
                                                {{ ucwords($process) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Product -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Product</label>
                                    <select name="product" id="modalProduct"
                                        class="form-select border-0 shadow-sm rounded-3">
                                        <option value="">All Products</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product }}" @selected(request('product') == $product)>
                                                {{ $product }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                            <button type="button" id="clearFilterBtn"
                                class="btn btn-link text-secondary fw-semibold px-4 py-2"
                                style="text-decoration: none; transition: background-color 0.3s ease;">
                                Clear
                            </button>
                            <button type="submit"
                                class="btn px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
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
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Document
                                    Number
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Part Number
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Product</th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Model</th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Process</th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Notes</th>
                                {{-- <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Deadline</th> --}}
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Updated By
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Last Update
                                </th>
                                {{-- <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Status</th> --}}
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-x divide-gray-200">
                            @forelse ($documents as $doc)
                                <tr>
                                    <td class="px-2 py-3 text-xs text-center">
                                        {{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-3 text-left text-xs font-medium text-gray-800 min-w-[210px]">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-semibold">{{ $doc->document_number ?? '-' }}</div>
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
                                            <span class="{{ $statusClass }} w-max inline-block">
                                                {{ ucwords($statusName ?: '-') }}
                                            </span>
                                            <div class="text-xs text-gray-500">
                                                {{ optional($doc->department)->name ?? 'Unknown' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3 text-center text-xs font-medium min-w-[100px]">
                                        @if ($doc->partNumber->isNotEmpty())
                                            {{ $doc->partNumber->pluck('part_number')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-2 py-3 text-center text-xs">
                                        @if ($doc->product->isNotEmpty())
                                            {{ $doc->product->pluck('code')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-2 py-3 text-center text-xs">
                                        @if ($doc->productModel->isNotEmpty())
                                            {{ $doc->productModel->pluck('name')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-2 py-3 text-center text-xs capitalize">
                                        @if ($doc->process->isNotEmpty())
                                            {{ $doc->process->pluck('code')->join(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-xs max-w-[250px]">
                                        <div class="max-h-20 overflow-y-auto text-gray-600 leading-snug note-tooltip"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $doc->notes ? e(strip_tags($doc->notes)) : '-' }}">
                                            {!! $doc->notes ?? '-' !!}
                                        </div>
                                    </td>

                                    {{-- <td class="px-4 py-3 text-xs">
                                        <span class="text-gray-800">{{ $doc->deadline?->format('Y-m-d') ?? '-' }}</span>
                                    </td> --}}

                                    <td class="px-2 py-3 text-center text-xs">{{ $doc->user?->name ?? '-' }}</td>

                                    <td class="px-2 py-3 text-center text-xs">
                                        {{ $doc->updated_at?->format('Y-m-d') ?? '-' }}</td>

                                    {{-- @php
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
                                    <td class="px-4 py-3 text-xs">
                                        <span class="{{ $statusClass }}">{{ ucwords($statusName ?: '-') }}</span>
                                    </td> --}}

                                    <td class="px-2 py-3 text-xs text-center">
                                        <div class="flex justify-center items-center gap-2 relative">
                                            @php
                                                $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
                                                $isAdmin = in_array($role, ['admin', 'super admin']);
                                                $isAdminOrSuper = $isAdmin;
                                                $isUser = !$isAdmin;

                                                $userDeptIds = auth()->user()->departments->pluck('id')->toArray();
                                                $docDeptId = $doc->department_id ?? ($doc->department->id ?? null);
                                                $sameDepartment = $docDeptId && in_array($docDeptId, $userDeptIds);

                                                $status = strtolower($statusName);

                                                $showDownloadReport = $isAdmin && $status === 'approved';
                                            @endphp
                                            {{-- ================= FILE BUTTON ================= --}}
                                            <div class="relative inline-block overflow-visible">
                                                @php
                                                    $visibleFiles = $doc->files
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
                                                        <div class="py-1 text-xs max-h-80 overflow-y-auto">
                                                            @foreach ($files as $file)
                                                                <div
                                                                    class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 gap-2">
                                                                    <button type="button" title="View File"
                                                                        class="flex-1 text-left view-file-btn truncate {{ !empty($file['replaced_by_id']) ? 'text-red-700' : '' }}"
                                                                        data-file="{{ $file['url'] }}"
                                                                        data-doc-id="{{ $doc->id }}"
                                                                        data-doc-status="{{ $status }}"
                                                                        data-is-admin="{{ $isAdmin ? '1' : '0' }}"
                                                                        data-file-id="{{ $file['id'] }}"
                                                                        data-file-name="{{ $file['name'] }}"
                                                                        data-file-path="{{ $file['file_path'] }}">
                                                                        📄 {{ $file['name'] }}
                                                                    </button>
                                                                    @if (!empty($file['replaced_by_id']))
                                                                        <span
                                                                            class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 whitespace-nowrap">
                                                                            Replaced
                                                                        </span>
                                                                    @elseif ($showDownloadReport)
                                                                        <button type="button"
                                                                            class="file-download-report-btn text-blue-600 hover:text-blue-800 whitespace-nowrap"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#downloadReportModal"
                                                                            data-doc-id="{{ $doc->id }}"
                                                                            data-file-id="{{ $file['id'] }}"
                                                                            data-file-name="{{ $file['name'] }}"
                                                                            title="Download report for this file">
                                                                            <i class="bi bi-bar-chart"></i>
                                                                        </button>
                                                                    @endif
                                                                    {{-- <a href="{{ route('document-review.downloadWatermarkedFile', $file['id'], false) }}" target="_blank"
                                                                        class="text-sky-600 hover:text-sky-800 whitespace-nowrap ms-2"
                                                                        title="Download (watermarked)">
                                                                        <i class="bi bi-download"></i>
                                                                    </a> --}}
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
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-doc-status="{{ $status }}"
                                                        data-is-admin="{{ $isAdmin ? '1' : '0' }}"
                                                        data-file-id="{{ $files[0]['id'] ?? '' }}"
                                                        data-file-name="{{ $files[0]['name'] ?? '' }}"
                                                        data-file-path="{{ $files[0]['file_path'] ?? '' }}">
                                                        <i class="bi bi-eye"></i>
                                                        @if ($isReplacedFile)
                                                            <span class="ml-1 inline-block rounded-full bg-red-200 px-1 py-0.5 text-[10px] font-semibold text-red-800">
                                                                Replaced
                                                            </span>
                                                        @endif
                                                    </button>
                                                @endif

                                            </div>

                                            {{-- ==================  (ALL OTHER ACTIONS) ================== --}}
                                            @php
                                                // Use canEditDocument to determine edit/upload permission
                                                $canEdit = auth()->check() && auth()->user()->canEditDocument($doc);
                                                $roleNames = auth()->check()
                                                    ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower(trim((string) $r)))->toArray()
                                                    : [];
                                                $isSupervisorRole = in_array('supervisor', $roleNames, true);
                                                $canShowEditOnline = !$isSupervisorRole;
                                                // Only admin or users allowed by canEditDocument can edit
                                                $showEdit = ($isAdmin || $canEdit) && $status !== 'need review';

                                                // Approval actions are handled in dedicated approval queue page.
                                                $showApproveReject = false;

                                                $showMenu = $showEdit || $showDownloadReport;
                                            @endphp
                                            @if ($showMenu)
                                                <div class="relative inline-block text-left">
                                                    <button type="button"
                                                        onclick="document.getElementById('actionMenu-{{ $doc->id }}').classList.toggle('hidden')"
                                                        class="modern-action-trigger w-8 h-8 flex justify-center items-center rounded-full hover:bg-gray-200">
                                                        <i class="bi bi-three-dots-vertical text-lg"></i>
                                                    </button>

                                                    <div id="actionMenu-{{ $doc->id }}"
                                                        class="hidden absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-[9999] py-1 text-sm">
                                                        {{-- Edit actions: separate "Edit Online" (OnlyOffice) and "Edit" (upload/revise) --}}
                                                        @if ($showEdit)
                                                            @if ($currentFiles->isNotEmpty())
                                                                @php
                                                                    // Prefer the most recent file for direct online edit when single
                                                                    $latestFile = $currentFiles->first();
                                                                @endphp

                                                                {{-- Edit Online: if single file, link directly to editor; if multiple, open select-file modal --}}
                                                                @if ($canShowEditOnline && $currentFiles->count() === 1 && $latestFile)
                                                                    <a href="{{ route('editor.show', $latestFile->id) }}"
                                                                        target="_blank"
                                                                        class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-sky-600"
                                                                        title="Edit File">
                                                                        <i class="bi bi-pencil mr-2"></i> Edit Online
                                                                    </a>
                                                                @elseif ($canShowEditOnline)
                                                                    <button type="button"
                                                                        class="open-select-file-modal flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-sky-600"
                                                                        data-mapping-id="{{ $doc->id }}"
                                                                        title="Select file to edit online">
                                                                        <i class="bi bi-pencil mr-2"></i> Edit Online
                                                                    </button>
                                                                @endif

                                                                {{-- Keep existing Edit (upload/replace) button available --}}
                                                                <button type="button"
                                                                    class="open-revise-modal flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-yellow-600"
                                                                    data-doc-id="{{ $doc->id }}"
                                                                    title="Upload File">
                                                                    <i class="bi bi-box-arrow-up-right mr-2"></i> Upload
                                                                </button>
                                                            @else
                                                                {{-- No files yet: keep Edit (upload) button only --}}
                                                                <button type="button"
                                                                    class="open-revise-modal flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-yellow-600"
                                                                    data-doc-id="{{ $doc->id }}"
                                                                    title="Upload fILE">
                                                                    <i class="bi bi-box-arrow-up-right mr-2"></i> Upload
                                                                </button>
                                                            @endif
                                                        @endif

                                                        {{-- Download Report (single file) --}}
                                                        @if ($showDownloadReport && $currentFiles->count() === 1)
                                                            <button type="button"
                                                                class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-blue-600 file-download-report-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#downloadReportModal"
                                                                data-doc-id="{{ $doc->id }}"
                                                                data-file-id="{{ $currentFiles->first()?->id ?? '' }}"
                                                                data-file-name="{{ $currentFiles->first()?->file_name ?? basename($currentFiles->first()?->file_path ?? '') }}"
                                                                title="Download Report for Each File">
                                                                <i class="bi bi-bar-chart mr-2"></i> Download Report
                                                            </button>
                                                        @endif

                                                        {{-- Download as PDF (with watermark) - only when approved --}}
                                                        @if ($status === 'approved')
                                                            <button type="button"
                                                                class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-purple-600 download-pdf-btn"
                                                                data-doc-id="{{ $doc->id }}"
                                                                data-doc-name="{{ $doc->document_number ?? 'document' }}"
                                                                title="Download document as PDF (with watermark)">
                                                                <i class="bi bi-file-pdf mr-2"></i> Download as PDF
                                                            </button>
                                                        @endif

                                                        {{-- Approve --}}
                                                        @if ($showApproveReject)
                                                            <button type="button"
                                                                class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-green-600 btn-approve"
                                                                data-id="{{ $doc->id }}">
                                                                <i class="bi bi-check2-circle mr-2"></i> Approve
                                                            </button>

                                                            {{-- Reject --}}
                                                            <button type="button"
                                                                class="flex items-center w-full px-3 py-2 text-left hover:bg-gray-50 text-red-600"
                                                                data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                                data-id="{{ $doc->id }}">
                                                                <i class="bi bi-x-circle mr-2"></i> Reject
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
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
                            <a id="viewFullBtn" href="#" target="_blank"
                                class="btn btn-outline-info btn-sm d-none">
                                <i class="bi bi-arrows-fullscreen"></i> View Full
                            </a>
                            <a id="printFileBtn" href="#" class="btn btn-outline-secondary btn-sm d-none">
                                <i class="bi bi-printer"></i> Print
                            </a>
                            <a id="downloadFileBtn" href="#" download
                                class="btn btn-outline-primary btn-sm d-none" style="display:none !important;" aria-hidden="true" tabindex="-1">
                                <i class="bi bi-download"></i> Download PDF
                            </a>

                            {{-- <a id="downloadWatermarkedBtn" href="#" target="_blank" class="btn btn-outline-primary btn-sm d-none" title="Download (watermarked)">
                                <i class="bi bi-download"></i> Watermarked
                            </a> --}}

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
    </div>
    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')
    @include('contents.document-review.partials.modal-reject')
    @include('contents.document-review.partials.modal-download-report')
    <!-- Modal: Select File to Edit -->
    <div id="selectFileToEditModal" class="hidden fixed inset-0 flex items-center justify-center z-[9999]">
        <div class="bg-white rounded-4 shadow-lg w-full max-w-md relative p-4 select-file-dialog">
            <div class="flex justify-between items-center mb-3">
                <h5 class="fw-semibold">Pilih File untuk Diedit</h5>
                <button type="button" id="selectFileCloseBtn" class="btn-close">&times;</button>
            </div>

            <div id="selectFileList" class="max-h-60 overflow-y-auto mb-3">
                <p class="text-sm text-gray-500">Loading files...</p>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" id="selectFileCancel"
                    class="px-4 py-1.5 border border-gray-300 rounded text-gray-700">Batal</button>
                <button id="selectFileConfirm" type="button" class="px-4 py-1.5 bg-sky-600 text-white rounded"
                    disabled>Edit</button>
            </div>
        </div>
    </div>
    <style>
        :root {
            --btn-ink: #0f172a;
            --btn-border: #dbe4ef;
            --btn-primary: #2563eb;
            --btn-primary-dark: #1d4ed8;
        }

        .modern-pill-btn {
            height: 42px !important;
            border-radius: 999px !important;
            padding: 0 16px !important;
            background: linear-gradient(180deg, #ffffff, #f8fbff) !important;
            border: 1px solid var(--btn-border) !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08) !important;
        }

        .modern-pill-btn:hover {
            border-color: #93c5fd !important;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2) !important;
            transform: translateY(-1px);
        }

        .modern-square-btn {
            width: 42px;
            height: 42px;
            padding: 0 !important;
            border-radius: 14px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #ffffff, #f8fbff) !important;
            border: 1px solid var(--btn-border) !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08) !important;
        }

        .modern-square-btn:hover {
            border-color: #93c5fd !important;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2) !important;
            transform: translateY(-1px);
        }

        .modern-action-trigger {
            width: 36px !important;
            height: 36px !important;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            background: linear-gradient(180deg, #ffffff, #f8fafc) !important;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
            transition: all 0.18s ease;
        }

        .modern-action-trigger:hover {
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        .toggle-files-dropdown {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 6px 12px rgba(15, 23, 42, 0.08);
            transition: all 0.18s ease;
        }

        .toggle-files-dropdown:hover {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        [id^="actionMenu-"] {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12) !important;
            overflow: hidden;
        }

        [id^="actionMenu-"] a,
        [id^="actionMenu-"] button {
            font-weight: 600;
            transition: background-color 0.16s ease;
        }

        [id^="actionMenu-"] a:hover,
        [id^="actionMenu-"] button:hover {
            background: #f8fafc !important;
        }

        #filterModal .modal-footer #clearFilterBtn {
            border: 1px solid var(--btn-border);
            border-radius: 10px;
            background: #fff;
            color: #64748b !important;
        }

        #filterModal .modal-footer button[type="submit"] {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--btn-primary), var(--btn-primary-dark)) !important;
            color: #fff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.28);
        }

        #selectFileCancel,
        #selectFileConfirm {
            border-radius: 10px !important;
            font-weight: 600;
        }

        #selectFileConfirm {
            border: none;
            background: linear-gradient(135deg, var(--btn-primary), var(--btn-primary-dark)) !important;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.28);
        }

        #selectFileCancel:hover,
        #selectFileConfirm:hover {
            transform: translateY(-1px);
        }

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

        /* Select File Modal: ensure light card style and lighter backdrop */
        #selectFileToEditModal {
            background: rgba(10, 12, 20, 0.20);
        }

        #selectFileToEditModal .select-file-dialog {
            background: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(16, 24, 40, 0.12);
        }

        #selectFileToEditModal .select-file-dialog h5 {
            color: #0f172a;
        }

        #selectFileToEditModal .select-file-dialog .btn-close {
            background: #f8fafc;
            border: 1px solid rgba(2, 6, 23, 0.04);
            color: #475569;
            border-radius: 999px;
            padding: 6px 8px;
        }

        #selectFileToEditModal .select-file-dialog .px-4 {
            border-radius: 8px;
        }

        #selectFileToEditModal .select-file-dialog .bg-sky-600 {
            background: linear-gradient(180deg, #1e90ff, #0f62ff);
        }
    </style>
    @push('scripts')
        <script>
            const currentPlant = "{{ $plant }}";
            document.addEventListener('DOMContentLoaded', function() {
                // Init tooltips for notes column
                const noteTooltips = Array.from(document.querySelectorAll('.note-tooltip[data-bs-toggle="tooltip"]'));
                noteTooltips.forEach(el => new bootstrap.Tooltip(el, {
                    boundary: 'window'
                }));

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
                const withCacheBuster = (url) => {
                    if (!url) return url;
                    const sep = url.includes('?') ? '&' : '?';
                    return `${url}${sep}_=${Date.now()}`;
                };

                let currentDocId = null; // Simpan docId yang sedang dibuka
                let currentFileId = null; // Simpan fileId yang sedang dibuka
                let currentFileType = null; // Simpan tipe file (pdf, excel, word, etc)
                let currentPreviewObjectUrl = null; // Blob URL for converted preview

                // Helper: Deteksi tipe file dari URL/path
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

                // Helper: Tambahkan parameter viewer untuk menyembunyikan toolbar bawaan PDF di iframe (bila didukung)
                function withPdfViewerParams(url) {
                    if (!url) return url;
                    // Hindari duplikasi jika sudah punya fragment
                    if (url.includes('#')) return url;
                    // Hide toolbar/print/download buttons in embedded PDF viewer
                    return `${url}#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0&zoom=page-width`;
                }

                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const docId = btn.dataset.docId;
                        currentDocId = docId; // Simpan untuk tracking

                        const clickedFileId = btn.dataset.fileId;
                        const clickedFilePath = btn.dataset.filePath;
                        let url = btn.dataset.file || '';

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

                        // Deteksi tipe file asli
                        const sourceFileType = getFileType(url);
                        currentFileType = sourceFileType;
                        console.log('File type detected:', sourceFileType);

                        // Untuk Excel: coba preview via OnlyOffice (readonly).
                        // Jika akses ditolak (beda department), fallback ke preview PDF conversion.
                        if (sourceFileType === 'excel' && currentFileId) {
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

                                // Jika 403 (beda department), lanjutkan ke fallback convert PDF di bawah.
                                if (ooRes.status !== 403) {
                                    // Untuk error lain, tetap fallback ke convert PDF agar user tetap bisa preview.
                                    console.warn('OnlyOffice preview unavailable, fallback to PDF conversion.');
                                }
                            } catch (err) {
                                // Jika endpoint OnlyOffice gagal, fallback ke convert PDF di bawah.
                                console.warn('OnlyOffice request failed, fallback to PDF conversion.', err);
                            }
                        }

                        // Toggle tombol Print & Download hanya untuk PDF dan ketika dokumen berstatus Approved
                        const printBtnToggle = document.getElementById('printFileBtn');
                        const downloadBtnToggle = document.getElementById('downloadFileBtn');
                        const downloadWatermarkedBtn = document.getElementById(
                            'downloadWatermarkedBtn');
                        const viewFullBtnToggle = document.getElementById('viewFullBtn');
                        const docStatus = (btn.dataset.docStatus || '').toString().toLowerCase();
                        const isAdminFlag = (btn.dataset.isAdmin || '') === '1';
                        let previewUrl = url;

                        // For non-PDF sources, request server-side conversion and preview inline as PDF.
                        if (sourceFileType !== 'pdf' && currentDocId) {
                            const convertUrl = new URL(`/document-review/${currentDocId}/download-as-pdf`,
                                window.location.origin);
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
                                        // Keep default message if body is not JSON.
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
                                        text: convertError.message ||
                                            'Unable to convert file to PDF preview.'
                                    });
                                } else {
                                    alert(convertError.message ||
                                        'Unable to convert file to PDF preview.');
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

                        if (canPrint) {
                            printBtnToggle.classList.remove('d-none');
                        } else {
                            printBtnToggle.classList.add('d-none');
                        }

                        if (canDownload) {
                            downloadBtnToggle.classList.remove('d-none');
                            if (downloadWatermarkedBtn) {
                                downloadWatermarkedBtn.classList.remove('d-none');
                                if (currentFileId) downloadWatermarkedBtn.href =
                                    `/document-review/file/${currentFileId}/download-watermarked`;
                            }
                        } else {
                            downloadBtnToggle.classList.add('d-none');
                            if (downloadWatermarkedBtn) {
                                downloadWatermarkedBtn.classList.add('d-none');
                                downloadWatermarkedBtn.href = '#';
                            }
                        }

                        // View Full: remain visible for PDF files (regardless of approval)
                        if (isPdf) {
                            viewFullBtnToggle.classList.remove('d-none');
                        } else {
                            viewFullBtnToggle.classList.add('d-none');
                        }

                        // Untuk iframe PDF, coba sembunyikan toolbar default dengan fragment params
                        const finalPreviewUrl = currentFileType === 'pdf'
                            ? withPdfViewerParams(previewUrl)
                            : previewUrl;
                        previewFrame.dataset.url = finalPreviewUrl;
                        // Gunakan URL yang sudah disanitasi agar toolbar tetap tersembunyi saat "View Full"
                        viewFullBtn.href = finalPreviewUrl;
                        previewModal.show();
                    });
                });

                // Untuk PDF: tidak log saat load iframe, hanya log ketika user klik tombol download/print

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
                        currentDocId = null; // Reset docId
                        currentFileId = null; // Reset file id
                        currentFileType = null; // Reset file type

                        // Sembunyikan tombol Print, Download, dan View Full saat modal ditutup
                        const printBtnToggle = document.getElementById('printFileBtn');
                        const downloadBtnToggle = document.getElementById('downloadFileBtn');
                        const viewFullBtnToggle = document.getElementById('viewFullBtn');
                        printBtnToggle.classList.add('d-none');
                        downloadBtnToggle.classList.add('d-none');
                        viewFullBtnToggle.classList.add('d-none');
                    });

                // === PRINT BUTTON (tidak log download) ===
                const printFileBtn = document.getElementById('printFileBtn');

                printFileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const frame = document.getElementById('filePreviewFrame');
                    const fileUrl = frame.src;

                    if (!fileUrl) {
                        alert('No file loaded.');
                        return;
                    }

                    // Langsung print tanpa log
                    frame.focus();
                    try {
                        frame.contentWindow.print();
                    } catch (err) {
                        console.error('Unable to auto-print:', err);
                        window.print();
                    }
                });

                // === DOWNLOAD BUTTON (log download untuk PDF) ===
                const downloadFileBtn = document.getElementById('downloadFileBtn');

                downloadFileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const frame = document.getElementById('filePreviewFrame');
                    const fileUrl = frame.src;

                    if (!fileUrl) {
                        alert('No file loaded.');
                        return;
                    }

                    // Log download untuk PDF
                    if (currentDocId && currentFileType === 'pdf') {
                        fetch(`/document-review/${currentDocId}/log-download`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'download_pdf',
                                file_type: 'pdf',
                                document_file_id: currentFileId
                            })
                        }).then(() => {
                            console.log('PDF download logged');
                        }).catch(err => console.error('Failed to log download:', err));
                    }

                    // Trigger download
                    const link = document.createElement('a');
                    link.href = fileUrl;
                    link.download = currentFileType === 'pdf' ? 'document.pdf' : 'file';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });

                // === VIEW FULL BUTTON (tidak dihitung download) ===
                viewFullBtn.addEventListener('click', function() {
                    // Tidak melog; hanya buka di tab baru
                });

                // Tidak lagi menghitung ctrl+P atau klik kanan sebagai download; hanya tombol print/save


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
<div class="space-y-3">
${data.files.map(file => `
                                                                                                    <div class="border rounded p-2 bg-gray-50">
                                                                                                        <div class="flex items-center justify-between">
                                                                                                            <span class="text-sm">📄 ${file.original_name}</span>

                                                                                                            <div class="flex gap-2">
                                                                                                                {{-- <a href="/storage/${file.file_path}"
                                                                                                    target="_blank"
                                                                                                    class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                                                                                    View
                                                                                                </a> --}}

                                                                                                                <button type="button"
                                                                                                                    class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded replace-btn"
                                                                                                                    data-file-id="${file.id}">
                                                                                                                    Replace
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        <div class="replace-container mt-2 hidden" id="replace-box-${file.id}"></div>
                                                                                                    </div>
                                                                                                `).join('')}
</div>`;

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
                 * DOWNLOAD AS PDF BUTTON
                 */
                document.querySelectorAll('.download-pdf-btn').forEach(btn => {
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const docId = this.getAttribute('data-doc-id');
                        const docName = this.getAttribute('data-doc-name') || 'document';

                        // If document has multiple active files, ask user which one to convert/download.
                        let selectedFileId = null;
                        try {
                            const filesRes = await fetch(`/document-review/${docId}/files`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });

                            if (filesRes.ok) {
                                const filesJson = await filesRes.json();
                                const files = Array.isArray(filesJson?.files) ? filesJson.files : [];

                                if (files.length === 1) {
                                    selectedFileId = files[0]?.id || null;
                                } else if (files.length > 1) {
                                    if (typeof Swal !== 'undefined') {
                                        const escapeHtml = (value) => String(value ?? '')
                                            .replace(/&/g, '&amp;')
                                            .replace(/</g, '&lt;')
                                            .replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;')
                                            .replace(/'/g, '&#039;');

                                        const radioHtml = files.map((f, idx) => {
                                            const id = String(f?.id ?? '');
                                            const label = f?.original_name || f?.file_path || `File ${idx + 1}`;
                                            const checked = idx === 0 ? 'checked' : '';

                                            return `
                                                <label style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;margin-bottom:8px;">
                                                    <input type="radio" name="download_pdf_file_choice" value="${escapeHtml(id)}" ${checked} style="margin:0;">
                                                    <span style="font-size:14px;line-height:1.3;word-break:break-word;">${escapeHtml(label)}</span>
                                                </label>
                                            `;
                                        }).join('');

                                        const pick = await Swal.fire({
                                            title: 'Pilih File',
                                            html: `
                                                <div style="text-align:left;font-size:14px;color:#4b5563;margin-bottom:12px;">
                                                    Dokumen ini punya beberapa file aktif. Pilih satu untuk Download as PDF.
                                                </div>
                                                <div style="max-height:260px;overflow:auto;padding-right:4px;">
                                                    ${radioHtml}
                                                </div>
                                            `,
                                            showCancelButton: true,
                                            confirmButtonText: 'Lanjutkan',
                                            cancelButtonText: 'Batal',
                                            width: 640,
                                            focusConfirm: false,
                                            preConfirm: () => {
                                                const selected = document.querySelector('input[name="download_pdf_file_choice"]:checked');
                                                if (!selected) {
                                                    Swal.showValidationMessage('Pilih salah satu file terlebih dahulu.');
                                                    return false;
                                                }

                                                return selected.value;
                                            }
                                        });

                                        if (!pick.isConfirmed) {
                                            return;
                                        }

                                        selectedFileId = pick.value || null;
                                    } else {
                                        // Fallback without Swal: default to latest item.
                                        selectedFileId = files[files.length - 1]?.id || null;
                                    }
                                }
                            }
                        } catch (err) {
                            console.error('Failed to load active files for download selection:', err);
                        }

                        const downloadUrl = new URL(`/document-review/${docId}/download-as-pdf`, window.location.origin);
                        if (selectedFileId) {
                            downloadUrl.searchParams.set('file_id', String(selectedFileId));
                        }

                        // Show modal loading so user still sees progress when action dropdown auto-closes.
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Converting document...',
                                text: 'Please wait while PDF is being generated.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        }

                        // Call the download endpoint
                        fetch(downloadUrl.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/pdf, application/json'
                                }
                            })
                            .then(async (response) => {
                                // Check if response is JSON (error) or PDF (success)
                                const contentType = response.headers.get('content-type');

                                if (!response.ok) {
                                    // Handle error response
                                    if (contentType && contentType.includes(
                                        'application/json')) {
                                        const errorData = await response.json();
                                        throw new Error(errorData.message || errorData.error ||
                                            `HTTP ${response.status}`);
                                    } else {
                                        throw new Error(
                                            `HTTP ${response.status}: Failed to convert document`
                                            );
                                    }
                                }

                                // Check if response is valid PDF
                                if (!contentType || !contentType.includes('application/pdf')) {
                                    const text = await response.text();
                                    console.error('Invalid content type:', contentType, 'Body:',
                                        text.substring(0, 200));
                                    throw new Error(
                                        'Invalid response type: expected PDF, got ' +
                                        contentType);
                                }

                                // Get filename from Content-Disposition header
                                const contentDisposition = response.headers.get(
                                    'content-disposition');
                                let filename =
                                    `${docName}_${new Date().toISOString().slice(0,10)}.pdf`;
                                if (contentDisposition) {
                                    const matches = /filename="([^"]+)"/.exec(
                                        contentDisposition);
                                    if (matches) filename = matches[1];
                                }

                                return response.blob().then(blob => {
                                    // Verify blob is not empty
                                    if (blob.size === 0) {
                                        throw new Error('Received empty PDF file');
                                    }
                                    return {
                                        blob,
                                        filename
                                    };
                                });
                            })
                            .then(({
                                blob,
                                filename
                            }) => {
                                // Verify it's a valid PDF by checking magic bytes
                                const reader = new FileReader();
                                reader.onload = () => {
                                    const arr = new Uint8Array(reader.result).subarray(0, 4);
                                    const header = String.fromCharCode.apply(null, arr);

                                    if (header !== '%PDF') {
                                        console.error('Invalid PDF header:', header);
                                        alert(
                                            'Error: Downloaded file is not a valid PDF. Please check the server logs.');
                                        return;
                                    }

                                    // Create a download link
                                    const url = window.URL.createObjectURL(blob);
                                    const link = document.createElement('a');
                                    link.href = url;
                                    link.download = filename;
                                    document.body.appendChild(link);
                                    link.click();
                                    window.URL.revokeObjectURL(url);
                                    link.remove();

                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Done',
                                            text: 'PDF is ready and has been downloaded.',
                                            timer: 1400,
                                            showConfirmButton: false
                                        });
                                    }

                                    // Show success message
                                    console.log(`PDF downloaded successfully: ${filename}`);
                                };
                                reader.readAsArrayBuffer(blob.slice(0, 4));
                            })
                            .catch((error) => {
                                console.error('PDF download failed:', error);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed',
                                        text: `Failed to download PDF. ${error.message}`
                                    });
                                } else {
                                    alert(`Failed to download PDF:\n\n${error.message}`);
                                }
                            })
                            .finally(() => {
                                if (typeof Swal !== 'undefined' && Swal.isLoading()) {
                                    Swal.close();
                                }
                            });
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
                    const box = document.getElementById(`replace-box-${fileId}`);

                    box.classList.remove("hidden"); // <— tambahan penting agar tidak berantakan
                    // jika sebelumnya sudah ada input lalu di-replace, jangan hapus DOM-nya seluruhnya
                    box.classList.remove("hidden");

                    // hapus hanya input sebelumnya di box tersebut
                    const existingInput = box.querySelector('.replace-input-wrapper');
                    if (existingInput) existingInput.remove();

                    // tambahkan input baru
                    box.insertAdjacentHTML('beforeend', `
                        <div class="replace-input-wrapper">
                            ${renderNewFileInput(fileId, true)}
                        </div>
                    `);

                });
                filesContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-file-btn')) {
                        e.target.closest('.border').remove();
                    }
                });

                /**
                 * TEMPLATE: Input File Baru
                 */
                function renderNewFileInput(oldFileId = null, inline = false) {
                    return `
        <div class="border rounded p-3 bg-white shadow-sm relative mt-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">
                New File ${oldFileId ? "(Replacing existing file)" : ""}
            </label>

            <input type="file"
                name="revision_files[]"
                required
                class="block w-full border border-gray-300 rounded p-1 text-sm">

            <input type="hidden"
                name="revision_file_ids[]"
                value="${oldFileId ?? ""}">

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

                // === SELECT FILE TO EDIT MODAL LOGIC ===
                const selectFileModal = document.getElementById('selectFileToEditModal');
                const selectFileList = document.getElementById('selectFileList');
                const selectFileConfirm = document.getElementById('selectFileConfirm');
                let selectedFileId = null;

                function closeSelectFileModal() {
                    selectFileModal.classList.add('hidden');
                    selectFileList.innerHTML = '';
                    selectedFileId = null;
                    selectFileConfirm.disabled = true;
                }

                // expose to global so inline onclick calls (if any) work
                window.closeSelectFileModal = closeSelectFileModal;

                // wire modal close buttons by ID to avoid brittle selectors
                const selectFileCloseBtn = document.getElementById('selectFileCloseBtn');
                const selectFileCancelBtn = document.getElementById('selectFileCancel');
                if (selectFileCloseBtn) selectFileCloseBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeSelectFileModal();
                });
                if (selectFileCancelBtn) selectFileCancelBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeSelectFileModal();
                });

                document.querySelectorAll('.open-select-file-modal').forEach(btn => {
                    btn.addEventListener('click', async function(e) {
                        e.stopPropagation();
                        const mappingId = this.getAttribute('data-mapping-id');
                        if (!mappingId) return;

                        selectFileModal.classList.remove('hidden');
                        selectFileList.innerHTML =
                            '<p class="text-sm text-gray-500">Loading files...</p>';
                        selectFileConfirm.disabled = true;

                        try {
                            const res = await fetch(`/document-review/${mappingId}/files`);
                            const json = await res.json();
                            if (!json.success) {
                                selectFileList.innerHTML =
                                    '<p class="text-sm text-red-500">Failed to load files.</p>';
                                return;
                            }

                            const files = json.files || [];
                            if (files.length === 0) {
                                selectFileList.innerHTML =
                                    '<p class="text-sm text-gray-500">No files available.</p>';
                                return;
                            }

                            // store options for fallback
                            window._selectFileOptions = files;

                            selectFileList.innerHTML = files.map(f => `
                                <label class="flex items-center gap-2 px-2 py-1 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="radio" name="selectFileEdit" value="${f.id ?? ''}" class="select-file-radio">
                                    <span class="text-sm flex-1">${f.original_name || f.file_path}</span>
                                </label>
                            `).join('');

                            document.querySelectorAll('.select-file-radio').forEach(r => r
                                .addEventListener('change', function() {
                                    selectedFileId = this.value || null;
                                    selectFileConfirm.disabled = !selectedFileId;
                                }));

                            // default select first (and ensure selectedFileId set)
                            const first = document.querySelector('.select-file-radio');
                            if (first) {
                                first.checked = true;
                                first.dispatchEvent(new Event('change'));
                            }

                            selectFileConfirm.onclick = function() {
                                // fallback to first file id if selection failed
                                let targetId = selectedFileId;
                                if (!targetId && Array.isArray(window._selectFileOptions) &&
                                    window._selectFileOptions.length) {
                                    targetId = window._selectFileOptions[0].id;
                                }
                                if (!targetId) return;
                                closeSelectFileModal();
                                // redirect to editor show (encode to be safe)
                                window.location.href = '/editor/' + encodeURIComponent(
                                targetId);
                            };

                        } catch (err) {
                            selectFileList.innerHTML =
                                '<p class="text-sm text-red-500">Failed to load files.</p>';
                        }
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

                // === DOWNLOAD REPORT MODAL ===
                const downloadReportModal = document.getElementById('downloadReportModal');

                downloadReportModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const docId = button?.getAttribute('data-doc-id') || button?.getAttribute('data-id');
                    const fileId = button?.getAttribute('data-file-id');
                    const fileName = button?.getAttribute('data-file-name') || '-';

                    document.getElementById('reportFileName').textContent = fileName;

                    // Show loading
                    document.getElementById('downloadReportLoading').classList.remove('hidden');
                    document.getElementById('downloadReportContent').classList.add('hidden');

                    // Fetch download history
                    const reportUrl = fileId ?
                        `/document-review/${docId}/download-report?file_id=${fileId}` :
                        `/document-review/${docId}/download-report`;

                    fetch(reportUrl)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('downloadReportLoading').classList.add('hidden');
                            document.getElementById('downloadReportContent').classList.remove('hidden');

                            if (!data.success) {
                                document.getElementById('downloadReportContent').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    ${data.message || 'Failed to load download report.'}
                                                </div>
                                            `;
                                return;
                            }

                            // Set document info
                            document.getElementById('reportDocNumber').textContent = data.document_number ||
                                '-';
                            document.getElementById('reportFileName').textContent = data.file?.name ||
                                fileName || '-';
                            document.getElementById('reportTotalDownloads').textContent =
                                data.total_downloads ?? (Array.isArray(data.downloads) ?
                                    data.downloads.reduce((sum, d) => sum + d.download_count, 0) :
                                    0);

                            const tbody = document.getElementById('downloadReportTableBody');
                            const emptyState = document.getElementById('downloadReportEmpty');

                            if (data.downloads.length === 0) {
                                tbody.innerHTML = '';
                                emptyState.classList.remove('hidden');
                            } else {
                                emptyState.classList.add('hidden');
                                tbody.innerHTML = data.downloads.map((item, index) => `
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-900">
                                                            <div class="flex items-center">
                                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                                                    <span class="text-blue-600 font-semibold text-xs">
                                                                        ${item.user_name.charAt(0).toUpperCase()}
                                                                    </span>
                                                                </div>
                                                                ${item.user_name}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-center">
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                                ${item.download_count}x
                                                            </span>
                                                        </td>
                                                    </tr>
                                                `).join('');
                            }
                        })
                        .catch(err => {
                            console.error('Failed to load download report:', err);
                            document.getElementById('downloadReportLoading').classList.add('hidden');
                            document.getElementById('downloadReportContent').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    Failed to load download report. Please try again.
                                                </div>
                                            `;
                        });
                });
                const filterStatusBtn = document.getElementById('filterStatusBtn');
                const filterStatusDropdown = document.getElementById('filterStatusDropdown');
                const statusSearchInput = document.getElementById('statusSearchInput');
                if (filterStatusBtn && filterStatusDropdown) {
                    filterStatusBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const isVisible = !filterStatusDropdown.classList.contains('hidden');
                        document.querySelectorAll('#filterStatusDropdown').forEach(d => d.classList.add(
                            'hidden'));
                        if (isVisible) {
                            filterStatusDropdown.classList.add('hidden');
                            return;
                        }
                        // Position dropdown
                        const rect = filterStatusBtn.getBoundingClientRect();
                        filterStatusDropdown.style.position = 'fixed';
                        filterStatusDropdown.style.top = `${rect.bottom + 6}px`;
                        filterStatusDropdown.style.left = `${rect.left - 220}px`;
                        filterStatusDropdown.classList.remove('hidden');
                        filterStatusDropdown.classList.add('dropdown-fixed');
                    });
                    // Close dropdown on outside click
                    document.addEventListener('click', function(e) {
                        if (!filterStatusDropdown.contains(e.target) && !filterStatusBtn.contains(e.target)) {
                            filterStatusDropdown.classList.add('hidden');
                        }
                    });
                }
                // Search status in dropdown
                if (statusSearchInput) {
                    statusSearchInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase();
                        document.querySelectorAll('#statusList li').forEach(li => {
                            const label = li.querySelector('span.flex-1');
                            if (label && label.textContent.toLowerCase().includes(filter)) {
                                li.style.display = '';
                            } else {
                                li.style.display = 'none';
                            }
                        });
                    });
                }
                // Checkbox logic (multi-select, all) + live submit
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('status-checkbox')) {
                        const allCheckbox = document.getElementById('statusAllCheckbox');
                        const statusCheckboxes = Array.from(document.querySelectorAll('.status-checkbox'))
                            .filter(cb => cb !== allCheckbox);
                        if (e.target === allCheckbox) {
                            // Jika 'all' dicentang, centang semua, jika uncheck, uncheck semua
                            statusCheckboxes.forEach(cb => cb.checked = allCheckbox.checked);
                        } else {
                            // Jika status lain dicentang, uncheck 'all'
                            if (allCheckbox) allCheckbox.checked = false;
                        }
                        setTimeout(function() {
                            document.getElementById('searchForm').submit();
                        }, 10);
                    }
                });
            });
        </script>
    @endpush
@endsection
