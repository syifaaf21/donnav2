@extends('layouts.app')
@section('title', 'Part Number')

@section('content')
    <div class="container mx-auto px-4 py-2">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Part Number</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#createPartNumberModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Part Number</span>
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <form method="GET" id="searchForm" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto overflow-y-auto max-h-96">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2">No</th>
                            <th class="px-4 py-2">Part Number</th>
                            <th class="px-4 py-2">Product</th>
                            <th class="px-4 py-2">Model</th>
                            <th class="px-4 py-2">Process</th>
                            <th class="px-4 py-2">Plant</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partNumbers as $part)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    {{ ($partNumbers->currentPage() - 1) * $partNumbers->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-2">{{ $part->part_number }}</td>
                                <td class="px-4 py-2">{{ $part->product->name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $part->productModel->name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ ucwords($part->process->name) ?? '-' }}</td>
                                <td class="px-4 py-2">{{ ucwords($part->plant) }}</td>
                                <td class="px-4 py-2">
                                    <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#editPartNumberModal-{{ $part->id }}"
                                        data-bs-title="Edit Part Number"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('master.part_numbers.destroy', $part->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white hover:bg-red-700 p-2 rounded"
                                            data-bs-title="Delete Part Number">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-4">No part numbers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $partNumbers->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Modals Edit --}}
    @foreach ($partNumbers as $part)
        <div class="modal fade" id="editPartNumberModal-{{ $part->id }}" tabindex="-1"
            aria-labelledby="editPartNumberModalLabel-{{ $part->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('master.part_numbers.update', $part->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit">

                        <div class="modal-header bg-light text-gray-800 rounded-t-lg">
                            <h5 class="modal-title flex items-center gap-2 font-semibold"
                                id="editPartNumberModalLabel-{{ $part->id }}">
                                <i class="bi bi-pencil-square text-primary"></i> Edit Part Number
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="block font-medium">Part Number</label>
                                    <input type="text" name="part_number"
                                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        value="{{ $part->part_number }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="block font-medium">Plant</label>
                                    <select name="plant" id="plant_edit_{{ $part->id }}"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Plant</option>
                                        @foreach (['body', 'unit', 'electric'] as $plant)
                                            <option value="{{ $plant }}"
                                                {{ $part->plant == $plant ? 'selected' : '' }}>
                                                {{ ucfirst($plant) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="block font-medium">Product</label>
                                    <select name="product_id" id="product_id_edit_{{ $part->id }}"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ $part->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="block font-medium">Model</label>
                                    <select name="model_id" id="model_id_edit_{{ $part->id }}"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">Select Model</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model->id }}"
                                                {{ $part->model_id == $model->id ? 'selected' : '' }}>
                                                {{ $model->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="block font-medium">Process</label>
                                    <select name="process_id" id="process_edit_{{ $part->id }}"
                                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="" disabled {{ $part->process_id ? '' : 'selected' }}>--
                                            Select Process --</option>
                                        @foreach ($processes as $process)
                                            <option value="{{ $process->id }}"
                                                {{ $part->process_id == $process->id ? 'selected' : '' }}>
                                                {{ ucwords($process->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                            <button type="button"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Create --}}
    <div class="modal fade" id="createPartNumberModal" tabindex="-1" aria-labelledby="createPartNumberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('master.part_numbers.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-header bg-light text-dark rounded-top-4">
                        <h5 class="modal-title flex items-center gap-2 font-semibold" id="createPartNumberModalLabel">
                            <i class="bi bi-plus-circle me-2 text-primary"></i> Add New Part Number
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="part_number" class="form-label fw-semibold">Part Number</label>
                                <input type="text" id="part_number" name="part_number"
                                    class="form-control @error('part_number') is-invalid @enderror" required autofocus
                                    placeholder="Enter part number" value="{{ old('part_number') }}">
                                @error('part_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="plant" class="form-label fw-semibold">Plant</label>
                                <select id="plant" name="plant"
                                    class="form-select @error('plant') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Select Plant --</option>
                                    @foreach (['body', 'unit', 'electric'] as $plant)
                                        <option value="{{ $plant }}"
                                            {{ old('plant') == $plant ? 'selected' : '' }}>
                                            {{ ucfirst($plant) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="product_id" class="form-label fw-semibold">Product</label>
                                <select id="product_id" name="product_id"
                                    class="form-select @error('product_id') is-invalid @enderror"
                                    placeholder="Search or create product..." required disabled>
                                    @if (old('product_id'))
                                        <option value="{{ old('product_id') }}" selected>
                                            {{ old('product_name', 'Selected Product') }}
                                        </option>
                                    @endif
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="model_id" class="form-label fw-semibold">Model</label>
                                <select id="model_id" name="model_id"
                                    class="form-select @error('model_id') is-invalid @enderror"
                                    placeholder="Search or create model..." required disabled>
                                    @if (old('model_id'))
                                        <option value="{{ old('model_id') }}" selected>
                                            {{ old('model_name', 'Selected Model') }}</option>
                                    @endif
                                </select>
                                @error('model_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="process" class="form-label fw-semibold">Process</label>
                                <select id="process_id" name="process_id"
                                    class="form-select @error('process_id') is-invalid @enderror"
                                    placeholder="Search or create process..." required disabled>
                                    <option value="" disabled {{ old('process_id') ? '' : 'selected' }}>--
                                        Select Process --</option>
                                    @foreach ($processes as $process)
                                        <option value="{{ $process->id }}"
                                            {{ old('process_id') == $process->id ? 'selected' : '' }}>
                                            {{ ucfirst($process->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('process_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <x-sweetalert-confirm />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el, {
                    title: el.getAttribute('data-bs-title'),
                    placement: 'top',
                    trigger: 'hover'
                });
            });
            // Inisialisasi TomSelect untuk Create Modal (1 instance)
            const productTomSelect = new TomSelect("#product_id", {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                create: false,
                maxOptions: 50,
                persist: false,
            });

            const modelTomSelect = new TomSelect("#model_id", {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                create: false,
                maxOptions: 50,
                persist: false,
            });

            const processTomSelect = new TomSelect("#process_id", {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                create: false,
                maxOptions: 50,
                persist: false,
            });

            // Awalnya disable
            productTomSelect.disable();
            modelTomSelect.disable();
            processTomSelect.disable();

            // Event plant untuk create modal
            const plantSelect = document.getElementById('plant');
            plantSelect.addEventListener('change', function() {
                const plant = this.value;
                if (!plant) {
                    productTomSelect.disable();
                    modelTomSelect.disable();
                    processTomSelect.disable();
                    return;
                }

                fetch(`/api/get-options-by-plant?plant=${plant}`)
                    .then(res => res.json())
                    .then(data => {
                        productTomSelect.clearOptions();
                        modelTomSelect.clearOptions();
                        processTomSelect.clearOptions();

                        if (data.products && data.products.length) {
                            data.products.forEach(prod => {
                                productTomSelect.addOption({
                                    id: prod.id,
                                    name: prod.name
                                });
                            });
                            productTomSelect.enable();
                        } else {
                            productTomSelect.disable();
                        }

                        if (data.models && data.models.length) {
                            data.models.forEach(mod => {
                                modelTomSelect.addOption({
                                    id: mod.id,
                                    name: mod.name
                                });
                            });
                            modelTomSelect.enable();
                        } else {
                            modelTomSelect.disable();
                        }

                        if (data.processes && data.processes.length) {
                            data.processes.forEach(proc => {
                                processTomSelect.addOption({
                                    id: proc.id,
                                    name: proc.name
                                });
                            });
                            processTomSelect.enable();
                        } else {
                            processTomSelect.disable();
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching options:', err);
                        productTomSelect.disable();
                        modelTomSelect.disable();
                        processTomSelect.disable();
                    });
            });

            // Cancel button create modal
            const cancelCreateBtn = document.querySelector(
                '#createPartNumberModal button[data-bs-dismiss="modal"]');
            const formCreate = document.querySelector('#createPartNumberModal form');
            cancelCreateBtn.addEventListener('click', function() {
                formCreate.reset();
                productTomSelect.clear();
                modelTomSelect.clear();
                processTomSelect.clear();
            });


            // === EDIT MODALS ===
            const editModals = document.querySelectorAll('[id^="editPartNumberModal-"]');
            const tomSelectInstances = {};

            editModals.forEach(modalEl => {
                const modalId = modalEl.id;
                const form = modalEl.querySelector('form');
                const cancelButton = modalEl.querySelector('button[data-bs-dismiss="modal"]');

                // Extract partId
                const partId = modalId.split('-')[1];

                // Select element IDs inside this modal
                const productSelectId = `product_id_edit_${partId}`;
                const modelSelectId = `model_id_edit_${partId}`;
                const processSelectId = `process_edit_${partId}`;

                const productSelectEl = document.getElementById(productSelectId);
                const modelSelectEl = document.getElementById(modelSelectId);
                const processSelectEl = document.getElementById(processSelectId);

                // Initialize TomSelect for this modal
                tomSelectInstances[modalId] = {
                    productTomSelect: productSelectEl ? new TomSelect(productSelectEl, {
                        valueField: 'value',
                        labelField: 'text',
                        searchField: 'text',
                        create: true,
                        maxOptions: 50,
                        persist: false,
                    }) : null,

                    modelTomSelect: modelSelectEl ? new TomSelect(modelSelectEl, {
                        valueField: 'value',
                        labelField: 'text',
                        searchField: 'text',
                        create: true,
                        maxOptions: 50,
                        persist: false,
                    }) : null,

                    processTomSelect: processSelectEl ? new TomSelect(processSelectEl, {
                        valueField: 'value',
                        labelField: 'text',
                        searchField: 'text',
                        create: false,
                        maxOptions: 50,
                        persist: false,
                    }) : null,
                };

                let originalFormData = {};

                function saveOriginalFormData() {
                    originalFormData = {};
                    const elements = form.elements;
                    for (let i = 0; i < elements.length; i++) {
                        const el = elements[i];
                        if (el.name) {
                            originalFormData[el.name] = el.value;
                        }
                    }
                }

                function resetFormToOriginal() {
                    for (const name in originalFormData) {
                        if (originalFormData.hasOwnProperty(name)) {
                            const el = form.elements[name];
                            if (el) el.value = originalFormData[name];
                        }
                    }

                    const ts = tomSelectInstances[modalId];
                    if (ts.productTomSelect) ts.productTomSelect.setValue(originalFormData['product_id'] ||
                        '');
                    if (ts.modelTomSelect) ts.modelTomSelect.setValue(originalFormData['model_id'] || '');
                    if (ts.processTomSelect) ts.processTomSelect.setValue(originalFormData['process_id'] ||
                        '');
                }

                modalEl.addEventListener('show.bs.modal', saveOriginalFormData);

                cancelButton.addEventListener('click', function() {
                    resetFormToOriginal();
                });
            });

            // Jika ada error validation, show create modal
            @if ($errors->any())
                var myModal = new bootstrap.Modal(document.getElementById('createPartNumberModal'));
                myModal.show();
            @endif

            //Clear Seacrh
            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", function() {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }
        });
    </script>
@endpush
