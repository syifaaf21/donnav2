@extends('layouts.app')

@section('title', 'Document Review')

@section('content')
    <div class="p-6 bg-gray-100 min-h-screen space-y-6">

        <x-flash-message />
        <x-sweetalert-confirm />
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
            <div id="search-results">
                <!-- ✅ Tabs for each Plant -->
                <ul class="nav nav-tabs" id="plantTabs" role="tablist">
                    @foreach ($groupedByPlant as $plant => $groups)
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

                <div class="tab-content mt-3" id="plantTabsContent">
                    @foreach ($groupedByPlant as $plant => $groups)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="content-{{ Str::slug($plant) }}" role="tabpanel"
                            aria-labelledby="tab-{{ Str::slug($plant) }}">
                            @include('contents.document-review.partials.table', ['groupedData' => $groups])
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
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
                                            {{ ucwords($plant) }}</option>
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
                                            {{ $dept->name }}</option>
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
                                            {{ $doc->name }}</option>
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
                                    @foreach ($processes as $proc)
                                        <option value="{{ $proc }}"
                                            {{ request('process') == $proc ? 'selected' : '' }}>
                                            {{ ucwords($proc) }}</option>
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
    <!-- Modal for File Viewer -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content d-flex flex-column" style="height: 100vh;">
                <div class="modal-header justify-content-between">
                    <h5 class="modal-title">File Preview</h5>

                    <div class="d-flex gap-2">
                        @if (auth()->user() && auth()->user()->role && auth()->user()->role->name === 'Admin')
                            <!-- Approve Button -->
                            <form action="/approve-url" method="POST">
                                @csrf
                                <button type="button" class="btn btn-outline-success btn-sm open-approve-modal"
                                    data-doc-id="{{ $mapping->id ?? '' }}" data-bs-toggle="modal"
                                    data-bs-target="#approveModal">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <form id="rejectForm" action="" method="POST" class="reject-form">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm reject-button"
                                    data-doc-id="{{ $mapping->id ?? '' }}">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </form>
                        @endif
                        <!-- Close Button -->
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 flex-grow-1">
                    <iframe id="fileViewer" src="" frameborder="0" width="100%" height="100%"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('contents.document-review.partials.modal-approve')
    @include('contents.document-review.partials.modal-edit')

    <script>
        // Capitalize words
        function ucwords(str) {
            return str.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        // Init Select2
        $(document).ready(function() {
            // Initialize select2 on modal selects
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true,
                dropdownParent: $('#filterModal') // important for bootstrap modal
            });

            // Load part numbers and processes when plant changes
            $('#plant').on('change', function() {
                let selectedPlant = $(this).val();
                let $partNumberSelect = $('#part_number');
                let $processSelect = $('#process');

                // Reset selects first
                $partNumberSelect.empty().append('<option value="">Loading...</option>');
                $processSelect.empty().append('<option value="">Loading...</option>');

                // Destroy select2 before update
                if ($.fn.select2 && $partNumberSelect.hasClass("select2-hidden-accessible")) {
                    $partNumberSelect.select2('destroy');
                }
                if ($.fn.select2 && $processSelect.hasClass("select2-hidden-accessible")) {
                    $processSelect.select2('destroy');
                }

                if (selectedPlant) {
                    $.ajax({
                        url: '{{ route('document-review.getDataByPlant') }}',
                        type: 'GET',
                        data: {
                            plant: selectedPlant
                        },
                        success: function(response) {
                            $partNumberSelect.empty().append(
                                '<option value="">Select Part Number</option>');
                            if (response.part_numbers.length > 0) {
                                response.part_numbers.forEach(function(item) {
                                    $partNumberSelect.append(
                                        `<option value="${item.id}">${item.part_number}</option>`
                                    );
                                });
                            } else {
                                $partNumberSelect.append(
                                    '<option disabled>No part numbers found</option>');
                            }

                            $processSelect.empty().append(
                                '<option value="">Select Process</option>');
                            if (response.processes.length > 0) {
                                response.processes.forEach(function(proc) {
                                    $processSelect.append(
                                        `<option value="${proc}">${ucwords(proc)}</option>`
                                    );
                                });
                            } else {
                                $processSelect.append(
                                    '<option disabled>No processes found</option>');
                            }

                            $partNumberSelect.select2({
                                width: '100%',
                                placeholder: 'Select an option',
                                allowClear: true,
                                dropdownParent: $('#filterModal')
                            });
                            $processSelect.select2({
                                width: '100%',
                                placeholder: 'Select an option',
                                allowClear: true,
                                dropdownParent: $('#filterModal')
                            });
                        },
                        error: function() {
                            alert('Failed to fetch data by plant.');
                            $partNumberSelect.empty().append(
                                '<option value="">Select Part Number</option>');
                            $processSelect.empty().append(
                                '<option value="">Select Process</option>');
                        }
                    });
                } else {
                    // Reset selects if no plant selected
                    $partNumberSelect.empty().append('<option value="">Select Part Number</option>');
                    $processSelect.empty().append('<option value="">Select Process</option>');

                    $partNumberSelect.select2({
                        width: '100%',
                        placeholder: 'Select an option',
                        allowClear: true,
                        dropdownParent: $('#filterModal')
                    });
                    $processSelect.select2({
                        width: '100%',
                        placeholder: 'Select an option',
                        allowClear: true,
                        dropdownParent: $('#filterModal')
                    });
                }
            });
        });

        // view detail
        $(document).on('click', '.toggle-detail', function() {
            const target = $(this).data('target');
            const escapedTarget = target.replace(/ /g, '\\ ');
            $(escapedTarget).toggleClass('hidden');
        });


        // 1) global setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 2) search handler (tetap pakai yang sudah ada)
        $('#live-search').on('keyup', function() {
            let keyword = $(this).val();

            $.ajax({
                url: '{{ route('document-review.liveSearch') }}',
                type: 'GET',
                data: {
                    keyword: keyword
                },
                success: function(data) {
                    $('#search-results').html(data);

                    // reinit Alpine/feather jika perlu (lihat diskusi sebelumnya)
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
                    // tampilkan pesan error + server response (berguna debugging)
                    $('#search-results').html('<p class="text-red-500">Search failed.</p>');
                }
            });
        });


        $(document).on('click', '.view-file-btn', function() {
            const fileUrl = $(this).data('file');
            const status = $(this).data('status');
            const docId = $(this).data('doc-id');

            $('#fileViewer').attr('src', fileUrl);

            // Atur tombol berdasarkan status
            const isActionable = status === 'need review';

            // Update tombol Approve (di dalam modal)
            const $approveBtn = $('#viewFileModal .open-approve-modal');
            const $rejectBtn = $('#viewFileModal form:has(button[type="submit"]) button[type="submit"]');

            // Set data-doc-id juga kalau diperlukan
            $approveBtn.attr('data-doc-id', docId);
            $('#rejectForm').attr('action', `/document-review/${docId}/reject`);

            // Enable/Disable
            $approveBtn.prop('disabled', !isActionable);
            $rejectBtn.prop('disabled', !isActionable);
        });

        // ✅ Approve Modal Handler
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

        // ✅ Validasi tanggal sebelum submit
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


        // Kosongkan iframe saat modal ditutup (optional)
        document.getElementById('viewFileModal').addEventListener('hidden.bs.modal', function() {
            const iframe = document.getElementById('fileViewer');
            if (iframe) iframe.src = '';
        });

        document.querySelectorAll('.edit-doc-btn').forEach(button => {
        button.addEventListener('click', () => {
            const dataRoute = button.getAttribute('data-route');
            const dataFiles = JSON.parse(button.getAttribute('data-files'));
            const dataNotes = button.getAttribute('data-notes');

            // set action form di modal revise
            document.getElementById('reviseForm').action = dataRoute;
            document.querySelector('input[name="notes"]').value = dataNotes;

            // generate input file untuk setiap file lama
            const container = document.querySelector('.existing-files-container');
            if (dataFiles.length === 0) {
                container.innerHTML = '<p class="text-muted">No files available for revision.</p>';
            } else {
                container.innerHTML = dataFiles.map(file => `
                    <div class="mb-3">
                        <label for="file-${file.id}" class="form-label">Replace file: ${file.name}</label>
                        <input type="file" name="files[${file.id}]" id="file-${file.id}" class="form-control" />
                    </div>
                `).join('');
            }

            // buka modal (Bootstrap)
            const reviseModal = new bootstrap.Modal(document.getElementById('reviseModal'));
            reviseModal.show();
        });
    });
    </script>
@endpush
