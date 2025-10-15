<!-- Sidebar Toggle External -->
<button id="sidebarToggleExternal"
    class="fixed top-4 left-4 z-50 w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center md:hidden">
    <i data-feather="menu" class="w-5 h-5"></i>
</button>

<aside id="sidebar"
    class="fixed md:relative z-40 bg-white border-r border-gray-200 shadow-sm h-screen flex flex-col transition-all duration-300 ease-in-out w-64">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 border-b bg-white">
        <div class="flex items-center gap-2">
            <div class="h-24 max-w-36 min-w-10 flex items-center justify-center">
                <img src="{{ asset('images/donna.png') }}" alt="Logo" id="sidebarLogo" style="width: 150px; height: auto;"
                    class="object-contain transition-all duration-300" data-full="{{ asset('images/donna.png') }}"
                    data-icon="{{ asset('images/donna-icon.png') }}" />
            </div>
            <span class="font-semibold text-gray-800 text-lg tracking-tight sidebar-text"></span>
        </div>

        <button id="toggleSidebar"
            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 transition border border-gray-700">
            <i data-feather="chevron-left" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1 text-gray-700 font-medium">
        @include('layouts.partials.menu-sidebar')
    </nav>

    <!-- Footer (Profile Dropup) -->
    <div class="relative border-t bg-white p-3">
        <div class="flex items-center gap-3 cursor-pointer group" id="profileToggle">
            <div class="w-10 h-10 flex items-center justify-center bg-sky-100 text-sky-700 font-bold rounded-full text-sm transition-all duration-300 transform"
                id="profileIcon">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 sidebar-text mt-3">
                <p class="font-semibold text-gray-800 leading-none mb-1">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->role->name ?? 'User' }}</p>
            </div>
        </div>
    </div>
</aside>
