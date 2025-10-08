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
                @include('contents.master.document-review.partials.modal-add')
            @endif
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
                            <div class="table-responsive" style="max-height: 500px; overflow: auto;">
                                <table class="table table-sm modern-table align-middle table-hover mb-0"
                                    style="min-width: 1400px; white-space: nowrap;">


                                    @include('contents.master.document-review.partials.table-header')

                                    @php
                                        $parents = $documents->filter(
                                            fn($doc) => $doc->document && is_null($doc->document->parent_id),
                                        );
                                    @endphp

                                    @if ($parents->isEmpty())
                                        <tr>
                                            <td colspan="14" class="text-center text-muted py-4">
                                                <i class="bi bi-folder-x fs-4 d-block"></i>
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
                                                    'rowNumber' => $loop->iteration, // misalnya 1, 2, 3
                                                    'depth' => 0,
                                                    'numbering' => $loop->iteration . '', // kirim '1', '2', '3'
                                                ]
                                            )
                                        @endforeach
                                    @endif
                                </table>

                                {{-- Render semua modal di luar table supaya ga kedip --}}
                                @foreach ($documents as $doc)
                                    @include('contents.master.document-review.partials.modal-edit', [
                                        'mapping' => $doc,
                                    ])
                                    {{-- @include('contents.master.document-review.partials.modal-revise', [
                                        'mapping' => $doc,
                                    ])
                                    @include('contents.master.document-review.partials.modal-approve', [
                                        'mapping' => $doc,
                                    ]) --}}
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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


@endsection

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
@push('scripts')
    <x-sweetalert-confirm />

    <script>
        // Autofill Department
        // const docSelect = document.getElementById('documentSelect');
        // const deptField = document.getElementById('departmentField');
        // docSelect?.addEventListener('change', function() {
        //     deptField.value = this.options[this.selectedIndex].dataset.department || '';
        // });

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

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file;
                    const extension = fileUrl.split('.').pop().toLowerCase();

                    // Cek format file
                    if (extension === 'pdf') {
                        iframe.src = fileUrl;
                    } else if (['doc', 'docx', 'xls', 'xlsx'].includes(extension)) {
                        iframe.src =
                            `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;
                    } else {
                        iframe.src = '';
                        alert('File format not supported for preview.');
                    }
                });
            });

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
