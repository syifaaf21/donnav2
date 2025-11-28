<!-- Sidebar Content (no outer <aside>, layout provides the <aside>) -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-white rounded-2xl h-screen flex flex-col transition-all duration-300 ease-in-out w-20 m-2 ">

    <div class="flex items-center justify-between px-4">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/madonna.png') }}" alt="Logo" id="sidebarLogo"
                style="width: 32px; height: auto;" class="object-contain transition-all duration-300"
                data-full="{{ asset('images/madonna.png') }}"
                data-icon="{{ asset('images/madonna-logo.png') }}" />
        </div>

        {{-- Make toggle visible on all viewports and add ARIA attributes --}}
        <button id="toggleSidebar" type="button" data-sidebar-toggle
            class="flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 disabled:opacity-50"
            aria-controls="sidebar" aria-expanded="false" aria-pressed="false" aria-label="Toggle sidebar" title="Toggle sidebar">
            <span class="sr-only">Toggle sidebar</span>
            <!-- decorative chevron; aria-hidden since we have an accessible label -->
            <i data-feather="chevron-right" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto">
        @include('layouts.partials.menu-sidebar')
    </nav>

    {{-- footer omitted --}}
</aside>
