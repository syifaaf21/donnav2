@extends('layouts.app')
@section('title', 'Edit Auditee Action')

@section('content')
    <div x-data="editApp()" class="p-6">

        <h2 class="text-2xl font-semibold mb-4">Edit Auditee Action</h2>

        <form action="{{ route('ftpp.auditee-action.update', $finding->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="audit_finding_id" x-model="selectedId">
            <input type="hidden" name="action" value="update_auditee_action">
            <input type="hidden" name="pic" value="{{ auth()->user()->id }}">
            <input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-2">

                <!-- LEFT: 5 WHY -->
                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                    <div class="font-semibold text-lg text-gray-800">AUDITEE</div>

                    <div>
                        <label class="font-semibold text-gray-800">Issue Causes (5 Why)</label>

                        <template x-for="i in 5">
                            <div class="mt-2 space-y-2">
                                <div class="flex flex-col space-y-1">
                                    <label class="text-gray-600">Why-<span x-text="i"></span> (Mengapa):</label>
                                    <input type="text" name="why[]"
                                        class="w-full border-b border-gray-400 p-2 focus:ring-2 focus:ring-blue-400"
                                        x-model="form['why_'+i+'_mengapa']">

                                    <label class="text-gray-600">Cause (Karena):</label>
                                    <input type="text" name="cause[]"
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
                                        <input type="text" name="activity[]" class="w-full p-1 border-none"
                                            x-model="form['corrective_'+i+'_activity']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="text" name="pic[]" class="w-full p-1 border-none"
                                            x-model="form['corrective_'+i+'_pic']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" name="planning_date[]" class="w-full p-1 border-none"
                                            x-model="form['corrective_'+i+'_planning']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" name="actual_date[]" class="w-full p-1 border-none"
                                            x-model="form['corrective_'+i+'_actual']">
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
                                        <input type="text" name="activity[]" class="w-full p-1 border-none"
                                            x-model="form['preventive_'+i+'_activity']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="text" name="pic[]" class="w-full p-1 border-none"
                                            x-model="form['preventive_'+i+'_pic']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" name="planning_date[]" class="w-full p-1 border-none"
                                            x-model="form['preventive_'+i+'_planning']">
                                    </td>
                                    <td class="border border-gray-200">
                                        <input type="date" name="actual_date[]" class="w-full p-1 border-none"
                                            x-model="form['preventive_'+i+'_actual']">
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
                                <label class="font-semibold text-gray-800">Yokoten?</label>
                                <div class="flex gap-6">
                                    <label><input type="radio" name="yokoten" value="1" x-model="form.yokoten">
                                        Yes</label>
                                    <label><input type="radio" name="yokoten" value="0" x-model="form.yokoten">
                                        No</label>
                                </div>
                            </div>
                        </div>

                        <div x-show="form.yokoten == 1">
                            <label class="font-semibold text-gray-800">Please Specify:</label>
                            <textarea name="yokoten_area" x-model="form.yokoten_area" class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
                        </div>

                        @php
                            $action = $finding?->auditeeAction;
                            $actionId = $action?->id ?? 'null';
                        @endphp

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

                    </div>

                    <!-- Attachments -->
                    <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                        <div class="font-semibold text-lg text-gray-800">Attachments</div>

                        <div id="previewImageContainer2" class="flex flex-wrap gap-2"></div>
                        <div id="previewFileContainer2" class="flex flex-col gap-1"></div>

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
                                        class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                        <i data-feather="image" class="w-4 h-4"></i> Upload Images
                                    </button>
                                    <button id="attachDocs2" type="button"
                                        class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                        <i data-feather="file-text" class="w-4 h-4"></i> Upload Documents
                                    </button>
                                    <div class="border-t mt-1"></div>
                                    <button id="attachBoth2" type="button"
                                        class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                        <i data-feather="upload" class="w-4 h-4"></i> Open Combined Picker
                                    </button>
                                </div>

                                <!-- Hidden file inputs -->
                                <input type="file" id="photoInput2" name="photos2[]" accept="image/*" multiple
                                    class="hidden">
                                <input type="file" id="fileInput2" name="files2[]"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
                                <input type="file" id="combinedInput2" name="attachments2[]"
                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </form>

    </div>
@endsection
<script>
    function editApp() {
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

                    // ========================
                    // 5 WHY (ambil dari tabel tt_why_causes)
                    // ========================
                    if (action.why_causes?.length) {
                        action.why_causes.forEach((row, idx) => {
                            const i = idx + 1;
                            this.form[`why_${i}_mengapa`] = row.why_description || '';
                            this.form[`cause_${i}_karena`] = row.cause_description || '';
                        });

                    }

                    // ========================
                    // CORRECTIVE ACTION
                    // ========================
                    if (action.corrective_actions && Array.isArray(action.corrective_actions)) {
                        action.corrective_actions.forEach((row, idx) => {
                            const i = idx + 1;
                            this.form[`corrective_${i}_activity`] = row.activity ?? '';
                            this.form[`corrective_${i}_pic`] = row.pic ?? '';
                            this.form[`corrective_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                            this.form[`corrective_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                        });
                    }

                    // ========================
                    // PREVENTIVE ACTION
                    // ========================
                    if (action.preventive_actions && Array.isArray(action.preventive_actions)) {
                        action.preventive_actions.forEach((row, idx) => {
                            const i = idx + 1;
                            this.form[`preventive_${i}_activity`] = row.activity ?? '';
                            this.form[`preventive_${i}_pic`] = row.pic ?? '';
                            this.form[`preventive_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                            this.form[`preventive_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                        });
                    }
                    // =========================
                    // ATTACHMENTS (existing files)
                    // =========================
                    // if (action && action.attachments && Array.isArray(action.attachments)) {
                    //     this.loadExistingAttachments(action.attachments);
                    // }
                    this.$nextTick(() => {
                        const previewImageContainer2 = document.getElementById('previewImageContainer2');
                        const previewFileContainer2 = document.getElementById('previewFileContainer2');

                        if (!previewImageContainer2 || !previewFileContainer2) {
                            console.warn('⚠️ Preview container belum ada di DOM');
                            return;
                        }

                        // use the local 'action' captured above, fallback to form properties if needed
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
                                    // Image preview
                                    previewImageContainer2.innerHTML += `
                                        <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded" />
                                    `;
                                } else {
                                    // Document preview
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
