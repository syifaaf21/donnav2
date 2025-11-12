<ul class="space-y-1 px-2 py-4">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all {{ request()->is('/') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="home" class="w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">Dashboard</span>
        </a>
    </li>

    <li class="mt-4">
        <h6 class="text-xs uppercase text-gray-400 font-semibold sidebar-text px-3">Docs</h6>
    </li>

    <li>
        <a href="{{ route('document-control.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all {{ Route::is('document-control*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="settings" class="w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">Document Control</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-review.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all {{ Route::is('document-review*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="check-square" class="w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">Document Review</span>
        </a>
    </li>

    <li>
        <a href="{{ route('ftpp.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all {{ Route::is('ftpp*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="alert-octagon" class="w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">FTPP</span>
        </a>
    </li>

    <hr class="my-2 border-gray-200">

    <!-- Master Data -->
    @if (in_array(strtolower(auth()->user()->role->name), ['super admin', 'admin']))
        <li>
            <a type="button"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-left font-medium sidebar-transition"
                data-collapse="masterDataMenu">
                <div class="flex items-center gap-3">
                    <i data-feather="database" class="w-4 h-4 text-gray-100"></i>
                    <span class="sidebar-text text-gray-100">Master Data</span>
                </div>
                <i data-feather="chevron-right"
                    class="w-4 h-4 transition-transform group-[.open]:rotate-90 text-gray-100"></i>
            </a>

            <!-- Submenu -->
            <ul id="masterDataMenu" class="ml-2 mt-1 hidden space-y-1">
                <li>
                    <a href="{{ route('master.departments.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.departments.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="briefcase" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Department</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.products.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.products.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="package" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Product</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.models.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.models.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="box" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Model</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.processes.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.processes.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="activity" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Process</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.part_numbers.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.part_numbers.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="box" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Part Number</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.hierarchy.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.hierarchy.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="git-branch" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">Hierarchy</span>
                    </a>
                </li>

                <!-- Documents Dropdown -->
                <li>
                    <a type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-sm text-left font-medium sidebar-transition"
                        data-collapse="documentsDropdown">
                        <div class="flex items-center gap-3">
                            <i data-feather="file-text" class="w-4 h-4 text-gray-100"></i>
                            <span class="sidebar-text text-gray-100">Document</span>
                        </div>
                        <i data-feather="chevron-right" class="w-4 h-4 transition-transform text-gray-100"></i>
                    </a>

                    <ul id="documentsDropdown" class="ml-1 mt-1 hidden space-y-1">
                        <li>
                            <a href="{{ route('master.document-control.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.document-control.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                                <i data-feather="settings" class="w-4 h-4 text-gray-100"></i>
                                <span class="sidebar-text text-gray-100">Control</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('master.document-review.index2') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.document-review.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                                <i data-feather="search" class="w-4 h-4 text-gray-100"></i>
                                <span class="sidebar-text text-gray-100">Review</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('master.ftpp.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.ftpp.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="alert-circle" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">FTPP</span>
                    </a>
                </li>

                <!-- Menu User hanya untuk Super Admin -->
                @if (strtolower(auth()->user()->role->name) === 'super admin')
                    <li>
                        <a href="{{ route('master.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('users.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                            <i data-feather="user" class="w-4 h-4 text-gray-100"></i>
                            <span class="sidebar-text text-gray-100">User</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif
</ul>
