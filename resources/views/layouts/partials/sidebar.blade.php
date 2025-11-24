<!-- Sidebar Content (no outer <aside>, layout provides the <aside>) -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-sidebar  rounded h-screen flex flex-col transition-all duration-300 ease-in-out w-64">

    <div class=" shadow-lg flex items-center justify-between px-4 border-b rounded-lg">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/madonna-inverse.png') }}" alt="Logo" id="sidebarLogo" style="width: 150px; height: auto;"
                class="object-contain transition-all duration-300" data-full="{{ asset('images/madonna-inverse.png') }}"
                data-icon="{{ asset('images/madonna-logo.png') }}"/>
        </div>

        <button id="toggleSidebar"
            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-200 transition border border-gray-200 rotate">
            <i data-feather="chevron-left"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto">
        @include('layouts.partials.menu-sidebar')
    </nav>

    <!-- Footer User Info -->
    <div class="relative border-t pl-6 py-2">
        <div class="flex items-center gap-3 cursor-pointer group">
            <div class="w-10 h-10 flex items-center justify-center bg-sky-100 text-sky-700 font-bold rounded-full text-sm transition-all duration-300 transform"
                id="profileIcon">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="ml-2">
                <div x-show="open" class="sidebar-text mt-3">
                    <p class="font-semibold text-gray-200 leading-none mb-1">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-100">{{ Auth::user()->role->name ?? 'User' }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
