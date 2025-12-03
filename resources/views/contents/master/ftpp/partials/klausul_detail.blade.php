@if ($klausul->headKlausul->isEmpty())
    <p class="text-gray-400 text-center py-4">Tidak ada Head Klausul untuk {{ $klausul->name }}.</p>
@else
    <table class="min-w-full border border-gray-300 text-sm mb-4">
        <thead style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
            <tr class="text-left">
                <th class="px-3 py-3 text-sm font-bold uppercase tracking-wider w-12" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                <th class="px-3 py-3 text-sm font-bold uppercase tracking-wider w-24" style="color: #1e2b50; letter-spacing: 0.5px;">Code</th>
                <th class="px-3 py-3 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Head Klausul</th>
                <th class="px-3 py-3 text-center text-sm font-bold uppercase tracking-wider w-32" style="color: #1e2b50; letter-spacing: 0.5px;">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
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
