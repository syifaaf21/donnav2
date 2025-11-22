@if ($klausul->headKlausul->isEmpty())
    <p class="text-gray-400 text-center py-4">Tidak ada Head Klausul untuk {{ $klausul->name }}.</p>
@else
    <table class="min-w-full border border-gray-300 text-sm mb-4">
        <thead class="bg-gray-100">
            <tr class="text-left">
                <th class="px-3 py-2 border-b w-12">No</th>
                <th class="px-3 py-2 border-b w-24">Code</th>
                <th class="px-3 py-2 border-b">Head Klausul</th>
                <th class="px-3 py-2 border-b text-center w-32">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($klausul->headKlausul as $i => $head)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border-b">{{ $i + 1 }}</td>
                    <td class="px-3 py-2 border-b">{{ $head->code }}</td>
                    <td class="px-3 py-2 border-b">{{ $head->name }}</td>
                    <td class="px-3 py-2 border-b text-center">
                        <button type="button" class="text-blue-600 hover:underline">Edit</button> |
                        <button type="button" class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
