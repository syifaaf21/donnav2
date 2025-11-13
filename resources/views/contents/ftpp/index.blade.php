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
                        <li @click="loadForm(item.id)" class="cursor-pointer px-2 py-2 border-b"
                            :class="{
                                'bg-red-500 text-white hover:bg-red-600': item.status_id === 7,
                                'bg-yellow-500 text-gray-700 hover:bg-yellow-600': item.status_id !== 7 && item
                                    .status_id !== 11,
                                'bg-green-600 text-white hover:bg-green-700': item.status_id === 11
                            }">
                            <div class="font-semibold text-sm" x-text="item.registration_number"></div>
                            <div class="text-xs" x-text="item.department?.name ?? '-'"></div>
                            <div class="text-xs mt-1" x-text="item.status?.name ?? 'Unknown Status'"></div>
                            <div class="flex gap-1">
                                <a :href="`/ftpp/${item.id}/download`" class="text-blue-600 hover:text-blue-800"
                                    title="Download PDF">
                                    <i data-feather="download"></i>
                                </a>
                            </div>
                        </li>
                    </template>

                    <template x-if="filteredFindings.length === 0">
                        <li class="text-gray-400 text-sm text-center py-4">No results found.</li>
                    </template>
                </ul>

                {{-- <ul>
                    @foreach ($findings as $item)
                        <li @click="loadForm({{ $item->id }})" class="cursor-pointer px-2 py-2 border-b"
                            :class="{
                                'bg-red-500 text-white hover:bg-red-600': {{ $item->status_id }} ===
                                    7, // Assuming 7 is 'Open'
                                'bg-yellow-500 text-gray-700 hover:bg-yellow-600': {{ $item->status_id }} !== 7 &&
                                    {{ $item->status_id }} !==
                                    11, // For other statuses
                                'bg-green-600 text-white hover:bg-green-700': {{ $item->status_id }} ===
                                    11 // For 'Closed'
                            }">
                            <div class="font-semibold text-sm">
                                {{ $item->registration_number }}
                            </div>
                            <div class="text-xs">
                                {{ $item->department->name ?? '-' }}
                            </div>

                            <!-- Display Status -->
                            <div class="text-xs mt-1">
                                <span
                                    :class="{
                                        'text-white': {{ $item->status_id }} === 7, // Assuming 7 is 'Open'
                                        'text-gray-700': {{ $item->status_id }} !== 7 && {{ $item->status_id }} !==
                                            11, // For other statuses
                                        'text-white': {{ $item->status_id }} === 11 // For 'Closed'
                                    }">
                                    {{ $item->status->name ?? 'Unknown Status' }}
                                </span>
                            </div>
                            <div class="flex gap-1">
                                <a href="{{ route('ftpp.download', $item->id) }}" class="text-blue-600 hover:text-blue-800"
                                    title="Download PDF">
                                    <i data-feather="download"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul> --}}
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

                        //
                        // ✅ SUB AUDIT TYPE (Level 2)
                        //
                        this.form.sub_audit = finding.sub_audit_type_id ?? [];
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

                        if (finding.attachments?.length) {
                            previewImageContainer.innerHTML = '';
                            previewFileContainer.innerHTML = '';

                            finding.attachments.forEach(a => {
                                if (a.type === 'image') {
                                    previewImageContainer.innerHTML += `
                                        <img src="${a.url}" class="w-24 h-24 object-cover border rounded" />
                                    `;
                                        <img src="${a.url}" class="w-24 h-24 object-cover border rounded" />
                                    `;
                                } else {
                                    previewFileContainer.innerHTML += `
                                    <div class="flex gap-2 text-sm border p-2 rounded">
                                        <i data-feather="file-text"></i> ${a.url.split('/').pop()}
                                    </div>`;
                                    <div class="flex gap-2 text-sm border p-2 rounded">
                                        <i data-feather="file-text"></i> ${a.url.split('/').pop()}
                                    </div>`;
                                }
                            });

                            feather.replace();
                        }

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

                            // Signature (jika kamu simpan path di DB)
                            this.form.dept_head_signature = act.dept_head_signature_url || null;
                            this.form.ldr_spv_signature = act.ldr_spv_signature_url || null;

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
            }
        }
    </script>
@endpush
