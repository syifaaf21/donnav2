<ul class="space-y-1 px-2 py-4">

    <!-- Dashboard -->
    <li>
        <a href="{{ route('dashboard') }}"
            class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all
        {{ request()->is('/') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="home" class="menu-icon w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-control.index') }}"
            class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all
        {{ Route::is('document-control*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="settings" class="menu-icon w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text">Document Control</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-review.index') }}"
            class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all
        {{ Route::is('document-review*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="check-square" class="menu-icon w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text">Document Review</span>
        </a>
    </li>

    <li>
        <a href="{{ route('ftpp.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all {{ Route::is('ftpp*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
            <i data-feather="alert-octagon" class="w-4 h-4 text-gray-100"></i>
            <span class="sidebar-text text-gray-100">FTPP</span>
        </a>
    </li>

    @if (in_array(strtolower(auth()->user()->roles->pluck('name')->first() ?? ''), ['super admin', 'admin']))
        <li>
            <a href="{{ route('archive.archived') }}"
                class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-all
                {{ Route::is('archive.archived*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                <i data-feather="settings" class="menu-icon w-4 h-4 text-gray-100"></i>
                <span class="sidebar-text">Archive</span>
            </a>
        </li>
    @endif

    <hr class="my-2 border-gray-200">

    <!-- Master Data -->
    @if (in_array(strtolower(auth()->user()->roles->pluck('name')->first() ?? ''), ['super admin', 'admin']))
        <li>
            <a type="button"
                class="collapse-toggle menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-left font-medium"
                data-collapse="masterDataMenu">
                <div class="flex items-center gap-3">
                    <i data-feather="database" class="menu-icon w-4 h-4 text-gray-100"></i>
                    <span class="sidebar-text text-gray-100">Master Data</span>
                </div>
                <i data-feather="chevron-right" class="menu-icon w-4 h-4 text-gray-100"></i>
            </a>
            <ul id="masterDataMenu" class="ml-2 mt-1 hidden space-y-1">

                <li>
                    <a href="{{ route('master.departments.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.departments.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="briefcase" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Department</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.products.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.products.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="package" class=" menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Product</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.models.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.models.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="box" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Model</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.processes.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.processes.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="activity" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Process</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.part_numbers.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.part_numbers.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="box" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Part Number</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.hierarchy.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                        {{ Route::is('master.hierarchy.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="git-branch" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">Hierarchy</span>
                    </a>
                </li>

                <!-- Document Dropdown -->
                <li>
                    <a type="button"
                        class="collapse-toggle menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-left font-medium"
                        data-collapse="documentsDropdown">
                        <div class="flex items-center gap-3">
                            <i data-feather="file-text" class="menu-icon w-4 h-4 text-gray-100"></i>
                            <span class="sidebar-text text-gray-100">Document</span>
                        </div>
                        <i data-feather="chevron-right" class="menu-icon w-4 h-4 text-gray-100"></i>
                    </a>

                    <ul id="documentsDropdown" class="ml-2 mt-1 hidden space-y-1">
                        <li>
                            <a href="{{ route('master.document-control.index') }}"
                                class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                                {{ Route::is('master.document-control.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                                <i data-feather="settings" class="menu-icon w-4 h-4 text-gray-100"></i>
                                <span class="sidebar-text">Control</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('master.document-review.index2') }}"
                                class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                                {{ Route::is('master.document-review.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                                <i data-feather="search" class="menu-icon w-4 h-4 text-gray-100"></i>
                                <span class="sidebar-text">Review</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('master.ftpp.index') }}"
                        class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm {{ Route::is('master.ftpp.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="alert-circle" class="menu-icon w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text text-gray-100">FTPP</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.users.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 text-sm
                            {{ Route::is('users.*') ? 'bg-slate-800 text-gray-100' : 'text-gray-100' }}">
                        <i data-feather="user" class="w-4 h-4 text-gray-100"></i>
                        <span class="sidebar-text">User</span>
                    </a>
                </li>
            </ul>
        </li>
    @endif

</ul>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.magic('feather', () => {
            return () => feather.replace();
        });
    });

    // Render pertama kali
    document.addEventListener('DOMContentLoaded', () => {
        feather.replace();
    });
</script>
