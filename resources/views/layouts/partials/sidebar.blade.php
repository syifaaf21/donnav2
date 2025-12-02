<!-- Sidebar Content (no outer <aside>, layout provides the <aside>) -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-white rounded-2xl h-screen flex flex-col transition-all duration-300 ease-in-out w-20 m-2">

    <div class="flex items-center justify-between px-4">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/madonna.png') }}" alt="Logo" id="sidebarLogo"
                style="width: 32px; height: auto;" class="object-contain transition-all duration-300"
                data-full="{{ asset('images/madonna.png') }}"
                data-icon="{{ asset('images/madonna-logo.png') }}" />
        </div>

        {{-- Collapse button visible only when sidebar is expanded (JS will toggle hidden class) --}}
        <button id="toggleSidebar" type="button"
            class="flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 hidden"
            aria-controls="sidebar" aria-expanded="true" aria-pressed="false" aria-label="Collapse sidebar" title="Collapse sidebar">
            <span class="sr-only">Collapse sidebar</span>
            <i data-feather="chevron-left" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto">
        @include('layouts.partials.menu-sidebar')
    </nav>

    {{-- footer omitted --}}
</aside>
