<!-- Sidebar Content (no outer <aside>, layout provides the <aside>) -->
<aside id="sidebar"
    class="fixed md:relative z-40 bg-sidebar h-screen flex flex-col transition-all duration-300 ease-in-out">

    <div class="flex items-center justify-between px-4 border-b">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/madonna1.png') }}" class="h-14 object-contain">
        </div>

        <button id="toggleSidebar"
            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-200 transition border border-gray-200">
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


{{-- SCRIPT SIDEBAR TOGGLING --}}
<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggles = document.querySelectorAll(".collapse-toggle");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", () => {
            const targetId = toggle.getAttribute("data-target");
            const menu = document.getElementById(targetId);

            if (!menu) return;

            // show/hide
            menu.classList.toggle("hidden");

            // rotate chevron
            const icon = toggle.querySelector("i[data-feather='chevron-right']");
            if (icon) {
                icon.style.transition = "transform .2s";
                icon.style.transform = menu.classList.contains("hidden")
                    ? "rotate(0deg)"
                    : "rotate(90deg)";
            }
        });
    });
});
</script>
