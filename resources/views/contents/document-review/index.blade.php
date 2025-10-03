@extends('layouts.app')
@section('title', 'Document Review')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- 🔍 Search + Filter --}}
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap" id="searchForm">
                <div class="input-group" style="width: 600px; max-width: 100%;">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search by Document Name, Document Number, or Part Number"
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearSearch">
                        Clear
                    </button>
                </div>

                {{-- 🎯 Filter --}}
                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>

            @if (auth()->user()->role->name == 'Admin')
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#addDocumentModal" data-bs-title="Add New Document Review">
                    <i class="bi bi-plus-circle"></i> Add Document Review
                </button>
            @endif
        </div>


        <!-- Modal Filter -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="GET" id="filterForm">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold" id="filterModalLabel">
                                <i class="bi bi-funnel me-2 text-primary"></i> Filter Documents
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-3">
                            {{-- Status --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="Need Review" {{ request('status') == 'Need Review' ? 'selected' : '' }}>
                                        Need Review</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                            </div>

                            {{-- Department --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Deadline --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deadline</label>
                                <input type="date" name="deadline" class="form-control"
                                    value="{{ request('deadline') }}">
                            </div>
                        </div>
                        <div class="modal-footer border-0 justify-content-between bg-light rounded-bottom-4">
                            <button type="button" id="clearFilters" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-funnel"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabs per Plant --}}
        <ul class="nav nav-tabs mb-3" id="plantTabs" role="tablist">
            @foreach ($groupedByPlant as $plant => $documents)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif"
                        id="{{ \Illuminate\Support\Str::slug($plant) }}-tab" data-bs-toggle="tab"
                        data-bs-target="#{{ \Illuminate\Support\Str::slug($plant) }}" type="button" role="tab">
                        <i class="bi bi-building-gear me-1"></i>{{ ucfirst(strtolower($plant)) }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="plantTabsContent">
            @foreach ($groupedByPlant as $plant => $documents)
                <div class="tab-pane fade @if ($loop->first) show active @endif"
                    id="{{ \Illuminate\Support\Str::slug($plant) }}" role="tabpanel">

                    <div class="table-wrapper">
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                @include('contents.document-review.partials.nested-rows')
                                </div>
                                {{-- <div class="mt-3">
                                    {!! $documents->withQueryString()->links() !!}
                                </div> --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Modal Add --}}
            @include('contents.document-review.partials.modal-add')
        </div>
        <!-- 📄 Modal Fullscreen View File -->
        <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content border-0 rounded-0 shadow-none">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-semibold">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i> Document Viewer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0">
                        <iframe id="fileViewer" src="" width="100%" height="100%" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
        {{-- Snackbar Bulk Action --}}
        <div id="snackbar" class="snackbar shadow-lg d-flex justify-content-between align-items-center">
            <div>
                <span id="selectedCount">0 selected</span>
            </div>

            <form id="bulkDeleteForm" action="{{ route('bulkDestroy') }}" method="POST" class="mb-0">
                @csrf
                {{-- container untuk input hidden ids[] yang akan dibuat oleh JS --}}
                <div id="bulkIdsContainer"></div>

                <button id="bulkDeleteBtn" type="submit" class="btn btn-outline-danger btn-sm" disabled>
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </form>
        </div>
    @endsection

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


            // Snackbar script
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('selectAll');
                const snackbar = document.getElementById('snackbar');
                const selectedCount = document.getElementById('selectedCount');
                const bulkDeleteForm = document.getElementById('bulkDeleteForm');
                const bulkIdsContainer = document.getElementById('bulkIdsContainer');
                const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

                function getRowCheckboxes() {
                    return Array.from(document.querySelectorAll('.row-checkbox'));
                }

                function updateSnackbar() {
                    const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                    selectedCount.textContent = `${checkedBoxes.length} selected`;
                    snackbar.classList.toggle('show', checkedBoxes.length > 0);
                    bulkDeleteBtn.disabled = checkedBoxes.length === 0;
                }

                // master checkbox handler
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        getRowCheckboxes().forEach(cb => cb.checked = this.checked);
                        updateSnackbar();
                    });
                }

                // listen perubahan pada checkbox (event delegation)
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.classList && e.target.classList.contains('row-checkbox')) {
                        // if any manual uncheck, uncheck master
                        if (!e.target.checked && selectAll) selectAll.checked = false;

                        // if all are checked, set master checked
                        const all = getRowCheckboxes();
                        if (selectAll && all.length > 0) {
                            selectAll.checked = all.every(cb => cb.checked);
                        }

                        updateSnackbar();
                    }
                });

                // on submit: build hidden inputs ids[] then submit
                bulkDeleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const checkedBoxes = getRowCheckboxes().filter(cb => cb.checked);
                    if (checkedBoxes.length === 0) {
                        alert('No documents selected.');
                        return;
                    }

                    if (!confirm(
                            `Are you sure you want to delete ${checkedBoxes.length} selected document(s)?`)) {
                        return;
                    }

                    // clear previous inputs
                    bulkIdsContainer.innerHTML = '';

                    // create hidden inputs ids[]
                    checkedBoxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkIdsContainer.appendChild(input);
                    });

                    // submit native
                    bulkDeleteForm.submit();
                });

                // init
                updateSnackbar();
            });
        </script>
    @endpush
