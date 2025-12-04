<ul class="py-4">

    <!-- Dashboard -->
    <li>
        <a href="{{ route('dashboard') }}" data-bs-title="Dashboard"
            class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200
        {{ request()->is('dashboard*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
            <i data-feather="home" class="menu-icon w-4 h-4"></i>
            <span class="sidebar-text ">Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-control.index') }}" data-bs-title="Document Control"
            class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200
        {{ Route::is('document-control*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
            <i data-feather="settings" class="menu-icon w-4 h-4"></i>
            <span class="sidebar-text">Document Control</span>
        </a>
    </li>

    <li>
        <a href="{{ route('document-review.index') }}" data-bs-title="Document Review"
            class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200
        {{ Route::is('document-review*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
            <i data-feather="check-square" class="menu-icon w-4 h-4"></i>
            <span class="sidebar-text">Document Review</span>
        </a>
    </li>

    <li>
        <a href="{{ route('ftpp.index') }}" data-bs-title="FTPP"
            class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200
            {{ Route::is('ftpp*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
            <i data-feather="alert-octagon" class="w-4 h-4"></i>
            <span class="sidebar-text">FTPP</span>
        </a>
    </li>

    @if (in_array(strtolower(auth()->user()->roles->pluck('name')->first() ?? ''), ['super admin', 'admin']))
        <li>
            <a href="{{ route('archive.index') }}" data-bs-title="Archive"
                class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200
                {{ Route::is('archive*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                <i data-feather="archive" class="menu-icon w-4 h-4"></i>
                <span class="sidebar-text">Archive</span>
            </a>
        </li>
    @endif

    <hr class="my-2 border-gray-200">

    <!-- Master Data -->
    @if (in_array(strtolower(auth()->user()->roles->pluck('name')->first() ?? ''), ['super admin', 'admin']))
        <li>
            <a data-bs-title="Master Data"
                class="collapse-toggle menu-item w-full flex items-center justify-between px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-left font-medium
                {{ Route::is('master*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}"
                data-collapse="masterDataMenu">
                <div class="flex items-center gap-3">
                    <i data-feather="database" class="menu-icon w-4 h-4"></i>
                    <span class="sidebar-text">Master Data</span>
                </div>
                <i data-feather="chevron-right" class="menu-icon w-4 h-4"></i>
            </a>
            <ul id="masterDataMenu" class="ml-2 mt-1 hidden">

                <li>
                    <a href="{{ route('master.departments.index') }}" data-bs-title="Department"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.departments.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="briefcase" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Department</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.products.index') }}" data-bs-title="Product"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.products.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="package" class=" menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Product</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.models.index') }}"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.models.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="box" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Model</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.processes.index') }}" data-bs-title="Process"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.processes.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="activity" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Process</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.part_numbers.index') }}" data-bs-title="Part Number"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.part_numbers.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="box" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Part Number</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('master.hierarchy.index') }}" data-bs-title="Hierarchy"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                        {{ Route::is('master.hierarchy.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="git-branch" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">Hierarchy</span>
                    </a>
                </li>

                <!-- Document Dropdown -->
                <li>
                    <a type="button" data-bs-title="Document"
                        class="collapse-toggle menu-item w-full flex items-center justify-between px-2 py-3 rounded-l-full hover:bg-gray-200 text-left font-medium
                        {{ Route::is('master.document*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}"
                        data-collapse="documentsDropdown">
                        <div class="flex items-center gap-3">
                            <i data-feather="file-text" class="menu-icon w-4 h-4"></i>
                            <span class="sidebar-text text-gray-700">Document</span>
                        </div>
                        <i data-feather="chevron-right" class="menu-icon w-4 h-4"></i>
                    </a>

                    <ul id="documentsDropdown" class="ml-2 mt-1 hidden space-y-1">
                        <li>
                            <a href="{{ route('master.document-control.index') }}" data-bs-title="Document Control"
                                class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                                {{ Route::is('master.document-control.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                                <i data-feather="settings" class="menu-icon w-4 h-4"></i>
                                <span class="sidebar-text">Control</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('master.document-review.index2') }}" data-bs-title="Document Review"
                                class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                                {{ Route::is('master.document-review.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                                <i data-feather="search" class="menu-icon w-4 h-4"></i>
                                <span class="sidebar-text">Review</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('master.ftpp.index') }}" data-bs-title="FTPP"
                        class="menu-item flex text-sm items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm {{ Route::is('master.ftpp.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="alert-circle" class="menu-icon w-4 h-4"></i>
                        <span class="sidebar-text">FTPP</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('master.users.index') }}" data-bs-title="User"
                        class="flex items-center gap-3 px-2 py-[10px] rounded-l-full hover:bg-gray-200 text-sm
                            {{ Route::is('users.*') ? 'bg-gradient-to-r from-primaryDark to-primary shadow-md text-white font-medium' : 'text-gray-700 hover:text-gray-900' }}">
                        <i data-feather="user" class="w-4 h-4"></i>
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

<style>
    /* Hilangkan underline pada semua link menu-sidebar */
    .menu-item {
        text-decoration: none !important;
    }
</style>
