<footer class="mt-2 bg-[#0b1f3a] w-full">
    <div class="px-6 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center">

            <!-- Left + Middle -->
            <div class="flex flex-col md:flex-row items-center gap-6">

                <p class="text-xs text-slate-300 leading-relaxed">
                    &copy; {{ date('Y') }}
                    <span class="font-semibold text-white">Madonna</span> – All rights reserved
                </p>

                <span class="hidden md:block w-px h-5 bg-slate-500/40"></span>

                <div class="flex gap-4 text-xs text-slate-300">
                    <a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a>
                    <a href="{{ route('notifications.index') }}" class="hover:text-white transition">Notifications</a>
                    <a href="{{ route('logout') }}" class="hover:text-white transition">Logout</a>
                </div>
            </div>

            <!-- Right (sejajar dengan Quick Links) -->
            <div class="flex flex-col items-end md:self-center">

                <p class="text-xs text-slate-400">Powered by</p>
                <p class="font-semibold text-white text-xs">AISIN Indonesia Automotive</p>

            </div>

        </div>
    </div>
</footer>
