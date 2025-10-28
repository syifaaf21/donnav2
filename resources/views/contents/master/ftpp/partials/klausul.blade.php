{{-- Klausul Table --}}
<div class="flex justify-between items-center mb-3">
    <h2 class="text-lg font-semibold text-gray-700">Klausul</h2>
    <button class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">
        <i class="bi bi-plus"></i> Add Klausul
    </button>
</div>

<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-300 text-sm mb-4">
        <thead class="bg-gray-100">
            <tr class="text-left">
                <th class="px-3 py-2 border-b">No</th>
                <th class="px-3 py-2 border-b">Name</th>
                <th class="px-3 py-2 border-b text-center w-32">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($klausuls as $index => $klausul)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border-b">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 border-b">{{ $klausul->name }}</td>
                    <td class="px-3 py-2 border-b text-center">
                        <button class="text-blue-600 hover:underline">Edit</button> |
                        <button class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
                {{-- Head Klausul --}}
                @if ($klausul->headKlausul->count() > 0)
                    <tr>
                        <td colspan="3" class="bg-gray-50 px-3 py-2">
                            <div class="ml-6">
                                <h3 class="text-sm font-semibold mb-1 text-gray-700">Head Klausul</h3>
                                <table class="w-full border border-gray-200 text-xs">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-2 py-1 border-b w-12">No</th>
                                            <th class="px-2 py-1 border-b w-24">Code</th>
                                            <th class="px-2 py-1 border-b">Name</th>
                                            <th class="px-2 py-1 border-b text-center w-24">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($klausul->headKlausul as $i => $head)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-2 py-1 border-b">{{ $i + 1 }}</td>
                                                <td class="px-2 py-1 border-b">{{ $head->code }}</td>
                                                <td class="px-2 py-1 border-b">{{ $head->name }}</td>
                                                <td class="px-2 py-1 border-b text-center">
                                                    <button class="text-blue-600 hover:underline">Edit</button>
                                                    |
                                                    <button class="text-red-600 hover:underline">Delete</button>
                                                </td>
                                            </tr>
                                            {{-- Sub Klausul --}}
                                            @if ($head->subKlausul->count() > 0)
                                                <tr>
                                                    <td colspan="4" class="bg-gray-50 px-2 py-1">
                                                        <div class="ml-6">
                                                            <h4 class="text-xs font-semibold mb-1 text-gray-700">
                                                                Sub Klausul</h4>
                                                            <table class="w-full border border-gray-200 text-[11px]">
                                                                <thead class="bg-gray-100">
                                                                    <tr>
                                                                        <th class="px-2 py-1 border-b w-12">
                                                                            No</th>
                                                                        <th class="px-2 py-1 border-b w-24">
                                                                            Code</th>
                                                                        <th class="px-2 py-1 border-b">
                                                                            Name</th>
                                                                        <th class="px-2 py-1 border-b text-center w-24">
                                                                            Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($head->subKlausul as $j => $sub)
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="px-2 py-1 border-b">
                                                                                {{ $j + 1 }}
                                                                            </td>
                                                                            <td class="px-2 py-1 border-b">
                                                                                {{ $sub->code }}
                                                                            </td>
                                                                            <td class="px-2 py-1 border-b">
                                                                                {{ $sub->name }}
                                                                            </td>
                                                                            <td class="px-2 py-1 border-b text-center">
                                                                                <button
                                                                                    class="text-blue-600 hover:underline">Edit</button>
                                                                                |
                                                                                <button
                                                                                    class="text-red-600 hover:underline">Delete</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="3" class="text-center text-gray-400 py-4">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
