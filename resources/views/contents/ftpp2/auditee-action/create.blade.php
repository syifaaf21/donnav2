@extends('layouts.app')
@section('title', 'Assign Auditee Action')
@section('subtitle', 'Please fill in the details below to assign auditee actions for the FTPP finding.')
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
            <li class="text-gray-700 font-bold">Assign Auditee Action</li>
        </ol>
    </nav>
@endsection

@php
    $role = strtolower(auth()->user()->roles->pluck('name')->first() ?? '');
@endphp
@section('content')
    <div class="mx-auto px-4">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Assign Auditee Action</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Please fill in the details below to assign auditee actions for the FTPP finding.
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
                    <li class="text-gray-700 font-bold">Assign Auditee Action</li>
                </ol>
            </nav>
        </div> --}}

        <div class="space-y-6 mt-2">
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
                                in_array(strtolower($finding->status->name ?? ''), ['need revision', 'need assign', 'draft']);
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

                // ✅ PINDAHKAN semua operasi DOM ke $nextTick
                this.$nextTick(() => {
                    // Sub Audit
                    this.loadSubAudit();

                    // Sub Klausul
                    this.loadSubKlausul();

                    // ===== RENDER ATTACHMENTS (FINDING) =====
                    let previewImageContainer = document.getElementById('previewImageContainer');
                    let previewFileContainer = document.getElementById('previewFileContainer');

                    if (previewImageContainer && previewFileContainer) {
                        const files = this.form.file ?? [];

                        previewImageContainer.innerHTML = '';
                        previewFileContainer.innerHTML = '';

                        if (files && files.length) {
                            const baseUrl = '/storage/';

                            files.forEach(f => {
                                const path = f.file_path ?? f.path ?? '';
                                const fullUrl = baseUrl + path;
                                const filename = f.original_name ?? path.split('/').pop() ?? '';

                                if ((path + filename).match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                    // IMAGE THUMBNAIL
                                    const img = document.createElement('img');
                                    img.src = fullUrl;
                                    img.className = 'w-24 h-24 object-cover border rounded cursor-pointer hover:opacity-80 transition';
                                    img.onclick = () => showImagePreviewModal(fullUrl, filename);
                                    previewImageContainer.appendChild(img);
                                } else if (filename.match(/\.pdf$/i)) {
                                    // PDF CARD
                                    const pdfCard = document.createElement('div');
                                    pdfCard.className = 'border rounded p-3 cursor-pointer hover:bg-gray-50 transition w-28 text-center';
                                    pdfCard.innerHTML = `
                                        <i data-feather="file-text" class="text-red-500 mx-auto" style="width: 40px; height: 40px;"></i>
                                        <span class="text-xs mt-2 block truncate" title="${filename}">${filename}</span>
                                    `;
                                    pdfCard.onclick = () => showFilePreviewModal(fullUrl, filename);
                                    previewFileContainer.appendChild(pdfCard);
                                } else {
                                    // OTHER FILES
                                    const fileCard = document.createElement('div');
                                    fileCard.className = 'border rounded p-3 cursor-pointer hover:bg-gray-50 transition w-28 text-center';
                                    fileCard.innerHTML = `
                                        <i data-feather="file" class="text-gray-500 mx-auto" style="width: 40px; height: 40px;"></i>
                                        <span class="text-xs mt-2 block truncate" title="${filename}">${filename}</span>
                                    `;
                                    fileCard.onclick = () => showFilePreviewModal(fullUrl, filename);
                                    previewFileContainer.appendChild(fileCard);
                                }
                            });

                            if (typeof feather !== 'undefined') feather.replace();
                        } else {
                            previewImageContainer.innerHTML = '<span class="text-gray-400 text-sm">No attachments</span>';
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

                    this.$nextTick(() => {
                        const previewImageContainer2 = document.getElementById('previewImageContainer2');
                        const previewFileContainer2 = document.getElementById('previewFileContainer2');

                        if (!previewImageContainer2 || !previewFileContainer2) {
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

                                if ((f.file_path ?? filename).match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                    previewImageContainer2.innerHTML += `
                                        <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded cursor-pointer hover:opacity-80" onclick="window.open('${fullUrl}', '_blank')" />
                                    `;
                                } else {
                                    previewFileContainer2.innerHTML += `
                                        <a href="${fullUrl}" target="_blank" class="flex gap-2 text-sm border p-2 rounded items-center text-blue-600 hover:underline">
                                            <i data-feather="file-text"></i> ${filename}
                                        </a>
                                    `;
                                }
                            });

                            if (typeof feather !== 'undefined' && feather.replace) {
                                feather.replace();
                            }
                        }
                    });
                }
            },

            loadSubAudit() {
                let list = @json($subAudit);
                const subContainer = document.getElementById('subAuditType');

                if (!subContainer) return; // ✅ Guard jika element tidak ada

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
                const container = document.getElementById('selectedSubContainer');

                if (!container) {
                    console.warn('⚠️ Container selectedSubContainer tidak ditemukan');
                    return;
                }

                console.log('🔍 Sub Klausuls data:', list); // ✅ Debug

                container.innerHTML = "";

                if (!list.length) {
                    container.innerHTML = `<span class="text-gray-400 text-sm">No clauses</span>`;
                    return;
                }

                list.forEach(s => {
                    const code = s.code ?? '';
                    const name = s.name ?? '';

                    container.insertAdjacentHTML('beforeend', `
                        <span class="bg-green-100 px-2 py-1 rounded text-xs mr-1 mb-1 inline-block">
                            ${code}${code && name ? ' - ' : ''}${name}
                        </span>
                    `);
                });
            },
        }
    }
</script>
