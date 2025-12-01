@extends('layouts.app')
@section('title', 'FTPP')

@php
    $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
@endphp
@section('content')
    <div class="mx-auto my-2 px-4">
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-2" aria-label="Breadcrumb">
            <ol class="list-reset flex space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li>/</li>
                <li>
                    <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                        <i class="bi bi-folder me-1"></i>FTPP
                    </a>
                </li>
                <li>/</li>
                <li class="text-gray-700 font-medium">Assign Auditee Action</li>
            </ol>
        </nav>
        <div class="bg-white p-6 border border-gray-200 rounded-xl shadow-lg space-y-6 mt-2">
            {{-- Back button --}}
            <div class="mb-3">
                <a href="{{ route('ftpp.index') }}"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded hover:bg-gray-200 text-sm text-gray-700">
                    <i data-feather="arrow-left" class="w-4 h-4"></i>
                    <span class="ml-2">Back</span>
                </a>
            </div>

            <h4>Assign Auditee Action</h4>

            <div x-data="editFtppApp()" x-init="init()">
                <form action="{{ route('ftpp.auditee-action.store', $finding->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    {{-- Show create-audit-finding for: super admin, admin, auditor --}}
                    @include('contents.ftpp2.auditee-action.partials.show-audit-finding', [
                        'readonly' => true,
                    ])

                    {{-- Show create-auditee-action for: super admin, admin, user --}}
                    @if (in_array($role, ['super admin', 'admin', 'user', 'supervisor', 'leader']))
                        @php
                            $statusNeedsReview =
                                ($finding->status->need_review ?? null) === true ||
                                in_array(strtolower($finding->status->name ?? ''), ['need revision', 'open']);
                        @endphp

                        @include('contents.ftpp2.auditee-action.partials.create-auditee-action', [
                            'readonly' => !$statusNeedsReview,
                        ])
                    @endif
                </form>
            </div>

        </div>
    </div>
@endsection
<script>
    function editFtppApp(data) {
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
