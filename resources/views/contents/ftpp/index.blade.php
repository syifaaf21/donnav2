@extends('layouts.app')
@section('title', 'FTPP')

@section('content')
    <div x-data="ftppApp()" class="container mx-auto my-2 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- LEFT SIDE --}}
            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 overflow-auto h-[90vh]">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-700">FTPP List</h2>
                    @if (in_array(optional(auth()->user()->role)->name, ['Admin', 'Auditor']))
                        <button @click="createNew()" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">
                            + Add
                        </button>
                    @endif
                </div>

                <!-- 🔍 Single Search Bar -->
                <div class="m-3">
                    <input type="text" x-model="search" placeholder="Search..."
                        class="border border-gray-300 rounded-md px-2 py-1 w-full text-sm focus:ring focus:ring-blue-200">
                </div>

                <ul>
                    <template x-for="item in filteredFindings" :key="item.id">
                        <li @click="loadForm(item.id)"
                            class="cursor-pointer px-2 py-2 border rounded shadow hover:bg-slate-50">
                            <div class="font-semibold text-sm" x-text="item.registration_number"></div>
                            <div class="text-xs" x-text="item.department?.name ?? '-'"></div>
                            <div class="text-xs mt-1" x-text="item.status?.name ?? 'Unknown Status'"
                                :class="{
                                    'text-red-500 hover:text-red-600': item.status_id === 7,
                                    'text-yellow-500 hover:text-yellow-600': item.status_id !== 7 && item
                                        .status_id !== 11,
                                    'text-green-600 hover:text-green-700': item.status_id === 11
                                }">
                            </div>
                            <div class="flex gap-1">
                                <a :href="`/ftpp/${item.id}/download`" class="text-blue-600 hover:text-blue-800"
                                    title="Download PDF">
                                    <i data-feather="download"></i>
                                </a>
                                <button @click.stop="deleteFinding(item.id)" class="text-red-600 hover:text-red-800"
                                    title="Delete">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </li>
                    </template>

                    <template x-if="filteredFindings.length === 0">
                        <li class="text-gray-400 text-sm text-center py-4">No results found.</li>
                    </template>
                </ul>
            </div>

            {{-- RIGHT SIDE --}}
            <div class="lg:col-span-9 bg-white border border-gray-100 rounded-2xl shadow-sm p-3 overflow-auto h-[90vh]">
                <template x-if="!formLoaded">
                    <div class="text-center text-gray-400 mt-20">
                        Choose FTPP from the list or click add
                    </div>
                </template>

                <template x-if="formLoaded">
                    <form action="{{ isset($finding) ? route('ftpp.update', $finding->id) : route('ftpp.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (isset($finding))
                            @method('PUT')
                        @endif

                        {{-- HEADER --}}
                        <div class="text-center font-bold text-lg mb-2">
                            FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                        </div>

                        @include('contents.ftpp.partials.auditor-input')
                        @include('contents.ftpp.partials.auditee-input')
                        @include('contents.ftpp.partials.auditor-verification')
                    </form>
                </template>
            </div>
        </div>
    </div>
@endsection
<x-sweetalert-confirm />
@push('scripts')
    <script>
        function ftppApp() {
            return {
                search: '',
                findings: @json($findings),
                formLoaded: false,
                mode: 'create',
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
                        const res = await fetch(`/ftpp/${id}`);

                        if (!res.ok) throw new Error('Failed to fetch data');

                        const finding = await res.json();

                        console.log(finding);

                        this.mode = 'edit';
                        this.form = finding; // isi semua field utama
                        this.selectedId = id;
                        this.formLoaded = true;
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
                        if (finding.audit_type_id) {
                            fetch(`/ftpp/get-data/${finding.audit_type_id}`)
                                .then(res => res.json())
                                .then(data => {
                                    const subContainer = document.getElementById('subAuditType');
                                    subContainer.innerHTML = '';

                                    if (data.sub_audit?.length) {
                                        data.sub_audit.forEach(s => {
                                            subContainer.insertAdjacentHTML('beforeend', `
                                                <label class="block">
                                                    <input type="radio"
                                                        name="sub_audit_type_id"
                                                        value="${s.id}"
                                                        ${s.id === finding.sub_audit_type_id ? 'checked' : ''}
                                                    >
                                                    ${s.name}
                                                </label>
                                    `);
                                        });
                                    } else {
                                        subContainer.innerHTML =
                                            '<small class="text-gray-500">There is no sub audit type</small>';
                                    }
                                })
                                .catch(err => console.error('❌ Gagal ambil sub audit type:', err));
                        }

                        //
                        // ✅ PLANT / DEPT / PROCESS / PRODUCT DISPLAY
                        //
                        const dept = finding.department?.name;
                        const proc = finding.process?.name;
                        const prod = finding.product?.name;

                        this.form._plant_display = [dept, proc, prod].filter(Boolean).join(' / ') || '-';

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

                            if (!previewImageContainer || !previewFileContainer) {
                                console.warn('⚠️ Preview container belum ada di DOM');
                                return;
                            }

                            if (finding.file?.length) {
                                previewImageContainer.innerHTML = '';
                                previewFileContainer.innerHTML = '';

                                // Assuming this is your base URL. Replace it as needed.
                                const baseUrl = 'http://127.0.0.1:8000/storage/'; // For local development
                                // const fullUrl = `/storage/app/public`;

                                finding.file.forEach(a => {
                                    if (a.file_path && typeof a.file_path === 'string') {
                                        // Construct full URL for the image
                                        const fullUrl = baseUrl + a.file_path;

                                        // Check if it's an image based on file extension
                                        if (a.file_path.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                            // Image preview
                                            previewImageContainer.innerHTML += `
                                                <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded" />
                                            `;
                                        } else {
                                            // Document preview
                                            previewFileContainer.innerHTML += `
                                            <div class="flex gap-2 text-sm border p-2 rounded">
                                                <i data-feather="file-text"></i> ${a.file_path.split('/').pop()}
                                            </div>`;
                                        }
                                    } else {
                                        console.warn('Invalid file path for attachment:',
                                            a); // Log problematic item
                                    }
                                });

                                feather.replace();
                            }
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

                            this.$nextTick(() => {
                                const previewDeptHeadSignature = document.getElementById(
                                    'previewDeptHeadSignature');
                                const previewLdrSpvSignature = document.getElementById(
                                    'previewLdrSpvSignature');

                                if (!previewDeptHeadSignature || !previewLdrSpvSignature) {
                                    console.warn('⚠️ Preview container belum ada di DOM');
                                    return;
                                }

                                const baseUrl = 'http://127.0.0.1:8000/storage/';

                                // 🔹 Tampilkan tanda tangan Dept Head
                                if (act.dept_head_signature) {
                                    previewDeptHeadSignature.innerHTML = '';

                                    // Jika array (misal di masa depan disimpan banyak file)
                                    if (Array.isArray(act.dept_head_signature)) {
                                        act.dept_head_signature.forEach(sig => {
                                            const path = typeof sig === 'string' ? sig : sig
                                                .dept_head_signature;
                                            if (path && path.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                                previewDeptHeadSignature.innerHTML += `
                        <img src="${baseUrl + path}" class="w-24 h-24 object-cover border rounded" />
                    `;
                                            }
                                        });
                                    }
                                    // Jika string tunggal
                                    else if (typeof act.dept_head_signature === 'string') {
                                        if (act.dept_head_signature.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                            previewDeptHeadSignature.innerHTML = `
                    <img src="${baseUrl + act.dept_head_signature}" class="w-24 h-24 object-cover border rounded" />
                `;
                                        }
                                    }
                                }

                                // 🔹 (optional) kalau nanti mau tambahkan preview untuk Ldr/Spv signature juga
                                if (act.ldr_spv_signature) {
                                    previewLdrSpvSignature.innerHTML = '';

                                    if (Array.isArray(act.ldr_spv_signature)) {
                                        act.ldr_spv_signature.forEach(sig => {
                                            const path = typeof sig === 'string' ? sig : sig
                                                .ldr_spv_signature;
                                            if (path && path.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                                previewLdrSpvSignature.innerHTML += `
                        <img src="${baseUrl + path}" class="w-24 h-24 object-cover border rounded" />
                    `;
                                            }
                                        });
                                    } else if (typeof act.ldr_spv_signature === 'string') {
                                        if (act.ldr_spv_signature.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                            previewLdrSpvSignature.innerHTML = `
                    <img src="${baseUrl + act.ldr_spv_signature}" class="w-24 h-24 object-cover border rounded" />
                `;
                                        }
                                    }
                                }

                                feather.replace();
                            });


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

                                if (act.file?.length) {
                                    previewImageContainer2.innerHTML = '';
                                    previewFileContainer2.innerHTML = '';

                                    // Assuming this is your base URL. Replace it as needed.
                                    const baseUrl = 'http://127.0.0.1:8000/storage/'; // For local development
                                    // const baseUrl = 'https://yourapp.com/'; // For production

                                    act.file.forEach(a => {
                                        if (a.file_path && typeof a.file_path === 'string') {
                                            // Construct full URL for the image
                                            const fullUrl = baseUrl + a.file_path;

                                            // Check if it's an image based on file extension
                                            if (a.file_path.match(/\.(jpg|jpeg|png|gif|bmp)$/i)) {
                                                // Image preview
                                                previewImageContainer2.innerHTML += `
                                                <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded" />
                                            `;
                                            } else {
                                                // Document preview
                                                previewFileContainer2.innerHTML += `
                                                <div class="flex gap-2 text-sm border p-2 rounded">
                                                    <i data-feather="file-text"></i> ${a.file_path.split('/').pop()}
                                                </div>`;
                                            }
                                        } else {
                                            console.warn('Invalid file path for attachment:',
                                                a); // Log problematic item
                                        }
                                    });

                                    feather.replace();
                                }
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
                        alert('Gagal mengambil data FTPP');
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
                    if (!confirm("Are you sure you want to delete this item?")) return;
                    try {
                        const res = await fetch(`/ftpp/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (res.ok) {
                            this.filteredFindings = this.filteredFindings.filter(f => f.id !== id);
                            alert("Deleted successfully.");
                        } else {
                            alert("Failed to delete.");
                        }
                    } catch (err) {
                        console.error(err);
                        alert("Error deleting item.");
                    }
                },
            }
        }
    </script>
@endpush
