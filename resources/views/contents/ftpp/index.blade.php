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

                <ul>
                    @foreach ($findings as $item)
                        <li @click="loadForm({{ $item->id }})" class="cursor-pointer px-2 py-2 border-b hover:bg-blue-50"
                            :class="selectedId === {{ $item->id }} ? 'bg-blue-100' : ''">
                            <div class="font-semibold text-sm text-gray-700">
                                {{ $item->registration_number }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $item->department->name ?? '-' }}
                            </div>
                        </li>
                    @endforeach
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
                    <form @submit.prevent action="{{ route('ftpp.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- HEADER --}}
                        <div class="text-center font-bold text-lg mb-2">FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                        </div>

                        {{-- AUDITOR INPUT --}}
                        @include('contents.ftpp.partials.auditor-input')
                        {{-- END AUDITOR INPUT --}}

                        {{-- AUDITEE INPUT --}}
                        @include('contents.ftpp.partials.auditee-input')

                        {{-- AUDITOR VERIFY --}}
                        @include('contents.ftpp.partials.auditor-verification')
                        {{-- END AUDITOR VERIFY --}}

                        {{-- STATUS --}}
                        <div class="flex justify-between mt-3">
                            <div class="text-lg font-bold">
                                Status:
                                <span :class="form.status_id == 1 ? 'text-red-500' : 'text-green-600'">
                                    <span x-text="form.status_id == 1 ? 'OPEN' : 'CLOSE'"></span>
                                </span>
                            </div>

                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const klausulData = @json($klausuls);
    </script>

    <script>
        function ftppApp() {
            return {
                formLoaded: false,
                selectedId: null,
                form: {
                    status_id: 1,
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
                    this.selectedId = null;
                    this.form = {
                        status_id: 1,
                        klausul_list: []
                    };
                    this.formLoaded = true;
                },

                async loadForm(id) {
                    try {
                        const res = await fetch(`/ftpp/${id}`);

                        if (!res.ok) throw new Error('Failed to fetch data');

                        const finding = await res.json();

                        this.form = finding; // isi semua field utama
                        this.selectedId = id;
                        this.formLoaded = true;

                        //
                        // ✅ SUB AUDIT TYPE (Level 2)
                        //
                        this.form.sub_audit = finding.sub_audit ?? [];
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
                        selectedAuditees = finding.auditees ?? [];
                        this.form.auditee_ids = selectedAuditees.map(a => a.id).join(',');

                        this.form._auditee_html =
                            (finding.auditees ?? [])
                            .map(a => `
                                <span class="bg-blue-100 px-2 py-1 rounded flex items-center gap-1">
                                    ${a.name}
                                </span>
                            `)
                            .join('');

                        //
                        // ✅ SUB KLAUSUL
                        //
                        this.form.sub_klausul_id = (finding.sub_klausul ?? []).map(s => s.id);

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
                                } else {
                                    previewFileContainer.innerHTML += `
                <div class="flex gap-2 text-sm border p-2 rounded">
                    <i data-feather="file-text"></i> ${a.url.split('/').pop()}
                </div>`;
                                }
                            });

                            feather.replace();
                        }

                    } catch (error) {
                        console.error(error);
                        alert('Gagal mengambil data FTPP');
                    }
                },

                async saveAuditeeAction() {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('audit_finding_id', this.selectedId);
                    
                    formData.append('action', 'save_auditee_action');

                    formData.append('root_cause', this.form.root_cause || '');
                    formData.append('yokoten', this.form.yokoten || 0);
                    formData.append('yokoten_area', this.form.yokoten_area || '');

                    try {
                        const res = await fetch('{{ route('ftpp.store') }}', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await res.json();

                        if (result.success) {
                            alert('✅ Data berhasil disimpan!');
                        } else {
                            alert('❌ Gagal: ' + (result.message || 'Unknown error'));
                        }
                    } catch (error) {
                        alert('❌ Error: ' + error.message);
                    }
                },
            }
        }
    </script>
@endpush
