@extends('layouts.app')
@section('title', 'Edit Audit Finding')
@section('subtitle',
    ' Edit finding for FTPP #' .
    $finding->registration_number .
    '. Please update the details below to edit
    the FTPP finding.')
@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-folder me-1"></i> FTPP
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Edit Finding</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="px-6 space-y-6">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Edit Finding</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Edit finding for FTPP #{{ $finding->registration_number }}. Please update the details below to edit
                        the FTPP finding.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>
                        <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-folder me-1"></i> FTPP
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">Edit Finding</li>
                </ol>
            </nav>
        </div> --}}

        <div>
            <div x-data="editFindingApp()" x-init="init()">
                <form action="{{ route('ftpp.audit-finding.update', $finding->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- CARD WRAPPER -->
                    <div class="gap-4">
                        <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                            <h5 class="font-bold text-gray-700">AUDITOR / INISIATOR</h5>

                            <!-- AUDIT TYPE -->
                            <div>
                                <label class="font-semibold block">Audit Type: <span class="text-danger">*</span></label>
                                <div class="mt-2 space-y-1">
                                    @foreach ($auditTypes as $type)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="audit_type_id" value="{{ $type->id }}"
                                                x-model="form.audit_type_id">
                                            {{ $type->name }}
                                        </label>
                                    @endforeach
                                </div>

                                <!-- SUB AUDIT TYPE -->
                                <div id="subAuditType" class="mt-2 ml-4 space-y-1">
                                    @foreach ($subAudit as $sub)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="sub_audit_type_id" value="{{ $sub->id }}"
                                                disabled>
                                            {{ $sub->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- DEPARTMENT / PROCESS / PRODUCT -->
                            <div class="flex items-center space-y-1">
                                <label class="font-semibold">Department / Process / Product: <span
                                        class="text-danger">*</span></label>

                                <input type="hidden" id="selectedPlant" name="plant">
                                <input type="hidden" id="selectedDepartment" name="department_id"
                                    x-model="form.department_id">
                                <input type="hidden" id="selectedProcess" name="process_id" x-model="form.process_id">
                                <input type="hidden" id="selectedProduct" name="product_id" x-model="form.product_id">

                                <div class="flex items-center ml-2">
                                    <button type="button"
                                        class="px-3 py-1 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
                                        onclick="openPlantSidebar()">
                                        Choose
                                    </button>
                                    <div id="plantDeptDisplay" x-text="form._plant_display ?? '-'"
                                        class="ml-4 text-gray-700 border border-gray-600 w-[500px] rounded px-2 py-1">
                                        -
                                    </div>
                                </div>
                            </div>

                            <!-- AUDITEE -->
                            <div class="flex items-center space-y-1">
                                <label class="font-semibold">Auditee: <span class="text-danger">*</span></label>

                                <div class="flex items-center ml-2">
                                    <button type="button" onclick="openAuditeeSidebar()"
                                        class="px-3 py-1 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                                        Choose
                                    </button>

                                    <div id="selectedAuditees" x-html="form._auditee_html ?? '-'"
                                        class="flex flex-wrap gap-2 ml-4 items-center border border-gray-600 w-[500px] h-8.5 rounded px-2 py-1">
                                        -
                                    </div>
                                </div>

                                <!-- Hidden holder for selected auditees (NOT submitted directly).
                                    We avoid naming this input so only the dynamic `auditee_ids[]` inputs
                                    created by `saveHeaderOnly()` are included in the POST request. -->
                                <input type="hidden" id="auditee_ids" x-model="form.auditee_ids">
                            </div>

                            <!-- ROW: Auditor / Date / Reg Number (tidak full width -> gunakan grid kolom) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="font-semibold block">Auditor / Inisiator: <span
                                            class="text-danger">*</span></label>
                                    <select name="auditor_id" x-model="form.auditor_id"
                                        class="border border-gray-300 rounded w-full p-2 focus:outline-none">
                                        <option value="">-- Choose Auditor --</option>
                                        @foreach ($auditors as $auditor)
                                            <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="font-semibold block">Date: <span class="text-danger">*</span></label>
                                    <input type="date" name="created_at" x-model="form.created_at"
                                        class="border border-gray-300 rounded w-full p-2 focus:outline-none"
                                        value="{{ now()->toDateString() }}">
                                </div>

                                <div>
                                    <label class="font-semibold block">Registration Number: <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="registration_number" id="reg_number"
                                        x-model="form.registration_number"
                                        class="border border-gray-300 rounded w-full p-2 bg-gray-100">
                                </div>
                            </div>
                            <!-- FINDING CATEGORY -->
                            <div>
                                <label class="font-semibold">Finding Category: <span class="text-danger">*</span></label>
                                <div class="mt-1">
                                    @foreach ($findingCategories as $category)
                                        <label class="mr-4">
                                            <input type="radio" name="finding_category_id"
                                                x-model="form.finding_category_id" value="{{ $category->id }}">
                                            {{ ucfirst($category->name) }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                                <!-- FINDING -->
                                <div>
                                    <label class="font-semibold">Finding / Issue: <span
                                            class="text-danger">*</span></label>
                                    <textarea name="finding_description" x-model="form.finding_description" class="w-full border rounded p-2 h-24"
                                        required></textarea>
                                </div>

                                <div class="flex justify-between items-start">
                                    <!-- DUE DATE -->
                                    <div>
                                        <label class="font-semibold">Duedate: <span class="text-danger">*</span></label>
                                        <input type="date" name="due_date" x-model="form.due_date"
                                            class="border-b border-gray-400 ml-2" required
                                            min="{{ now()->toDateString() }}">
                                    </div>

                                    <!-- CLAUSE SELECT -->
                                    <div class="text-right">
                                        <input type="hidden" id="selectedSub" name="sub_klausul_id[]">

                                        <div class="flex flex-col items-end">
                                            <button type="button" onclick="openSidebar()"
                                                class="px-3 py-1  bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                                                Select Clause
                                            </button>
                                            <div class="border border-gray-600 rounded w-[500px] h-auto mt-2 px-2 py-1">
                                                <div id="selectedSubContainer" class="flex flex-wrap gap-2 justify-end">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ATTACHMENT --}}
                            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                                <div class="font-semibold text-lg text-gray-700">Attachments</div>

                                {{-- Tips Alert --}}
                                <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2">
                                    <i
                                        class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                                        <p class="text-xs text-yellow-700 leading-relaxed">
                                            Only <strong>PDF, PNG, JPG, and JPEG</strong> files are allowed.
                                            Maximum total file size is <strong>20 MB</strong>.
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <!-- Preview containers -->
                                    <div id="previewImageContainer" class="mt-2 flex flex-wrap gap-2"></div>
                                    <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>

                                    <!-- Attachment button (paperclip) -->
                                    <div class="relative inline-block">
                                        @if (in_array(optional(auth()->user()->roles->first())->name, ['Admin', 'Auditor']))
                                            <button id="attachBtn" type="button"
                                                class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                                                aria-haspopup="true" aria-expanded="false" title="Attach files">
                                                <i data-feather="paperclip" class="w-4 h-4"></i>
                                                <span id="attachCount" class="text-xs text-gray-600 hidden">0</span>
                                            </button>
                                        @endif

                                        <!-- Small menu seperti email (hidden, muncul saat klik) -->
                                        <div id="attachMenu"
                                            class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow-lg z-20">
                                            <button id="attachImages" type="button"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                <i data-feather="image" class="w-4 h-4"></i>
                                                <span class="text-sm">Upload Images</span>
                                            </button>
                                            <button id="attachDocs" type="button"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                <i data-feather="file-text" class="w-4 h-4"></i>
                                                <span class="text-sm">Upload Documents</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden file inputs - UBAH ke 'attachments[]' untuk konsistensi -->
                                    <input type="file" id="photoInput" name="attachments[]" accept="image/*" multiple
                                        class="hidden">
                                    <input type="file" id="fileInput" name="attachments[]" accept=".pdf" multiple
                                        class="hidden">

                                    <!-- ✅ Error message container for attachments -->
                                    <div id="attachmentErrorContainer"
                                        class="hidden mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded-r">
                                        <div class="flex items-start">
                                            <i data-feather="alert-circle"
                                                class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
                                            <div id="attachmentErrorMessage" class="text-sm text-red-700"></div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="saveChangesFinding()"
                                    class="ml-auto mt-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white px-3 py-1 rounded-md hover:from-primaryDark hover:to-primaryLight transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Sidebar Klausul -->
        <div id="sidebarKlausul"
            class="fixed top-24 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

            <!-- Header -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
                <h2 class="font-semibold text-lg text-gray-800">Select Clause</h2>
                <button type="button" onclick="closeSidebar()" class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
                    <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
                </button>
            </div>

            <!-- Clause -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Clause:</label>
            <select id="selectKlausul"
                class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 mb-4">
                <option value="">-- Choose Clause --</option>
            </select>

            <!-- Head Clause -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Head Clause:</label>
            <select id="selectHead"
                class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 disabled:opacity-50 mb-4" disabled>
                <option value="">-- Choose Head Clause --</option>
            </select>

            <!-- Sub Clause -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Sub Clause:</label>
            <select id="selectSub"
                class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50 disabled:opacity-50 mb-4" disabled>
                <option value="">-- Choose Sub Clause --</option>
            </select>

            <!-- Submit Button -->
            <button type="button" onclick="addSubKlausul()"
                class="flex items-center justify-center gap-2 px-4 py-2 w-full rounded-lg
               bg-gradient-to-r from-primaryLight to-primaryDark text-white hover:from-primaryDark hover:to-primaryLight transition-colors">
                <i data-feather="plus" class="w-4 h-4"></i> Add
            </button>
        </div>

        {{-- Sidebar Select Dept/Proc/Prod by Plant --}}
        <div id="sidebarPlant"
            class="fixed top-24 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
                <h2 class="text-lg font-semibold text-gray-800">Select Plant / Department</h2>
                <button type="button" onclick="closePlantSidebar()"
                    class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
                    <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
                </button>
            </div>

            <!-- Plant -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Plant:</label>
            <select id="plantSelect"
                class="border border-gray-300 p-2 w-full rounded-lg mb-4 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Choose Plant --</option>
                <option value="Body">Body</option>
                <option value="Unit">Unit</option>
                <option value="Electric">Electric</option>
                <option value="All">All</option>
            </select>

            <!-- Department -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Department:</label>
            <select id="sidebarDepartment"
                class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4" disabled>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <!-- Process -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Process:</label>
            <select id="sidebarProcess"
                class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4" disabled>
                @foreach ($processes as $proc)
                    <option value="{{ $proc->id }}">{{ ucfirst($proc->name) }}</option>
                @endforeach
            </select>

            <!-- Product -->
            <label class="block text-sm font-medium text-gray-700 mb-1">Product:</label>
            <select id="sidebarProduct"
                class="tom-select w-full border rounded-lg p-2 bg-gray-50 disabled:opacity-50 mb-4" disabled>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>

            <button type="button" onclick="submitSidebarPlant()"
                class="flex items-center justify-center gap-2 px-4 py-2 w-full rounded-lg bg-gradient-to-l from-primaryLight to-primaryDark text-white
               hover:from-primaryDark hover:to-primaryLight transition-colors shadow">
                <i data-feather="plus" class="w-4 h-4"></i> Add
            </button>
        </div>

        {{-- Sidebar select auditee --}}
        <div id="auditeeSidebar"
            class="fixed top-24 right-2 w-full md:w-1/3 lg:w-1/4 h-fit bg-white/95 backdrop-blur-xl shadow-2xl
           border-l border-gray-200 p-4 hidden overflow-y-auto rounded-xl">

            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 sticky top-0 bg-white/95">
                <h2 class="text-lg font-semibold text-gray-800">Select Auditee</h2>
                <button type="button" onclick="closeAuditeeSidebar()"
                    class="p-1 bg-red-600 hover:bg-red-100  rounded-full">
                    <i data-feather="x" class="w-4 h-4 text-white hover:text-red-600"></i>
                </button>
            </div>

            <select id="auditeeSelect" multiple class="w-full border rounded-lg p-2 bg-gray-50"
                placeholder="Search or select auditee..."></select>

            <button type="button" onclick="saveAuditeeSelection()"
                class="flex items-center justify-center gap-2 mt-4 bg-blue-600 text-white px-4 py-2 w-full rounded-lg
               hover:bg-blue-700 transition shadow">
                <i data-feather="plus" class="w-4 h-4"></i> Add
            </button>
        </div>
    </div>
@endsection
<x-flash-message />
@push('scripts')
    {{-- Display data handle --}}
    <script>
        function editFindingApp(data) {
            return {
                selectedId: null,
                form: {
                    status_id: 7,
                    audit_type_id: "",
                    sub_audit_type_id: "",
                    auditor_id: "",
                    created_at: "",
                    due_date: "",
                    registration_number: "",
                    finding_description: "",
                    finding_category_id: "",
                    auditee_ids: "",
                    sub_klausul_id: [],

                    sub_audit: [],
                    auditees: [],
                    sub_klausul: [],
                },

                init() {

                    // Inject data dari Laravel → Alpine
                    this.form = @json($finding);

                    this.selectedId = this.form.id;

                    // convert tanggal
                    this.form.created_at = this.form.created_at?.substring(0, 10);
                    this.form.due_date = this.form.due_date?.substring(0, 10);

                    // tampilkan plant
                    this.form._plant_display = [this.form.department?.name, this.form.process?.name, this.form.product
                            ?.name
                        ]
                        .filter(Boolean)
                        .join(" / ");

                    // Auditee
                    this.form.auditee_ids = (this.form.auditee ?? []).map(a => a.id);

                    // render auditees with remove button for each
                    this.form._auditee_html = (this.form.auditee ?? [])
                        .map(a =>
                            `<span data-id="${a.id}" class='bg-blue-100 px-2 py-1 rounded flex items-center gap-2'>${a.name} <button type="button" class="remove-auditee text-red-500" data-id="${a.id}">×</button></span>`
                        )
                        .join("");

                    // Sub Audit
                    this.loadSubAudit();

                    // Sub Klausul
                    this.loadSubKlausul();

                    this.$nextTick(() => {
                        // pastikan container preview ada; jika tidak, buat dan sisipkan ke form
                        let previewImageContainer = document.getElementById('previewImageContainer');
                        let previewFileContainer = document.getElementById('previewFileContainer');

                        if (!previewImageContainer || !previewFileContainer) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'mt-4 border p-2 rounded';

                            previewImageContainer = document.createElement('div');
                            previewImageContainer.id = 'previewImageContainer';
                            previewImageContainer.className = 'flex gap-2 flex-wrap';

                            previewFileContainer = document.createElement('div');
                            previewFileContainer.id = 'previewFileContainer';
                            previewFileContainer.className = 'mt-2 flex flex-col gap-2';

                            wrapper.appendChild(previewImageContainer);
                            wrapper.appendChild(previewFileContainer);

                            const target = document.querySelector('form') || document.body;
                            target.appendChild(wrapper);
                        }

                        // render existing files jika tersedia di form (attachments/file)
                        const files = this.form.attachments ?? this.form.file ?? [];

                        if (files && files.length) {
                            previewImageContainer.innerHTML = '';
                            previewFileContainer.innerHTML = '';

                            const baseUrl = '/storage/';

                            files.forEach(f => {
                                const path = f.file_path ?? f.path ?? '';
                                const fullUrl = baseUrl + path;
                                const filename = f.original_name ?? path.split('/').pop() ?? '';
                                const fid = f.id ?? f.document_file_id ?? f.file_id ?? '';

                                if ((path + filename).match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                    const wrapper = document.createElement('div');
                                    wrapper.className = 'relative';

                                    const img = document.createElement('img');
                                    img.src = fullUrl;
                                    img.className = 'w-24 h-24 object-cover border rounded';

                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className =
                                        'absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs remove-attachment';
                                    btn.setAttribute('data-id', fid);
                                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';

                                    btn.addEventListener('click', async (e) => {
                                        if (!fid) return alert('Cannot remove unsaved file');
                                        if (!confirm(
                                                'Remove this attachment? This will delete the file from storage.'
                                            )) return;
                                        try {
                                            const token = document.querySelector(
                                                'meta[name="csrf-token"]')?.getAttribute(
                                                'content') || '';
                                            const res = await fetch(
                                                `/ftpp/audit-finding/attachment/${fid}`, {
                                                    method: 'DELETE',
                                                    headers: {
                                                        'X-CSRF-TOKEN': token,
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    }
                                                });
                                            const data = await res.json().catch(() => ({}));
                                            if (!res.ok) {
                                                alert(data.message ||
                                                    'Failed to remove attachment');
                                                return;
                                            }

                                            // remove element from DOM
                                            wrapper.remove();
                                            // update Alpine state
                                            this.form.attachments = (this.form.attachments ||
                                            []).filter(x => String(x.id) !== String(fid));
                                            this.form.file = (this.form.file || []).filter(x =>
                                                String(x.id) !== String(fid));
                                        } catch (err) {
                                            console.error(err);
                                            alert('Error removing attachment');
                                        }
                                    });

                                    wrapper.appendChild(img);
                                    wrapper.appendChild(btn);
                                    previewImageContainer.appendChild(wrapper);
                                } else {
                                    const wrapper = document.createElement('div');
                                    wrapper.className =
                                        'flex gap-2 text-sm border p-2 rounded items-center';

                                    const icon = document.createElement('i');
                                    icon.setAttribute('data-feather', 'file-text');

                                    const name = document.createElement('span');
                                    name.textContent = filename;

                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className =
                                        'ml-auto bg-red-600 text-white rounded-full p-1 text-xs remove-attachment';
                                    btn.setAttribute('data-id', fid);
                                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';

                                    btn.addEventListener('click', async () => {
                                        if (!fid) return alert('Cannot remove unsaved file');
                                        if (!confirm(
                                                'Remove this attachment? This will delete the file from storage.'
                                            )) return;
                                        try {
                                            const token = document.querySelector(
                                                'meta[name="csrf-token"]')?.getAttribute(
                                                'content') || '';
                                            const res = await fetch(
                                                `/ftpp/audit-finding/attachment/${fid}`, {
                                                    method: 'DELETE',
                                                    headers: {
                                                        'X-CSRF-TOKEN': token,
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    }
                                                });
                                            const data = await res.json().catch(() => ({}));
                                            if (!res.ok) {
                                                alert(data.message ||
                                                    'Failed to remove attachment');
                                                return;
                                            }

                                            wrapper.remove();
                                            this.form.attachments = (this.form.attachments ||
                                            []).filter(x => String(x.id) !== String(fid));
                                            this.form.file = (this.form.file || []).filter(x =>
                                                String(x.id) !== String(fid));
                                        } catch (err) {
                                            console.error(err);
                                            alert('Error removing attachment');
                                        }
                                    });

                                    wrapper.append(icon, name, btn);
                                    previewFileContainer.appendChild(wrapper);
                                }
                            });

                            if (typeof feather !== 'undefined' && feather.replace) {
                                feather.replace();
                            }

                            // attach remove handlers for auditee remove buttons (confirm + AJAX)
                            const auditeeContainer = document.getElementById('selectedAuditees');
                            if (auditeeContainer) {
                                auditeeContainer.querySelectorAll('.remove-auditee').forEach(btn => {
                                    btn.addEventListener('click', async (ev) => {
                                        const id = btn.getAttribute('data-id');
                                        if (!confirm(
                                                'Remove this auditee from finding? This will remove it immediately.'
                                            )) return;

                                        try {
                                            const token = document.querySelector(
                                                'meta[name="csrf-token"]')?.getAttribute(
                                                'content') || '';
                                            const res = await fetch(
                                                `/ftpp/audit-finding/${this.form.id}/auditee/${id}`, {
                                                    method: 'DELETE',
                                                    headers: {
                                                        'X-CSRF-TOKEN': token,
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    }
                                                });
                                            const data = await res.json().catch(() => ({}));
                                            if (!res.ok) {
                                                alert(data.message ||
                                                    'Failed to remove auditee');
                                                return;
                                            }

                                            // update local Alpine state and UI
                                            this.form.auditee = (this.form.auditee || [])
                                                .filter(x => String(x.id) !== String(id));
                                            this.form.auditee_ids = (this.form.auditee || [])
                                                .map(x => x.id);
                                            this.form._auditee_html = (this.form.auditee || [])
                                                .map(a =>
                                                    `<span data-id="${a.id}" class='bg-blue-100 px-2 py-1 rounded flex items-center gap-2'>${a.name} <button type="button" class="remove-auditee text-red-500" data-id="${a.id}">×</button></span>`
                                                ).join('');

                                            // re-attach handlers for new buttons
                                            this.$nextTick(() => {
                                                const newContainer = document
                                                    .getElementById('selectedAuditees');
                                                if (!newContainer) return;
                                                newContainer.querySelectorAll(
                                                    '.remove-auditee').forEach(
                                                    b => {
                                                        b.addEventListener('click',
                                                            async (e) => b
                                                                .dispatchEvent(
                                                                    new Event(
                                                                        'click')
                                                                ));
                                                    });
                                            });
                                        } catch (err) {
                                            console.error(err);
                                            alert('Error removing auditee');
                                        }
                                    });
                                });
                            }
                        }
                    });

                    // console.log("FORM DATA:", this.form);

                },

                loadSubAudit() {
                    let list = @json($subAudit);
                    const subContainer = document.getElementById('subAuditType');
                    subContainer.innerHTML = "";

                    if (!list.length) {
                        subContainer.innerHTML = `<small class="text-gray-500">No Sub Audit Type</small>`;
                        return;
                    }

                    list.forEach(s => {
                        subContainer.insertAdjacentHTML('beforeend', `
                    <label>
                        <input type="radio" name="sub_audit_type_id"
                            value="${s.id}"
                            ${s.id === this.form.sub_audit_type_id ? 'checked' : ''}>
                        ${s.name}
                    </label>
                `);
                    });
                },

                loadSubKlausul() {
                    const list = this.form.sub_klausuls ?? [];

                    // Wait for DOM to be updated / elements to exist
                    this.$nextTick(() => {
                        const container = document.getElementById('selectedSubContainer');

                        if (!container) return;

                        container.innerHTML = "";

                        if (!list.length) {
                            container.innerHTML = `<small class="text-gray-500">No Sub Klausul</small>`;
                            return;
                        }

                        list.forEach(s => {
                            // support different shapes: { code, name } or nested objects or alternative keys
                            const code = s.code ?? s.klausul_code ?? (s.sub_klausul?.code ?? '') ?? '';
                            const name = s.name ?? s.title ?? (s.sub_klausul?.name ?? '') ?? '';

                            const span = document.createElement('span');
                            span.className =
                                'bg-blue-100 px-2 py-1 rounded mr-1 inline-block flex items-center gap-2';
                            const sid = s.id ?? (s.sub_klausul ? s.sub_klausul.id : '');
                            span.dataset.id = sid;
                            span.innerHTML =
                                `${code} - ${name} <button type="button" class="remove-sub text-red-500" data-id="${sid}">×</button>`;
                            container.appendChild(span);

                            // attach click handler to remove sub klausul from Alpine state
                            const btn = span.querySelector('.remove-sub');
                            if (btn) {
                                btn.addEventListener('click', async () => {
                                    const removeId = btn.getAttribute('data-id');
                                    if (!confirm(
                                            'Remove this sub-klausul from finding? This will remove it immediately.'
                                        )) return;
                                    try {
                                        const token = document.querySelector(
                                            'meta[name="csrf-token"]')?.getAttribute(
                                            'content') || '';
                                        const res = await fetch(
                                            `/ftpp/audit-finding/${this.form.id}/sub-klausul/${removeId}`, {
                                                method: 'DELETE',
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                    'Accept': 'application/json',
                                                    'Content-Type': 'application/json'
                                                }
                                            });
                                        const data = await res.json().catch(() => ({}));
                                        if (!res.ok) {
                                            alert(data.message ||
                                                'Failed to remove sub-klausul');
                                            return;
                                        }

                                        // update state and re-render
                                        this.form.sub_klausuls = (this.form.sub_klausuls || [])
                                            .filter(x => String(x.id ?? (x.sub_klausul ? x
                                                .sub_klausul.id : '')) !== String(removeId));
                                        this.loadSubKlausul();
                                    } catch (err) {
                                        console.error(err);
                                        alert('Error removing sub-klausul');
                                    }
                                });
                            }
                        });
                    });
                },
            }
        }
    </script>

    {{-- sidebar for input data handle --}}
    <script>
        // === SIDEBAR OPEN/CLOSE ===
        function openSidebar() {
            document.getElementById('sidebarKlausul').classList.remove('hidden');

            let auditType = document.querySelector('input[name="audit_type_id"]:checked')?.value;
            if (!auditType) return alert('Please choose audit type first');

            // Ambil klausul berdasarkan audit type
            fetch(`/filter-klausul/${auditType}`)
                .then(res => res.json())
                .then(data => {
                    let klausulSelect = document.getElementById('selectKlausul');
                    klausulSelect.innerHTML = '<option value="">-- Choose Clause --</option>';
                    data.forEach(item => {
                        klausulSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                });
        }

        function closeSidebar() {
            document.getElementById('sidebarKlausul').classList.add('hidden');
        }

        // === Audit type relation handler (sub audit type, auditor, klausul) ===
        document.addEventListener('DOMContentLoaded', function() {
            console.log("✅ FTTP script ready");

            // --- Event delegation untuk audit type (tetap pakai karena sudah aman) ---
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name="audit_type_id"]')) {
                    const auditTypeId = e.target.value;
                    console.log("✅ Audit Type dipilih:", auditTypeId);

                    // Ambil data ke server
                    fetch(`/ftpp/get-data/${auditTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("✅ Data diterima:", data);

                            document.getElementById('reg_number').value = data.reg_number;

                            const subContainer = document.getElementById('subAuditType');
                            subContainer.innerHTML = '';
                            if (data.sub_audit?.length) {
                                data.sub_audit.forEach(s => {
                                    subContainer.insertAdjacentHTML('beforeend', `
                                            <label class="block">
                                                <input type="radio" name="sub_audit_type_id" value="${s.id}">
                                                ${s.name}
                                            </label>
                                        `);
                                    const radio = document.querySelector(
                                        `#subAuditType input[value="${s.id}"]`);
                                    if (radio) radio.disabled = false;
                                });
                            } else {
                                subContainer.innerHTML =
                                    '<small class="text-gray-500">There is no sub audit type</small>';
                            }

                            // ✅ Filter Auditor berdasarkan audit type
                            let auditorSelect = document.querySelector('select[name="auditor_id"]');
                            auditorSelect.innerHTML = '<option value="">-- Choose Auditor --</option>';
                            data.auditors.forEach(auditor => {
                                auditorSelect.innerHTML += `
                                        <option value="${auditor.id}">${auditor.name}</option>
                                    `;
                            });
                        })
                        .catch(error => console.error("❌ Error:", error));
                }
            });

            // --- Open/Close sidebar (tidak berubah) ---
            window.openSidebar = function() {
                document.getElementById('sidebarKlausul').classList.remove('hidden');

                let auditType = document.querySelector('input[name="audit_type_id"]:checked')?.value;
                if (!auditType) return alert('Please choose audit type first');

                fetch(`/filter-klausul/${auditType}`)
                    .then(r => r.json())
                    .then(data => {
                        const klausulSelect = document.getElementById('selectKlausul');
                        klausulSelect.innerHTML = '<option value="">-- Choose Clause --</option>';
                        data.forEach(item => {
                            klausulSelect.insertAdjacentHTML('beforeend',
                                `<option value="${item.id}">${item.name}</option>`);
                        });

                        // reset head & sub
                        const headSelect = document.getElementById('selectHead');
                        const subSelect = document.getElementById('selectSub');
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                    })
                    .catch(err => console.error('Error filter-klausul:', err));
            };

            window.closeSidebar = function() {
                document.getElementById('sidebarKlausul').classList.add('hidden');
            };

            // --- Event delegation for klausul/head/sub selects ---
            document.addEventListener('change', async function(e) {
                const target = e.target;

                // user pilih klausul -> load head klausul
                if (target && target.id === 'selectKlausul') {
                    const klausulId = target.value;
                    console.log('Klausul selected:', klausulId);

                    const headSelect = document.getElementById('selectHead');
                    const subSelect = document.getElementById('selectSub');

                    // reset UI
                    headSelect.innerHTML = '<option value="">-- Loading... --</option>';
                    headSelect.disabled = true;
                    subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                    subSelect.disabled = true;

                    if (!klausulId) {
                        // jika di-set ke kosong, kembalikan disabled state
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                        return;
                    }

                    try {
                        const res = await fetch(`/head-klausul/${klausulId}`);
                        const data = await res.json();
                        console.log('head-klausul response', data);

                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        if (data.length > 0) {
                            data.forEach(h => {
                                headSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${h.id}">${h.code} - ${h.name}</option>`
                                );
                            });
                            headSelect.disabled = false;
                        } else {
                            headSelect.disabled = true;
                        }
                    } catch (err) {
                        console.error('Error fetching head-klausul:', err);
                        headSelect.innerHTML = '<option value="">-- Choose Head Clause --</option>';
                        headSelect.disabled = true;
                    }
                }

                // user pilih head -> load sub klausul
                if (target && target.id === 'selectHead') {
                    const headId = target.value;
                    console.log('Head selected:', headId);
                    const subSelect = document.getElementById('selectSub');
                    subSelect.innerHTML = '<option value="">-- Loading... --</option>';
                    subSelect.disabled = true;

                    if (!headId) {
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                        return;
                    }

                    try {
                        const res = await fetch(`/sub-klausul/${headId}`);
                        const data = await res.json();
                        console.log('sub-klausul response', data);

                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        if (data.length > 0) {
                            data.forEach(s => {
                                subSelect.insertAdjacentHTML('beforeend',
                                    `<option value="${s.id}">${s.code} - ${s.name}</option>`
                                );
                            });
                            subSelect.disabled = false;
                        } else {
                            subSelect.disabled = true;
                        }
                    } catch (err) {
                        console.error('Error fetching sub-klausul:', err);
                        subSelect.innerHTML = '<option value="">-- Choose Sub Clause --</option>';
                        subSelect.disabled = true;
                    }
                }
            });

            // pilihSubKlausul remains global function for button onclick
            window.pilihSubKlausul = function() {
                const subSelect = document.getElementById('selectSub');
                if (!subSelect || !subSelect.value) {
                    return alert('Please choose a sub clause first');
                }
                const subId = subSelect.value;
                const subText = subSelect.selectedOptions[0].text;
                document.getElementById('selectedSub').value = subId;
                closeSidebar();
            };

        });

        // === PILIH SUB KLAUSUL (masukkan ke form utama) ===
        function pilihSubKlausul() {
            let subId = document.getElementById('selectSub').value;
            let subText = document.getElementById('selectSub').selectedOptions[0].text;

            document.getElementById('selectedSub').value = subId;

            closeSidebar();
        }

        let selectedSubIds = []; // simpan id yang sudah dipilih

        function addSubKlausul() {
            const subSelect = document.getElementById('selectSub');
            const subId = subSelect.value;
            const subText = subSelect.selectedOptions[0]?.text;

            if (!subId) return alert('Please choose a sub clause first');
            if (selectedSubIds.includes(subId)) return alert('This sub clause is already added');

            selectedSubIds.push(subId);

            // Render preview di form utama
            const container = document.getElementById('selectedSubContainer');
            const span = document.createElement('span');
            span.className = "flex items-end gap-1 bg-blue-100 text-gray-700 px-2 py-1 rounded";
            span.dataset.id = subId;
            span.innerHTML =
                `${subText} <button type="button" class="text-red-500 font-bold"><i data-feather="x" class="w-4 h-4"></i></button>`;
            container.appendChild(span);
            feather.replace(); // render ulang icon feather


            // tombol hapus
            span.querySelector('button').onclick = function() {
                selectedSubIds = selectedSubIds.filter(id => id !== subId);
                span.remove();
            }

            container.appendChild(span);
        }

        // Handle select Department/Process/Product by plant
        let tsDept, tsProc, tsProd; // biar tidak double init

        function openPlantSidebar() {
            document.getElementById('sidebarPlant').classList.remove('hidden');

            const plantSelect = document.getElementById('plantSelect');
            const deptSelect = document.getElementById('sidebarDepartment');
            const procSelect = document.getElementById('sidebarProcess');
            const prodSelect = document.getElementById('sidebarProduct');

            async function loadPlantRelatedData(plant) {
                const deptSelect = document.getElementById('sidebarDepartment');
                const procSelect = document.getElementById('sidebarProcess');
                const prodSelect = document.getElementById('sidebarProduct');

                // Disable select dulu
                deptSelect.disabled = true;
                procSelect.disabled = true;
                prodSelect.disabled = true;

                // Hancurkan TomSelect lama jika ada
                if (tsDept) {
                    tsDept.destroy();
                    tsDept = null;
                }
                if (tsProc) {
                    tsProc.destroy();
                    tsProc = null;
                }
                if (tsProd) {
                    tsProd.destroy();
                    tsProd = null;
                }

                // Reset HTML <select>
                deptSelect.innerHTML = '<option value=""></option>';
                procSelect.innerHTML = '<option value=""></option>';
                prodSelect.innerHTML = '<option value=""></option>';

                if (!plant || plant === "") return;

                try {
                    const [dRes, pRes, prRes] = await Promise.all([
                        fetch(`/get-departments/${plant}`),
                        fetch(`/get-processes/${plant}`),
                        fetch(`/get-products/${plant}`)
                    ]);
                    const [depts, procs, prods] = await Promise.all([
                        dRes.json(),
                        pRes.json(),
                        prRes.json()
                    ]);

                    // Inisialisasi TomSelect lagi
                    tsDept = new TomSelect('#sidebarDepartment', {
                        allowEmptyOption: true,
                        placeholder: "Search Department...",
                        searchField: ['text'],
                        options: depts.map(d => ({
                            value: d.id,
                            text: d.name
                        }))
                    });
                    tsProc = new TomSelect('#sidebarProcess', {
                        allowEmptyOption: true,
                        placeholder: "Search Process...",
                        searchField: ['text'],
                        options: procs.map(p => ({
                            value: p.id,
                            text: p.name.charAt(0).toUpperCase() + p.name.slice(1)
                        }))
                    });
                    tsProd = new TomSelect('#sidebarProduct', {
                        allowEmptyOption: true,
                        placeholder: "Search Product...",
                        searchField: ['text'],
                        options: prods.map(p => ({
                            value: p.id,
                            text: p.name
                        }))
                    });

                    // Enable select
                    tsDept.enable();
                    tsProc.enable();
                    tsProd.enable();

                } catch (error) {
                    console.error('Error loading plant data:', error);
                }
            }

            // Pasang event listener sekali di sini
            plantSelect.addEventListener('change', function() {
                loadPlantRelatedData(this.value);
            });
        }

        function closePlantSidebar() {
            document.getElementById('sidebarPlant').classList.add('hidden');
        }

        async function submitSidebarPlant() {
            const plant = document.getElementById('plantSelect').value;
            const deptSelect = document.getElementById('sidebarDepartment');
            const procSelect = document.getElementById('sidebarProcess');
            const prodSelect = document.getElementById('sidebarProduct');

            const dept = deptSelect.value;
            const proc = procSelect.value;
            const prod = prodSelect.value;

            if (!plant) {
                return alert("Please choose plant first");
            }

            // ✅ harus pilih department
            if (!dept) {
                return alert("Please select Department.");
            }

            // Simpan ke input hidden (kalau kosong, biarkan kosong/null)
            document.getElementById('selectedPlant').value = plant;
            document.getElementById('selectedDepartment').value = dept || "";
            document.getElementById('selectedProcess').value = proc || "";
            document.getElementById('selectedProduct').value = prod || "";

            // Tampilkan teks di halaman
            // tampilkan teks sesuai yang dipilih
            let displayText = [];

            if (deptSelect.value) {
                displayText.push(deptSelect.selectedOptions[0]?.text);
            }
            if (procSelect.value) {
                displayText.push(procSelect.selectedOptions[0]?.text);
            }
            if (prodSelect.value) {
                displayText.push(prodSelect.selectedOptions[0]?.text);
            }

            // kalau tidak ada yang dipilih, kasih "-"
            document.getElementById('plantDeptDisplay').innerText =
                displayText.length > 0 ? displayText.join(' / ') : '-';

            closePlantSidebar();

        }

        let auditeeSelect;

        async function loadAuditeeOptions(dept) {
            try {
                const res = await fetch(`/get-auditee/${dept}`);
                const data = await res.json();

                const select = document.getElementById('auditeeSelect');
                select.innerHTML = ''; // kosongkan dulu

                // masukkan data ke <select>
                data.forEach(a => {
                    const option = document.createElement('option');
                    option.value = a.id;
                    option.textContent = a.name;
                    select.appendChild(option);
                });

                // aktifkan TomSelect kalau belum
                if (!auditeeSelect) {
                    auditeeSelect = new TomSelect('#auditeeSelect', {
                        plugins: ['remove_button'],
                        persist: false,
                        create: false,
                        maxItems: null,
                        placeholder: "Select or search auditee...",
                    });
                } else {
                    auditeeSelect.clear();
                    auditeeSelect.clearOptions();
                    auditeeSelect.addOptions(data.map(a => ({
                        value: a.id,
                        text: a.name
                    })));
                }

            } catch (err) {
                console.error("Error loading auditee:", err);
            }
        }

        function openAuditeeSidebar() {
            const dept = document.getElementById('selectedDepartment').value;
            if (!dept) return alert("Please choose department/plant first!");
            loadAuditeeOptions(dept);
            document.getElementById('auditeeSidebar').classList.remove('hidden');
        }

        function closeAuditeeSidebar() {
            document.getElementById('auditeeSidebar').classList.add('hidden');
        }

        let selectedAuditees = []; // Simpan id auditee

        function saveAuditeeSelection() {
            const auditeeSelect = document.getElementById('auditeeSelect');
            selectedAuditees = Array.from(auditeeSelect.selectedOptions).map(option => ({
                id: option.value,
                name: option.text
            }));

            // Deduplicate by id in case the UI/control returns duplicates
            selectedAuditees = Array.from(new Map(selectedAuditees.map(a => [a.id, a])).values());

            // Tampilkan ke UI
            document.getElementById('selectedAuditees').innerHTML = selectedAuditees.map(a => `
                <span data-id="${a.id}" class="bg-blue-100 px-2 py-1 rounded flex items-center gap-1">
                    ${a.name}
                    <button type="button" onclick="removeAuditee('${a.id}')">
                        <i data-feather="x" class="w-4 h-4 text-red-500"></i>
                    </button>
                </span>
            `).join('');

            // Simpan ke input hidden
            document.getElementById('auditee_ids').value = selectedAuditees.map(a => a.id).join(',');

            feather.replace();
            closeAuditeeSidebar();
        }

        function removeAuditee(id) {
            selectedAuditees = selectedAuditees.filter(a => a.id !== id);
            document.getElementById('auditee_ids').value = selectedAuditees.map(a => a.id).join(',');
            document.querySelector(`span[data-id="${id}"]`)?.remove();
        }
    </script>

    <!-- Attachment Upload Handle Script: trigger inputs, preview, count, click-outside -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const attachBtn = document.getElementById('attachBtn');
            const attachMenu = document.getElementById('attachMenu');
            const attachImages = document.getElementById('attachImages');
            const attachDocs = document.getElementById('attachDocs');
            const attachCount = document.getElementById('attachCount');

            const photoInput = document.getElementById('photoInput');
            const fileInput = document.getElementById('fileInput');

            const previewImageContainer = document.getElementById('previewImageContainer');
            const previewFileContainer = document.getElementById('previewFileContainer');

            // 🔹 Store files accumulated from multiple selections
            let accumulatedPhotoFiles = [];
            let accumulatedFileFiles = [];

            // 🔹 Helper update file list setelah dihapus
            function updateFileInput(input, filesArray) {
                const dt = new DataTransfer();
                filesArray.forEach(file => dt.items.add(file));
                input.files = dt.files;
            }

            // 🔹 Update badge total attachment
            function updateAttachCount() {
                const total = (photoInput.files?.length || 0) + (fileInput.files?.length || 0);
                if (total > 0) {
                    attachCount.textContent = total;
                    attachCount.classList.remove('hidden');
                } else {
                    attachCount.classList.add('hidden');
                }
            }

            // 🔹 Preview Image + tombol delete
            function displayImages() {
                // ⚠️ JANGAN hapus semua - hanya update bagian new files
                // Cari atau buat container khusus untuk new files
                let newFilesContainer = previewImageContainer.querySelector('.new-files-container');

                if (!newFilesContainer) {
                    newFilesContainer = document.createElement('div');
                    newFilesContainer.className = 'new-files-container flex flex-wrap gap-2';
                    previewImageContainer.appendChild(newFilesContainer);
                } else {
                    newFilesContainer.innerHTML = ''; // Hanya clear new files
                }

                accumulatedPhotoFiles.forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = "relative";

                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = "w-24 h-24 object-cover border rounded";

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                    btn.className = "absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs";
                    btn.onclick = (e) => {
                        e.preventDefault();
                        accumulatedPhotoFiles.splice(index, 1);
                        updateFileInput(photoInput, accumulatedPhotoFiles);
                        displayImages();
                        updateAttachCount();
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(btn);
                    newFilesContainer.appendChild(wrapper);
                    feather.replace();
                });
            }

            // 🔹 Preview File + tombol delete
            function displayFiles() {
                // ⚠️ JANGAN hapus semua - hanya update bagian new files
                // Cari atau buat container khusus untuk new files
                let newFilesContainer = previewFileContainer.querySelector('.new-files-container');

                if (!newFilesContainer) {
                    newFilesContainer = document.createElement('div');
                    newFilesContainer.className = 'new-files-container flex flex-col gap-2';
                    previewFileContainer.appendChild(newFilesContainer);
                } else {
                    newFilesContainer.innerHTML = ''; // Hanya clear new files
                }

                accumulatedFileFiles.forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = "flex items-center gap-2 text-sm border p-2 rounded";

                    const icon = document.createElement('i');
                    icon.setAttribute('data-feather', 'file-text');

                    const name = document.createElement('span');
                    name.textContent = file.name;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                    btn.className = "ml-auto bg-red-600 text-white rounded-full p-1 text-xs";
                    btn.onclick = (e) => {
                        e.preventDefault();
                        accumulatedFileFiles.splice(index, 1);
                        updateFileInput(fileInput, accumulatedFileFiles);
                        displayFiles();
                        updateAttachCount();
                    };

                    wrapper.append(icon, name, btn);
                    newFilesContainer.appendChild(wrapper);
                    feather.replace();
                });
            }

            // 🔹 Event Listener Input - ACCUMULATE files (don't replace)
            photoInput.addEventListener('change', (e) => {
                // Add new files to accumulated list
                const newFiles = Array.from(photoInput.files);
                newFiles.forEach(file => {
                    // Check if file already exists to avoid duplicates
                    const isDuplicate = accumulatedPhotoFiles.some(f => f.name === file.name && f.size === file.size);
                    if (!isDuplicate) {
                        accumulatedPhotoFiles.push(file);
                    }
                });

                // Update input with accumulated files
                updateFileInput(photoInput, accumulatedPhotoFiles);
                displayImages();
                updateAttachCount();
            });

            fileInput.addEventListener('change', (e) => {
                // Add new files to accumulated list
                const newFiles = Array.from(fileInput.files);
                newFiles.forEach(file => {
                    // Check if file already exists to avoid duplicates
                    const isDuplicate = accumulatedFileFiles.some(f => f.name === file.name && f.size === file.size);
                    if (!isDuplicate) {
                        accumulatedFileFiles.push(file);
                    }
                });

                // Update input with accumulated files
                updateFileInput(fileInput, accumulatedFileFiles);
                displayFiles();
                updateAttachCount();
            });

            // 🔹 Toggle menu
            attachBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                attachMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', () => attachMenu.classList.add('hidden'));

            attachImages.addEventListener('click', () => photoInput.click());
            attachDocs.addEventListener('click', () => fileInput.click());
        });
    </script>
    {{-- Store header data handler --}}
    <script>
        async function saveChangesFinding() {
            const form = document.querySelector(
                'form[action="{{ route('ftpp.audit-finding.update', $finding->id) }}"]');
            if (!form) return alert('Form not found');

            // ✅ 1. Hapus error lama
            const errorContainer = document.getElementById('attachmentErrorContainer');
            const errorMessage = document.getElementById('attachmentErrorMessage');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
            }
            if (errorMessage) {
                errorMessage.innerHTML = '';
            }

            // ✅ 2. Function untuk tampilkan error di field attachment
            function showAttachmentError(message) {
                if (!errorContainer || !errorMessage) {
                    console.error('❌ Error container not found in DOM');
                    alert(message.replace(/<[^>]*>/g, ''));
                    return;
                }

                // Tampilkan error
                errorMessage.innerHTML = message;
                errorContainer.classList.remove('hidden');

                // Re-render feather icons
                if (typeof feather !== 'undefined' && feather.replace) {
                    feather.replace();
                }

                // Scroll ke error
                errorContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                console.log('✅ Error displayed in container');
            }

            // ✅ 3. VALIDASI TOTAL FILE SIZE (CLIENT-SIDE)
            const photoInput = document.getElementById('photoInput');
            const fileInput = document.getElementById('fileInput');

            let totalSize = 0;
            let fileDetails = [];

            // Hitung total size dari photos (images)
            if (photoInput && photoInput.files) {
                Array.from(photoInput.files).forEach(file => {
                    totalSize += file.size;
                    fileDetails.push({
                        name: file.name,
                        size: file.size,
                        type: 'image'
                    });
                });
            }

            // Hitung total size dari files (PDF)
            if (fileInput && fileInput.files) {
                Array.from(fileInput.files).forEach(file => {
                    totalSize += file.size;
                    fileDetails.push({
                        name: file.name,
                        size: file.size,
                        type: 'pdf'
                    });
                });
            }

            // Convert ke MB untuk display
            const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);

            console.log(`📊 Total file size: ${totalSize} bytes (${totalSizeMB} MB)`);
            console.log('Files:', fileDetails);

            // ✅ 4. CHECK jika melebihi 20MB - TAMPILKAN DI FIELD (BUKAN ALERT)
            if (totalSize > 20 * 1024 * 1024) { // 20MB in bytes
                const errorHtml = `
                    <p class="font-semibold mb-1">❌ Total file size exceeds 20MB</p>
                    <p>Current total size: <strong>${totalSizeMB} MB</strong></p>
                    <p>
                        Please compress your PDF files and reupload it.
                    </p>
                `;
                showAttachmentError(errorHtml);
                return; // ⛔ STOP submit
            }

            // ✅ 5. CHECK individual file size - TAMPILKAN DI FIELD (BUKAN ALERT)
            let individualErrors = [];

            // Check individual image files (max 3MB)
            if (photoInput && photoInput.files) {
                Array.from(photoInput.files).forEach(file => {
                    if (file.size > 3 * 1024 * 1024) { // 3MB
                        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        individualErrors.push(
                            `🖼️ Image "${file.name}" is ${sizeMB}MB. Maximum is 3MB per image.`);
                    }
                });
            }

            // Check individual PDF files (max 10MB)
            if (fileInput && fileInput.files) {
                Array.from(fileInput.files).forEach(file => {
                    if (file.size > 10 * 1024 * 1024) { // 10MB
                        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        individualErrors.push(`📄 PDF "${file.name}" is ${sizeMB}MB. Maximum is 10MB per PDF.`);
                    }
                });
            }

            if (individualErrors.length > 0) {
                const errorHtml = `
                    <p class="font-semibold mb-2">❌ Individual file size limit exceeded</p>
                    <ul class="list-disc list-inside space-y-1">
                        ${individualErrors.map(e => `<li>${e}</li>`).join('')}
                    </ul>
                `;
                showAttachmentError(errorHtml);
                return; // ⛔ STOP submit
            }

            // ✅ 6. Jika lolos validasi, lanjutkan submit form
            // remove any previously added dynamic inputs to avoid duplicates
            document.querySelectorAll('[data-dyn-input]').forEach(n => n.remove());

            // Prevent the static single `selectedSub` hidden input from submitting an empty value
            const staticSelectedSub = document.getElementById('selectedSub');
            if (staticSelectedSub) {
                staticSelectedSub.removeAttribute('name');
            }

            // Add action indicator
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'save_header';
            actionInput.setAttribute('data-dyn-input', '1');
            form.appendChild(actionInput);

            // Collect auditee ids
            let auditeeIds = [];
            if (typeof selectedAuditees !== 'undefined' && selectedAuditees.length) {
                auditeeIds = selectedAuditees.map(a => String(a.id));
            } else {
                auditeeIds = Array.from(document.querySelectorAll('#selectedAuditees [data-id]')).map(el => String(el
                    .getAttribute('data-id')));
                if ((!auditeeIds || auditeeIds.length === 0) && document.getElementById('auditee_ids')) {
                    const raw = document.getElementById('auditee_ids').value || '';
                    auditeeIds = raw ? raw.split(',').map(s => s.trim()).filter(Boolean) : [];
                }
                if ((!auditeeIds || auditeeIds.length === 0) && typeof __initialAuditees !== 'undefined') {
                    auditeeIds = (__initialAuditees || []).map(a => String(a.id));
                }
            }

            auditeeIds = Array.from(new Set(auditeeIds.filter(Boolean)));
            auditeeIds.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'auditee_ids[]';
                inp.value = id;
                inp.setAttribute('data-dyn-input', '1');
                form.appendChild(inp);
            });

            // Collect sub_klausul ids
            let subIds = [];
            if (typeof selectedSubIds !== 'undefined' && selectedSubIds.length) {
                subIds = selectedSubIds.map(String);
            } else {
                subIds = Array.from(document.querySelectorAll('#selectedSubContainer [data-id]')).map(el => String(el
                    .getAttribute('data-id')));
                if ((!subIds || subIds.length === 0) && document.getElementById('selectedSub')) {
                    const v = document.getElementById('selectedSub').value;
                    if (v) subIds = [String(v)];
                }
                if ((!subIds || subIds.length === 0) && typeof __initialSubKlausulIds !== 'undefined') {
                    subIds = (__initialSubKlausulIds || []).map(String);
                }
            }

            subIds = Array.from(new Set(subIds.filter(Boolean)));
            subIds.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'sub_klausul_id[]';
                inp.value = Number(id);
                inp.setAttribute('data-dyn-input', '1');
                form.appendChild(inp);
            });

            // Submit the form
            form.submit();
        }
    </script>
@endpush
