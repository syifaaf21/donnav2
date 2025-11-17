@extends('layouts.app')
@section('title', 'FTPP2')

@section('content')
    <div class="p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold">FTPP2 - Audit Findings & Auditee Actions</h2>
            <a href="{{ route('ftpp2.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-700">
                <i class="fas fa-plus"></i>
                Add
            </a>
        </div>

        <div class="flex gap-6">
            {{-- Left: status tabs/sidebar --}}
            <aside class="w-64">
                <div class="bg-slate-800 text-gray-100 rounded-lg p-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('ftpp2.index') }}"
                                class="flex justify-between items-center py-2 rounded {{ request()->filled('status_id') ? 'text-gray-300' : 'bg-slate-700 text-white' }}">
                                <span>All</span>
                                <span
                                    class="text-sm bg-slate-700 text-white px-1 py-0.5 rounded">{{ $totalCount ?? 0 }}</span>
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
                                        class="flex justify-between items-center py-2 rounded hover:bg-slate-700 {{ request('status_id') == $status->id ? 'bg-slate-700 text-white' : 'text-gray-100' }}">
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
                <div class="mb-3">
                    <input id="live-search" type="text" placeholder="Search..."
                        class="block w-1/3 rounded-lg border border-gray-300 p-2 focus:ring focus:ring-blue-300 focus:outline-none"
                        autocomplete="off">
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
                                        Actions
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <form method="POST" action="{{ route('ftpp2.destroy', $finding->id) }}"
                                                onsubmit="return confirm('Delete this record?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800"
                                                    title="Delete">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </form>
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

                // No-AJAX delete: use standard form submissions (DELETE via POST + _method)

            })();
        </script>
    @endpush

@endsection
