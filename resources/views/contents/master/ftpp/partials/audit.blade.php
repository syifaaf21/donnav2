<div class="flex justify-between items-center mb-3">
    <h2 class="text-lg font-semibold text-gray-700">Audit Type</h2>
    <button class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">
        <i class="bi bi-plus"></i> Add Audit
    </button>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr class="text-left">
                <th class="px-3 py-2 border-b w-10">No</th>
                <th class="px-3 py-2 border-b">Audit Type</th>
                <th class="px-3 py-2 border-b">Sub Audit Type</th>
                <th class="px-3 py-2 border-b text-center w-32">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($audits as $index => $audit)
                <tr class="hover:bg-gray-50 align-top">
                    <td class="px-3 py-2 border-b">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 border-b font-medium text-gray-800">{{ $audit->name }}</td>
                    <td class="px-3 py-2 border-b">
                        @if ($audit->subAudit->isNotEmpty())
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($audit->subAudit as $sub)
                                    <li>{{ $sub->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 border-b text-center">
                        <button class="text-blue-600 hover:underline">Edit</button> |
                        <button class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-gray-400 py-4">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
