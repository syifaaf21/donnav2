@extends('layouts.app')
@section('title', 'FTPP')

@section('content')
    <div class="p-2" x-data="showModal()" @open-show-modal.window="openShowModal($event.detail)">
        <div class="mb-6">
            <h4 class="text-gray-800">Audit Findings Monitoring & Auditee Actions</h4>
        </div>

        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <!-- LEFT: Search + Filter -->
            <div class="flex items-center gap-2 w-full md:w-1/3">

                <!-- SEARCH -->
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input id="live-search" type="text" placeholder="Search..." autocomplete="off"
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 shadow-sm
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 hidden">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>

                <!-- FILTER BUTTON -->
                <div x-data="statusFilter()" class="relative">
                    <button @click="toggle($event)"
                        class="bg-white border border-gray-200 rounded-xl shadow p-2 hover:bg-gray-100 transition">
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
                                @foreach (request()->except(['page', 'status_id']) as $key => $val)
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endforeach

                                <div class="max-h-64 overflow-y-auto px-2 py-1 space-y-2">

                                    <!-- ALL -->
                                    <label
                                        class="flex justify-between items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" value="all" x-model="selected" @change="submitForm"
                                                :checked="{{ request()->filled('status_id') ? 'false' : 'true' }}">
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
                                            $isChecked = in_array($status->id, (array) request()->status_id);
                                            $icons = [
                                                'open' => 'alert-circle',
                                                'submitted' => 'upload-cloud',
                                                'checked by dept head' => 'user-check',
                                                'approved by auditor' => 'check-circle',
                                                'need revision' => 'alert-triangle',
                                                'close' => 'lock',
                                            ];
                                        @endphp

                                        @if (array_key_exists($name, $icons))
                                            <label
                                                class="flex justify-between items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" name="status_id[]" value="{{ $status->id }}"
                                                        @change="submitForm" {{ $isChecked ? 'checked' : '' }}>
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
            <div class="flex items-center gap-3">

                @if (in_array(optional(auth()->user()->role)->name, ['Super Admin', 'Admin', 'Auditor']))
                    <a href="{{ route('ftpp.audit-finding.create') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-700 text-white font-medium
                       shadow hover:bg-blue-800 hover:shadow-md transition-all duration-150">
                        <i data-feather="plus" class="w-4 h-4"></i>
                        Add Finding
                    </a>
                @endif

                @if (in_array(optional(auth()->user()->role)->name, ['Super Admin', 'Admin', 'Auditor', 'Dept-Head']))
                    <a href="{{ route('approval.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-600 text-white font-medium
                       shadow hover:bg-gray-700 hover:shadow-md transition-all duration-150">
                        <i data-feather="pen-tool" class="w-4 h-4"></i>
                        Approval
                    </a>
                @endif
            </div>

        </div>

        <div class="flex gap-6">
            {{-- Main column --}}
            <div class="flex-1">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registration No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Auditor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Auditee
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{-- Actions --}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($findings as $finding)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $finding->registration_number ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            @php
                                                $statusColors = [
                                                    'open' => 'bg-red-500 text-white',
                                                    'submitted' => 'bg-yellow-500 text-gray-900',
                                                    'checked by dept head' => 'bg-yellow-500 text-gray-900',
                                                    'need revision' => 'bg-yellow-500 text-gray-900',
                                                    'approved by auditor' => 'bg-blue-500 text-white',
                                                    'close' => 'bg-green-500 text-white',
                                                ];
                                                $statusName = optional($finding->status)->name ?? '-';
                                                $statusClass = $statusColors[strtolower($statusName)] ?? '';
                                            @endphp
                                            <span class="{{ $statusClass }} p-1 rounded">{{ $statusName }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ optional($finding->department)->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ optional($finding->auditor)->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if ($finding->auditee && $finding->auditee->isNotEmpty())
                                                {{ $finding->auditee->pluck('name')->join(', ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $finding->due_date ? \Carbon\Carbon::parse($finding->due_date)->format('Y/m/d') : '-' }}
                                        </td>
                                        <td class="flex px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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

                                                        @if (strtolower(optional($finding->status)->name ?? '') !== 'open')
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

                                                        <!-- ITEM: Assign Auditee Action -->
                                                        @php $statusName = strtolower(optional($finding->status)->name ?? '') @endphp
                                                        @if ($statusName === 'open')
                                                            <a href="{{ route('ftpp.audit-finding.edit', $finding->id) }}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-yellow-500 hover:bg-gray-50 transition">
                                                                <i data-feather="edit" class="w-4 h-4"></i>
                                                                Edit Audit Finding
                                                            </a>
                                                        @endif
                                                        @if ($statusName === 'need revision')
                                                            <a href="{{ route('ftpp.auditee-action.edit', $finding->id) }}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit" class="w-4 h-4"></i>
                                                                Revise Auditee Action
                                                            </a>
                                                        @elseif ($statusName === 'submitted')
                                                            <a href="{{ route('ftpp.auditee-action.edit', $finding->id) }}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-yellow-500 hover:bg-gray-50 transition">
                                                                <i data-feather="edit" class="w-4 h-4"></i>
                                                                Edit Auditee Action
                                                            </a>
                                                        @else
                                                            <a href="{{ route('ftpp.auditee-action.create', $finding->id) }}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                                                Assign Auditee Action
                                                            </a>
                                                        @endif
                                                        @if (in_array(optional(auth()->user()->role)->name, ['Super Admin', 'Admin', 'Auditor']))
                                                            <!-- ITEM: Delete -->
                                                            <form method="POST"
                                                                action="{{ route('ftpp.destroy', $finding->id) }}"
                                                                onsubmit="return confirm('Delete this record?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" @click="open = false"
                                                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 hover:bg-gray-50 transition">
                                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-500" colspan="7">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $findings->links() }}
                </div>
            </div> {{-- end .flex-1 --}}
        </div> {{-- end .flex --}}

        @include('contents.ftpp2.show')
    </div> {{-- end .p-6 --}}

@endsection
@push('scripts')
    <script>
        (function() {
            const input = document.getElementById('live-search');
            if (!input) return;

            let timeout = null;
            const route = "{{ route('ftpp.search') }}";

            const statusColors = {
                'open': 'bg-red-500 text-white',
                'submitted': 'bg-yellow-500 text-gray-900',
                'checked by dept head': 'bg-yellow-500 text-gray-900',
                'need revision': 'bg-yellow-500 text-gray-900',
                'approve by auditor': 'bg-blue-500 text-white',
                'close': 'bg-green-500 text-white'
            };

            function renderRow(f, csrf) {
                const statusName = f.status?.name ?? '-';
                const cls = statusColors[f.status.toLowerCase()] ?? '';

                const department = f.department?.name ?? '-';
                const auditor = f.auditor?.name ?? '-';

                const auditee = Array.isArray(f.auditee) && f.auditee.length ?
                    f.auditee.map(a => a.name).join(', ') :
                    '-';

                const due = f.due_date ?
                    new Date(f.due_date).toISOString().slice(0, 10).replace(/-/g, '/') :
                    '-';

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${f.registration_number ?? '-'}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <span class="${cls} p-1 rounded">${f.status}</span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.department}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.auditor}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.auditee}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.due_date}</td>

                        <td class="flex px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div x-data="{ open: false, x: 0, y: 0 }" class="relative">

                                <!-- BUTTON -->
                                <button type="button"
                                    @click.prevent="
                                        open = true;
                                        const rect = $event.target.getBoundingClientRect();
                                        x = rect.right - 160;
                                        y = rect.bottom + window.scrollY;
                                    "
                                    class="p-1.5 hover:bg-gray-100 rounded-full transition">
                                    <i data-feather="more-vertical" class="w-5 h-5 text-gray-600"></i>
                                </button>

                                <!-- DROPDOWN -->
                                <template x-teleport="body">
                                    <div x-show="open" @click.outside="open = false"
                                        x-transition.opacity.duration.150ms
                                        class="absolute bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] overflow-hidden"
                                        :style="\`top:\${y}px; left:\${x}px; width:170px;\`">

                                        ${statusName.toLowerCase() !== 'open' ? `
                                                        <button type="button"
                                                            @click="open = false; $dispatch('open-show-modal', ${f.id})"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="eye" class="w-4 h-4"></i> Show
                                                        </button>
                                                    ` : ''}

                                        <a href="/ftpp/${f.id}/download"
                                            @click="open = false"
                                            class="flex items-center gap-2 px-3 py-2.5 text-sm text-blue-600 hover:bg-gray-50 transition">
                                            <i data-feather="download" class="w-4 h-4"></i> Download
                                        </a>

                                        ${f.status.toLowerCase() === 'need revision'
                                            ? `
                                                            <a href="/ftpp/auditee-action/${f.id}/edit"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit" class="w-4 h-4"></i> Revise Auditee Action
                                                            </a>`
                                            : `
                                                            <a href="/ftpp/auditee-action/${f.id}"
                                                                @click="open = false"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit-2" class="w-4 h-4"></i> Assign Auditee Action
                                                            </a>`
                                        }

                                        <form method="POST" action="/ftpp/${f.id}"
                                            onsubmit="return confirm('Delete this record?')">
                                            <input type="hidden" name="_token" value="${csrf}">
                                            <input type="hidden" name="_method" value="DELETE">

                                            <button type="submit"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 hover:bg-gray-50 transition">
                                                <i data-feather="trash-2" class="w-4 h-4"></i> Delete
                                            </button>
                                        </form>

                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function renderRows(items) {
                const tbody = document.querySelector("table tbody");
                if (!tbody) return;

                if (!items.length) {
                    tbody.innerHTML =
                        '<tr><td class="px-6 py-4 text-sm text-gray-500" colspan="7">No records found.</td></tr>';
                    return;
                }

                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                tbody.innerHTML = items.map(f => renderRow(f, csrf)).join('');

                feather.replace();
            }

            input.addEventListener('input', function(e) {
                const v = e.target.value.trim();
                clearTimeout(timeout);

                timeout = setTimeout(() => {
                    if (!v) {
                        window.location = "{{ route('ftpp.index') }}";
                        return;
                    }

                    fetch(route + '?q=' + encodeURIComponent(v))
                        .then(r => r.json())
                        .then(data => renderRows(data))
                        .catch(err => console.error(err));
                }, 300);
            });
        })();

        document.addEventListener("DOMContentLoaded", function() {
            const input = document.getElementById("live-search");
            const clearBtn = document.getElementById("clearSearch");

            if (!input || !clearBtn) return;

            // Tampil atau hilangkan tombol X
            input.addEventListener("input", function() {
                if (input.value.trim() !== "") {
                    clearBtn.classList.remove("hidden");
                } else {
                    clearBtn.classList.add("hidden");
                }
            });

            // Fungsi hapus input + reset data
            clearBtn.addEventListener("click", function() {
                input.value = "";
                clearBtn.classList.add("hidden");

                // Jika mau reload data default:
                window.location = "{{ route('ftpp.index') }}";
            });
        });

        function showModal() {
            return {
                isOpen: false,
                loading: false,
                content: '',
                currentId: null,

                openShowModal(id) {
                    this.isOpen = true;
                    this.loading = true;
                    this.currentId = id;

                    fetch(`/ftpp/${id}`)
                        .then(res => res.text())
                        .then(html => {
                            this.content = html;
                            this.loading = false;
                        });
                },

                close() {
                    this.isOpen = false;
                    this.content = '';
                },
            };
        }

        // function showModal() {
        //     return {
        //         isOpen: false,
        //         pdfUrl: null,

        //         openShowModal(id) {
        //             this.isOpen = true;

        //             // URL ke route preview PDF
        //             this.pdfUrl = `/ftpp/${id}/preview-pdf`;
        //         },

        //         close() {
        //             this.isOpen = false;
        //             this.pdfUrl = null;
        //         },
        //     };
        // }
    </script>
    <script>
        function statusFilter() {
            return {
                open: false,

                selected: [], // ← WAJIB supaya x-model tidak error

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

                submitForm() {
                    document.getElementById('statusFilterForm').submit();
                }
            };
        }
    </script>
@endpush
