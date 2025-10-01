@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="d-flex align-items-center" id="searchForm">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by Document Name, Document Number, or Part Number"
                        value="{{ request('search') }}" style="width: 510px; min-width: 300px;"> <!-- inline style -->

                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="clearSearch">
                        Clear
                    </button>
                </div>
            </form>

            @if (auth()->user()->role->name == 'Admin')
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#addDocumentModal" data-bs-title="Add New Document Review">
                    <i class="bi bi-plus-circle"></i> Add Document Review
                </button>
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
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-building-gear me-1"></i> Plant: {{ ucfirst(strtolower($plant)) }}
                        </h6>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table modern-table align-middle table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Document Name</th>
                                            <th>Document Number</th>
                                            <th>Part Number</th>
                                            <th>File</th>
                                            <th>Department</th>
                                            <th>Reminder Date</th>
                                            <th>Deadline</th>
                                            <th>Status</th>
                                            <th>Version</th>
                                            <th>Notes</th>
                                            <th>Updated By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $mapping)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $mapping->document->name }}</td>
                                                <td>{{ $mapping->document_number }}</td>
                                                <td>{{ $mapping->partNumber->part_number ?? '-' }}</td>
                                                <td>
                                                    @if ($mapping->file_path)
                                                        <a href="{{ asset('storage/' . $mapping->file_path) }}"
                                                            target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-file-earmark-text"></i> View
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>{{ $mapping->department->name ?? '-' }}</td>
                                                <td>{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}
                                                </td>
                                                <td>{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}
                                                </td>

                                                <td>
                                                    @switch($mapping->status->name)
                                                        @case('Approved')
                                                            <span class="badge bg-success">Approved</span>
                                                        @break

                                                        @case('Rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @break

                                                        @case('Need Review')
                                                            <span class="badge bg-warning text-dark">Need Review</span>
                                                        @break

                                                        @default
                                                            <span
                                                                class="badge bg-secondary">{{ $mapping->status->name ?? '-' }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $mapping->version }}</td>
                                                <td>{{ $mapping->notes }}</td>
                                                <td>{{ $mapping->user->name ?? '-' }}</td>

                                                <td class="text-nowrap">
                                                    @if (auth()->user()->role->name == 'Admin')
                                                        {{-- Edit --}}
                                                        <button class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal{{ $mapping->id }}"
                                                            data-bs-title="Edit Metadata">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>


                                                        {{-- Delete --}}
                                                        <form action="{{ route('document-review.destroy', $mapping->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                data-bs-title="Delete Document">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>


                                                        {{-- Revisi --}}
                                                        <button class="btn btn-outline-warning btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#reviseModal{{ $mapping->id }}"
                                                            data-bs-title="Revise Document">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>


                                                        {{-- Approve / Reject --}}
                                                        @if ($mapping->status->name == 'Need Review')
                                                            {{-- Tombol Approve buka modal --}}
                                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#approveModal{{ $mapping->id }}"
                                                                data-bs-title="Approve Document">
                                                                <i class="bi bi-check2-circle"></i>
                                                            </button>

                                                            {{-- Tombol Reject tetap form --}}
                                                            <form
                                                                action="{{ route('document-review.reject', $mapping->id) }}"
                                                                method="POST" class="d-inline reject-form">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                    data-bs-title="Reject Document">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            </form>
                                                        @elseif ($mapping->status->name == 'Approved')
                                                            {{-- Sudah Approved --}}
                                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                                disabled>
                                                                <i class="bi bi-check2-all"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                                disabled>
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        @elseif ($mapping->status->name == 'Rejected')
                                                            {{-- Sudah Rejected --}}
                                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                                disabled>
                                                                <i class="bi bi-check2-circle"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                                disabled>
                                                                <i class="bi bi-x-circle-fill"></i>
                                                            </button>
                                                        @else
                                                            {{-- Status lain --}}
                                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                                <i class="bi bi-slash-circle"></i>
                                                            </button>
                                                        @endif
                                                    @else
                                                        {{-- User hanya revisi --}}
                                                        <button class="btn btn-outline-warning btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#reviseModal{{ $mapping->id }}">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Include modal --}}
                                            @include('contents.document-review.modal-edit')
                                            @include('contents.document-review.modal-revise')
                                            @include('contents.document-review.modal-approve')
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {!! $documents->withQueryString()->links() !!}
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal Add --}}
        @include('contents.document-review.modal-add')
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

        document.getElementById('clearSearch')?.addEventListener('click', function() {
            const form = document.getElementById('searchForm');
            if (!form) return;

            // Kosongkan input search
            form.querySelector('input[name="search"]').value = '';

            // Submit form tanpa parameter search
            form.submit();
        });
    </script>
@endpush
