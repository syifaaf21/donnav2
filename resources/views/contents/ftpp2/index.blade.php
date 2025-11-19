@extends('layouts.app')
@section('title', 'FTPP2')

@section('content')
    <div class="p-2" x-data="showModal()" @open-show-modal.window="openShowModal($event.detail)">
        <div class="mb-6">
            <h4 class="text-gray-800">Audit Findings Monitoring & Auditee Actions</h4>
        </div>
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <!-- Search -->
            <div class="w-full md:w-1/3">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input id="live-search" type="text" placeholder="Search..." autocomplete="off"
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 shadow-sm
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-3">

                @if (in_array(optional(auth()->user()->role)->name, ['Admin', 'Auditor']))
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
            {{-- Left: status tabs/sidebar --}}
            <aside class="w-64 bg-white justify-center rounded-xl shadow-sm py-4 pr-4 border border-gray-100">
                <ul class="space-y-1">

                    {{-- ALL --}}
                    <li>
                        <a href="{{ route('ftpp.index') }}"
                            class="flex justify-between items-center px-1 py-2 rounded-lg transition
                    {{ request()->filled('status_id') ? 'text-gray-700 hover:bg-gray-100' : 'bg-blue-600 text-white shadow-sm' }}">
                            <span class="flex items-center gap-2">
                                <i data-feather="list" class="w-4 h-4"></i>
                                All
                            </span>

                            <span
                                class="text-xs px-2 py-0.5 rounded-full
                    {{ request()->filled('status_id') ? 'bg-gray-200 text-gray-800' : 'bg-white text-blue-700' }}">
                                {{ $totalCount ?? 0 }}
                            </span>
                        </a>
                    </li>

                    {{-- LOOP STATUS --}}
                    @foreach ($statuses as $status)
                        @php
                            $name = strtolower($status->name);
                            $active = request('status_id') == $status->id;

                            // mapping ikon per status
                            $icons = [
                                'open' => 'alert-circle',
                                'submitted' => 'upload-cloud',
                                'checked by dept head' => 'user-check',
                                'approve by auditor' => 'check-circle',
                                'need revision' => 'alert-triangle',
                                'close' => 'lock',
                            ];
                        @endphp

                        @if (array_key_exists($name, $icons))
                            <li>
                                <a href="{{ route('ftpp.index', array_merge(request()->except('page'), ['status_id' => $status->id])) }}"
                                    class="flex justify-between items-center px-1 py-2 rounded-lg transition
                            {{ $active ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">

                                    <span class="flex items-center gap-2 capitalize">
                                        <i data-feather="{{ $icons[$name] }}" class="w-4 h-4"></i>
                                        {{ $status->name }}
                                    </span>

                                    {{-- Badge counter --}}
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full
                            {{ $active ? 'bg-white text-blue-700' : 'bg-gray-200 text-gray-800' }}">
                                        {{ $status->audit_finding_count }}
                                    </span>
                                </a>
                            </li>
                        @endif
                    @endforeach

                </ul>
            </aside>

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
                                                    'approve by auditor' => 'bg-blue-500 text-white',
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
                                                        open = !open;
                                                        const rect = $el.getBoundingClientRect();
                                                        x = rect.left - 150;   // posisi horizontal lebih pas
                                                        y = rect.top + 28;     // posisi vertical lebih rapi
                                                    "
                                                    class="p-1.5 hover:bg-gray-100 rounded-full transition">
                                                    <i data-feather="more-vertical" class="w-5 h-5 text-gray-600"></i>
                                                </button>

                                                <!-- DROPDOWN -->
                                                <template x-teleport="body">
                                                    <div x-show="open" @click.outside="open = false"
                                                        x-transition.opacity.duration.150ms
                                                        class="absolute bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] overflow-hidden"
                                                        :style="`top:${y}px; left:${x}px; width:170px; position:fixed`">

                                                        <!-- ITEM: Show -->
                                                        <button type="button"
                                                            @click="$dispatch('open-show-modal', {{ $finding->id }})"
                                                            class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                            <i data-feather="eye" class="w-4 h-4"></i>
                                                            Show
                                                        </button>

                                                        <!-- ITEM: Download -->
                                                        <a href="{{ route('ftpp.download', $finding->id) }}"
                                                            class="flex items-center gap-2 px-3 py-2.5 text-sm text-blue-600 hover:bg-gray-50 transition">
                                                            <i data-feather="download" class="w-4 h-4"></i>
                                                            Download
                                                        </a>

                                                        <!-- ITEM: Assign Auditee Action -->
                                                        @php $statusName = strtolower(optional($finding->status)->name ?? '') @endphp

                                                        @if ($statusName === 'need revision')
                                                            <a href="{{ route('ftpp.auditee-action.edit', $finding->id) }}"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit" class="w-4 h-4"></i>
                                                                Revise Auditee Action
                                                            </a>
                                                        @else
                                                            <a href="{{ route('ftpp.auditee-action.create', $finding->id) }}"
                                                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                                                Assign Auditee Action
                                                            </a>
                                                        @endif
                                                        <!-- ITEM: Delete -->
                                                        <form method="POST"
                                                            action="{{ route('ftpp.destroy', $finding->id) }}"
                                                            onsubmit="return confirm('Delete this record?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 hover:bg-gray-50 transition">
                                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                                                Delete
                                                            </button>
                                                        </form>

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

            function renderRows(items) {
                const tbody = document.querySelector('table tbody');
                if (!tbody) return;
                if (!items.length) {
                    tbody.innerHTML =
                        '<tr><td class="px-6 py-4 text-sm text-gray-500" colspan="7">No records found.</td></tr>';
                    return;
                }
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                tbody.innerHTML = items.map(f => {
                    const statusName = f.status || '-';
                    const cls = statusColors[statusName.toLowerCase()] || '';
                    const due = f.due_date || '-';
                    const auditee = f.auditee || '-';
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.registration_number || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm"><span class="${cls} p-1 rounded">${statusName}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.department || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${f.auditor || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${auditee}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${due}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <form method="POST" action="/ftpp2/${f.id}" onsubmit="return confirm('Delete this record?')" class="inline">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            input.addEventListener('input', function(e) {
                const v = e.target.value.trim();
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (!v) {
                        // empty -> reload page to restore server-side filters/pagination
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
    </script>
@endpush
