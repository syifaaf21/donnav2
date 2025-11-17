@extends('layouts.app')
@section('title', 'FTPP2')

@section('content')
    <div class="p-2">

        <div class="flex gap-6">
            {{-- Left: status tabs/sidebar --}}
            <aside class="w-64 h-100 bg-white text-gray-800 rounded-lg shadow p-4">
                <div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('ftpp2.index') }}"
                                class="flex justify-between items-center p-2 rounded {{ request()->filled('status_id') ? 'text-gray-800' : 'bg-slate-700 text-white' }}">
                                <span>All</span>
                                <span
                                    class="text-sm bg-white text-gray-800 px-1 py-0.5 rounded">{{ $totalCount ?? 0 }}</span>
                            </a>
                        </li>
                        @foreach ($statuses as $status)
                            @if (in_array(strtolower($status->name), [
                                    'open',
                                    'submitted',
                                    'checked by dept head',
                                    'approve by auditor',
                                    'need revision',
                                    'close',
                                ]))
                                <li>
                                    <a href="{{ route('ftpp2.index', array_merge(request()->except('page'), ['status_id' => $status->id])) }}"
                                        class="flex justify-between items-center p-2 rounded hover:bg-slate-700 hover:text-white {{ request('status_id') == $status->id ? 'bg-slate-700 text-white' : 'text-gray-800' }}">
                                        <span>{{ $status->name }}</span>
                                        <span
                                            class="text-sm bg-slate-700 text-white px-1 py-0.5 rounded">{{ $status->audit_finding_count }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </aside>

            {{-- Main column --}}
            <div class="flex-1">
                <div class="mb-4 flex items-center justify-between">
                    <input id="live-search" type="text" placeholder="Search..."
                        class="block w-1/3 rounded-lg border shadow border-gray-300 p-2 focus:ring focus:ring-blue-400 focus:outline-none"
                        autocomplete="off">
                    @if (in_array(optional(auth()->user()->role)->name, ['Admin', 'Auditor']))
                        <a href="{{ route('ftpp2.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 shadow bg-blue-800 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                            Add
                        </a>
                    @endif

                </div>

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
                                                <button type="button"
                                                    @click.prevent="
                open = !open;
                const rect = $el.getBoundingClientRect();
                x = rect.left - 120;   // posisi horizontal dropdown
                y = rect.top + 25;     // posisi vertical dropdown
            "
                                                    class="p-1 hover:bg-gray-200 rounded">
                                                    <i data-feather="more-vertical" class="w-5 h-5"></i>
                                                </button>

                                                <!-- Teleport dropdown ke BODY -->
                                                <template x-teleport="body">
                                                    <div x-show="open" @click.outside="open = false" x-transition
                                                        class="absolute bg-white border rounded-md shadow-lg z-[9999]"
                                                        :style="`top:${y}px; left:${x}px; width:140px; position:fixed`">
                                                        <a :href=""
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 hover:bg-gray-100">
                                                            <i data-feather="eye" class="w-4 h-4"></i> Show
                                                        </a>

                                                        <a :href="`/ftpp2/${item.id}/download`"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                                            <i data-feather="download" class="w-4 h-4"></i> Download
                                                        </a>

                                                        <a href="{{ route('ftpp2.edit', $finding->id) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-yellow-500 hover:bg-gray-100">
                                                            <i data-feather="edit" class="w-4 h-4"></i> Edit
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('ftpp2.destroy', $finding->id) }}"
                                                            onsubmit="return confirm('Delete this record?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                                <i data-feather="trash-2" class="w-4 h-4"></i> Delete
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
    </div> {{-- end .p-6 --}}

    @push('scripts')
        <script>
            (function() {
                const input = document.getElementById('live-search');
                if (!input) return;

                let timeout = null;
                const route = "{{ route('ftpp2.search') }}";

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
                            window.location = "{{ route('ftpp2.index') }}";
                            return;
                        }
                        fetch(route + '?q=' + encodeURIComponent(v))
                            .then(r => r.json())
                            .then(data => renderRows(data))
                            .catch(err => console.error(err));
                    }, 300);
                });
            })();
        </script>
    @endpush

@endsection
