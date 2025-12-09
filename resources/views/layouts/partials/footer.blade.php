<footer class="mt-2 px-4 w-full bg-gradient-to-b from-[#0b1f3a] via-[#0e2a52] to-[#113563] rounded-t-3xl shadow shadow-black/30">
    <div class="max-w-screen-xl mx-auto px-2 py-2">

        <div class="flex flex-col md:flex-row justify-between items-center gap-4">

            <!-- Left + Quick Links -->
            <div class="flex flex-col md:flex-row items-center gap-6 text-center md:text-left">

                <p class="text-xs mt-3 text-slate-300 leading-relaxed">
                    &copy; {{ date('Y') }}
                    <span class="font-semibold text-white">Madonna</span> — All rights reserved
                </p>

                <span class="hidden md:block w-px h-5 bg-slate-400/20 mx-3"></span>

                <div class="flex gap-4 text-xs text-slate-300 font-medium items-center">
                    <a href="{{ route('dashboard') }}" class="text-white hover:text-white transition">Dashboard</a>
                    <a href="{{ route('notifications.index') }}" class="text-white  hover:text-white transition">Notifications</a>
                    <a href="{{ route('logout') }}" class="text-white hover:text-white transition" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

            </div>

            <!-- Right -->
            <div class="flex justify-between items-center text-center mt-3 md:text-right">
                <p class="text-xs text-slate-300/60 mr-2">Copy Right</p>
                <p class="font-semibold text-white text-xs whitespace-nowrap">
                    AISIN Indonesia Automotive
                </p>
            </div>
        </div>
    </div>
</footer>
