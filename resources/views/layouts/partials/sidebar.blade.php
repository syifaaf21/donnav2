<!-- ✅ Modern Sidebar with Profile Dropup -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-white border-r border-gray-200 shadow-sm h-screen flex flex-col transition-all duration-300 ease-in-out w-64">

    <!-- 🔹 Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/donna-logo.png') }}" alt="Logo" class="h-8 w-auto transition-all duration-300">
            <span class="font-semibold text-gray-800 text-lg tracking-tight sidebar-text"></span>
        </div>

        <button id="toggleSidebar"
            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 transition">
            <i data-feather="chevron-left" class="w-5 h-5"></i>
        </button>

        <button id="closeSidebarBtn" class="md:hidden">
            <i data-feather="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- 🔹 Menu -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1 text-gray-700 font-medium">
        @include('layouts.partials.menu-sidebar')
    </nav>

    <!-- 🔹 Footer (Profile Dropup) -->
    <!-- Footer Sidebar -->
    <div class="relative border-t bg-gray-50 p-3">
        <div class="flex items-center gap-3 cursor-pointer group" id="profileToggle">
            <!-- Inisial user -->
            <div
                class="w-10 h-10 flex items-center justify-center bg-sky-100 text-sky-700 font-bold rounded-full text-sm">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 sidebar-text">
                <p class="font-semibold text-gray-800 leading-none">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->role->name ?? 'User' }}</p>
            </div>
            <i data-feather="chevron-up" class="w-4 h-4 text-gray-500 transition-transform" id="chevronIcon"></i>
        </div>

        <!-- Dropup menu -->
        <div id="profileDropup"
            class="absolute bottom-16 left-3 right-3 bg-white border rounded-lg shadow-lg p-2 hidden animate-fadeIn">
            <a href="{{ route('profile.index') }}"
                class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                <i data-feather="user" class="w-4 h-4 text-gray-500"></i> Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                    <i data-feather="log-out" class="w-4 h-4"></i> Logout
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- ✅ Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        feather.replace();

        // Sidebar collapse toggle
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleSidebar");
        const sidebarTexts = document.querySelectorAll(".sidebar-text");
        const icon = toggleBtn.querySelector("i");

        // Sidebar collapse
        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("w-64");
            sidebar.classList.toggle("w-20");
            sidebarTexts.forEach(t => t.classList.toggle("hidden"));
            if (sidebar.classList.contains("w-20")) {
                icon.setAttribute("data-feather", "chevron-right");
            } else {
                icon.setAttribute("data-feather", "chevron-left");
            }
            feather.replace();
        });

        // Profile dropup toggle
        const profileToggle = document.getElementById("profileToggle");
        const profileDropup = document.getElementById("profileDropup");
        profileToggle.addEventListener("click", () => {
            profileDropup.classList.toggle("hidden");
        });

        // Dropdown menu toggle (Master Data, Documents, etc.)
        const collapseBtns = document.querySelectorAll("[data-collapse]");
        collapseBtns.forEach(btn => {
            const target = document.getElementById(btn.dataset.collapse);
            const icon = btn.querySelector("i");

            btn.addEventListener("click", () => {
                target.classList.toggle("hidden");
                icon.classList.toggle("rotate-90");
            });
        });
    });
</script>
