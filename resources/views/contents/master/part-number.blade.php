@extends('layouts.app')
@section('title', 'Part Number')

@section('content')
    <div class="container py-2">
        <div class="d-flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Master</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Part Number</a>
                    </li>
                </ol>
            </nav>
            {{-- Tombol Add Part Number --}}
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold btn btn-primary rounded-md shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                data-bs-toggle="modal" data-bs-target="#createPartNumberModal">
                <i class="bi bi-plus-circle"></i> Add Part Number
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <form method="GET" class="flex items-center gap-2 flex-wrap" id="searchForm">
                        <div class="relative max-w-md w-full">
                            <input type="text" name="search" id="searchInput"
                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search..." value="{{ request('search') }}">
                            <button
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                                type="submit" title="Search">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button"
                                class="absolute right-8 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                                id="clearSearch" title="Clear">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="table-wrapper mb-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="min-w-full table-auto text-sm text-left text-gray-600">
                            <thead class="bg-gray-100 text-gray-700 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2">No</th>
                                    <th class="px-4 py-2">Part Number</th>
                                    <th class="px-4 py-2">Product</th>
                                    <th class="px-4 py-2">Model</th>
                                    <th class="px-4 py-2">Process</th>
                                    <th class="px-4 py-2">Plant</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partNumbers as $part)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            {{ ($partNumbers->currentPage() - 1) * $partNumbers->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-2">{{ $part->part_number }}</td>
                                        <td class="px-4 py-2">{{ $part->product->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $part->productModel->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ ucwords($part->process->name) ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ ucwords($part->plant) }}</td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <button class="text-blue-600 hover:text-blue-700" data-bs-toggle="modal"
                                                data-bs-target="#editPartNumberModal-{{ $part->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('master.part_numbers.destroy', $part->id) }}"
                                                method="POST" class="delete-form inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center px-4 py-2">No part number found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    <div class="mt-3">
                        {{ $partNumbers->withQueryString()->links() }}
                    </div>
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

                                <div class="modal-header bg-gray-100 text-gray-800 rounded-t-lg">
                                    <h5 class="modal-title flex items-center gap-2 font-semibold"
                                        id="editPartNumberModalLabel-{{ $part->id }}">
                                        <i class="bi bi-nut-fill"></i> Edit Part Number
                                    </h5>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="block font-medium">Part Number</label>
                                        <input type="text" name="part_number"
                                            class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            value="{{ $part->part_number }}" required>
                                    </div>

                                    <div class="mb-3">
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

                                    <div class="mb-3">
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

                                    <div class="mb-3">
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

                                    <div class="mb-3">
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
                                <div class="modal-footer border-0 p-3 justify-between bg-gray-100 rounded-b-lg">
                                    <button type="button" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md"
                                        data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                        <i class="bi bi-check-circle"></i> Save Changes
                                    </button>
                                </div>
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
                        <div class="modal-header">
                            <h5 class="modal-title flex items-center gap-2 font-semibold"
                                id="editPartNumberModalLabel-{{ $part->id }}">
                                <i class="bi bi-nut-fill"></i> Add New Part Number
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
                                                {{ old('product_name', 'Selected Product') }}</option>
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

                        <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Part Number
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
