<footer
  class="mt-12 mx-5 transition-all duration-300 hover:bg-gradient-to-b hover:from-[#f0f7ff] hover:to-[#eaf4ff]">
  <div class="px-4 ">
    <!-- Main Footer Content -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
      <div class="flex justify-center md:justify-start items-center">
        <p class="text-xs text-slate-600 leading-relaxed text-center md:text-left">
          &copy; {{ date('Y') }}
          <span class="font-semibold text-sky-600">Madonna</span>
          – All rights reserved
        </p>
      </div>
      <!-- Brand Section -->
      <div class="flex flex-col items-start md:items-center md:col-span-1">
        <div class="flex items-center gap-2 mb-2">
          {{-- <div
            class="w-8 h-8 bg-gradient-to-tr from-primary to-primaryDark rounded-lg flex items-center justify-center"
          >
            <svg
              class="w-5 h-5 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
          </div> --}}

          <span class="font-bold text-slate-800 text-lg">Madonna</span>
        </div>
        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
          Management of Document and Operation Network Aisin
        </p>
      </div>

      <!-- Powered By Section -->
      <div class="flex flex-col items-end md:items-center md:col-span-1">
        <p class="text-xs text-slate-500 mb-1">Powered by</p>
        <p class="font-semibold text-slate-700 text-sm">AISIN Indonesia Automotive</p>
      </div>
    </div>
  </div>
</footer>
