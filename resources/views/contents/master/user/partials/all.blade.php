{{-- All --}}
<div class="bg-white shadow rounded-xl overflow-hidden p-3">

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
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs sticky top-0 z-10 shadow-sm">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">NPK</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Department</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 align-top whitespace-nowrap text-gray-700">
                            {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                        </td>

                        <td class="px-4 py-3 align-top">
                            <div class="truncate max-w-[14rem]" title="{{ $user->name }}">
                                {{ ucwords(strtolower($user->name)) }}
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <div class="text-sm text-gray-600" title="{{ $user->npk }}">{{ $user->npk }}</div>
                        </td>

                        <td class="px-4 py-3 align-top">
                            <div class="truncate max-w-[18rem] text-sm text-gray-600" title="{{ $user->email }}">
                                {{ $user->email }}
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top">
                            @if ($user->roles->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        @php
                                            $name = $role->name;
                                            $colorMap = [
                                                'Super Admin' => 'bg-red-50 text-red-700',
                                                'Admin' => 'bg-red-100 text-red-800',
                                                'Dept Head' => 'bg-yellow-50 text-yellow-800',
                                                'Leader' => 'bg-amber-50 text-amber-800',
                                                'Supervisor' => 'bg-blue-50 text-blue-700',
                                                'Auditor' => 'bg-indigo-50 text-indigo-700',
                                                'User' => 'bg-gray-50 text-gray-600',
                                            ];
                                            $classes = $colorMap[$name] ?? 'bg-blue-50 text-blue-700';
                                        @endphp

                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $classes }}"
                                            title="{{ $name }}">
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top">
                            @if ($user->departments->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-sm text-gray-600"
                                        title="{{ $user->departments->pluck('name')->join(', ') }}">
                                        {{ $user->departments->pluck('name')->join(', ') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                {{-- Edit Button --}}
                                <button type="button"
                                    class=" btn-edit-user flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors duration-200 flex-shrink-0"
                                    data-id="{{ $user->id }}" data-bs-title="Edit User" title="Edit user"
                                    aria-label="Edit user {{ $user->name }}">
                                    <i data-feather="edit" class="w-4 h-4" aria-hidden="true"></i>
                                </button>

                                {{-- Delete Button --}}
                                {{-- Delete Button --}}
                                @php
                                    $userRoles = $user->roles->pluck('name')->toArray();
                                @endphp

                                @if (!in_array('Admin', $userRoles) && !in_array('Super Admin', $userRoles))
                                    <form action="{{ route('master.users.destroy', $user->id) }}" method="POST"
                                        class="flex items-center m-0 p-0 delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors duration-200 flex-shrink-0 align-middle"
                                            data-bs-title="Delete User" title="Delete user"
                                            aria-label="Delete user {{ $user->name }}">
                                            <i data-feather="trash-2" class="w-4 h-4" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-6">No users found.</td>
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
