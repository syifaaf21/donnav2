<div class="flex justify-between items-center mb-3">
    <h2 class="text-lg font-semibold text-gray-700">Finding Category</h2>
    <button class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">
        <i class="bi bi-plus"></i> Add Category
    </button>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr class="text-left">
                <th class="px-3 py-2 border-b">No</th>
                <th class="px-3 py-2 border-b">Name</th>
                <th class="px-3 py-2 border-b text-center w-32">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($findingCategories as $index => $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border-b">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 border-b">{{ $category->name }}</td>
                    <td class="px-3 py-2 border-b text-center">
                        <button class="text-blue-600 hover:underline">Edit</button> |
                        <button class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-gray-400 py-4">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
