<ul class="space-y-1 px-2">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ request()->is('/') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
            <i data-feather="home" class="w-4 h-4 text-gray-800"></i>
            <span class="sidebar-text text-gray-800">Dashboard</span>
        </a>
    </li>

    <li class="mt-4">
        <h6 class="text-xs uppercase text-gray-400 font-semibold sidebar-text px-3">Docs</h6>
    </li>

    <li>
        <a href="{{ route('document-control.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ Route::is('document-control*') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
            <i data-feather="settings" class="w-4 h-4 text-gray-800"></i>
            <span class="sidebar-text text-gray-800">Document Control</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-review.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ Route::is('document-review*') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
            <i data-feather="check-square" class="w-4 h-4 text-gray-800"></i>
            <span class="sidebar-text text-gray-800">Document Review</span>
        </a>
    </li>

    <hr class="my-2 border-gray-200">

    <!-- Master Data -->
    <li>
        <button type="button"
            class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 text-left font-medium sidebar-transition"
            data-collapse="masterDataMenu">
            <div class="flex items-center gap-3">
                <i data-feather="database" class="w-4 h-4 text-gray-800"></i>
                <span class="sidebar-text text-gray-800">Master Data</span>
            </div>
            <i data-feather="chevron-right"
                class="w-4 h-4 transition-transform group-[.open]:rotate-90 text-gray-800"></i>
        </button>

        <!-- Submenu -->
        <ul id="masterDataMenu" class="ml-2 mt-1 hidden space-y-1">
            <li>
                <a href="{{ route('master.departments.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('departments.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="briefcase" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Department</span>
                </a>
            </li>
            <li>
                <a href="{{ route('master.products.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('products.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="package" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Product</span>
                </a>
            </li>
            <li>
                <a href="{{ route('master.models.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('models.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="box" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Model</span>
                </a>
            </li>
            <li>
                <a href="{{ route('master.processes.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('processes.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="activity" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Process</span>
                </a>
            </li>
            <li>
                <a href="{{ route('master.part_numbers.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('part_numbers.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="box" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Part Numbers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('master.hierarchy.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('hierarchy.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="git-branch" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Hierarchy</span>
                </a>
            </li>
            <!-- ✅ Documents Dropdown -->
            <li>
                <button type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-left font-medium sidebar-transition"
                    data-collapse="documentsDropdown">
                    <div class="flex items-center gap-3">
                        <i data-feather="file-text" class="w-4 h-4 text-gray-800"></i>
                        <span class="sidebar-text text-gray-800">Documents</span>
                    </div>
                    <i data-feather="chevron-right" class="w-4 h-4 transition-transform text-gray-800"></i>
                </button>

                <ul id="documentsDropdown" class="ml-1 mt-1 hidden space-y-1">
                    <li>
                        <a href="{{ route('master.document-control.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('document-control.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                            <i data-feather="settings" class="w-4 h-4 text-gray-800"></i>
                            <span class="sidebar-text text-gray-800">Control</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.document-review.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('document-review.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                            <i data-feather="search" class="w-4 h-4 text-gray-800"></i>
                            <span class="sidebar-text text-gray-800">Review</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('master.users.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('users.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-800' }}">
                    <i data-feather="user" class="w-4 h-4 text-gray-800"></i>
                    <span class="sidebar-text text-gray-800">Users</span>
                </a>
            </li>
        </ul>
    </li>
</ul>
