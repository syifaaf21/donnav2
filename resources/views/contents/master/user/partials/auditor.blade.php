{{-- Auditor --}}
<div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
    {{-- Search Bar --}}
    <div class="p-2 border-b border-gray-100 flex justify-end">
        <form method="GET" class="searchForm flex items-center w-full max-w-sm relative">
            <input type="text" name="search"
                class="searchInput w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Search..." value="{{ request('search') }}">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                <i class="bi bi-search"></i>
            </button>
            <button type="button"
                class="clearSearch absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-96">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Department</th>
                    <th>Audit Type</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @if ($user->role->name === 'Auditor')
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ ucwords(strtolower($user->name)) }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ $user->department->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $user->auditType->name ?? '-' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                {{-- Edit Button --}}
                                <button type="button" data-bs-toggle="modal" data-id="{{ $user->id }}"
                                    data-bs-title="Edit User"
                                    class="btn-edit-user bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                    <i data-feather="edit" class="w-4 h-4"></i>
                                </button>
                                {{-- Delete Button --}}
                                @if (in_array(auth()->user()->role->name, ['Admin', 'Super Admin']))
                                    <form action="{{ route('master.users.destroy', $user->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-600 text-white hover:bg-red-700 p-2 rounded"
                                            data-bs-title="Delete User">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-4">No auditors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-4" id="pagination-links">
    {{ $users->withQueryString()->links('vendor.pagination.tailwind') }}
</div>

<x-sweetalert-confirm />
