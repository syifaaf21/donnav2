@extends('layouts.app')
@section('title', 'Master Part Number')
@section('subtitle', 'Manage Part Number records.')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-500 font-medium">Master</li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Part Number</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="mx-auto px-4 py-2 bg-white rounded-lg shadow">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2 text-white">
                    <h3 class="fw-bold">Part Number Master</h3>
                    <p class="text-sm" style="font-size: 0.85rem;">
                        Manage part numbers. Use the "Add Part Number" button to create new entries and the actions column
                        to edit or delete existing records.
                    </p>
                </div>
            </div> --}}

        {{-- Breadcrumbs --}}
        {{-- <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1"
                aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500 font-medium">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">Part Number</li>
                </ol>
            </nav>
        </div> --}}

        <div class="overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 my-4">
                <div class="w-full sm:w-auto flex items-center gap-2">
                    {{-- Search --}}
                    <form id="searchForm" method="GET" class="flex items-center w-full sm:w-auto">
                        <div class="relative w-full sm:w-96 md:w-[400px]">
                            <input type="text" name="search" id="searchInput"
                                class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
                            focus:border-sky-400 focus:ring-2 focus:ring-sky-200 transition-all duration-200 shadow-sm"
                                placeholder="Type to search..." value="{{ request('search') }}">

                            <label for="searchInput"
                                class="absolute left-3 -top-2.5 bg-white px-1 rounded text-xs text-sky-600
           transition-all duration-150
           peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400
           peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-sky-600">
                                Type to search...
                            </label>
                        </div>
                    </form>

                    {{-- Filter plant (berdekatan dengan search) --}}
                    <div class="flex items-center gap-2">
                        <select id="filterPlant"
                            class="form-select border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[150px] bg-white">
                            <option value="">All Plants</option>
                            @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                <option value="{{ $plant }}" {{ request('plant') == $plant ? 'selected' : '' }}>
                                    {{ ucfirst($plant) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Add Button --}}
                <div class="w-full sm:w-auto flex justify-end">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createPartNumberModal"
                        class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Part Number</span>
                    </button>
                </div>
            </div>

            <div id="tableContainer">
                {{-- Table --}}
               <div
                    class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
                    <table class="min-w-full divide-y divide-gray-200 text-gray-700">
                        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                            <tr>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">No</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">Part
                                    Number</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">Product
                                </th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">Model</th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">Process
                                </th>
                                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200">Plant</th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                                    style="color: #1e2b50; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-x divide-gray-200">
                            @forelse ($partNumbers as $part)
                                <tr class="hover:bg-gray-50 transition-all duration-150">
                                    <td class="px-4 py-3 border-r border-gray-200">
                                        {{ ($partNumbers->currentPage() - 1) * $partNumbers->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold border-r border-gray-200">{{ $part->part_number }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ $part->product->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ $part->productModel->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ $part->process ? ucwords($part->process->name) : '-' }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200">{{ ucwords($part->plant) }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-200 text-center">
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#editPartNumberModal-{{ $part->id }}"
                                            data-bs-title="Edit Part Number"
                                            class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                            <i data-feather="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.part_numbers.destroy', $part->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class=" w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2"
                                                data-bs-title="Delete Part Number">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">

                                            <!-- Icon Folder / Search / Empty State -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor"
                                                class="w-12 h-12 mb-2 text-gray-400">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2
                                     2 0 012-2h4l2 2h6a2 2 0 012 2v14a2 2
                                     0 01-2 2z" />
                                            </svg>

                                            <span class="text-gray-500 text-sm">
                                                No part numbers found.
                                            </span>
                                        </div>
                                    </td>
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
    </div>

    {{-- Modals Edit / Create (existing code preserved) --}}
    @foreach ($partNumbers as $part)
        <div class="modal fade" id="editPartNumberModal-{{ $part->id }}" tabindex="-1"
            aria-labelledby="editPartNumberModalLabel-{{ $part->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('master.part_numbers.update', $part->id) }}" method="POST"
                    class="modal-content rounded-4 shadow-lg border-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">

                    {{-- Header --}}
                    <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                        style="background-color: #f5f5f7;">
                        <h5 class="modal-title fw-semibold" id="editPartNumberModalLabel-{{ $part->id }}"
                            style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Part Number
                        </h5>
                        <button type="button"
                            class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="width: 36px; height: 36px; border: 1px solid #ddd;">
                            <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body p-5">
                        <div class="row g-4">
                            {{-- Part Number --}}
                            <div class="col-md-12">
                                <label class="form-label fw-medium">Part Number <span class="text-danger">*</span></label>
                                <input type="text" name="part_number"
                                    class="form-control rounded-3 border-0 shadow-sm @error('part_number') is-invalid @enderror"
                                    value="{{ $part->part_number }}" required>
                                @error('part_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plant --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Plant <span class="text-danger">*</span></label>
                                <select name="plant" id="plant_edit_{{ $part->id }}"
                                    class="form-select rounded-3 border-0 shadow-sm @error('plant') is-invalid @enderror"
                                    required>
                                    <option value="">Select Plant</option>
                                    @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                        <option value="{{ $plant }}" @selected($part->plant == $plant)>
                                            {{ ucfirst($plant) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Product <span class="text-danger">*</span></label>
                                <select name="product_id" id="product_id_edit_{{ $part->id }}"
                                    class="form-select rounded-3 border-0 shadow-sm @error('product_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected($part->product_id == $product->id)>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Model --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Model <span class="text-danger">*</span></label>
                                <select name="model_id" id="model_id_edit_{{ $part->id }}"
                                    class="form-select rounded-3 border-0 shadow-sm @error('model_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Model</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}" @selected($part->model_id == $model->id)>
                                            {{ $model->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Process --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Process <span class="text-danger">*</span></label>
                                <select name="process_id" id="process_edit_{{ $part->id }}"
                                    class="form-select rounded-3 border-0 shadow-sm @error('process_id') is-invalid @enderror"
                                    required>
                                    <option value="" disabled @if (!$part->process_id) selected @endif>
                                        Select Process</option>
                                    @foreach ($processes as $process)
                                        <option value="{{ $process->id }}" @selected($part->process_id == $process->id)>
                                            {{ ucwords($process->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer bg-light rounded-b-xl flex items-center justify-content-between p-4">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Modal Create --}}
    <div class="modal fade" id="createPartNumberModal" tabindex="-1" aria-labelledby="createPartNumberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('master.part_numbers.store') }}" method="POST" novalidate
                class="modal-content border-0 shadow-lg rounded-4">
                @csrf

                {{-- Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold" id="createPartNumberModalLabel"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-plus-circle text-primary me-2"></i> Add New Part Number
                    </h5>
                    <button type="button"
                        class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="width: 36px; height: 36px; border: 1px solid #ddd;">
                        <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-5">
                    <div class="row g-4">
                        {{-- Part Number --}}
                        <div class="col-md-12">
                            <label for="part_number" class="form-label fw-medium">Part Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="part_number" name="part_number"
                                class="form-control rounded-3 border-0 shadow-sm @error('part_number') is-invalid @enderror"
                                value="{{ old('part_number') }}" required autofocus placeholder="Enter part number">
                            @error('part_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Plant --}}
                        <div class="col-md-6">
                            <label for="plant" class="form-label fw-medium">Plant <span
                                    class="text-danger">*</span></label>
                            <select id="plant" name="plant"
                                class="form-select rounded-3 border-0 shadow-sm @error('plant') is-invalid @enderror"
                                required>
                                <option value="" disabled selected>-- Select Plant --</option>
                                @foreach (['Body', 'Unit', 'Electric'] as $plant)
                                    <option value="{{ $plant }}" @selected(old('plant') == $plant)>
                                        {{ ucfirst($plant) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Product --}}
                        <div class="col-md-6">
                            <label for="product_id" class="form-label fw-medium">Product <span
                                    class="text-danger">*</span></label>
                            <select id="product_id" name="product_id"
                                class="form-select rounded-3 border-0 shadow-sm @error('product_id') is-invalid @enderror"
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

                        {{-- Model --}}
                        <div class="col-md-6">
                            <label for="model_id" class="form-label fw-medium">Model <span
                                    class="text-danger">*</span></label>
                            <select id="model_id" name="model_id"
                                class="form-select rounded-3 border-0 shadow-sm @error('model_id') is-invalid @enderror"
                                placeholder="Search or create model..." required disabled>
                                @if (old('model_id'))
                                    <option value="{{ old('model_id') }}" selected>
                                        {{ old('model_name', 'Selected Model') }}
                                    </option>
                                @endif
                            </select>
                            @error('model_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Process --}}
                        <div class="col-md-6">
                            <label for="process_id" class="form-label fw-medium">Process <span
                                    class="text-danger">*</span></label>
                            <select id="process_id" name="process_id"
                                class="form-select rounded-3 border-0 shadow-sm @error('process_id') is-invalid @enderror"
                                placeholder="Search or create process..." required disabled>
                                <option value="" disabled @if (!old('process_id')) selected @endif>Select
                                    Process</option>
                                @foreach ($processes as $process)
                                    <option value="{{ $process->id }}" @selected(old('process_id') == $process->id)>
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

                {{-- Footer --}}
                <div class="modal-footer bg-light rounded-b-xl flex items-center justify-content-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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

            // ✅ FIX: Jika ada old plant value, trigger change event
            @if (old('plant'))
                setTimeout(() => {
                    const plant = @json(old('plant'));
                    plantSelect.value = plant;
                    plantSelect.dispatchEvent(new Event('change'));
                }, 100);
            @endif

            // Cancel button create modal
            const cancelCreateBtn = document.querySelector(
                '#createPartNumberModal button[data-bs-dismiss="modal"]');
            const formCreate = document.querySelector('#createPartNumberModal form');
            cancelCreateBtn.addEventListener('click', function() {
                formCreate.reset();
                productTomSelect.clear();
                modelTomSelect.clear();
                processTomSelect.clear();
                productTomSelect.disable();
                modelTomSelect.disable();
                processTomSelect.disable();
            });


            // === EDIT MODALS ===
            const editModals = document.querySelectorAll('[id^="editPartNumberModal-"]');
            const tomSelectInstances = {};

            editModals.forEach(modalEl => {
                const modalId = modalEl.id;
                const form = modalEl.querySelector('form');
                const cancelButton = modalEl.querySelector('button[data-bs-dismiss="modal"]');

                const partId = modalId.split('-')[1];

                const productSelectId = `product_id_edit_${partId}`;
                const modelSelectId = `model_id_edit_${partId}`;
                const processSelectId = `process_edit_${partId}`;

                const productSelectEl = document.getElementById(productSelectId);
                const modelSelectEl = document.getElementById(modelSelectId);
                const processSelectEl = document.getElementById(processSelectId);

                // ✅ Buat TomSelect dulu
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

                // === Event change Plant pada EDIT modal (SETELAH TomSelect dibuat) ===
                const plantSelectEdit = document.getElementById(`plant_edit_${partId}`);

                if (plantSelectEdit) {
                    plantSelectEdit.addEventListener('change', function() {
                        const selectedPlant = this.value;
                        const ts = tomSelectInstances[modalId];

                        if (!ts) return;

                        // Reset nilai
                        ts.productTomSelect?.clear(true);
                        ts.modelTomSelect?.clear(true);
                        ts.processTomSelect?.clear(true);

                        fetch(`/api/get-options-by-plant?plant=${selectedPlant}`)
                            .then(res => res.json())
                            .then(data => {
                                ts.productTomSelect?.clearOptions();
                                ts.modelTomSelect?.clearOptions();
                                ts.processTomSelect?.clearOptions();

                                // Product
                                if (data.products?.length) {
                                    data.products.forEach(p => {
                                        ts.productTomSelect.addOption({
                                            value: p.id,
                                            text: p.name
                                        });
                                    });
                                    ts.productTomSelect.enable();
                                } else ts.productTomSelect.disable();

                                // Model
                                if (data.models?.length) {
                                    data.models.forEach(m => {
                                        ts.modelTomSelect.addOption({
                                            value: m.id,
                                            text: m.name
                                        });
                                    });
                                    ts.modelTomSelect.enable();
                                } else ts.modelTomSelect.disable();

                                // Process
                                if (data.processes?.length) {
                                    data.processes.forEach(pr => {
                                        ts.processTomSelect.addOption({
                                            value: pr.id,
                                            text: pr.name
                                        });
                                    });
                                    ts.processTomSelect.enable();
                                } else ts.processTomSelect.disable();
                            });
                    });
                }

                // === Save & Reset original data ===
                let originalFormData = {};

                function saveOriginalFormData() {
                    originalFormData = {};
                    [...form.elements].forEach(el => {
                        if (el.name) originalFormData[el.name] = el.value;
                    });
                }

                function resetFormToOriginal() {
                    for (const key in originalFormData) {
                        if (form.elements[key]) form.elements[key].value = originalFormData[key];
                    }

                    const ts = tomSelectInstances[modalId];
                    ts.productTomSelect?.setValue(originalFormData['product_id'] || '');
                    ts.modelTomSelect?.setValue(originalFormData['model_id'] || '');
                    ts.processTomSelect?.setValue(originalFormData['process_id'] || '');
                }

                modalEl.addEventListener('show.bs.modal', saveOriginalFormData);
                cancelButton.addEventListener('click', resetFormToOriginal);
            });

            // Jika ada error validation, show create modal
            @if ($errors->any())
                var myModal = new bootstrap.Modal(document.getElementById('createPartNumberModal'));
                myModal.show();
            @endif

            function bindDeleteSweetAlert() {
                document.querySelectorAll('.delete-form').forEach(form => {
                    // Hapus handler lama jika ada (penting untuk re-bind)
                    form.removeEventListener('submit', form._swalHandler);

                    const handler = e => {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!'
                        }).then(result => {
                            if (result.isConfirmed) form.submit();
                        });
                    };

                    form.addEventListener('submit', handler);
                    form._swalHandler = handler; // Simpan reference handler untuk bisa di-remove nanti
                });
            }

            // ===================== GLOBAL SELECTORS =====================
            const tableContainer = document.getElementById("tableContainer");
            const searchInput = document.getElementById("searchInput");
            const clearBtn = document.getElementById("clearSearch");
            const filterPlant = document.getElementById("filterPlant");
            let timer;
            const delay = 300;

            // ===================== URL BUILDER =====================
            function buildURL(pageUrl = null) {
                const search = encodeURIComponent(searchInput.value || "");
                const plant = encodeURIComponent(filterPlant.value || "");

                // Jika pagination memberikan URL
                if (pageUrl) {
                    const urlObj = new URL(pageUrl);
                    urlObj.searchParams.set("search", search);
                    urlObj.searchParams.set("plant", plant);
                    return urlObj.toString();
                }

                // Default (tanpa pagination)
                return `{{ route('master.part_numbers.index') }}?search=${search}&plant=${plant}`;
            }

            // ===================== AJAX FETCH =====================
            function fetchData(url) {
                fetch(url, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const dom = new DOMParser().parseFromString(html, "text/html");
                        tableContainer.innerHTML = dom.querySelector("#tableContainer").innerHTML;

                        bindPagination();
                        bindDeleteSweetAlert();

                        if (window.feather) feather.replace();
                    });
            }

            // ===================== LIVE SEARCH =====================
            searchInput.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchData(buildURL());
                }, delay);
            });

            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    fetchData(buildURL());
                }
            });

            // ===================== FILTER PLANT =====================
            filterPlant.addEventListener("change", function() {
                fetchData(buildURL());
            });

            // ===================== AJAX PAGINATION =====================
            function bindPagination() {
                document.querySelectorAll("#tableContainer .pagination a").forEach(a => {
                    a.addEventListener("click", function(e) {
                        e.preventDefault();
                        const finalURL = buildURL(this.href);
                        fetchData(finalURL);
                    });
                });
            }

            // ===================== INITIAL BINDING =====================
            bindPagination();
            bindDeleteSweetAlert(); // 🔧 Fix: Bind delete sweetalert on initial page load

        });
    </script>
@endpush

@push('styles')
    <style>
        /* Default border */
        #createPartNumberModal input.form-control,
        #createPartNumberModal select.form-select {
            border: 1px solid #d1d5db !important;
            /* abu-abu halus */
            box-shadow: none !important;
        }

        /* Hover (opsional) */
        #createPartNumberModal input.form-control:hover,
        #createPartNumberModal select.form-select:hover {
            border-color: #bfc3ca !important;
        }

        /* Fokus / diklik */
        #createPartNumberModal input.form-control:focus,
        #createPartNumberModal select.form-select:focus {
            border-color: #3b82f6 !important;
            /* biru */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
            /* efek biru lembut */
        }

        [id^="editPartNumberModal-"] input.form-control,
        [id^="editPartNumberModal-"] select.form-select {
            border: 1px solid #d1d5db !important;
            box-shadow: none !important;
        }

        /* Hover */
        [id^="editPartNumberModal-"] input.form-control:hover,
        [id^="editPartNumberModal-"] select.form-select:hover {
            border-color: #bfc3ca !important;
        }

        /* Fokus */
        [id^="editPartNumberModal-"] input.form-control:focus,
        [id^="editPartNumberModal-"] select.form-select:focus {
            border-color: #3b82f6 !important;
            /* biru */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
        }
    </style>
@endpush
