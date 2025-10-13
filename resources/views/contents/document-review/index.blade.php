@extends('layouts.app')

@section('title', 'Location Management')

@section('content')
    <div class="p-6 bg-gray-100 min-h-screen space-y-6">

        <!-- ✅ Filter Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('document-review.index') }}">
                <div class="flex flex-wrap gap-4 items-end">

                    <!-- Plant -->
                    <div class="w-full sm:w-1/6">
                        <label for="plant" class="block text-sm font-medium text-gray-700 mb-1">Plant</label>
                        <select name="plant" id="plant" class="select2 w-full border border-gray-300 rounded p-2">
                            <option value="">All Plants</option>
                            @foreach ($plants as $plant)
                                <option value="{{ $plant }}">{{ ucwords($plant) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="w-full sm:w-1/6">
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department" id="department" class="select2 w-full border border-gray-300 rounded p-2">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Document -->
                    <div class="w-full sm:w-1/6">
                        <label for="document_id" class="block text-sm font-medium text-gray-700 mb-1">Document</label>
                        <select name="document_id" id="document_id"
                            class="select2 w-full border border-gray-300 rounded p-2">
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
                    <div class="w-full sm:w-1/6">
                        <label for="part_number" class="block text-sm font-medium text-gray-700 mb-1">Part No</label>
                        <select name="part_number" id="part_number"
                            class="select2 w-full border border-gray-300 rounded p-2">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Model -->
                    <div class="w-full sm:w-1/6">
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <select name="model" id="model" class="select2 w-full border border-gray-300 rounded p-2">
                            <option value="">All Models</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Process -->
                    <div class="w-full sm:w-1/6">
                        <label for="process" class="block text-sm font-medium text-gray-700 mb-1">Process</label>
                        <select name="process" id="process" class="select2 w-full border border-gray-300 rounded p-2">
                            <option value="">All Processes</option>
                            @foreach ($processes as $proc)
                                <option value="{{ $proc }}">{{ ucwords($proc) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2 ml-auto mt-1">
                        <!-- Search Button -->
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded transition"
                            title="Search">
                            <i class="bi bi-search"></i>
                        </button>

                        <!-- Clear Button -->
                        <a href="{{ route('document-review.index') }}"
                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded transition" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- ✅ Results Card -->
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <table class="min-w-full table-auto text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Part Number</th>
                        <th class="px-4 py-2">Model</th>
                        <th class="px-4 py-2">Process</th>
                        <th class="px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groupedData as $index => $group)
                        @php
                            $first = $group->first();
                            $partNumber = $first->partNumber;
                            $model = $partNumber->productModel->name ?? '-';
                            $process = $partNumber->process ?? '-';
                        @endphp
                        <tr class="border-b border-gray-200 bg-white hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $partNumber->part_number }}</td>
                            <td class="px-4 py-2">{{ $model }}</td>
                            <td class="px-4 py-2">{{ ucwords($process) }}</td>
                            <td class="px-4 py-2 text-center space-x-1">
                                <!-- 👁 Toggle Detail -->
                                <button type="button"
                                    class="toggle-detail bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs"
                                    data-target="#detail-{{ $index }}" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- ✏️ Revisi -->
                                <a href=""
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs"
                                    title="Revisi">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>

                        </tr>

                        <!-- 🔽 Detail Row -->
                        <tr id="detail-{{ $index }}" class="hidden bg-gray-50 border-t border-b border-gray-200">
                            <td colspan="5" class="p-4">
                                <table class="w-full table-fixed text-sm text-left text-gray-600">
                                    <thead class="bg-gray-200 text-gray-600">
                                        <tr>
                                            <th class="px-2 py-1 w-8">No</th>
                                            <th class="px-2 py-1">Document Number</th>
                                            <th class="px-2 py-1">Notes</th>
                                            <th class="px-2 py-1">Reminder Date</th>
                                            <th class="px-2 py-1">Deadline</th>
                                            <th class="px-2 py-1">Last Update</th>
                                            <th class="px-2 py-1">Updated By</th>
                                            <th class="px-2 py-1">Status</th>
                                            <th class="px-2 py-1 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group as $i => $doc)
                                            <tr class="border-b">
                                                <td class="px-2 py-1">{{ $i + 1 }}</td>
                                                <td class="px-2 py-1">{{ $doc->document_number }}</td>
                                                <td class="px-2 py-1">{{ $doc->notes ?? '-' }}</td>
                                                <td class="px-2 py-1">
                                                    {{ optional($doc->reminder_date)->format('Y-m-d') ?? '-' }}</td>
                                                <td class="px-2 py-1">
                                                    {{ optional($doc->deadline)->format('Y-m-d') ?? '-' }}</td>
                                                <td class="px-2 py-1">
                                                    {{ optional($doc->updated_at)->format('Y-m-d') ?? '-' }}</td>
                                                <td class="px-2 py-1">{{ $doc->user->name ?? '-' }}</td>
                                                <td class="px-2 py-1">{{ $doc->status->name ?? '-' }}</td>
                                                <td class="px-2 py-1 text-center space-x-1">
                                                    <!-- 🔍 View Files -->
                                                    <a href=""
                                                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                                        title="View Files">
                                                        <i data-feather="file-text" class="w-3 h-3"></i>
                                                    </a>

                                                    <!-- ✅ Approve -->
                                                    <form action="" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs"
                                                            onclick="return confirm('Are you sure you want to approve this document?')"
                                                            title="Approve">
                                                            <i data-feather="check-circle" class="w-3 h-3"></i>
                                                        </button>
                                                    </form>

                                                    <!-- ❌ Reject -->
                                                    <form action="" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs"
                                                            onclick="return confirm('Are you sure you want to reject this document?')"
                                                            title="Reject">
                                                            <i data-feather="x-circle" class="w-3 h-3"></i>
                                                        </button>
                                                    </form>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                <p class="text-sm">No data found. Apply filters to see results.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // Capitalize words
        function ucwords(str) {
            return str.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        // Init Select2
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
        });

        // Plant change handler
        $('#plant').on('change', function() {
            let selectedPlant = $(this).val();
            let $partNumberSelect = $('#part_number');
            let $processSelect = $('#process');

            if ($.fn.select2 && $partNumberSelect.hasClass("select2-hidden-accessible")) {
                $partNumberSelect.select2('destroy');
            }
            if ($.fn.select2 && $processSelect.hasClass("select2-hidden-accessible")) {
                $processSelect.select2('destroy');
            }

            $partNumberSelect.html('<option value="">Loading...</option>');
            $processSelect.html('<option value="">Loading...</option>');

            $.ajax({
                url: '{{ route('document-review.getDataByPlant') }}',
                type: 'GET',
                data: {
                    plant: selectedPlant
                },
                success: function(response) {
                    $partNumberSelect.empty().append('<option value="">Select Part Number</option>');
                    if (response.part_numbers.length > 0) {
                        response.part_numbers.forEach(function(item) {
                            $partNumberSelect.append(
                                `<option value="${item.id}">${item.part_number}</option>`);
                        });
                    } else {
                        $partNumberSelect.append('<option disabled>No part numbers found</option>');
                    }

                    $processSelect.empty().append('<option value="">Select Process</option>');
                    if (response.processes.length > 0) {
                        response.processes.forEach(function(proc) {
                            $processSelect.append(
                                `<option value="${proc}">${ucwords(proc)}</option>`);
                        });
                    } else {
                        $processSelect.append('<option disabled>No processes found</option>');
                    }

                    $partNumberSelect.select2({
                        width: '100%',
                        placeholder: 'Select an option',
                        allowClear: true
                    });
                    $processSelect.select2({
                        width: '100%',
                        placeholder: 'Select an option',
                        allowClear: true
                    });
                },
                error: function() {
                    alert('Failed to fetch data by plant.');
                }
            });
        });

        $(document).ready(function() {
            $('.toggle-detail').on('click', function() {
                const target = $(this).data('target');
                // Escape spasi di selector supaya jQuery bisa temukan elemen
                const escapedTarget = target.replace(/ /g, '\\ ');
                $(escapedTarget).toggleClass('hidden');
            });
        });
    </script>
@endpush
