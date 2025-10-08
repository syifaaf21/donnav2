<ul class="space-y-1 px-2">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ request()->is('/') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
                    <i data-feather="home" class="w-5 h-5 text-blue-500"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>

            <li class="mt-4">
                <h6 class="text-xs uppercase text-gray-400 font-semibold sidebar-text px-3">Docs</h6>
            </li>

            <li>
                <a href="{{ route('document-control.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ Route::is('document-control*') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
                    <i data-feather="settings" class="w-5 h-5 text-teal-500"></i>
                    <span class="sidebar-text">Document Control</span>
                </a>
            </li>

            <li>
                <a href="{{ route('document-review.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all {{ Route::is('document-review*') ? 'bg-gray-100 font-semibold text-gray-800' : '' }}">
                    <i data-feather="check-square" class="w-5 h-5 text-gray-600"></i>
                    <span class="sidebar-text">Document Review</span>
                </a>
            </li>

            <hr class="my-2 border-gray-200">

            <!-- Master Data -->
            <li>
                <button type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 text-left font-medium sidebar-transition"
                    data-collapse="masterDataMenu">
                    <div class="flex items-center gap-3">
                        <i data-feather="database" class="w-5 h-5 text-red-500"></i>
                        <span class="sidebar-text">Master Data</span>
                    </div>
                    <i data-feather="chevron-right" class="w-4 h-4 transition-transform group-[.open]:rotate-90"></i>
                </button>

                <!-- Submenu -->
                <ul id="masterDataMenu" class="ml-6 mt-1 hidden space-y-1">
                    <li>
                        <a href="{{ route('master.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('users.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-600' }}">
                            <i data-feather="user" class="w-4 h-4 text-blue-400"></i>
                            <span class="sidebar-text">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.part_numbers.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('part_numbers.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-600' }}">
                            <i data-feather="box" class="w-4 h-4 text-green-500"></i>
                            <span class="sidebar-text">Part Numbers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.hierarchy.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('hierarchy.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-600' }}">
                            <i data-feather="git-branch" class="w-4 h-4 text-yellow-500"></i>
                            <span class="sidebar-text">Hierarchy</span>
                        </a>
                    </li>
                    <!-- ✅ Documents Dropdown -->
                    <li>
                        <button type="button"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-left font-medium sidebar-transition"
                            data-collapse="documentsDropdown">
                            <div class="flex items-center gap-3">
                                <i data-feather="file-text" class="w-4 h-4 text-pink-500"></i>
                                <span class="sidebar-text">Documents</span>
                            </div>
                            <i data-feather="chevron-right" class="w-4 h-4 transition-transform"></i>
                        </button>

                        <ul id="documentsDropdown" class="ml-6 mt-1 hidden space-y-1">
                            <li>
                                <a href="{{ route('master.document-control.index') }}"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('document-control.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-600' }}">
                                    <i data-feather="settings" class="w-4 h-4 text-cyan-500"></i>
                                    <span class="sidebar-text">Control</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master.document-review.index') }}"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm {{ Route::is('document-review.*') ? 'bg-blue-100 text-gray-800' : 'text-gray-600' }}">
                                    <i data-feather="search" class="w-4 h-4 text-gray-500"></i>
                                    <span class="sidebar-text">Review</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
