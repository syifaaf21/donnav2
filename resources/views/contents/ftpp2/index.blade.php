@extends('layouts.app')
@section('title', 'FTPP')
@section('subtitle', 'Manage and organize your FTPP documents efficiently')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">FTPP</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="mx-auto px-6 bg-white rounded-xl py-4" x-data="showModal()"
        @open-show-modal.window="openShowModal($event.detail)">

        {{-- Define default values for variables --}}
        @php
            $filterType = $filterType ?? 'created';
            $userRoles = $userRoles ?? auth()->user()->roles->pluck('name')->toArray();
        @endphp

        {{-- TAB FILTER FOR AUDITOR --}}
        @php
            $currentUserRoles = auth()->user()->roles->pluck('name')->toArray();
            $isAuditor = in_array('Auditor', $currentUserRoles);
        @endphp

        @if ($isAuditor)
            <div class="mb-6 border-b border-gray-200">
                <div class="flex gap-4">
                    <a href="{{ route('ftpp.index', array_merge(request()->except('filter_type'), ['filter_type' => 'created'])) }}"
                        class="px-4 py-3 font-medium border-b-2 transition-colors {{ $filterType === 'created' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        <i data-feather="edit" class="inline w-4 h-4 mr-2"></i>
                        My Created FTPP
                    </a>
                    <a href="{{ route('ftpp.index', array_merge(request()->except('filter_type'), ['filter_type' => 'assigned'])) }}"
                        class="px-4 py-3 font-medium border-b-2 transition-colors {{ $filterType === 'assigned' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        <i data-feather="inbox" class="inline w-4 h-4 mr-2"></i>
                        Assigned to Me
                    </a>
                </div>
            </div>
        @endif

        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- LEFT: Search + Filter -->
            <div class="flex items-center gap-2 w-full md:w-1/3">

                <!-- SEARCH -->
                <form method="GET" id="searchForm" class="flex flex-col items-end w-auto space-y-1">
                    {{-- Preserve filter_type --}}
                    <input type="hidden" name="filter_type" value="{{ $filterType }}">

                    <div class="relative w-96">
                        <!-- Input -->
                        <input type="text" name="search" id="searchInput"
                            class="peer w-full rounded-xl border border-gray-300 bg-white pl-4 pr-20 py-2.5
                            text-sm text-gray-700 shadow-sm transition-all duration-200
                            focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                            placeholder="Type to search..." value="{{ request('search') }}">

                        <!-- Floating Label -->
                        <label for="searchInput"
                            class="absolute left-4 px-1 bg-white text-gray-400 rounded transition-all duration-150
                                pointer-events-none
                                {{ request('search')
                                    ? '-top-3 text-xs text-sky-600'
                                    : 'top-2.5 peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>

                        <!-- Clear Button -->
                        @if (request('search'))
                            <a href="{{ route('ftpp.index', ['filter_type' => $filterType]) }}"
                                class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5
                                    rounded-lg text-gray-400
                                    hover:text-red-600 transition">
                                <i data-feather="x" class="w-5 h-5"></i>
                            </a>
                        @endif

                        <!-- Search Button -->
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5
                                rounded-lg text-gray-400 hover:text-blue-700 transition">
                            <i data-feather="search" class="w-5 h-5"></i>
                        </button>
                    </div>
                </form>

                <!-- FILTER BUTTON -->
                <div x-data="statusFilter()" x-init="init()" class="relative">
                    <button @click="toggle($event)"
                        class="bg-white border border-gray-200 rounded-xl shadow p-2.5 hover:bg-gray-100 transition">
                        <i data-feather="filter" class="w-5 h-5"></i>
                    </button>

                    <!-- Teleported dropdown -->
                    <template x-teleport="body">
                        <div x-show="open" x-transition.opacity.duration.150ms @click.outside="open=false"
                            class="absolute bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] p-2 mt-1"
                            :style="`top:${dropdown.y}px; left:${dropdown.x}px; width:300px;`">

                            <div class="p-2 border-b font-semibold text-gray-700">
                                Filter by Status
                            </div>

                            <form id="statusFilterForm" method="GET">
                                {{-- preserve other request params (handle arrays correctly) --}}
                                @foreach (request()->except(['page', 'status_id']) as $key => $val)
                                    @if (is_array($val))
                                        @foreach ($val as $v)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                    @endif
                                @endforeach

                                <div class="max-h-64 overflow-y-auto px-2 py-1 space-y-2">

                                    <!-- ALL -->
                                    <label
                                        class="flex justify-between items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" value="all" x-model="selected" @change="onAllChange">
                                            <span>All</span>
                                        </div>
                                        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full">
                                            {{ $totalCount ?? 0 }}
                                        </span>
                                    </label>

                                    <!-- STATUS LOOP -->
                                    @foreach ($statuses as $status)
                                        @php
                                            $name = strtolower($status->name);
                                            $icons = [
                                                'need assign' => 'alert-circle',
                                                'draft' => 'file-text',
                                                'need check' => 'upload-cloud',
                                                'need approval by auditor' => 'user-check',
                                                'need approval by lead auditor' => 'check-circle',
                                                'need revision' => 'alert-triangle',
                                                'close' => 'lock',
                                            ];
                                        @endphp

                                        @if (array_key_exists($name, $icons))
                                            <label
                                                class="flex justify-between items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" name="status_id[]" value="{{ $status->id }}"
                                                        x-model="selected" @change="onStatusChange">
                                                    <i data-feather="{{ $icons[$name] }}" class="w-4 h-4"></i>
                                                    <span class="capitalize">{{ $status->name }}</span>
                                                </div>

                                                <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full">
                                                    {{ $status->audit_finding_count }}
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach

                                </div>
                            </form>

                        </div>
                    </template>
                </div>
            </div>
            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-4">
                @php
                    $userRoles = auth()->user()->roles->pluck('name')->toArray();
                @endphp

                @if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles) || in_array('Auditor', $userRoles))
                    <a href="{{ route('ftpp.audit-finding.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-blue-700 font-medium
                       shadow hover:bg-blue-200 hover:shadow-md transition-all duration-150">
                        <i data-feather="plus" class="w-4 h-4"></i>
                        Add Finding
                    </a>
                @endif

                @if (in_array('Super Admin', $userRoles) ||
                        in_array('Admin', $userRoles) ||
                        in_array('Auditor', $userRoles) ||
                        in_array('Lead Auditor', $userRoles) ||
                        in_array('Dept Head', $userRoles))
                    @php
                        $badgeCount = 0;
                        $findCount = fn($needle) => collect($statuses)->first(
                            fn($s) => strtolower($s->name) === strtolower($needle),
                        )->audit_finding_count ?? 0;

                        if (in_array('Dept Head', $userRoles)) {
                            $user = auth()->user();
                            $userDepts = $user->departments ?? ($user->department ?? null);

                            $deptIds = [];
                            if (
                                $userDepts instanceof \Illuminate\Database\Eloquent\Collection ||
                                $userDepts instanceof \Illuminate\Support\Collection
                            ) {
                                $deptIds = $userDepts->pluck('id')->toArray();
                            } elseif ($userDepts) {
                                $deptIds = [$userDepts->id];
                            }

                            if (!empty($deptIds)) {
                                $badgeCount += \App\Models\AuditFinding::whereIn('department_id', $deptIds)
                                    ->whereHas('status', function ($q) {
                                        $q->whereRaw('LOWER(name) = ?', ['need check']);
                                    })
                                    ->count();
                            }
                        }

                        if (in_array('Auditor', $userRoles)) {
                            $badgeCount += $findCount('need approval by auditor');
                        }

                        if (in_array('Lead Auditor', $userRoles)) {
                            $badgeCount += $findCount('need approval by lead auditor');
                        }

                        if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles)) {
                            $badgeCount += $findCount('need approval by lead auditor');
                        }
                    @endphp

                    <div class="relative">
                        @if ($badgeCount > 0)
                            <span
                                class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-semibold leading-none text-white bg-red-600 rounded-full z-10">
                                {{ $badgeCount }}
                            </span>
                        @endif

                        <a href="{{ route('approval.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-blue-700 font-medium
                           shadow hover:bg-blue-200 hover:shadow-md transition-all duration-150">
                            <i data-feather="pen-tool" class="w-4 h-4"></i>
                            Approval
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div id="liveTableWrapper">
            <!-- Table -->
            <div class="flex-1">
                <div class="overflow-hidden px-2">
                    <div class="overflow-x-auto bg-white shadow-xl shadow-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden"
                            x-data="{
                                selectedIds: [],
                                selectAll: false,
                                toggleAll() {
                                    if (this.selectAll) {
                                        this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
                                    } else {
                                        this.selectedIds = [];
                                    }
                                    this.$dispatch('update-selected', this.selectedIds);
                                },
                                toggleRow(id) {
                                    if (this.selectedIds.includes(id)) {
                                        this.selectedIds = this.selectedIds.filter(item => item !== id);
                                    } else {
                                        this.selectedIds.push(id);
                                    }
                                    this.selectAll = this.selectedIds.length === document.querySelectorAll('.row-checkbox').length;
                                    this.$dispatch('update-selected', this.selectedIds);
                                }
                            }">
                            <thead style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                                <tr>
                                    @if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles) || in_array('Auditor', $userRoles))
                                        <th class="px-4 py-2 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                            style="color: #1e2b50; letter-spacing: 0.5px; width: 50px;">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                    @endif
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Registration No
                                    </th>
                                    <th class="px-2 py-2 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Status
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Department
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Auditor
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Auditee
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Due Date
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                        style="color: #1e2b50; letter-spacing: 0.5px;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-x divide-gray-200">

                                @forelse($findings as $finding)
                                    <tr
                                        class="hover:bg-white transition-all duration-300 hover:shadow-lg hover:scale-y-105 origin-center">
                                        @if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles) || in_array('Auditor', $userRoles))
                                            <td class="px-2 py-2 whitespace-nowrap text-center border-r border-gray-200">
                                                <input type="checkbox"
                                                    class="row-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    value="{{ $finding->id }}"
                                                    :checked="selectedIds.includes('{{ $finding->id }}')"
                                                    @change="toggleRow('{{ $finding->id }}')">
                                            </td>
                                        @endif
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-sm font-semibold text-gray-900 border-r border-gray-200">
                                            {{ $finding->registration_number ?? '-' }}</td>
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-center text-sm border-r border-gray-200">
                                            @php
                                                $statusColors = [
                                                    'need assign' => 'bg-red-100 text-red-600',
                                                    'draft' => 'bg-gray-100 text-gray-600',
                                                    'need check' => 'bg-yellow-100 text-yellow-700',
                                                    'need approval by auditor' => 'bg-yellow-100 text-yellow-700',
                                                    'need revision' => 'bg-yellow-100 text-yellow-700',
                                                    'need approval by lead auditor' => 'bg-blue-100 text-blue-600',
                                                    'close' => 'bg-green-100 text-green-600',
                                                ];
                                                $statusName = optional($finding->status)->name ?? '-';
                                                $statusClass = $statusColors[strtolower($statusName)] ?? '';
                                            @endphp
                                            <span class="{{ $statusClass }} p-1 rounded">{{ $statusName }}</span>
                                        </td>
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ optional($finding->department)->name ?? '-' }}</td>
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ optional($finding->auditor)->name ?? '-' }}</td>
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 truncate max-w-[150px]"
                                            @if ($finding->auditee && $finding->auditee->isNotEmpty())
                                                title="{{ $finding->auditee->pluck('name')->join(', ') }}"
                                            @endif>

                                            @if ($finding->auditee && $finding->auditee->isNotEmpty())
                                                {{ $finding->auditee->pluck('name')->join(', ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td
                                            class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ $finding->due_date ? \Carbon\Carbon::parse($finding->due_date)->format('Y/m/d') : '-' }}
                                        </td>
                                        <td
                                            class="flex px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            <div x-data="{ open: false, x: 0, y: 0 }" class="relative">
                                                <!-- BUTTON -->
                                                <button type="button"
                                                    @click.prevent="
                                                             open = true;
                                                             const rect = $event.target.getBoundingClientRect();
                                                             x = rect.right - 160;          // lebih presisi horizontal
                                                             y = rect.bottom + window.scrollY; // fix posisi vertical & scroll
                                                         "
                                                    class="p-1.5 hover:bg-gray-100 rounded-full transition">
                                                    <i data-feather="more-vertical" class="w-5 h-5 text-gray-600"></i>
                                                </button>

                                                <!-- DROPDOWN -->
                                                <template x-teleport="body">
                                                    <div x-show="open" @click.outside="open = false"
                                                        x-transition.opacity.duration.150ms
                                                        class="absolute bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] overflow-hidden"
                                                        :style="`top:${y}px; left:${x}px; width:170px;`">

                                                        @php $statusName = strtolower(optional($finding->status)->name ?? '') @endphp

                                                        <!-- Draft Finding: Only Show Edit and Delete -->
                                                        @if ($statusName === 'draft finding')
                                                            @if (in_array(optional(auth()->user()->roles->first())->name, ['Super Admin', 'Admin', 'Auditor']))
                                                                <!-- ITEM: Edit -->
                                                                <a href="{{ route('ftpp.audit-finding.edit', $finding->id) }}"
                                                                    @click="open = false"
                                                                    class="flex items-center gap-2 px-3 py-2.5 text-sm text-yellow-500 hover:bg-gray-50 transition">
                                                                    <i data-feather="edit" class="w-4 h-4"></i>
                                                                    Edit
                                                                </a>

                                                                <!-- ITEM: Delete -->
                                                                <form id="delete-form-{{ $finding->id }}" method="POST"
                                                                    action="{{ route('ftpp.destroy', $finding->id) }}"
                                                                    onsubmit="return false;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" @click="open = false"
                                                                        onclick="confirmSweetDelete('delete-form-{{ $finding->id }}')"
                                                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 hover:bg-gray-50 transition">
                                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        <!-- Non Draft Finding: Show other options -->
                                                        @else
                                                            @if (strtolower(optional($finding->status)->name ?? '') !== 'need assign')
                                                                <!-- ITEM: Show -->
                                                                <button type="button"
                                                                    @click="
                                                                     open = false;
                                                                     $dispatch('open-show-modal', {{ $finding->id }})"
                                                                    class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                    <i data-feather="eye" class="w-4 h-4"></i>
                                                                    Show
                                                                </button>
                                                            @endif

                                                            <!-- ITEM: Download -->
                                                            <a href="{{ route('ftpp.download', $finding->id) }}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-blue-600 hover:bg-gray-50 transition">
                                                                <i data-feather="download" class="w-4 h-4"></i>
                                                                Download
                                                            </a>

                                                            @if ($statusName === 'need assign')
                                                                @if (in_array(optional(auth()->user()->roles->first())->name, ['Super Admin', 'Admin', 'Auditor']))
                                                                    <a href="{{ route('ftpp.audit-finding.edit', $finding->id) }}"
                                                                        @click="open = false"
                                                                        class="flex items-center gap-2 px-3 py-2.5 text-sm text-yellow-500 hover:bg-gray-50 transition">
                                                                        <i data-feather="edit" class="w-4 h-4"></i>
                                                                        Edit
                                                                    </a>
                                                                @endif
                                                            @endif
                                                            @if ($statusName === 'need revision')
                                                                <a href="{{ route('ftpp.auditee-action.edit', $finding->id) }}"
                                                                    @click="open = false"
                                                                    class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                    <i data-feather="edit" class="w-4 h-4"></i>
                                                                    Revise
                                                                </a>
                                                            @elseif ($statusName === 'need check')
                                                                @if (in_array(optional(auth()->user()->roles->first())->name, [
                                                                        'Super Admin',
                                                                        'Admin',
                                                                        'Auditor',
                                                                        'Supervisor',
                                                                        'Leader',
                                                                        'User',
                                                                    ]))
                                                                    <a href="{{ route('ftpp.auditee-action.edit', $finding->id) }}"
                                                                        @click="open = false"
                                                                        class="flex items-center gap-2 px-3 py-2.5 text-sm text-yellow-500 hover:bg-gray-50 transition">
                                                                        <i data-feather="edit" class="w-4 h-4"></i>
                                                                        Edit
                                                                    </a>
                                                                @endif
                                                            @else
                                                                @if (in_array($statusName, ['need assign', 'draft']) && in_array(optional(auth()->user()->roles->first())->name, ['Super Admin', 'Admin', 'User', 'Supervisor', 'Leader']))
                                                                    <a href="{{ route('ftpp.auditee-action.create', $finding->id) }}"
                                                                        @click="open = false"
                                                                        class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                                                        Assign
                                                                    </a>
                                                                @endif
                                                            @endif
                                                            @if (in_array(optional(auth()->user()->roles->first())->name, ['Super Admin', 'Admin', 'Auditor', 'Lead Auditor']))
                                                                <!-- ITEM: Delete (SweetAlert confirm) -->
                                                                <form id="delete-form-{{ $finding->id }}" method="POST"
                                                                    action="{{ route('ftpp.destroy', $finding->id) }}"
                                                                    onsubmit="return false;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" @click="open = false"
                                                                        onclick="confirmSweetDelete('delete-form-{{ $finding->id }}')"
                                                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 hover:bg-gray-50 transition">
                                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr colspan="12">
                                        <td colspan="12">
                                            <div
                                                class="flex flex-col items-center justify-center py-8 text-gray-400 text-sm gap-2 min-h-[120px]">
                                                <i class="bi bi-inbox text-4xl"></i>
                                                <span>No Records found</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $findings->links() }}
                    </div>
                </div>
            </div>
        </div>

        @include('contents.ftpp2.show')

        <!-- Floating Bulk Delete Button -->
        <div x-data="{ selectedIds: [] }" @update-selected.window="selectedIds = $event.detail">
            <div x-show="selectedIds.length > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50"
                style="display: none;">
                <button type="button" @click="confirmBulkDelete(selectedIds)"
                    class="flex items-center gap-2 px-4 py-3 rounded-full bg-red-600 text-white font-medium
                           shadow-2xl hover:bg-red-700 hover:shadow-red-500/50 transition-all duration-300
                           transform hover:scale-105 active:scale-95">
                    <i data-feather="trash-2" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Delete <span x-text="selectedIds.length"></span> item(s)</span>
                </button>
            </div>
        </div>
    </div> {{-- end .p-6 --}}

@endsection
@push('scripts')
    <script>
        function showModal() {
            return {
                isOpen: false,
                loading: false,
                pdfUrl: null,
                currentId: null,

                openShowModal(id) {
                    this.isOpen = true;
                    this.loading = true;
                    this.currentId = id;

                    // Use preview-pdf route in iframe (no HTML fetch)
                    this.pdfUrl = `/ftpp/${id}/preview-pdf`;

                    // Wait briefly to allow iframe to start loading, then hide loading.
                    // You can also listen for iframe load event if needed.
                    const iframe = document.getElementById('previewFrame');
                    if (iframe) {
                        iframe.onload = () => {
                            this.loading = false;
                        };
                    } else {
                        // fallback: remove loading after 800ms
                        setTimeout(() => {
                            this.loading = false;
                        }, 800);
                    }
                },

                close() {
                    this.isOpen = false;
                    this.pdfUrl = null;
                    this.loading = false;
                    this.currentId = null;
                },
            };
        }
    </script>
    <script>
        function statusFilter() {
            return {
                open: false,

                // inisialisasi selected dari request; jika tidak ada request status_id, default = ['all']
                selected: @json(request()->filled('status_id') ? (array) request()->status_id : ['all']),

                dropdown: {
                    x: 0,
                    y: 0
                },

                toggle(event) {
                    const rect = event.target.getBoundingClientRect();
                    this.dropdown.x = rect.left;
                    this.dropdown.y = rect.bottom + window.scrollY + 5;
                    this.open = !this.open;

                    setTimeout(() => {
                        if (feather) feather.replace();
                    }, 10);
                },

                init() {
                    // set initial checkbox state based on "selected" array
                    const allChecked = this.selected.includes('all');
                    const inputs = document.querySelectorAll('#statusFilterForm input[name="status_id[]"]');
                    inputs.forEach(i => {
                        if (allChecked) {
                            i.checked = false;
                        } else {
                            i.checked = this.selected.includes(i.value);
                        }
                    });
                },

                // jika user pilih "All", pastikan status checkbox individual tidak terkirim
                onAllChange() {
                    // jika 'all' sekarang ada di selected -> remove name dari status checkboxes
                    const isAll = this.selected.includes('all');
                    const inputs = document.querySelectorAll('#statusFilterForm input[name="status_id[]"]');
                    inputs.forEach(i => {
                        if (isAll) {
                            i.removeAttribute('name');
                            i.checked = false;
                        } else {
                            i.setAttribute('name', 'status_id[]');
                        }
                    });
                    // jika all dipilih, pastikan selected hanya berisi 'all'
                    if (isAll) this.selected = ['all'];
                    this.submitForm();
                },

                onStatusChange() {
                    // bila ada satu atau lebih status terpilih => pastikan 'all' dikeluarkan
                    const isAllIndex = this.selected.indexOf('all');
                    if (isAllIndex !== -1) {
                        this.selected.splice(isAllIndex, 1);
                    }
                    // restore name attribute for status inputs before submit
                    const inputs = document.querySelectorAll(
                        '#statusFilterForm input[type="checkbox"][value]:not([value="all"])');
                    inputs.forEach(i => i.setAttribute('name', 'status_id[]'));
                    this.submitForm();
                },

                submitForm() {
                    document.getElementById('statusFilterForm').submit();
                }
            };
        }
    </script>
    {{-- define once: SweetAlert confirm helper (fallback to native confirm if Swal unavailable) --}}
    <script>
        if (typeof confirmSweetDelete === 'undefined') {
            function confirmSweetDelete(formId) {
                if (typeof Swal === 'undefined') {
                    if (confirm('Delete this record?')) {
                        document.getElementById(formId).submit();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(formId).submit();
                    }
                });
            }
        }

        // Bulk Delete Function
        function confirmBulkDelete(ids) {
            if (!ids || ids.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No items selected',
                    text: 'Please select at least one item to delete.'
                });
                return;
            }

            Swal.fire({
                title: 'Delete ' + ids.length + ' item(s)?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send bulk delete request
                    fetch('{{ route("ftpp.bulk-destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message || 'Items have been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete items.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting items.'
                        });
                    });
                }
            });
        }
    </script>
@endpush
