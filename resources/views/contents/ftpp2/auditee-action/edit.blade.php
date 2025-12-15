@extends('layouts.app')
@section('title', 'Edit Auditee Action')
@section('subtitle', 'Edit auditee action for finding #' . $finding->registration_number .
    '. Please update the details below for the auditee action.')
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
            <li class="text-gray-700 font-bold">Edit Auditee Action</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div x-data="editFtppApp()" x-init="init()" class="px-6 space-y-6">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Edit Auditee Action</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Edit auditee action for finding #{{ $finding->registration_number }}.
                        Please update the details below for the auditee action.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1"
                aria-label="Breadcrumb">
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
                    <li class="text-gray-700 font-bold">Edit Auditee Action</li>
                </ol>
            </nav>
        </div> --}}

        <form action="{{ route('ftpp.auditee-action.update', $finding->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="audit_finding_id" x-model="selectedId">
            <input type="hidden" name="action" value="update_auditee_action">
            <input type="hidden" name="pic" value="{{ auth()->user()->name }}">
            <input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-2">
                <!-- LEFT: 5 WHY -->
                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                    <h5 class="font-bold">Auditee</h5>

                    <div>
                        <label class="font-semibold text-gray-800">Issue Causes (5 Why)</label>

                        <template x-for="i in 5">
                            <div class="mt-2 space-y-2">
                                <div class="flex flex-col space-y-1">
                                    <label class="text-gray-600">Why-<span x-text="i"></span> (Mengapa):</label>
                                    <input type="text" :name="'why_' + i + '_mengapa'"
                                        class="w-full border-b border-gray-400 p-2 focus:ring-2 focus:ring-blue-400"
                                        x-model="form['why_'+i+'_mengapa']">

                                    <label class="text-gray-600">Cause (Karena):</label>
                                    <input type="text" :name="'cause_' + i + '_karena'"
                                        class="w-full border-b border-gray-400 p-2 focus:ring-2 focus:ring-blue-400"
                                        x-model="form['cause_'+i+'_karena']">
                                </div>
                            </div>
                        </template>

                        <div class="mt-4">
                            <label class="font-semibold text-gray-800">Root Cause <span
                                    class="text-red-500">*</span></label>
                            <textarea name="root_cause" x-model="form.root_cause"
                                class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-blue-400" required></textarea>
                        </div>
                    </div>
                </div>


                <!-- RIGHT COLUMN: Corrective + Preventive + Yokoten -->
                <div class="space-y-6">

                    <!-- Corrective + Preventive -->
                    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                        <table class="w-full border border-gray-200 text-sm mt-2">

                            <tr class="bg-gray-100 font-semibold text-center">
                                <td class="border border-gray-200 p-1 w-8">No</td>
                                <td class="border border-gray-200 p-1">Activity</td>
                                <td class="border border-gray-200 p-1 w-32">PIC</td>
                                <td class="border border-gray-200 p-1 w-28">Planning</td>
                                <td class="border border-gray-200 p-1 w-28">Actual</td>
                            </tr>

                            <!-- Corrective -->
                            <tr>
                                <td colspan="5" class="border border-gray-200 p-1 font-semibold">Corrective Action
                                </td>
                            </tr>

                            <template x-for="i in 4">
                                <tr class="corrective-row">
                                    <td class="border border-gray-200 text-center" x-text="i"></td>
                                    <td class="border border-gray-200">
                                        <input type="text" :name="'corrective_' + i + '_activity'"
                                            class="w-full p-1 border-none" x-model="form['corrective_'+i+'_activity']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="text" :name="'corrective_pic[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['corrective_'+i+'_pic']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" :name="'corrective_planning[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['corrective_'+i+'_planning']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" :name="'corrective_actual[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['corrective_'+i+'_actual']">
                                    </td>
                                </tr>
                            </template>

                            <!-- Preventive -->
                            <tr>
                                <td colspan="5" class="border border-gray-200 p-1 font-semibold">Preventive Action
                                </td>
                            </tr>

                            <template x-for="i in 4">
                                <tr class="preventive-row">
                                    <td class="border border-gray-200 text-center" x-text="i"></td>
                                    <td class="border border-gray-200">
                                        <input type="text" :name="'preventive_' + i + '_activity'"
                                            class="w-full p-1 border-none" x-model="form['preventive_'+i+'_activity']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="text" :name="'preventive_pic[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['preventive_'+i+'_pic']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" :name="'preventive_planning[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['preventive_'+i+'_planning']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" :name="'preventive_actual[' + i + ']'"
                                            class="w-full p-1 border-none" x-model="form['preventive_'+i+'_actual']">
                                    </td>
                                </tr>
                            </template>
                        </table>
                    </div>


                    <!-- Yokoten -->
                    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                        <div class="font-semibold text-lg text-gray-800">Yokoten</div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="font-semibold text-gray-800">Yokoten? <span class="text-danger">*</span></label>
                                <div class="flex gap-6">
                                    <label><input type="radio" name="yokoten" value="1" x-model="form.yokoten">
                                        Yes</label>
                                    <label><input type="radio" name="yokoten" value="0" x-model="form.yokoten">
                                        No</label>
                                </div>
                            </div>
                        </div>

                        <div x-show="form.yokoten == 1">
                            <label class="font-semibold text-gray-800">Please Specify: <span class="text-danger">*</span></label>
                            <textarea name="yokoten_area" x-model="form.yokoten_area" class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
                        </div>

                        @php
                            $action = $finding?->auditeeAction;
                            $actionId = $action?->id ?? 'null';
                        @endphp

                        <div class="font-semibold text-lg text-gray-800">Attachments</div>

                        {{-- Tips Alert --}}
                        <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2">
                            <i class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                                <p class="text-xs text-yellow-700 leading-relaxed">
                                    Only <strong>PDF, PNG, JPG, and JPEG</strong> files are allowed.
                                    Maximum total file size is <strong>10 MB</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="items-center gap-4 mt-4">

                                <!-- Attachment button -->
                                <button id="attachBtn2" type="button"
                                    class="items-center gap-2 px-4 py-2 border rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none"
                                    title="Attach files">
                                    <i data-feather="paperclip" class="w-4 h-4"></i>
                                    <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                                </button>

                                <!-- Attachment Menu -->
                                <div id="attachMenu2"
                                    class="hidden absolute mt-12 w-44 bg-white border rounded-xl shadow-lg z-20">
                                    <button id="attachImages2" type="button"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                        <i data-feather="image" class="w-4 h-4"></i> Upload Images
                                    </button>
                                    <button id="attachDocs2" type="button"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                        <i data-feather="file-text" class="w-4 h-4"></i> Upload Documents
                                    </button>
                                </div>

                                <!-- Hidden file inputs -->
                                <input type="file" id="photoInput2" name="attachments[]" accept="image/*" multiple
                                    class="hidden">
                                <input type="file" id="fileInput2" name="attachments[]" accept=".pdf" multiple
                                    class="hidden">

                                <!-- ✅ Error message container for attachments -->
                                <div id="attachmentErrorContainer" class="hidden mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded-r">
                                    <div class="flex items-start">
                                        <i data-feather="alert-circle" class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
                                        <div id="attachmentErrorMessage" class="text-sm text-red-700"></div>
                                    </div>
                                </div>

                                {{-- Laravel server-side errors --}}
                                @error('attachments')
                                    <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded-r">
                                        <div class="flex items-start">
                                            <i data-feather="alert-circle" class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
                                            <p class="text-sm text-red-700">{!! $message !!}</p>
                                        </div>
                                    </div>
                                @enderror

                                {{-- Render existing attachments server-side so users can remove them --}}
                                @php
                                    $existingFiles =
                                        $finding->auditeeAction?->file ?? ($finding->auditeeAction?->attachments ?? []);
                                @endphp

                                <div id="existingFilesContainer" class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($existingFiles as $file)
                                        <div id="existing-file-{{ $file->id }}" class="relative border rounded p-1">
                                            @if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $file->file_path))
                                                <img src="{{ asset('storage/' . $file->file_path) }}"
                                                    class="w-24 h-24 object-cover rounded" />
                                            @else
                                                <div class="flex items-center gap-2 p-2 text-sm">
                                                    <i data-feather="file-text"></i>
                                                    <span>{{ $file->original_name ?? basename($file->file_path) }}</span>
                                                </div>
                                            @endif

                                            <button type="button" onclick="markRemoveAttachment({{ $file->id }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">×</button>

                                            <input type="hidden" id="existing-attachment-input-{{ $file->id }}"
                                                name="existing_attachments[]" value="{{ $file->id }}">
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Selected / new previews -->
                                <div id="previewImageContainer2" class="flex flex-wrap gap-2 mt-2"></div>
                                <div id="previewFileContainer2" class="flex flex-col gap-1 mt-2"></div>
                            </div>
                        </div>



                    </div>

                    <!-- Attachments -->
                    <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                        <!-- Leader/SPV -->
                        <div class="p-4 bg-gray-50 border border-gray-300 rounded-md text-center max-w-xs">
                            <div>Created</div>

                            {{-- Tampilkan stamp jika ldr_spv_signature = 1 --}}
                            @if ($action && $action->ldr_spv_signature == 1)
                                <img src="/images/usr-approve.png" class="mx-auto h-24">
                            @endif

                            <div class="mb-1 font-semibold text-gray-800">Leader / SPV</div>

                            <input type="text" value="{{ auth()->user()->name }}"
                                class="w-full border border-gray-300 rounded text-center py-1" readonly>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark hover:from-primaryDark hover:to-primaryLight transition-colors text-white rounded">
                                Save Changes
                            </button>
                        </div>
                    </div>

                </div>

            </div>


        </form>

    </div>
@endsection

@push('scripts')
    <script>
        function editFtppApp() {
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

                    this.form._auditee_html = (this.form.auditee ?? [])
                        .map(a => `<span class='bg-blue-100 px-2 py-1 rounded'>${a.name}</span>`)
                        .join("");

                    // Sub Audit
                    this.loadSubAudit();

                    // Sub Klausul
                    this.loadSubKlausul();

                    this.$nextTick(() => {
                        // If existing files are already rendered server-side, skip creating
                        // and rendering the duplicate preview containers to avoid double previews.
                        if (document.getElementById('existingFilesContainer')) {
                            return;
                        }

                        // ensure preview containers exist
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

                                if ((path + filename).match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                    const img = document.createElement('img');
                                    img.src = fullUrl;
                                    img.className = 'w-24 h-24 object-cover border rounded';
                                    previewImageContainer.appendChild(img);
                                } else {
                                    const div = document.createElement('div');
                                    div.className = 'flex gap-2 text-sm border p-2 rounded items-center';
                                    div.innerHTML = `<i data-feather="file-text"></i> ${filename}`;
                                    previewFileContainer.appendChild(div);
                                }
                            });

                            if (typeof feather !== 'undefined' && feather.replace) {
                                feather.replace();
                            }
                        }
                    });

                    // auditee action data
                    const action = this.form.auditeeAction ?? this.form.auditee_action ?? null;

                    if (action) {
                        // Root Cause
                        this.form.root_cause = action.root_cause ?? '';

                        // Yokoten
                        this.form.yokoten = action.yokoten ?? '';
                        this.form.yokoten_area = action.yokoten_area ?? '';

                        // 5 WHY
                        if (action.why_causes?.length) {
                            action.why_causes.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`why_${i}_mengapa`] = row.why_description || '';
                                this.form[`cause_${i}_karena`] = row.cause_description || '';
                            });
                        }

                        // CORRECTIVE
                        if (action.corrective_actions && Array.isArray(action.corrective_actions)) {
                            action.corrective_actions.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`corrective_${i}_activity`] = row.activity ?? '';
                                this.form[`corrective_${i}_pic`] = row.pic ?? '';
                                this.form[`corrective_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                                this.form[`corrective_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                            });
                        }

                        // PREVENTIVE
                        if (action.preventive_actions && Array.isArray(action.preventive_actions)) {
                            action.preventive_actions.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`preventive_${i}_activity`] = row.activity ?? '';
                                this.form[`preventive_${i}_pic`] = row.pic ?? '';
                                this.form[`preventive_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                                this.form[`preventive_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                            });
                        }

                        this.$nextTick(() => {
                            const previewImageContainer2 = document.getElementById('previewImageContainer2');
                            const previewFileContainer2 = document.getElementById('previewFileContainer2');

                            if (!previewImageContainer2 || !previewFileContainer2) {
                                console.warn('⚠️ Preview container belum ada di DOM');
                                return;
                            }

                            const aa = action ?? this.form.auditeeAction ?? this.form.auditee_action ?? null;

                            const files = aa?.file ?? aa?.attachments ?? [];

                            if (files && files.length) {
                                previewImageContainer2.innerHTML = '';
                                previewFileContainer2.innerHTML = '';

                                const baseUrl = '/storage/';

                                files.forEach(f => {
                                    const fullUrl = baseUrl + (f.file_path ?? f.path ?? '');
                                    const filename = f.original_name ?? (f.file_path ?? f.path ?? '').split(
                                        '/').pop() ?? '';

                                    if ((f.file_path ?? filename).match(
                                            /\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                        previewImageContainer2.innerHTML += `
                                        <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded" />
                                    `;
                                    } else {
                                        previewFileContainer2.innerHTML += `
                                        <div class="flex gap-2 text-sm border p-2 rounded">
                                            <i data-feather="file-text"></i> ${filename}
                                        </div>
                                    `;
                                    }
                                });

                                if (typeof feather !== 'undefined' && feather.replace) {
                                    feather.replace();
                                }
                            }
                        });
                    }
                    console.log("Auditee Action:", this.form.auditeeAction);
                },

                loadSubAudit() {
                    let list = @json($subAudit ?? []);
                    const subContainer = document.getElementById('subAuditType');
                    if (!subContainer) return;
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

                    this.$nextTick(() => {
                        const container = document.getElementById('selectedSubContainer');
                        if (!container) return;
                        container.innerHTML = "";

                        if (!list.length) {
                            container.innerHTML = `<small class="text-gray-500">No Sub Klausul</small>`;
                            return;
                        }

                        list.forEach(s => {
                            const code = s.code ?? s.klausul_code ?? (s.sub_klausul?.code ?? '') ?? '';
                            const name = s.name ?? s.title ?? (s.sub_klausul?.name ?? '') ?? '';

                            container.insertAdjacentHTML('beforeend', `
                    <span class="bg-blue-100 px-2 py-1 rounded mr-1 inline-block">
                        ${code} - ${name}
                    </span>
                `);
                        });
                    });
                },
            }
        }
    </script>
@endpush

@push('scripts')
    <script>
        // Attachment menu + preview logic (copied from create partial)
        (function() {
            const attachBtn2 = document.getElementById('attachBtn2');
            const attachMenu2 = document.getElementById('attachMenu2');
            const attachImages2 = document.getElementById('attachImages2');
            const attachDocs2 = document.getElementById('attachDocs2');
            const attachCount2 = document.getElementById('attachCount2');

            const photoInput2 = document.getElementById('photoInput2');
            const fileInput2 = document.getElementById('fileInput2');

            const previewImageContainer2 = document.getElementById('previewImageContainer2');
            const previewFileContainer2 = document.getElementById('previewFileContainer2');

            if (!photoInput2 || !fileInput2) return;

            function updatefileInput2(input, filesArray2) {
                const dt = new DataTransfer();
                filesArray2.forEach(file2 => dt.items.add(file2));
                input.files = dt.files;
            }

            function updateAttachCount2() {
                const total = (photoInput2.files?.length || 0) + (fileInput2.files?.length || 0);
                if (attachCount2) {
                    if (total > 0) {
                        attachCount2.textContent = total;
                        attachCount2.classList.remove('hidden');
                    } else {
                        attachCount2.classList.add('hidden');
                    }
                }
            }

            function displayImages2() {
                if (!previewImageContainer2) return;
                previewImageContainer2.innerHTML = '';
                Array.from(photoInput2.files).forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = "relative";

                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = "w-24 h-24 object-cover border rounded";

                    const btn = document.createElement('button');
                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                    btn.className = "absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs";
                    btn.onclick = () => {
                        const newFiles2 = Array.from(photoInput2.files);
                        newFiles2.splice(index, 1);
                        updatefileInput2(photoInput2, newFiles2);
                        displayImages2();
                        updateAttachCount2();
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(btn);
                    previewImageContainer2.appendChild(wrapper);
                    if (typeof feather !== 'undefined' && feather.replace) feather.replace();
                });
            }

            function displayFiles2() {
                if (!previewFileContainer2) return;
                previewFileContainer2.innerHTML = '';
                Array.from(fileInput2.files).forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = "flex items-center gap-2 text-sm border p-2 rounded";

                    const icon = document.createElement('i');
                    icon.setAttribute('data-feather', 'file-text');

                    const name = document.createElement('span');
                    name.textContent = file.name;

                    const btn = document.createElement('button');
                    btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
                    btn.className = "ml-auto bg-red-600 text-white rounded-full p-1 text-xs";
                    btn.onclick = () => {
                        const newFiles = Array.from(fileInput2.files);
                        newFiles.splice(index, 1);
                        updatefileInput2(fileInput2, newFiles);
                        displayFiles2();
                        updateAttachCount2();
                    };

                    wrapper.append(icon, name, btn);
                    previewFileContainer2.appendChild(wrapper);
                    if (typeof feather !== 'undefined' && feather.replace) {
                        feather.replace();
                    }
                });
            }

            photoInput2.addEventListener('change', () => {
                displayImages2();
                updateAttachCount2();
            });

            fileInput2.addEventListener('change', () => {
                displayFiles2();
                updateAttachCount2();
            });

            attachBtn2?.addEventListener('click', (e) => {
                e.stopPropagation();
                attachMenu2.classList.toggle('hidden');
            });

            document.addEventListener('click', () => attachMenu2.classList.add('hidden'));

            attachImages2?.addEventListener('click', () => photoInput2.click());
            attachDocs2?.addEventListener('click', () => fileInput2.click());

            // markRemoveAttachment for existing files — confirm then delete via AJAX
            window.markRemoveAttachment = function(id) {
                if (!confirm(
                        'Are you sure you want to delete this attachment? This will remove the file from storage and the database.'
                    )) return;

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(`/ftpp/auditee-action/attachment/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf || '',
                        'Accept': 'application/json',
                    }
                }).then(res => res.json()).then(json => {
                    if (json && json.success) {
                        const el = document.getElementById('existing-file-' + id);
                        if (el) el.remove();
                        const existingInput = document.getElementById('existing-attachment-input-' + id);
                        if (existingInput) existingInput.remove();
                        // optional: show a brief message
                        try {
                            alert('Attachment deleted');
                        } catch (e) {}
                    } else {
                        console.error('Failed to delete attachment', json);
                        alert('Failed to delete attachment');
                    }
                }).catch(err => {
                    console.error('Error deleting attachment', err);
                    alert('Error deleting attachment');
                });
            };
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // ✅ VALIDASI CLIENT-SIDE sebelum submit form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="{{ route('ftpp.auditee-action.update', $finding->id) }}"]');

            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Stop default submit dulu

                // ✅ 1. Hapus error lama
                const errorContainer = document.getElementById('attachmentErrorContainer');
                if (errorContainer) {
                    errorContainer.classList.add('hidden');
                }

                // ✅ 2. VALIDASI TOTAL FILE SIZE
                const photoInput2 = document.getElementById('photoInput2');
                const fileInput2 = document.getElementById('fileInput2');

                let totalSize = 0;
                let fileDetails = [];

                // Hitung total size dari photos (images)
                if (photoInput2 && photoInput2.files) {
                    Array.from(photoInput2.files).forEach(file => {
                        totalSize += file.size;
                        fileDetails.push({
                            name: file.name,
                            size: file.size,
                            type: 'image'
                        });
                    });
                }

                // Hitung total size dari files (PDF)
                if (fileInput2 && fileInput2.files) {
                    Array.from(fileInput2.files).forEach(file => {
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

                // ✅ 3. CHECK jika melebihi 10MB
                if (totalSize > 10 * 1024 * 1024) { // 10MB in bytes
                    showAttachmentError(`

                        <p class="font-semibold mb-1">❌ Total file size exceeds 10MB</p>
                        <p>Current total size: <strong>${totalSizeMB} MB</strong></p>
                        <p>
                            Please compress your PDF files and reupload it.
                        </p>
                    `);
                    return; // ⛔ STOP submit
                }

                // ✅ 4. CHECK individual file size
                let individualErrors = [];

                // Check individual image files (max 3MB)
                if (photoInput2 && photoInput2.files) {
                    Array.from(photoInput2.files).forEach(file => {
                        if (file.size > 3 * 1024 * 1024) { // 3MB
                            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                            individualErrors.push(`🖼️ Image "${file.name}" is ${sizeMB}MB. Maximum is 3MB per image.`);
                        }
                    });
                }

                // Check individual PDF files (max 10MB)
                if (fileInput2 && fileInput2.files) {
                    Array.from(fileInput2.files).forEach(file => {
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

                // ✅ 5. Jika lolos semua validasi, lanjut submit
                form.submit();
            });

            // ✅ Helper function untuk show error menggunakan container yang sudah ada
            function showAttachmentError(message) {
                const errorContainer = document.getElementById('attachmentErrorContainer');
                const errorMessage = document.getElementById('attachmentErrorMessage');

                if (!errorContainer || !errorMessage) {
                    console.error('❌ Error container not found in DOM');
                    // Fallback: show alert
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
        });
    </script>
@endpush
