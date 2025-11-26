<!-- MODAL DETAIL -->
<div x-show="isOpen" x-cloak
    class="fixed inset-0 w-screen h-screen overflow-auto bg-black/60 flex items-center justify-center z-[9999]">
    <!-- Close button di luar container -->
    <button type="button" @click="close()"
        class="absolute top-4 right-4 bg-white rounded-full shadow px-3 py-1 text-gray-700
               hover:bg-red-500 hover:text-white z-[10001]">
        <i class="bi bi-x"></i>
    </button>

    <!-- Container modal -->
    <div class="relative w-[95%] h-[90%] z-[10000]">

        <!-- PDF PREVIEW -->
        <iframe x-show="pdfUrl" :src="pdfUrl" id="previewFrame"
            class="w-full h-full rounded-lg shadow-xl bg-white" frameborder="0"></iframe>

        <!-- Loading -->
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center">
            <div class="bg-white px-4 py-2 rounded shadow">Loading preview...</div>
        </div>

    </div>
</div>
