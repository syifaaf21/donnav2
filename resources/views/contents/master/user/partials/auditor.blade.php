{{-- Table --}}
<div class="">
    <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600 overflow-x-auto">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">NPK</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Department</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <tr>
                <td class="px-4 py-2">
                    {{ $loop->iteration }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
