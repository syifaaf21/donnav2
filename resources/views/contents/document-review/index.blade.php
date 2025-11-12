@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen space-y-6">
        <x-flash-message />

        <!-- Breadcrumb -->
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

        <!-- Search + Filter (tetap di kanan) -->
        <div class="flex justify-end items-center mb-4 space-x-3">
            <input type="text" id="live-search" placeholder="Search..."
                class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-300 w-64" />
            <button type="button" class="btn btn-primary flex items-center gap-1" data-bs-toggle="modal"
                data-bs-target="#filterModal">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs flex flex-wrap gap-2 border-b border-gray-300 mb-4" role="tablist">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link rounded-t-lg px-4 py-2 @if ($loop->first) active bg-white border-b-0 font-semibold @else text-gray-600 hover:bg-gray-100 border @endif"
                        id="tab-{{ $plant }}" data-bs-toggle="tab" data-bs-target="#tab-content-{{ $plant }}"
                        type="button" role="tab" aria-controls="tab-content-{{ $plant }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ ucfirst($plant) }}
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="plantTabContent">
            @foreach ($groupedByPlant as $plant => $documentsByCode)
                <div class="tab-pane fade @if ($loop->first) show active @endif"
                    id="tab-content-{{ $plant }}" role="tabpanel" aria-labelledby="tab-{{ $plant }}">
                    <div class="accordion space-y-3" id="accordion-{{ $plant }}">
                        @foreach ($documentsByCode as $docCode => $documentMappings)
                            @php
                                $uniqueId = Str::slug($plant . '-' . $docCode . '-' . $loop->index);
                                $collapseId = 'collapse-' . $uniqueId;
                                $headingId = 'heading-' . $uniqueId;
                            @endphp

                            <div class="accordion-item border rounded-lg shadow-sm overflow-hidden">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button collapsed bg-white text-gray-800 font-medium"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        <i class="bi bi-folder-fill me-2 text-blue-500"></i>
                                        {{ $docCode }}
                                    </button>
                                </h2>
                                <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                    aria-labelledby="{{ $headingId }}" data-bs-parent="#accordion-{{ $plant }}">
                                    <div class="accordion-body bg-gray-50">
                                        @include('contents.document-review.partials.table', [
                                            'groupedData' => $documentMappings->groupBy(fn($item) => $item->id),
                                        ])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div class="modal-header border-b">
                    <h5 class="modal-title" id="filterModalLabel">Filter Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" action="{{ route('document-review.index') }}">
                    <div class="modal-body space-y-4">
                        <div class="grid grid-cols-2 gap-4">

                            <div>
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

                            <div>
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

                            <div>
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

                            <div>
                                <label for="part_number" class="form-label">Part Number</label>
                                <select name="part_number" id="part_number" class="form-select select2">
                                    <option value="">Select Part Number</option>
                                </select>
                            </div>

                            <div>
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

                    <div class="modal-footer border-t">
                        <a href="{{ route('document-review.index') }}" class="btn btn-secondary">Clear</a>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')
@endsection
@push('scripts')
    <x-sweetalert-confirm />
    {{-- Pastikan Bootstrap JS dimuat --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>

    {{-- Opsional: smooth scroll ke panel yang baru dibuka --}}
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const triggers = document.querySelectorAll('[data-bs-toggle="collapse"]');
        //     triggers.forEach(trigger => {
        //         trigger.addEventListener('click', () => {
        //             const target = document.querySelector(trigger.dataset.bsTarget);
        //             if (target) {
        //                 const collapse = bootstrap.Collapse.getOrCreateInstance(target);
        //                 collapse.toggle();
        //             }
        //         });
        //     });
        // });
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

            // $(document).on('click', '.toggle-detail', function() {
            //     const target = $(this).data('target');
            //     const escapedTarget = target.replace(/ /g, '\\ ');
            //     $(escapedTarget).toggleClass('hidden');
            // });

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

            const viewFileModal = document.getElementById('viewFileModal');
            if (viewFileModal) {
                viewFileModal.addEventListener('hidden.bs.modal', function() {
                    const iframe = document.getElementById('fileViewer');
                    if (iframe) iframe.src = '';
                });
            }
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

            // Toggle detail
            $(document).on('click', '.toggle-detail', function(e) {
                e.preventDefault();

                const $target = $($(this).data('target'));
                $target.toggleClass('hidden');

                // Jika toggle-detail ada di dalam dropdown, jangan biarkan dropdown menutup
                const $dropdown = $(this).closest('.dropdown');
                if ($dropdown.length) {
                    $dropdown.addClass('show'); // pastikan dropdown tetap terbuka
                    $dropdown.find('.dropdown-menu').addClass('show');
                }
            });

            // Cegah dropdown auto-close ketika klik di dalam dropdown-menu
            $(document).on('click', '.dropdown-menu', function(e) {
                e.stopPropagation();
            });

            // Tambahan: jika dropdown ingin tetap open ketika klik toggle-detail
            $('.dropdown').on('hide.bs.dropdown', function(e) {
                if ($(e.clickEvent?.target).closest('.toggle-detail').length) {
                    e.preventDefault(); // cegah dropdown menutup
                }
            });

        });
    </script>
@endpush
