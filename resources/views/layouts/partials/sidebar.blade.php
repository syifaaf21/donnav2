<!-- ✅ Sidebar -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-white border-r border-gray-200 shadow-sm h-screen flex flex-col sidebar-transition w-64 transition-all duration-300 ease-in-out">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b">
        <div class="flex items-center space-x-2">
            <img src="{{ asset('images/donna-logo.png') }}" alt="Logo" class="h-8 sidebar-logo transition-all duration-300">
            <span class="font-bold text-lg sidebar-text transition-all duration-300"></span>
        </div>
        <button id="toggleSidebar"
            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 transition">
            <i data-feather="chevron-left"></i>
        </button>
        <button id="closeSidebarBtn" class="md:hidden">
            <i data-feather="x"></i>
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto py-4">
        @include('layouts.partials.menu-sidebar')
    </div>
</aside>

<!-- ✅ Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        feather.replace();

        // Collapsible submenus
        const collapseBtns = document.querySelectorAll("[data-collapse]");
        collapseBtns.forEach(btn => {
            const target = document.getElementById(btn.dataset.collapse);
            btn.addEventListener("click", () => {
                target.classList.toggle("hidden");
                btn.classList.toggle("open");
            });
        });

        // ✅ Sidebar collapse/expand
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleSidebar");
        const texts = document.querySelectorAll(".sidebar-text");
        const logo = document.querySelector(".sidebar-logo");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("w-64");
            sidebar.classList.toggle("w-20");

            texts.forEach(text => text.classList.toggle("hidden"));
            logo.classList.toggle("hidden");

            const icon = toggleBtn.querySelector("i");
            if (sidebar.classList.contains("w-20")) {
                icon.setAttribute("data-feather", "chevron-right");
            } else {
                icon.setAttribute("data-feather", "chevron-left");
            }
            feather.replace();
        });
    });
</script>

<style>
    .sidebar-transition {
        transition: all 0.3s ease-in-out;
    }

    /* Hide scrollbar for aesthetic */
    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    #sidebar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
