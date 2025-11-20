<!-- MODAL DETAIL -->
<div
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 w-screen h-screen overflow-auto bg-black/60 flex items-center justify-center z-[9999]">
    <div class="relative w-[95%] h-[95%] flex justify-center py-8 z-[10000]">

        <div class="bg-white w-[794px] min-h-[1200px] shadow-2xl p-8 rounded-lg" x-html="content"></div>

        <button type="button" @click="isOpen=false"
            class="absolute top-4 right-4 bg-white rounded-full shadow px-3 py-1 text-gray-700 hover:bg-red-500 hover:text-white z-[10001]">
            ✖
        </button>
    </div>
</div>

{{-- <div
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 w-screen h-screen overflow-auto bg-black/60 flex items-center justify-center z-[9999]">

    <div class="relative w-[95%] h-[95%] z-[10000]">

        <!-- PDF PREVIEW -->
        <iframe x-show="pdfUrl"
                :src="pdfUrl"
                class="w-full h-full rounded-lg shadow-xl bg-white"></iframe>

        <!-- Close Button -->
        <button type="button" @click="isOpen=false"
            class="absolute top-4 right-4 bg-white rounded-full shadow px-3 py-1 text-gray-700 hover:bg-red-500 hover:text-white z-[10001]">
            ✖
        </button>
    </div>
</div> --}}

