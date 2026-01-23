@extends('layouts.app')
@section('title', 'Master Clause')
@section('subtitle', 'Manage Clauses')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-500 font-medium">Master</li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Clause</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div id="section-klausul" class="mx-auto px-4 py-2 bg-white rounded-lg shadow">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-2">
            {{-- Search Bar (left) --}}
            <form id="searchForm" method="GET" class="flex items-end w-full md:w-96">
                <div class="relative w-full">
                    <input type="text" name="search" id="searchInput"
                        class="peer w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700
                            focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:bg-white transition-all duration-200 shadow-sm"
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
            <button id="btnAddKlausul"
                class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                <i class="bi bi-plus"></i> Add Klausul
            </button>
        </div>

        {{-- Klausul List --}}
        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            @forelse ($klausuls as $klausul)
                <div class="klausul-item border-b border-gray-200 hover:bg-gray-50 transition">
                    {{-- Main Klausul Header --}}
                    <div class="p-4 flex justify-between items-center cursor-pointer" data-klausul-id="{{ $klausul->id }}"
                        onclick="toggleKlausulCollapse(this)">
                        <div class="flex items-center gap-3 flex-1">
                            <i class="bi bi-chevron-right text-gray-400 transition-transform w-5 h-5 klausul-chevron"></i>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $klausul->name }}</div>
                                <div class="text-xs text-gray-500">
                                    Audit Type:
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs"
                                        data-id="{{ $klausul->audit_type_id }}">{{ $klausul->audit->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button data-id="{{ $klausul->id }}"
                                class="btn-edit-klausul w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors p-2"
                                onclick="event.stopPropagation();">
                                <i data-feather="edit" class="w-4 h-4"></i>
                            </button>
                            <button data-id="{{ $klausul->id }}"
                                class="btn-delete-klausul w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2"
                                onclick="event.stopPropagation();">
                                <i data-feather="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Head Klausul List (Hidden by default) --}}
                    <div class="klausul-collapse hidden bg-gray-50 pl-12 py-2">
                        {{-- Add Head Button --}}
                        <div class="p-3 mb-2">
                            <button data-klausul-id="{{ $klausul->id }}"
                                class="btn-add-head text-xs px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-plus"></i> Add Head Klausul
                            </button>
                        </div>

                        @forelse ($klausul->headKlausuls as $head)
                            <div class="head-item border border-gray-300 rounded-lg p-3 mb-3">
                                {{-- Head Klausul Row with Toggle --}}
                                <div class="flex justify-between items-start cursor-pointer"
                                    onclick="toggleHeadKlausulCollapse(this)">
                                    <div class="flex-1 flex items-center gap-2">
                                        <i
                                            class="bi bi-chevron-right text-gray-400 transition-transform w-5 h-5 head-klausul-chevron"></i>
                                        <div>
                                            <div class="font-semibold text-gray-700">{{ $head->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Code: <span
                                                    class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $head->code ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button data-id="{{ $head->id }}"
                                            class="btn-edit-head w-7 h-7 d-flex align-items-center justify-content-center rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors p-0 text-xs"
                                            style="display: flex; align-items: center; justify-content: center;"
                                            onclick="event.stopPropagation();">
                                            <i data-feather="edit" class="w-4 h-4 mx-auto"></i>
                                        </button>
                                        <button data-id="{{ $head->id }}"
                                            class="btn-delete-head w-7 h-7 d-flex align-items-center justify-content-center rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-0 text-xs"
                                            style="display: flex; align-items: center; justify-content: center;"
                                            onclick="event.stopPropagation();">
                                            <i data-feather="trash-2" class="w-4 h-4 mx-auto"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="head-klausul-collapse hidden mt-3 pl-4 border-l-2 border-gray-300">
                                    <button data-head-id="{{ $head->id }}"
                                        class="btn-add-sub text-xs px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors mb-2"
                                        onclick="event.stopPropagation();">
                                        <i class="bi bi-plus"></i> Add Sub Klausul
                                    </button>
                                    @forelse ($head->subKlausuls as $sub)
                                        <div
                                            class="sub-item flex justify-between items-center bg-white border border-gray-200 rounded p-2 mb-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-700">{{ $sub->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    Code: <span class="font-mono">{{ $sub->code ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button data-id="{{ $sub->id }}" data-head-id="{{ $head->id }}"
                                                    class="btn-edit-sub w-6 h-6 d-flex align-items-center justify-content-center rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-colors p-0 text-xs"
                                                    style="display: flex; align-items: center; justify-content: center;"
                                                    onclick="event.stopPropagation();">
                                                    <i data-feather="edit" class="w-4 h-4 mx-auto"></i>
                                                </button>
                                                <button data-id="{{ $sub->id }}"
                                                    class="btn-delete-sub w-6 h-6 d-flex align-items-center justify-content-center rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-0 text-xs"
                                                    style="display: flex; align-items: center; justify-content: center;"
                                                    onclick="event.stopPropagation();">
                                                    <i data-feather="trash-2" class="w-4 h-4 mx-auto"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400 italic">No sub klausul</p>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm py-2 px-3">No head klausul</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="bi bi-inbox text-4xl mb-2"></i>
                    <p>No klausul available</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL ADD KLAUSUL --}}
    <div class="modal fade" id="modalAddKlausul" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-lg shadow-lg">
                <div
                    class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Add Klausul
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAddKlausul" method="POST" action="{{ route('master.ftpp.klausul.store') }}">
                    @csrf
                    <div class="modal-body p-4 space-y-3" style="max-height: 70vh; overflow-y: auto;">
                        <div>
                            <label class="form-label fw-semibold">Audit Type <span class="text-danger">*</span></label>
                            <select name="audit_type_id" class="form-select border-1" required>
                                <option value="">-- Choose Audit Type --</option>
                                @php
                                    $audits = App\Models\Audit::all();
                                @endphp
                                @foreach ($audits as $audit)
                                    <option value="{{ $audit->id }}">{{ $audit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Klausul Name <span class="text-danger">*</span></label>
                            <input type="text" name="klausul_name" class="form-control border-1"
                                placeholder="Input klausul name" required>
                        </div>

                        <hr class="my-4">

                        {{-- Head Klausul Container --}}
                        <div id="headKlausulContainer">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-semibold mb-0">Head Klausul</h6>
                                <button type="button" onclick="addHeadKlausulField()" class="btn btn-sm btn-success">
                                    <i class="bi bi-plus"></i> Add Head
                                </button>
                            </div>

                            {{-- First Head Klausul --}}
                            <div class="head-klausul-group border rounded p-3 mb-3 bg-light" data-head-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-primary">Head #1</strong>
                                    <button type="button" onclick="removeHeadKlausul(this)"
                                        class="btn btn-sm btn-danger d-none">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold">Head Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="heads[0][name]" class="form-control border-1"
                                        placeholder="Input head klausul name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Head Code</label>
                                    <input type="text" name="heads[0][code]" class="form-control border-1"
                                        placeholder="Input head code (optional)">
                                </div>

                                {{-- Sub Klausul for this Head --}}
                                <div class="sub-klausul-container" data-head-index="0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-semibold mb-0">Sub Klausul</label>
                                        <button type="button" onclick="addSubKlausulField(0)"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus"></i> Add Sub
                                        </button>
                                    </div>
                                    <div class="sub-klausul-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT KLAUSUL --}}
    <div class="modal fade" id="modalEditKlausul" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div
                    class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i> Edit Klausul
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditKlausul" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 space-y-3">
                        <div>
                            <label class="form-label fw-semibold">Audit Type <span class="text-danger">*</span></label>
                            <select name="audit_type_id" id="edit_audit_type_id" class="form-select border-1" required>
                                <option value="">-- Choose Audit Type --</option>
                                @php
                                    $audits = App\Models\Audit::all();
                                @endphp
                                @foreach ($audits as $audit)
                                    <option value="{{ $audit->id }}">{{ $audit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Klausul Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_klausul_name" class="form-control border-1"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer border-t">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ADD HEAD KLAUSUL --}}
    <div class="modal fade" id="modalAddHead" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div
                    class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Add Head Klausul
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAddHead" method="POST">
                    @csrf
                    <input type="hidden" name="audit_type_id" id="add_head_audit_type">
                    <input type="hidden" name="klausul_id" id="add_head_klausul_id">
                    <div class="modal-body p-4 space-y-3">
                        <div>
                            <label class="form-label fw-semibold">Klausul Name</label>
                            <input type="text" id="add_head_klausul_name" class="form-control border-1" disabled>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Head Klausul Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="head_name" class="form-control border-1"
                                placeholder="Input head klausul name" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Head Code</label>
                            <input type="text" name="head_code" class="form-control border-1"
                                placeholder="Input head code (optional)">
                        </div>
                        <hr class="my-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">Sub Klausul</label>
                                <button type="button" onclick="addSubKlausulFieldHead()" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus"></i> Add Sub
                                </button>
                            </div>
                            <div id="subKlausulListHead"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-t">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT HEAD KLAUSUL --}}
    <div class="modal fade" id="modalEditHead" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div
                    class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i> Edit Head Klausul
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditHead" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 space-y-3">
                        <div>
                            <label class="form-label fw-semibold">Head Name <span class="text-danger">*</span></label>
                            <input type="text" name="head_name" id="edit_head_name" class="form-control border-1"
                                required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Head Code</label>
                            <input type="text" name="head_code" id="edit_head_code" class="form-control border-1"
                                placeholder="Input head code (optional)">
                        </div>
                        <hr class="my-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">Sub Klausul</label>
                                <button type="button" onclick="addSubKlausulFieldEdit()" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus"></i> Add Sub
                                </button>
                            </div>
                            <div id="subKlausulListEdit"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-t">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ADD/EDIT SUB KLAUSUL --}}
    <div class="modal fade" id="modalSubKlausul" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div
                    class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title" id="subKlausulTitle">
                        <i class="bi bi-plus-circle me-2"></i> Add Sub Klausul
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSubKlausul" method="POST">
                    @csrf
                    <input type="hidden" name="sub_id" id="sub_id">
                    <input type="hidden" name="head_id" id="sub_head_id">
                    <div class="modal-body p-4 space-y-3">
                        <div>
                            <label class="form-label fw-semibold">Sub Name <span class="text-danger">*</span></label>
                            <input type="text" name="sub_names[0]" id="sub_name" class="form-control border-1"
                                required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Sub Code</label>
                            <input type="text" name="sub_codes[0]" id="sub_code" class="form-control border-1">
                        </div>
                    </div>
                    <div class="modal-footer border-t">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- HIDDEN DELETE FORMS --}}
    <form id="form-delete-klausul" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="form-delete-head" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="form-delete-sub" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection
@push('scripts')
    <script>
        function addSubKlausulFieldEdit(name = '', code = '') {
            const container = document.getElementById('subKlausulListEdit');
            const subIndex = container.children.length;
            const subDiv = document.createElement('div');
            subDiv.className = 'sub-klausul-item border rounded p-2 mb-2 bg-white';
            subDiv.innerHTML = [
                '<div class="d-flex justify-content-between align-items-center mb-2">',
                `<small class="fw-semibold">Sub #${subIndex + 1}</small>`,
                '<button type="button" onclick="removeSubKlausulEdit(this)" class="btn btn-sm btn-danger btn-xs">',
                '<i class="bi bi-x"></i>',
                '</button>',
                '</div>',
                '<div class="mb-2">',
                `<input type="text" name="sub_names[${subIndex}]" class="form-control form-control-sm border-1" placeholder="Sub name" value="${name || ''}" required>`,
                '</div>',
                '<div>',
                `<input type="text" name="sub_codes[${subIndex}]" class="form-control form-control-sm border-1" placeholder="Sub code (optional)" value="${code || ''}">`,
                '</div>'
            ].join('');
            container.appendChild(subDiv);
        }

        function removeSubKlausulEdit(btn) {
            btn.closest('.sub-klausul-item').remove();
        }

        // === SUB KLAUSUL FIELDS FOR ADD HEAD ===
        function addSubKlausulFieldHead() {
            const container = document.getElementById('subKlausulListHead');
            const subIndex = container.children.length;
            const subDiv = document.createElement('div');
            subDiv.className = 'sub-klausul-item border rounded p-2 mb-2 bg-white';
            subDiv.innerHTML = [
                '<div class="d-flex justify-content-between align-items-center mb-2">',
                `<small class="fw-semibold">Sub #${subIndex + 1}</small>`,
                '<button type="button" onclick="removeSubKlausulHead(this)" class="btn btn-sm btn-danger btn-xs">',
                '<i class="bi bi-x"></i>',
                '</button>',
                '</div>',
                '<div class="mb-2">',
                `<input type="text" name="sub_names[${subIndex}]" class="form-control form-control-sm border-1" placeholder="Sub name" required>`,
                '</div>',
                '<div>',
                `<input type="text" name="sub_codes[${subIndex}]" class="form-control form-control-sm border-1" placeholder="Sub code (optional)">`,
                '</div>'
            ].join('');
            container.appendChild(subDiv);
        }

        function removeSubKlausulHead(btn) {
            btn.closest('.sub-klausul-item').remove();
        }

        let headKlausulIndex = 0;

        function addHeadKlausulField() {
            headKlausulIndex++;
            const container = document.getElementById('headKlausulContainer');
            const headDiv = document.createElement('div');
            headDiv.className = 'head-klausul-group border rounded p-3 mb-3 bg-light';
            headDiv.setAttribute('data-head-index', headKlausulIndex);
            headDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary">Head #${headKlausulIndex + 1}</strong>
                        <button type="button" onclick="removeHeadKlausul(this)" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Head Name <span class="text-danger">*</span></label>
                        <input type="text" name="heads[${headKlausulIndex}][name]" class="form-control border-1" placeholder="Input head klausul name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Head Code</label>
                        <input type="text" name="heads[${headKlausulIndex}][code]" class="form-control border-1" placeholder="Input head code (optional)">
                    </div>
                    <div class="sub-klausul-container" data-head-index="${headKlausulIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Sub Klausul</label>
                            <button type="button" onclick="addSubKlausulField(${headKlausulIndex})" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus"></i> Add Sub
                            </button>
                        </div>
                        <div class="sub-klausul-list"></div>
                    </div>
                `;
            container.appendChild(headDiv);
        }

        function removeHeadKlausul(btn) {
            btn.closest('.head-klausul-group').remove();
            updateHeadNumbers();
        }

        function updateHeadNumbers() {
            document.querySelectorAll('.head-klausul-group').forEach((group, index) => {
                group.querySelector('strong').textContent = `Head #${index + 1}`;
            });
        }

        function addSubKlausulField(headIndex) {
            const container = document.querySelector(
                `.sub-klausul-container[data-head-index="${headIndex}"] .sub-klausul-list`);
            const subIndex = container.children.length;
            const subDiv = document.createElement('div');
            subDiv.className = 'sub-klausul-item border rounded p-2 mb-2 bg-white';
            subDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="fw-semibold">Sub #${subIndex + 1}</small>
                        <button type="button" onclick="removeSubKlausul(this)" class="btn btn-sm btn-danger btn-xs">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="heads[${headIndex}][subs][${subIndex}][name]" class="form-control form-control-sm border-1" placeholder="Sub name" required>
                    </div>
                    <div>
                        <input type="text" name="heads[${headIndex}][subs][${subIndex}][code]" class="form-control form-control-sm border-1" placeholder="Sub code (optional)">
                    </div>
                `;
            container.appendChild(subDiv);
        }

        function removeSubKlausul(btn) {
            btn.closest('.sub-klausul-item').remove();
        }

        function toggleKlausulCollapse(element) {
            const collapse = element.nextElementSibling;
            const chevron = element.querySelector('.klausul-chevron');
            collapse.classList.toggle('hidden');
            chevron.style.transform = collapse.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
        }

        document.addEventListener('DOMContentLoaded', () => {
            // === ADD KLAUSUL ===
            document.getElementById('btnAddKlausul').addEventListener('click', () => {
                // Reset form
                document.getElementById('formAddKlausul').reset();
                headKlausulIndex = 0;
                const container = document.getElementById('headKlausulContainer');
                // Keep only first head, remove others
                const allHeads = container.querySelectorAll('.head-klausul-group');
                allHeads.forEach((head, index) => {
                    if (index > 0) head.remove();
                });
                // Clear subs from first head
                container.querySelector('.sub-klausul-list').innerHTML = '';

                const modal = new bootstrap.Modal(document.getElementById('modalAddKlausul'));
                modal.show();
            });

            // === EDIT KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-edit-klausul').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const klausulItem = btn.closest('.klausul-item');
                    const klausulName = klausulItem.querySelector('.font-semibold').textContent
                        .trim();

                    document.getElementById('edit_klausul_name').value = klausulName;
                    document.getElementById('formEditKlausul').action =
                        `/master/ftpp/klausul/update-main/${id}`;

                    // Set audit type value
                    const auditTypeSpan = klausulItem.querySelector('.text-xs span[data-id]');
                    if (auditTypeSpan) {
                        const auditTypeId = auditTypeSpan.getAttribute('data-id');
                        document.getElementById('edit_audit_type_id').value = auditTypeId;
                    }

                    const modal = new bootstrap.Modal(document.getElementById(
                        'modalEditKlausul'));
                    modal.show();
                });
            });

            // === DELETE KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-delete-klausul').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    Swal.fire({
                        title: 'Delete Klausul?',
                        text: 'This will delete all related head and sub klausuls',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Delete'
                    }).then(result => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete-klausul');
                            form.action = `/master/ftpp/klausul/destroy-main/${id}`;
                            form.submit();
                        }
                    });
                });
            });

            // === ADD HEAD KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-add-head').forEach(btn => {
                btn.addEventListener('click', () => {
                    const klausulId = btn.dataset.klausulId;
                    const klausulItem = btn.closest('.klausul-item');
                    const klausulName = klausulItem.querySelector('.font-semibold').textContent;

                    document.getElementById('add_head_klausul_name').value = klausulName;
                    document.getElementById('add_head_klausul_id').value = klausulId;
                    const form = document.getElementById('formAddHead');
                    form.action = `/master/ftpp/klausul`;
                    // Remove _method input if exists (should be POST for add)
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (methodInput) methodInput.remove();

                    // Clear previous sub klausul fields
                    document.getElementById('subKlausulListHead').innerHTML = '';

                    const modal = new bootstrap.Modal(document.getElementById('modalAddHead'));
                    modal.show();
                });
            });

            // === EDIT HEAD KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-edit-head').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const headItem = btn.closest('.head-item');
                    const headName = headItem.querySelector('.font-semibold').textContent;
                    const headCode = headItem.querySelector('.font-mono')?.textContent.trim() ||
                        '';

                    document.getElementById('edit_head_name').value = headName;
                    document.getElementById('edit_head_code').value = headCode === '-' ? '' :
                        headCode;
                    // Set correct action for update route
                    document.getElementById('formEditHead').action =
                        `/master/ftpp/klausul/update/${id}`;

                    // Clear previous sub klausul fields
                    const subList = document.getElementById('subKlausulListEdit');
                    subList.innerHTML = '';

                    // Populate sub klausul fields from DOM
                    const subItems = headItem.querySelectorAll('.sub-item');
                    if (subItems.length > 0) {
                        subItems.forEach(sub => {
                            const subName = sub.querySelector('.text-sm').textContent
                                .trim();
                            const subCode = sub.querySelector('.font-mono').textContent
                                .trim() || '';
                            addSubKlausulFieldEdit(subName, subCode === '-' ? '' :
                                subCode);
                        });
                    }

                    const modal = new bootstrap.Modal(document.getElementById('modalEditHead'));
                    modal.show();
                });
            });

            // === DELETE HEAD KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-delete-head').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    Swal.fire({
                        title: 'Delete Head Klausul?',
                        text: 'This will delete all related sub klausuls',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Delete'
                    }).then(result => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete-head');
                            form.action = `/master/ftpp/klausul/${id}`;
                            form.submit();
                        }
                    });
                });
            });

            // === ADD SUB KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-add-sub').forEach(btn => {
                btn.addEventListener('click', () => {
                    const headId = btn.dataset.headId;
                    document.getElementById('subKlausulTitle').innerHTML =
                        '<i class="bi bi-plus-circle me-2"></i> Add Sub Klausul';
                    document.getElementById('sub_id').value = '';
                    document.getElementById('sub_name').value = '';
                    document.getElementById('sub_code').value = '';
                    document.getElementById('sub_head_id').value = headId;
                    const form = document.getElementById('formSubKlausul');
                    form.action = `/master/ftpp/klausul/sub`;
                    // Remove _method for POST
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (methodInput) methodInput.remove();
                    const modal = new bootstrap.Modal(document.getElementById('modalSubKlausul'));
                    modal.show();
                });
            });

            // === EDIT SUB KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-edit-sub').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    const headId = btn.dataset.headId;
                    const subItem = btn.closest('.sub-item');
                    const subName = subItem.querySelector('.text-sm').textContent;
                    const subCode = subItem.querySelector('.font-mono').textContent.replace(
                        'Code: ', '') || '';

                    document.getElementById('subKlausulTitle').innerHTML =
                        '<i class="bi bi-pencil-square me-2"></i> Edit Sub Klausul';
                    document.getElementById('sub_id').value = id;
                    document.getElementById('sub_name').value = subName;
                    document.getElementById('sub_code').value = subCode;
                    document.getElementById('sub_head_id').value = headId;
                    const form = document.getElementById('formSubKlausul');
                    form.action = `/master/ftpp/klausul/sub/${id}`;
                    // Set method to PUT for edit
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        form.appendChild(methodInput);
                    }
                    methodInput.value = 'PUT';
                    const modal = new bootstrap.Modal(document.getElementById('modalSubKlausul'));
                    modal.show();
                });
            });

            // === DELETE SUB KLAUSUL ===
            document.querySelectorAll('#section-klausul .btn-delete-sub').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    Swal.fire({
                        title: 'Delete Sub Klausul?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Delete'
                    }).then(result => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete-sub');
                            form.action = `/master/ftpp/klausul/sub/${id}`;
                            form.submit();
                        }
                    });
                });
            });
        });

        // Feather icons
        feather.replace();
    </script>

    {{-- Toggle head klausul --}}
    <script>
        function toggleHeadKlausulCollapse(element) {
            const collapse = element.parentElement.querySelector('.head-klausul-collapse');
            const chevron = element.querySelector('.head-klausul-chevron');
            if (collapse) {
                collapse.classList.toggle('hidden');
                if (chevron) {
                    chevron.style.transform = collapse.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
                }
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .form-control,
        .form-select {
            border: 1px solid #d1d5db !important;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
        }

        .klausul-item {
            transition: all 0.2s ease;
        }

        .klausul-collapse {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .head-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            transition: all 0.2s ease;
        }

        .head-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sub-item {
            background: linear-gradient(135deg, #fafbfc 0%, #ffffff 100%);
            transition: all 0.2s ease;
        }

        .sub-item:hover {
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        .head-klausul-group {
            background: linear-gradient(135deg, #f0f9ff 0%, #f8f9fa 100%);
            border: 2px solid #e0e7ff !important;
        }

        .sub-klausul-item {
            background: #ffffff;
            border: 1px solid #e5e7eb !important;
        }

        .sub-klausul-item:hover {
            background: #f9fafb;
        }

        .btn-xs {
            padding: 0.15rem 0.3rem;
            font-size: 0.75rem;
        }
    </style>
@endpush
