@extends('layouts.app')
@section('title', 'FTPP Approval')
@section('subtitle', 'Please review and approve the pending FTPP findings below.')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
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
            <li class="text-gray-700 font-bold">Approval</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class=" space-y-4">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">FTPP Approval</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Please review and approve the pending FTPP findings below.
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
                    <li class="text-gray-700 font-bold">Approval</li>
                </ol>
            </nav>
        </div> --}}

        <div x-data="ftppApp()" x-init="init()">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- LEFT SIDE --}}
                <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl shadow p-3 flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-lg font-semibold text-gray-700 flex items-center gap-3">
                            FTPP Approval List
                            <span
                                class="inline-flex items-center justify-center bg-primaryDark text-white text-xs font-semibold px-2 py-0.5 rounded">
                                <span x-text="findings.length"></span>
                            </span>
                        </h2>
                    </div>

                    <div class="space-y-2 flex-1 flex flex-col">
                        <div class="px-3 py-2">
                            <label class="sr-only">Search findings</label>
                            <div class="flex items-center">
                                <div class="relative flex-1">
                                    <input type="text" x-model="search" x-ref="searchInput"
                                        placeholder="Search findings by reg. number, dept, status..."
                                        class="w-full text-sm border rounded px-3 py-2 pr-10 focus:ring focus:ring-sky-100" />

                                    <button x-show="search" x-cloak type="button"
                                        @click="search = ''; $nextTick(()=> $refs.searchInput.focus())"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 rounded"
                                        aria-label="Clear search">
                                        <i class="bi bi-x-circle text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <ul class="px-2 overflow-auto flex-1">
                            <template x-if="findings.length === 0">
                                <li class="text-xs text-gray-400 px-2 py-4 text-center border rounded">No findings
                                    available.</li>
                            </template>

                            <template x-for="item in filteredFindings" :key="item.id">
                                <li @click="loadForm(item.id)" :title="item.registration_number"
                                    class="relative cursor-pointer mb-2 border rounded-lg bg-white hover:shadow-md transition p-3 flex items-start gap-3">
                                    <div class="absolute right-3 top-3" x-show="loading == item.id">
                                        <svg class="w-5 h-5 text-sky-600 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div class="font-semibold text-sm truncate" x-text="item.registration_number">
                                            </div>
                                        </div>

                                        <div class="mt-1 text-xs text-gray-600 flex flex-col gap-2">
                                            <span class="truncate" x-text="item.department?.name ?? '-'"
                                                style="max-width:160px"></span>
                                            <span class="" x-text="item.audit?.name ?? (item.audit_type?.name || '-')"
                                                style="max-width:140px" title=""></span>
                                            <span x-text="item.status?.name || 'Unknown'"
                                                :class="{
                                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700': (
                                                        item.status?.name || '').toLowerCase() === 'need check',
                                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800': (
                                                            item.status?.name || '')
                                                        .toLowerCase() === 'need approval by auditor',
                                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800': (
                                                            item.status?.name || '')
                                                        .toLowerCase() === 'need approval by lead auditor',
                                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700': (
                                                        item.status?.name || '').toLowerCase() === 'closed' || (
                                                        item.status?.name || '').toLowerCase() === 'resolved'
                                                }">
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- RIGHT SIDE --}}
                <div class="lg:col-span-9 bg-white border border-gray-100 rounded-2xl shadow p-4 overflow-auto relative">
                    <!-- Loading overlay -->
                    <div x-show="loading" x-cloak
                        class="absolute inset-0 bg-white/70 z-40 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-sky-600 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <div class="mt-2 text-sm text-gray-700">Loading FTPP details…</div>
                        </div>
                    </div>
                    <style>
                        /* Approval view table styling to resemble PDF layout */
                        .approval-view {
                            font-size: 12px;
                        }

                        .approval-view table {
                            border-collapse: collapse;
                            width: 100%;
                        }

                        .approval-view td,
                        .approval-view th {
                            border: 1px solid #000;
                            padding: 6px;
                            vertical-align: top;
                        }

                        .approval-view .header-table td {
                            border: 1px solid #000;
                        }

                        .approval-view .text-sm {
                            font-size: 12px;
                        }

                        .approval-view .approval-content {
                            background: #f7fafc;
                            padding: 6px;
                        }
                    </style>

                    <div class="approval-view">
                        <template x-if="!formLoaded">
                            <div class="text-center text-gray-400 mt-20">
                                Choose FTPP from the list or click add
                            </div>
                        </template>

                        <template x-if="formLoaded">
                            <form>
                                {{-- HEADER --}}
                                <table class="header-table border border-black rounded mb-2 w-full">
                                    <tr>
                                        <td class="header-logo border border-black">
                                            <img src="{{ asset('images/logo-aiia.png') }}" alt="AISIN Logo"
                                                class="w-40 h-auto">
                                        </td>
                                        <td class="header-title text-center">
                                            <h6>
                                                FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                                            </h6>
                                        </td>
                                    </tr>
                                </table>
                                @include('contents.ftpp2.approval.partials.auditor-input')
                                @include('contents.ftpp2.approval.partials.auditee-input')
                                @include('contents.ftpp2.approval.partials.auditor-verification')
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachment preview modal -->
        <div id="attachmentPreviewModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999]">
            <div class="bg-white rounded-lg shadow-xl w-11/12 lg:w-4/5 h-[80vh] relative overflow-hidden flex flex-col">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">Attachment Preview</h3>
                    <button type="button" id="closePreviewModal"
                        class="p-1 rounded-full bg-gray-100 hover:bg-red-200 text-gray-600 hover:text-red-800"
                        aria-label="Close preview">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <iframe id="attachmentPreviewFrame" src="" title="Attachment preview"
                    class="w-full flex-1 border-none"></iframe>
            </div>
        </div>
    @endsection
    <x-sweetalert-confirm />
    @push('scripts')
        <script>
            const baseStorageUrl = `${window.location.origin}/storage/`;

            function openAttachmentPreview(url) {
                const modal = document.getElementById('attachmentPreviewModal');
                const frame = document.getElementById('attachmentPreviewFrame');
                if (!modal || !frame) return;

                frame.src = url;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeAttachmentPreview() {
                const modal = document.getElementById('attachmentPreviewModal');
                const frame = document.getElementById('attachmentPreviewFrame');
                if (!modal || !frame) return;

                frame.src = '';
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function renderAttachmentPreview(imageContainer, fileContainer, files) {
                if (!imageContainer || !fileContainer) return;

                imageContainer.innerHTML = '';
                fileContainer.innerHTML = '';

                (files || []).forEach(file => {
                    const path = file?.file_path;
                    if (!path || typeof path !== 'string') return;

                    const url = `${baseStorageUrl}${path}`;
                    const filename = path.split('/').pop();
                    const isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(path);

                    if (isImage) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'relative group border rounded overflow-hidden';

                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = filename;
                        img.className = 'w-24 h-24 object-cover';

                        btn.appendChild(img);
                        btn.addEventListener('click', () => openAttachmentPreview(url));
                        imageContainer.appendChild(btn);
                    } else {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className =
                            'flex items-center gap-2 text-sm border p-2 rounded w-full text-left hover:bg-slate-50';
                        btn.innerHTML = `<i data-feather="file-text"></i><span class="truncate">${filename}</span>`;
                        btn.addEventListener('click', () => openAttachmentPreview(url));
                        fileContainer.appendChild(btn);
                    }
                });

                feather.replace();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('attachmentPreviewModal');
                const closeBtn = document.getElementById('closePreviewModal');

                closeBtn?.addEventListener('click', closeAttachmentPreview);
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeAttachmentPreview();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeAttachmentPreview();
                });
            });

            function ftppApp() {
                return {
                    search: '',
                    findings: @json($findings),
                    formLoaded: false,
                    loading: null, // id of finding currently loading (or null)
                    mode: 'create',
                    selectedId: null,
                    userRoles: @json(auth()->user()->roles->pluck('name')->map('strtolower')->toArray() ?? []),

                    init() {
                        console.log('🔍 Total findings from controller:', this.findings.length);
                        console.log('👤 User roles:', this.userRoles);
                        console.log('📋 All findings:', this.findings);
                        console.log('✅ Filtered findings:', this.filteredFindings);
                    },

                    form: {
                        status_id: 7,
                        audit_type_id: "",
                        sub_audit_type_id: "",
                        sub_audit_name: '',
                        auditor_id: "",
                        created_at: "",
                        due_date: "",
                        registration_number: "",
                        finding_description: "",
                        finding_category_id: "",
                        auditee_ids: "",
                        sub_klausul_id: [],
                        selected_lead_auditor_id: "",

                        sub_audit: [],
                        auditees: [],
                        sub_klausul: [],
                    },

                    createNew() {
                        this.mode = 'create';
                        this.selectedId = null;
                        this.form = {
                            status_id: 7,
                            klausul_list: []
                        };
                        this.formLoaded = true;
                        this.readonly = false;
                    },

                    async loadForm(id) {
                        try {
                            this.loading = id;
                            this.formLoaded = false;

                            const res = await fetch(`/approval/${id}`);

                            if (!res.ok) throw new Error('Failed to fetch data');

                            const finding = await res.json();

                            console.log(finding);

                            this.mode = 'edit';
                            this.form = finding; // isi semua field utama
                            this.selectedId = id;
                            this.formLoaded = true;
                            this.loading = null;
                            // Tampilkan tanda tangan jika ada
                            const deptHeadSignatureUrl = finding.dept_head_signature_url || '';
                            const ldrSpvSignatureUrl = finding.ldr_spv_signature_url || '';

                            // Update Alpine component tanda tangan
                            const deptHeadEl = document.querySelector('[x-data^="signatureForm(\'dept_head\'"]');
                            if (deptHeadEl) {
                                const alpineComp = Alpine.$data(deptHeadEl);
                                alpineComp.signatureUrl = deptHeadSignatureUrl;
                                alpineComp.signatureData = deptHeadSignatureUrl;
                            }

                            const ldrSpvEl = document.querySelector('[x-data^="signatureForm(\'ldr_spv\'"]');
                            if (ldrSpvEl) {
                                const alpineComp = Alpine.$data(ldrSpvEl);
                                alpineComp.signatureUrl = ldrSpvSignatureUrl;
                                alpineComp.signatureData = ldrSpvSignatureUrl;
                            }

                            //
                            // ✅ SUB AUDIT TYPE (Level 2)
                            //
                            // Use finding relation to display sub audit type (level 2)
                            if (finding.sub_audit) {
                                this.form.sub_audit_type_id = finding.sub_audit.id ?? finding.sub_audit_type_id ?? '';
                                this.form.sub_audit_name = finding.sub_audit.name || '';
                                const subContainer = document.getElementById('subAuditType');
                                if (subContainer) {
                                    subContainer.innerHTML =
                                        `<div class="mt-2 text-gray-700 font-semibold">${this.form.sub_audit_name}</div>`;
                                }
                            } else {
                                this.form.sub_audit_type_id = finding.sub_audit_type_id || '';
                                this.form.sub_audit_name = '';
                                const subContainer = document.getElementById('subAuditType');
                                if (subContainer) subContainer.innerHTML =
                                    '<small class="text-gray-500">There is no sub audit type</small>';
                            }

                            //
                            // ✅ PLANT / DEPT / PROCESS / PRODUCT DISPLAY
                            //
                            const dept = finding.department?.name;
                            const proc = finding.process?.name;
                            const prod = finding.product?.name;

                            this.form._plant_display = [dept, proc, prod]
                                .filter(Boolean)
                                .join(' / ') || '-';

                            //
                            // ✅ AUDITEE
                            //
                            selectedAuditees = finding.auditee ?? [];
                            this.form.auditee_ids = selectedAuditees.map(a => a.id).join(',');

                            this.form._auditee_html =
                                (finding.auditee ?? [])
                                .map(a => `
                                <span class="bg-blue-100 px-2 py-1 rounded flex items-center gap-1">
                                    ${a.name}
                                </span>
                            `)
                                .join('');

                            this.formLoaded = true;

                            // Tunggu Alpine render form dulu
                            this.$nextTick(() => {
                                const subKlausuls = finding.sub_klausul ?? finding.sub_klausuls ?? [];
                                selectedSubIds = subKlausuls.map(s => String(s.id));

                                const subContainer = document.getElementById('selectedSubContainer');
                                if (!subContainer) return console.warn('⚠️ Sub container belum muncul');

                                subContainer.innerHTML = '';
                                subKlausuls.forEach(s => {
                                    const span = document.createElement('span');
                                    span.className =
                                        "flex items-end gap-1 bg-blue-100 text-gray-700 px-2 py-1 rounded";
                                    span.dataset.id = s.id;
                                    span.innerHTML = `
                                    ${s.code ?? ''} - ${s.name}`;
                                    subContainer.appendChild(span);
                                });

                                feather.replace();
                            });

                            this.form.created_at = finding.created_at?.substring(0, 10) ?? '';
                            this.form.due_date = finding.due_date?.substring(0, 10) ?? '';

                            this.$nextTick(() => {
                                const previewImageContainer = document.getElementById('previewImageContainer');
                                const previewFileContainer = document.getElementById('previewFileContainer');

                                if (!previewImageContainer || !previewFileContainer) return;
                                renderAttachmentPreview(previewImageContainer, previewFileContainer, finding.file);
                            });

                            // Auditee action
                            if (finding.auditee_action) {
                                const act = finding.auditee_action;

                                this.form.auditee_action_id = act.id;

                                // Root cause & yokoten
                                this.form.root_cause = act.root_cause || '';
                                this.form.yokoten = act.yokoten;
                                this.form.yokoten_area = act.yokoten_area || '';

                                // 5 WHY
                                if (act.why_causes?.length) {
                                    act.why_causes.forEach((w, i) => {
                                        const idx = i + 1;
                                        this.form[`why_${idx}_mengapa`] = w.why_description || '';
                                        this.form[`cause_${idx}_karena`] = w.cause_description || '';
                                    });
                                }

                                // Corrective
                                if (act.corrective_actions?.length) {
                                    act.corrective_actions.forEach((c, i) => {
                                        const idx = i + 1;
                                        this.form[`corrective_${idx}_activity`] = c.activity || '';
                                        this.form[`corrective_${idx}_pic`] = c.pic || '';
                                        this.form[`corrective_${idx}_planning`] = c.planning_date?.substring(0,
                                            10) || '';
                                        this.form[`corrective_${idx}_actual`] = c.actual_date?.substring(0, 10) ||
                                            '';
                                    });
                                }

                                // Preventive
                                if (act.preventive_actions?.length) {
                                    act.preventive_actions.forEach((p, i) => {
                                        const idx = i + 1;
                                        this.form[`preventive_${idx}_activity`] = p.activity || '';
                                        this.form[`preventive_${idx}_pic`] = p.pic || '';
                                        this.form[`preventive_${idx}_planning`] = p.planning_date?.substring(0,
                                            10) || '';
                                        this.form[`preventive_${idx}_actual`] = p.actual_date?.substring(0, 10) ||
                                            '';
                                    });
                                }

                                // 🔹 Tampilkan tanda tangan Dept Head
                                this.form.auditee_action_id = act.id;

                                // ✅ Jika auditor sudah verifikasi, tampilkan stamp & sembunyikan tombol
                                if (act.ldr_spv_signature) {
                                    this.form.ldr_spv_signature = true;
                                    this.form.ldr_spv_signature_url = `/images/usr-approve.png`;
                                    this.form.ldr_spv_signature = true; // flag baru
                                } else {
                                    this.form.ldr_spv_signature = false;
                                }

                                // ✅ Jika lead auditor sudah acknowledge
                                if (act.dept_head_signature) {
                                    this.form.dept_head_signature = true;
                                    this.form.dept_head_signature_url = `/images/mgr-approve.png`;
                                    this.form.dept_head_signature = true; // flag baru
                                } else {
                                    this.form.dept_head_signature = false;
                                }


                                // cek act sendiri
                                console.log('act object:', act);

                                this.$nextTick(() => {
                                    const previewImageContainer2 = document.getElementById(
                                        'previewImageContainer2');
                                    const previewFileContainer2 = document.getElementById('previewFileContainer2');

                                    if (!previewImageContainer2 || !previewFileContainer2) {
                                        console.warn('⚠️ Preview container belum ada di DOM');
                                        return;
                                    }

                                    renderAttachmentPreview(previewImageContainer2, previewFileContainer2, act
                                        .file);
                                });

                                this.form.auditee_action_id = act.id;
                                this.form.effectiveness_verification = act.effectiveness_verification ||
                                    ''; // ✅ ambil dari auditee_action

                                // ✅ Jika auditor sudah verifikasi, tampilkan stamp & sembunyikan tombol
                                if (act.verified_by_auditor) {
                                    this.form.auditor_signature = true;
                                    this.form.auditor_signature_url = `/images/stamp-internal-auditor.png`;
                                    this.form.auditor_verified = true; // flag baru
                                } else {
                                    this.form.auditor_verified = false;
                                }

                                // ✅ Jika lead auditor sudah acknowledge
                                if (act.acknowledge_by_lead_auditor) {
                                    this.form.lead_auditor_signature = true;
                                    this.form.lead_auditor_signature_url = `/images/stamp-lead-auditor.png`;
                                    this.form.lead_auditor_ack = true; // flag baru
                                } else {
                                    this.form.lead_auditor_ack = false;
                                }
                            } else {
                                // kosongkan jika belum ada
                                for (let i = 1; i <= 5; i++) {
                                    this.form[`why_${i}_mengapa`] = '';
                                    this.form[`cause_${i}_karena`] = '';
                                }
                                for (let i = 1; i <= 4; i++) {
                                    ['corrective', 'preventive'].forEach(t => {
                                        this.form[`${t}_${i}_activity`] = '';
                                        this.form[`${t}_${i}_pic`] = '';
                                        this.form[`${t}_${i}_planning`] = '';
                                        this.form[`${t}_${i}_actual`] = '';
                                    });
                                }
                                this.form.root_cause = '';
                                this.form.yokoten = '';
                                this.form.yokoten_area = '';
                            }

                        } catch (error) {
                            console.error(error);
                            this.loading = null;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal mengambil data FTPP'
                                });
                            } else {
                                alert('Gagal mengambil data FTPP');
                            }
                        }
                    },
                    get filteredFindings() {
                        if (!this.search) return this.findings;

                        const keyword = this.search.toLowerCase();

                        return this.findings.filter(f =>
                            (f.registration_number || '').toLowerCase().includes(keyword) ||
                            (f.department?.name || '').toLowerCase().includes(keyword) ||
                            (f.status?.name || '').toLowerCase().includes(keyword)
                        );
                    },
                    async deleteFinding(id) {
                        if (typeof Swal === 'undefined') {
                            if (!confirm("Are you sure you want to delete this item?")) return;
                        } else {
                            const c = await Swal.fire({
                                title: 'Confirm delete',
                                text: 'Are you sure you want to delete this item?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete',
                                cancelButtonText: 'Cancel'
                            });
                            if (!c.isConfirmed) return;
                        }

                        try {
                            const res = await fetch(`/approval/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            if (res.ok) {
                                this.filteredFindings = this.filteredFindings.filter(f => f.id !== id);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted',
                                        text: 'Deleted successfully.'
                                    });
                                } else {
                                    alert("Deleted successfully.");
                                }
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed',
                                        text: 'Failed to delete.'
                                    });
                                } else {
                                    alert("Failed to delete.");
                                }
                            }
                        } catch (err) {
                            console.error(err);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error deleting item.'
                                });
                            } else {
                                alert("Error deleting item.");
                            }
                        }
                    },
                }
            }
        </script>
    @endpush
