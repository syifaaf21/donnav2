<!-- CARD WRAPPER -->
<div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg">
    <div @if ($readonly) class="opacity-90 pointer-events-none select-none" @endif>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <th class="w-56 px-4 py-2 font-semibold bg-gray-50">Audit Type</th>
                        <td class="px-4 py-2">
                            @foreach ($auditTypes as $type)
                                <template x-if="form.audit_type_id == {{ $type->id }}">
                                    <span>{{ $type->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Sub Audit Type</th>
                        <td class="px-4 py-2">
                            @foreach ($subAudit as $sub)
                                <template x-if="form.sub_audit_type_id == {{ $sub->id }}">
                                    <span>{{ $sub->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Department / Process / Product</th>
                        <td class="px-4 py-2" x-text="form._plant_display ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Auditee</th>
                        <td class="px-4 py-2">
                            <div id="selectedAuditees" x-html="form._auditee_html ?? '-'" class="flex flex-wrap gap-2">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Auditor / Inisiator</th>
                        <td class="px-4 py-2">
                            @foreach ($auditors as $auditor)
                                <template x-if="form.auditor_id == {{ $auditor->id }}">
                                    <span>{{ $auditor->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Date</th>
                        <td class="px-4 py-2" x-text="form.created_at ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Registration Number</th>
                        <td class="px-4 py-2" x-text="form.registration_number ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Finding Category</th>
                        <td class="px-4 py-2">
                            @foreach ($findingCategories as $category)
                                <template x-if="form.finding_category_id == {{ $category->id }}">
                                    <span>{{ ucfirst($category->name) }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Finding / Issue</th>
                        <td class="px-4 py-2 whitespace-pre-line" x-text="form.finding_description ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Duedate</th>
                        <td class="px-4 py-2" x-text="form.due_date ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Clauses/Categories</th>
                        <td class="px-4 py-2">
                            <div id="selectedSubContainer" class="flex flex-wrap gap-2"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="px-4 py-2 font-semibold bg-gray-50 align-top">Attachments</div>
        <div class="px-4 py-2">
            <div id="previewImageContainer" class="flex flex-wrap gap-2"></div>
            <div id="previewFileContainer" class="mt-2 flex flex-wrap gap-2"></div>
        </div>
    </div>
</div>

<!-- Modal Preview File -->
<div id="previewModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
    style="z-index: 9999;">
    <div id="previewModalContent"
         class="bg-white rounded-lg flex flex-col overflow-hidden shadow-2xl transition-all duration-300"
         style="z-index: 10000;">
        <div class="flex justify-between items-center px-6 py-4 border-b bg-white">
            <h3 class="font-semibold text-lg truncate max-w-md" id="previewTitle">Preview</h3>
            <button onclick="closePreviewModal()"
                    class="text-gray-500 hover:text-gray-700 transition ml-4 flex-shrink-0">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div id="previewContentArea" class="flex-1 overflow-hidden bg-gray-100 flex items-center justify-center">
            <!-- PDF / FILE FRAME -->
            <iframe id="previewFrame" class="w-full h-full border-0 hidden"></iframe>

            <!-- IMAGE PREVIEW -->
            <img id="previewImage" src="" class="hidden max-w-full max-h-full object-contain" alt="Preview">
        </div>
        <div class="px-6 py-4 border-t bg-white flex gap-3">
            <a id="previewDownloadBtn" href="#" download
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition shadow-sm">
                <i data-feather="download" class="w-4 h-4"></i> Download
            </a>
            <a id="previewOpenBtn" href="#" target="_blank"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center gap-2 transition shadow-sm">
                <i data-feather="external-link" class="w-4 h-4"></i> Open in New Tab
            </a>
        </div>
    </div>
</div>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();

    // ===== UNIFIED PREVIEW MODAL =====
    function showFilePreviewModal(url, filename) {
        const modal = document.getElementById('previewModal');
        const modalContent = document.getElementById('previewModalContent');
        const contentArea = document.getElementById('previewContentArea');
        const frame = document.getElementById('previewFrame');
        const image = document.getElementById('previewImage');
        const title = document.getElementById('previewTitle');
        const downloadBtn = document.getElementById('previewDownloadBtn');
        const openBtn = document.getElementById('previewOpenBtn');

        // Reset visibility
        frame.classList.add('hidden');
        image.classList.add('hidden');

        // Check if image or file
        if (filename.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
            // Show image
            image.src = url;
            image.classList.remove('hidden');
            title.textContent = filename;

            // Modal size for images - lebih compact
            modalContent.style.width = 'auto';
            modalContent.style.minWidth = '400px';
            modalContent.style.maxWidth = '90vw';
            modalContent.style.height = 'auto';
            modalContent.style.maxHeight = '90vh';
            contentArea.style.padding = '2rem';
            contentArea.style.maxHeight = 'calc(90vh - 140px)'; // minus header & footer
        } else {
            // Show iframe for PDF and other files
            frame.src = url;
            frame.classList.remove('hidden');
            title.textContent = filename;

            // Modal size for documents - lebih lebar
            modalContent.style.width = '95vw';
            modalContent.style.minWidth = 'auto';
            modalContent.style.maxWidth = '1400px';
            modalContent.style.height = '95vh';
            modalContent.style.maxHeight = '95vh';
            contentArea.style.padding = '0';
            contentArea.style.maxHeight = 'none';
        }

        downloadBtn.href = url;
        downloadBtn.download = filename;
        openBtn.href = url;

        modal.classList.remove('hidden');

        // Re-render feather icons
        setTimeout(() => {
            if (typeof feather !== 'undefined') feather.replace();
        }, 100);
    }

    function showImagePreviewModal(url, filename) {
        showFilePreviewModal(url, filename);
    }

    function closePreviewModal() {
        const modal = document.getElementById('previewModal');
        const frame = document.getElementById('previewFrame');
        const image = document.getElementById('previewImage');

        modal.classList.add('hidden');
        frame.src = '';
        image.src = '';
        frame.classList.add('hidden');
        image.classList.add('hidden');
    }

    function closeFilePreviewModal() {
        closePreviewModal();
    }

    function closeImagePreviewModal() {
        closePreviewModal();
    }

    // Close modal when clicking outside
    document.getElementById('previewModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePreviewModal();
    });

    // Close with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewModal();
        }
    });
</script>
