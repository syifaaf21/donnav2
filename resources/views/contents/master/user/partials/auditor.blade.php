{{-- Auditor --}}
<div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
    {{-- Search Bar --}}
    <div class="p-2 flex justify-end">
        <form method="GET" class="searchForm flex items-center w-full max-w-sm relative">
            <div class="relative w-96">
                <input type="text" name="search"
                    class="searchInput peer w-full rounded-xl border border-gray-300 bg-white pl-4 pr-20 py-2.5
                        text-sm text-gray-700 shadow-sm transition-all duration-200
                        focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                    placeholder="Type to search..." value="{{ request('search') }}">

                <!-- Floating Label -->
                <label for="searchInput"
                    class="absolute left-4 px-1 bg-white text-gray-400 rounded transition-all duration-150
                        pointer-events-none
                        {{ request('search')
                            ? '-top-3 text-xs text-sky-600'
                            : 'top-2.5 peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                    Type to search...
                </label>

                <button type="submit"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-blue-700 transition">
                    <i data-feather="search" class="w-5 h-5"></i>
                </button>
                @if (request('search'))
                <button type="button"
                    class="clearSearch absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
                @endif
            </div>
        </form>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-96">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
            <thead style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;" class="sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                    <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Name</th>
                    <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">NPK</th>
                    <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Audit Type</th>
                    <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    @if ($user->roles->pluck('name')->contains('Auditor'))
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 border-r border-gray-200">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td class="px-4 py-2 border-r border-gray-200 text-sm font-semibold">{{ ucwords(strtolower($user->name)) }}
                                <span class="text-xs text-gray-400 block">
                                    {{ $user->departments->pluck('name')->join(', ') ?: '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border-r border-gray-200 text-sm font-semibold">{{ $user->npk }}</td>
                            <td class="px-4 py-2 border-r border-gray-200">
                                {{ $user->auditTypes->pluck('name')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-4 py-2 border-r border-gray-200 flex gap-2">
                                {{-- Edit Button --}}
                                <button type="button" data-bs-toggle="modal" data-id="{{ $user->id }}"
                                    data-bs-title="Edit User"
                                    class="btn-edit-user w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors p-2 duration-200">
                                    <i data-feather="edit" class="w-4 h-4"></i>
                                </button>
                                {{-- Delete Button --}}
                                @if (in_array(auth()->user()->roles->pluck('name')->first(), ['Admin', 'Super Admin']))
                                    <form action="{{ route('master.users.destroy', $user->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2"
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
                        <td colspan="6" class="text-center text-gray-500 py-4">No auditors found.</td>
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
