@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    <div class="p-6 bg-white min-h-screen space-y-6">
        <x-flash-message />
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Document Review</li>
            </ol>
        </nav>

        <!-- Search + Filter bar -->
        <div class="flex justify-end items-center mb-4 space-x-3">
            <input type="text" id="live-search" placeholder="Search..."
                class="border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300 w-64" />
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel-fill mr-1"></i> Filter
            </button>
        </div>

        <!-- Table card -->
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <!-- Tabs for each Plant tetap di luar #search-results -->
            <ul class="nav nav-tabs" id="plantTabs" role="tablist">
                @foreach ($plants as $plant)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ Str::slug($plant) }}"
                            data-bs-toggle="tab" data-bs-target="#content-{{ Str::slug($plant) }}" type="button"
                            role="tab" aria-controls="content-{{ Str::slug($plant) }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ ucwords($plant) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Konten tabel dibungkus #search-results -->
            <div id="search-results">
                <div class="tab-content mt-3" id="plantTabsContent">
                    @foreach ($plants as $plant)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="content-{{ Str::slug($plant) }}" role="tabpanel"
                            aria-labelledby="tab-{{ Str::slug($plant) }}">

                            <div class="plant-results">
                                @if (isset($groupedByPlant[$plant]) && $groupedByPlant[$plant]->count() > 0)
                                    @include('contents.document-review.partials.table', [
                                        'groupedData' => $groupedByPlant[$plant],
                                    ])
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <p class="text-sm">No data available for {{ ucwords($plant) }} plant.</p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-lg">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" action="{{ route('document-review.index') }}">
                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Plant -->
                            <div class="col-md-4">
                                <label for="plant" class="form-label">Plant</label>
                                <select name="plant" id="plant" class="form-select select2">
                                    <option value="">All Plants</option>
                                    @foreach ($plants as $plant)
                                        <option value="{{ $plant }}"
                                            {{ request('plant') == $plant ? 'selected' : '' }}>
                                            {{ ucwords($plant) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="col-md-4">
                                <label for="department" class="form-label">Department</label>
                                <select name="department" id="department" class="form-select select2">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Document -->
                            <div class="col-md-4">
                                <label for="document_id" class="form-label">Document</label>
                                <select name="document_id" id="document_id" class="form-select select2">
                                    <option value="">All Documents</option>
                                    @foreach ($documentsMaster as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ request('document_id') == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Part Number -->
                            <div class="col-md-6">
                                <label for="part_number" class="form-label">Part Number</label>
                                <select name="part_number" id="part_number" class="form-select select2">
                                    <option value="">Select Part Number</option>
                                    {{-- Options will be loaded dynamically --}}
                                </select>
                            </div>

                            <!-- Process -->
                            <div class="col-md-6">
                                <label for="process" class="form-label">Process</label>
                                <select name="process" id="process" class="form-select select2">
                                    <option value="">All Processes</option>
                                    @foreach ($processes as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ request('process') == $id ? 'selected' : '' }}>
                                            {{ ucwords($name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('document-review.index') }}" class="btn btn-secondary">Clear</a>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- File Viewer Modal -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content d-flex flex-column" style="height: 100vh;">
                <!-- Modal Header -->
                <div class="modal-header justify-content-between">
                    <h5 class="modal-title">File Preview</h5>
                    @php
                        $role = auth()->user()?->role?->name;
                    @endphp

                    @if (in_array($role, ['Admin', 'Super Admin']))
                        <div class="p-3 d-flex gap-2 bg-light border-bottom">
                            <!-- Approve Button -->
                            <form action="/approve-url" method="POST" class="m-0">
                                @csrf
                                <button type="button" class="btn btn-outline-success btn-sm open-approve-modal"
                                    data-doc-id="{{ $mapping->id ?? '' }}" data-bs-toggle="modal"
                                    data-bs-target="#approveModal">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <form id="rejectForm" action="" method="POST" class="reject-form m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm reject-button"
                                    data-doc-id="{{ $mapping->id ?? '' }}">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </form>
                        </div>
                    @endif
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-0 flex-grow-1 d-flex flex-column">

                    <!-- Toolbar: Approve, Reject, Edit di atas iframe -->

                    <!-- File Viewer Iframe -->
                    <div class="flex-grow-1">
                        <iframe id="fileViewer" src="" frameborder="0" width="100%" height="100%"></iframe>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        // Capitalize words
        function ucwords(str) {
            return str.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        $(document).ready(function() {
            function showTabByFilter() {
                // Ambil nilai filter dari server (Blade request)
                let selectedPlant = "{{ request('plant') }}";
                let selectedDepartment = "{{ request('department') }}";
                let selectedProcess = "{{ request('process') }}";
                let selectedPartNumber = "{{ request('part_number') }}";

                let targetTabId = null;

                // Prioritas: plant > department > process > part number
                if (selectedPlant) {
                    targetTabId = '#tab-' + selectedPlant.replace(/\s+/g, '-').toLowerCase();
                } else if (selectedDepartment) {
                    // Mapping department → plant (jika ada)
                    targetTabId = '#tab-' + selectedDepartment.replace(/\s+/g, '-').toLowerCase();
                } else if (selectedProcess) {
                    // Mapping process → plant
                    targetTabId = '#tab-' + selectedProcess.replace(/\s+/g, '-').toLowerCase();
                } else if (selectedPartNumber) {
                    // Mapping part_number → plant
                    targetTabId = '#tab-' + selectedPartNumber.replace(/\s+/g, '-').toLowerCase();
                }

                // Aktifkan tab jika ada
                if (targetTabId) {
                    const tabEl = document.querySelector(targetTabId);
                    if (tabEl) {
                        const tabInstance = bootstrap.Tab.getOrCreateInstance(tabEl);
                        tabInstance.show();
                    }
                }
            }

            showTabByFilter();

            const $filterModal = $('#filterModal');

            // Inisialisasi select2 di modal
            function initSelect2() {
                $('.select2').select2({
                    width: '100%',
                    placeholder: 'Select an option',
                    allowClear: true,
                    dropdownParent: $filterModal
                });
            }
            initSelect2();

            // -----------------------------
            // FILTER BERDASARKAN PLANT
            // -----------------------------
            $('#plant').on('change', function() {
                let selectedPlant = $(this).val();

                // Jika plant tidak dipilih, ambil semua data
                $.ajax({
                    url: '{{ route('document-review.getFiltersByPlant') }}',
                    type: 'GET',
                    data: {
                        plant: selectedPlant || ''
                    },
                    success: function(response) {
                        // --- Department ---
                        let $department = $('#department');
                        $department.empty().append('<option value="">All Departments</option>');
                        response.departments.forEach(d => {
                            $department.append(
                                `<option value="${d.id}">${d.name}</option>`);
                        });

                        // --- Part Number ---
                        let $partNumber = $('#part_number');
                        $partNumber.empty().append(
                            '<option value="">Select Part Number</option>');
                        response.part_numbers.forEach(p => {
                            $partNumber.append(
                                `<option value="${p.id}">${p.part_number}</option>`);
                        });

                        // --- Process ---
                        let $process = $('#process');
                        $process.empty().append('<option value="">All Processes</option>');
                        response.processes.forEach(pr => {
                            $process.append(
                                `<option value="${pr.id}">${ucwords(pr.name)}</option>`
                            );
                        });

                        // Destroy dan reinit select2 agar tampil benar
                        if ($department.hasClass("select2-hidden-accessible")) $department
                            .select2('destroy');
                        if ($partNumber.hasClass("select2-hidden-accessible")) $partNumber
                            .select2('destroy');
                        if ($process.hasClass("select2-hidden-accessible")) $process.select2(
                            'destroy');
                        initSelect2();
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch filter data:', xhr);
                    }
                });
            });

            $filterModal.on('shown.bs.modal', function() {
                initSelect2();

                // 🔹 kalau plant belum dipilih → ambil semua data filter (departments, part_numbers, processes)
                const selectedPlant = $('#plant').val();
                if (!selectedPlant) {
                    $.ajax({
                        url: '{{ route('document-review.getFiltersByPlant') }}',
                        type: 'GET',
                        data: {
                            plant: ''
                        },
                        success: function(response) {
                            // --- Department ---
                            let $department = $('#department');
                            $department.empty().append(
                                '<option value="">All Departments</option>');
                            response.departments.forEach(d => {
                                $department.append(
                                    `<option value="${d.id}">${d.name}</option>`);
                            });

                            // --- Part Number ---
                            let $partNumber = $('#part_number');
                            $partNumber.empty().append(
                                '<option value="">Select Part Number</option>');
                            response.part_numbers.forEach(p => {
                                $partNumber.append(
                                    `<option value="${p.id}">${p.part_number}</option>`
                                );
                            });

                            // --- Process ---
                            let $process = $('#process');
                            $process.empty().append('<option value="">All Processes</option>');
                            response.processes.forEach(pr => {
                                $process.append(
                                    `<option value="${pr.id}">${ucwords(pr.name)}</option>`
                                );
                            });

                            // 🔹 Destroy hanya kalau sudah diinisialisasi
                            if ($department.hasClass("select2-hidden-accessible")) $department
                                .select2('destroy');
                            if ($partNumber.hasClass("select2-hidden-accessible")) $partNumber
                                .select2('destroy');
                            if ($process.hasClass("select2-hidden-accessible")) $process
                                .select2('destroy');

                            initSelect2();
                        },

                        error: function(xhr) {
                            console.error('Failed to fetch default filter data:', xhr);
                        }
                    });
                }
            });


            $filterModal.on('hidden.bs.modal', function() {
                $('.select2').select2('destroy');
            });

            $(document).on('click', '.toggle-detail', function() {
                const target = $(this).data('target');
                const escapedTarget = target.replace(/ /g, '\\ ');
                $(escapedTarget).toggleClass('hidden');
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#live-search').on('keyup', function() {
                let keyword = $(this).val();
                const $activePlantResults = $('.tab-pane.show.active .plant-results');
                const plantId = $activePlantResults.closest('.tab-pane').attr('id');

                $.ajax({
                    url: '{{ route('document-review.liveSearch') }}',
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        plant: plantId
                    },
                    success: function(data) {
                        $activePlantResults.html(data);
                        if (typeof feather !== 'undefined') feather.replace();
                        if (typeof Alpine !== 'undefined') {
                            document.querySelectorAll('[x-data]').forEach(el => {
                                Alpine.destroyTree(el);
                                Alpine.initTree(el);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX error', xhr);
                        $activePlantResults.html('<p class="text-red-500">Search failed.</p>');
                    }
                });
            });

            $(document).on('click', '.view-file-btn', function() {
                const fileUrl = $(this).data('file');
                const status = $(this).data('status');
                const docId = $(this).data('doc-id');

                $('#fileViewer').attr('src', fileUrl);
                const isActionable = status === 'need review';

                const $approveBtn = $('#viewFileModal .open-approve-modal');
                const $rejectBtn = $(
                    '#viewFileModal form:has(button[type="submit"]) button[type="submit"]');

                $approveBtn.attr('data-doc-id', docId);
                $('#rejectForm').attr('action', `/document-review/${docId}/reject`);

                $approveBtn.prop('disabled', !isActionable);
                $rejectBtn.prop('disabled', !isActionable);
            });

            $(document).on('click', '.open-approve-modal', function() {
                const docId = $(this).data('doc-id');
                const actionUrl = `/document-review/${docId}/approve-with-dates`;
                $('#approveForm').attr('action', actionUrl);
            });

            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('reject-form')) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Reject this document?',
                        text: 'Rejected document will need revision.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, reject it!'
                    }).then(result => {
                        if (result.isConfirmed) e.target.submit();
                    });
                }
            });

            $('#approveForm').on('submit', function(e) {
                const reminderDate = new Date($('#reminder_date').val());
                const deadlineDate = new Date($('#deadline').val());
                let valid = true;

                $('#reminderError').hide();
                $('#deadlineError').hide();

                if (reminderDate > deadlineDate) {
                    $('#reminderError').show();
                    $('#deadlineError').show();
                    valid = false;
                }

                if (!valid) e.preventDefault();
            });

            document.getElementById('viewFileModal').addEventListener('hidden.bs.modal', function() {
                const iframe = document.getElementById('fileViewer');
                if (iframe) iframe.src = '';
            });

            const reviseModalEl = document.getElementById('reviseModal');
            const reviseForm = document.getElementById('reviseForm');
            const fileContainer = document.querySelector('.existing-files-container');

            if (reviseModalEl) {
                const reviseModalInstance = new bootstrap.Modal(reviseModalEl);

                document.querySelectorAll('.edit-doc-btn').forEach(button => {
                    document.addEventListener('click', function(e) {
                        const button = e.target.closest('.edit-doc-btn');
                        if (!button) return;

                        const route = button.getAttribute('data-route');
                        const files = JSON.parse(button.getAttribute('data-files'));
                        const notes = button.getAttribute('data-notes') || '';

                        reviseForm.setAttribute('action', route);
                        reviseForm.querySelector('input[name="notes"]').value = notes;

                        if (files.length > 0) {
                            fileContainer.innerHTML = files.map(file => `
            <div class="mb-2">
                <label class="form-label">Revisi: ${file.name}</label>
                <input type="file" name="files[${file.id}]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
            </div>
        `).join('');
                        } else {
                            fileContainer.innerHTML =
                                '<p class="text-muted">No files available for revision.</p>';
                        }

                        reviseModalInstance.show();
                    });

                });

                reviseModalEl.addEventListener('hidden.bs.modal', function() {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    if (document.activeElement) document.activeElement.blur();
                });
            }
        });
    </script>
@endpush
