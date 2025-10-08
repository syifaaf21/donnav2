@extends('layouts.app2')
@section('title', 'Document Review')

@section('content')
    <div class="container">
        <div x-data="{ activeTab: '{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}' }" class="w-full">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                @foreach ($groupedByPlant as $plant => $documents)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <button @click="activeTab = '{{ $slug }}'"
                        :class="activeTab === '{{ $slug }}'
                            ?
                            'bg-blue-500 text-white border-blue-500' :
                            'bg-white text-gray-600 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-t-lg border border-gray-200 text-sm font-medium transition">
                        <i data-feather="settings" class="inline w-4 h-4 mr-1"></i>
                        {{ ucfirst(strtolower($plant)) }}
                    </button>
                @endforeach
            </div>

            <!-- Tab Contents -->
            <div>
                @foreach ($groupedByPlant as $plant => $documents)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <div x-show="activeTab === '{{ $slug }}'" x-transition>
                        {{-- Table --}}
                        <div class="overflow-auto rounded-lg shadow bg-white">
                            <table class="min-w-full text-sm text-left text-gray-700">
                                @include('contents.master.document-review.partials.table-header')

                                @php
                                    $parents = $documents->filter(
                                        fn($doc) => $doc->document && is_null($doc->document->parent_id),
                                    );
                                @endphp

                                @if ($parents->isEmpty())
                                    <tr>
                                        <td colspan="14" class="text-center py-8 text-gray-400">
                                            <i data-feather="folder-x" class="mx-auto w-6 h-6 mb-1"></i>
                                            No Document found for this tab.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($parents as $index => $parent)
                                        @include(
                                            'contents.master.document-review.partials.nested-row-recursive',
                                            [
                                                'mapping' => $parent,
                                                'documents' => $documents,
                                                'loopIndex' => 'parent-' . $index,
                                                'rowNumber' => $loop->iteration,
                                                'depth' => 0,
                                                'numbering' => $loop->iteration . '',
                                            ]
                                        )
                                    @endforeach
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <x-sweetalert-confirm />

        <script>
            // Autofill Department
            const docSelect = document.getElementById('documentSelect');
            const deptField = document.getElementById('departmentField');
            docSelect?.addEventListener('change', function() {
                deptField.value = this.options[this.selectedIndex].dataset.department || '';
            });

            document.addEventListener('DOMContentLoaded', function() {
                const tabButtons = document.querySelectorAll('#plantTabs button');

                function filterPartNumbersFor(selectElement, plantName) {
                    const options = selectElement.querySelectorAll('option');
                    options.forEach(opt => {
                        const plant = opt.dataset.plant?.trim().toLowerCase();
                        if (opt.selected) {
                            // jangan sembunyikan yang selected
                            opt.style.display = '';
                        } else {
                            opt.style.display = (!plantName || plant === plantName.toLowerCase() || opt
                                .value === '') ? '' : 'none';
                        }
                    });
                    if (!Array.from(options).some(o => o.selected)) {
                        selectElement.value = '';
                    }
                }

                function applyFilterToAllModals(plantName) {
                    // Add modal
                    const addSelect = document.getElementById('addPartNumberSelect');
                    if (addSelect) filterPartNumbersFor(addSelect, plantName);

                    // Edit modals
                    document.querySelectorAll('[id^="editPartNumberSelect"]').forEach(editSelect => {
                        filterPartNumbersFor(editSelect, plantName);
                    });
                }

                // filter saat halaman load sesuai tab aktif
                const firstTab = document.querySelector('#plantTabs button.active');
                if (firstTab) applyFilterToAllModals(firstTab.textContent.trim());

                tabButtons.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const plant = this.textContent.trim();
                        applyFilterToAllModals(plant);
                        localStorage.setItem('activePlantTab', this.id);
                    });
                });

                // restore tab terakhir jika reload
                const savedTabId = localStorage.getItem('activePlantTab');
                if (savedTabId) {
                    const savedBtn = document.getElementById(savedTabId);
                    const savedPane = document.querySelector(savedBtn?.dataset.bsTarget);
                    if (savedBtn && savedPane) {
                        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
                        document.querySelectorAll('#plantTabs button').forEach(b => b.classList.remove('active'));
                        savedBtn.classList.add('active');
                        savedPane.classList.add('show', 'active');
                        applyFilterToAllModals(savedBtn.textContent.trim());
                    }
                }
            });

            // tooltip
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
                tooltipTriggerList.map(function(el) {
                    return new bootstrap.Tooltip(el, {
                        title: el.getAttribute('data-bs-title'),
                        placement: 'top',
                        trigger: 'hover'
                    });
                });
            });
            document.addEventListener('DOMContentLoaded', function() {
                // 🔍 Clear Search
                document.getElementById('clearSearch')?.addEventListener('click', function() {
                    const form = document.getElementById('searchForm');
                    if (!form) return;

                    // Hapus input search
                    form.querySelector('input[name="search"]').value = '';

                    // Submit ulang tanpa search
                    form.submit();
                });

                // 🧹 Clear Filter
                document.getElementById('clearFilters')?.addEventListener('click', function() {
                    const form = document.getElementById('filterForm');
                    if (!form) return;

                    // Kosongkan semua input & select
                    form.querySelectorAll('input, select').forEach(el => el.value = '');

                    // Submit form untuk reset filter
                    form.submit();
                });
            });
            //View File in tab
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('viewFileModal');
                const iframe = document.getElementById('fileViewer');

                // Ketika tombol View diklik
                document.querySelectorAll('.view-file-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const fileUrl = this.dataset.file;
                        iframe.src = fileUrl;
                    });
                });

                // Reset iframe saat modal ditutup
                modal.addEventListener('hidden.bs.modal', () => {
                    iframe.src = '';
                });
            });

            // in form message
            document.addEventListener('DOMContentLoaded', function() {
                // Ambil semua form yang butuh validasi
                const forms = document.querySelectorAll('.needs-validation');

                Array.from(forms).forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault(); // Stop form submit
                            event.stopPropagation();
                        }

                        form.classList.add('was-validated'); // Tambahkan class validasi Bootstrap
                    }, false);
                });
            });
        </script>
    @endpush
@endsection
