@extends('layouts.app')
@section('title', 'FTPP')
@section('content')
    <div x-data="ftppApp()" class="container mx-auto my-2 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- LEFT SIDE --}}
            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 overflow-auto h-[90vh]">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-700">FTPP List</h2>
                    <button @click="createNew()" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">
                        + Add
                    </button>
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
                        Pilih FTPP dari daftar kiri atau klik “Add”
                    </div>
                </template>

                <template x-if="formLoaded">
                    <form @submit.prevent="saveForm" enctype="multipart/form-data">

                        {{-- HEADER --}}
                        <div class="text-center font-bold text-lg mb-2">FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                        </div>

                        {{-- AUDITOR INPUT --}}
                        <table class="w-full border border-black text-sm">
                            <tr>
                                <td class="border border-black p-1 w-1/3 align-top">
                                    Audit Type:
                                    <div x-data="{
                                        auditTypes: @js($auditTypes),
                                        form: {
                                            audit_type_id: '',
                                            sub_audit_type_id: '',
                                        },
                                        get selectedType() {
                                            return this.auditTypes.find(a => a.id == this.form.audit_type_id);
                                        }

                                    }">
                                        <!-- Level 1: Audit Type -->
                                        <div class="space-y-2">
                                            <template x-for="type in @js($auditTypes)" :key="type.id">
                                                <label class="block">
                                                    <input type="checkbox" :value="type.id"
                                                        @change="
                                                            form.audit_type_id = type.id;
                                                            form.sub_audit_type_id = '';
                                                        "
                                                        :checked="form.audit_type_id == type.id" required>
                                                    <span x-text="type.name"></span>
                                                </label>
                                            </template>
                                        </div>

                                        <!-- Level 2: Sub Audit Type -->
                                        <template
                                            x-if="selectedType && selectedType.sub_audit && selectedType.sub_audit.length > 0">
                                            <div class="ml-4 mt-2 space-x-3">
                                                <template x-for="sub in selectedType.sub_audit" :key="sub.id">
                                                    <label>
                                                        <input type="checkbox" :value="sub.id"
                                                            @change="form.sub_audit_type_id = sub.id"
                                                            :checked="form.sub_audit_type_id == sub.id" required>
                                                        <span x-text="sub.name"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Debug -->
                                        <!-- <pre x-text="JSON.stringify(form, null, 2)"></pre> -->
                                    </div>
                                </td>
                                <td class="border border-black p-1">
                                    <div x-data="{
                                        form: {
                                            department_id: '',
                                            auditee_id: '',
                                            // ... field lainnya
                                        },
                                        departments: @js($departments),
                                        auditeeList: [],

                                        async loadAuditeeByDept() {
                                            if (!this.form.department_id) {
                                                this.auditeeList = [];
                                                this.form.auditee_id = '';
                                                return;
                                            }

                                            try {
                                                const res = await fetch(`/ftpp/get-all?department_id=${this.form.department_id}`, {
                                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                                });
                                                this.auditeeList = await res.json();
                                                this.form.auditee_id = '';
                                            } catch (err) {
                                                console.error(err);
                                                alert('Gagal memuat data auditee');
                                            }
                                        }
                                    }">
                                        {{-- Department --}}
                                        <div>
                                            <label class="block text-sm text-gray-700 mb-1">Department /
                                                Proces:</label>
                                            <div class="flex items-center">
                                                <select x-model="form.department_id" @change="loadAuditeeByDept">
                                                    <option value="">-- Select Department --</option>
                                                    <template x-for="dept in departments" :key="dept.id">
                                                        <option :value="dept.id" x-text="dept.name"></option>
                                                    </template>
                                                </select>
                                                <span>/</span>
                                                <select x-model="form.process_id"
                                                    class="border-b border-gray-400 w-1/3 focus:outline-none">
                                                    <option value="">-- Choose Process --</option>
                                                    @foreach ($processes as $proc)
                                                        <option value="{{ $proc->id }}">{{ $proc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Auditee -->
                                        <div class="flex">
                                            <label class="block text-sm text-gray-700 mb-1">Auditee
                                                (PIC):</label>
                                            <select x-model="form.auditee_id">
                                                <option value="">-- Select Auditee --</option>
                                                <template x-for="auditee in auditeeList" :key="auditee.id">
                                                    <option :value="auditee.id" x-text="auditee.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Auditor -->
                                        <div class="flex">
                                            <label class="block text-sm text-gray-700 mb-1">Auditor /
                                                Inisiator:</label>
                                            <select x-model="form.auditor_id"
                                                class="border-b border-gray-400 w-2/3 focus:outline-none ml-2" required>
                                                <option value="">-- Choose Auditor --</option>
                                                @foreach ($auditors as $auditor)
                                                    <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                                                @endforeach
                                            </select>

                                        </div>
                                        <div>Date:
                                            <input type="date" x-model="form.due_date"
                                                class="border-b border-gray-400 w-2/3 ml-2">
                                        </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black p-1">
                                    Finding Category:
                                    <label><input type="radio" value="major" x-model="form.finding_category_id">
                                        Major</label>
                                    <label><input type="radio" value="minor" x-model="form.finding_category_id">
                                        Minor</label>
                                    <label><input type="radio" value="observation" x-model="form.finding_category_id">
                                        Observation</label>
                                    <span class="float-right">No Registrasi: <span
                                            x-text="form.registration_number ?? '-'"></span></span>
                                </td>
                            </tr>
                        </table>
                        <!-- Tombol Save Header -->
                        <div class="mt-3 text-right">
                            <button type="button" @click="saveHeader"
                                class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                                Save Header
                            </button>
                        </div>

                        {{-- TEMUAN --}}
                        <table class="w-full border border-black text-sm mt-2">
                            <tr class="bg-gray-100 font-semibold">
                                <td class="border border-black p-1">AUDITOR / INISIATOR</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-2">
                                    <div>
                                        <label>Finding / Issue:</label>
                                        <textarea x-model="form.finding_description" class="w-full border border-gray-400 rounded p-1 h-24" required></textarea>
                                    </div>
                                    <div class="flex justify-between mt-2">
                                        <div>Duedate:
                                            <input type="date" x-model="form.due_date" class="border-b border-gray-400"
                                                required>
                                        </div>
                                        <div x-data="klausulForm({ klausuls: klausulData, parentForm: form })" x-init="init()" class="mt-2">
                                            <template x-for="(item, index) in klausulList" :key="index">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <!-- Dropdown Klausul -->
                                                    <select x-model="klausulList[index].klausul_id"
                                                        @change="updateSubKlausul(index)">
                                                        <option value="">-- Select Clause --</option>
                                                        <template x-for="k in filteredKlausuls" :key="k.id">
                                                            <option :value="k.id" x-text="k.name"></option>
                                                        </template>
                                                    </select>

                                                    <!-- Dropdown Sub Klausul -->
                                                    <select x-model="klausulList[index].sub_klausul_id">
                                                        <option value="">-- Select Sub Clause --</option>
                                                        <template x-for="sub in klausulList[index].filteredSubKlausul"
                                                            :key="sub.id">
                                                            <option :value="sub.id" x-text="sub.name"></option>
                                                        </template>
                                                    </select>

                                                    <!-- Tombol hapus baris -->
                                                    <button type="button" @click="removeRow(index)"
                                                        class="text-red-600 hover:text-red-800">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            </template>

                                            <!-- Tombol tambah klausul -->
                                            <button type="button" @click="addRow"
                                                class="mt-1 text-xs text-blue-600 hover:underline">
                                                + Add Klausul
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        {{-- END AUDITOR INPUT --}}

                        {{-- AUDITEE INPUT --}}
                        {{-- 5 WHY --}}
                        <table class="w-full border border-black text-sm mt-2">
                            <tr class="bg-gray-100 font-semibold">
                                <td class="border border-black p-1">AUDITEE</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-1">
                                    <div class="font-semibold mb-1">Issue Causes (5 Why)</div>
                                    <template x-for="i in 5">
                                        <div class="ml-2 mb-1">
                                            <label>Why-<span x-text="i"></span> (Mengapa):</label>
                                            <input type="text" class="w-full border-b border-gray-400"
                                                x-model="form['why_'+i+'_mengapa']">
                                            <label>Cause (Karena):</label>
                                            <input type="text" class="w-full border-b border-gray-400"
                                                x-model="form['why_'+i+'_karena']">
                                        </div>
                                    </template>
                                    <div class="mt-1">
                                        Root Cause:
                                        <textarea x-model="form.root_cause" class="w-full border border-gray-400 rounded p-1"></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- TINDAKAN KOREKSI & PERBAIKAN --}}
                        <table class="w-full border border-black text-sm mt-2">
                            <tr class="bg-gray-100 font-semibold text-center">
                                <td class="border border-black p-1 w-8">No</td>
                                <td class="border border-black p-1">Activity</td>
                                <td class="border border-black p-1 w-32">PIC</td>
                                <td class="border border-black p-1 w-28">Planning</td>
                                <td class="border border-black p-1 w-28">Actual</td>
                            </tr>

                            {{-- Koreksi --}}
                            <tr>
                                <td colspan="5" class="border border-black p-1 font-semibold">Corrective Action</td>
                            </tr>
                            <template x-for="i in 4">
                                <tr>
                                    <td class="border border-black text-center" x-text="i"></td>
                                    <td class="border border-black"><input type="text" class="w-full border-none p-1"
                                            x-model="form['corrective_'+i+'_activity']"></td>
                                    <td class="border border-black"><input type="text" class="w-full border-none p-1"
                                            x-model="form['corrective_'+i+'_pic']"></td>
                                    <td class="border border-black"><input type="date" class="w-full border-none p-1"
                                            x-model="form['corrective_'+i+'_planning']"></td>
                                    <td class="border border-black"><input type="date" class="w-full border-none p-1"
                                            x-model="form['corrective_'+i+'_actual']"></td>
                                </tr>
                            </template>

                            {{-- Perbaikan --}}
                            <tr>
                                <td colspan="5" class="border border-black p-1 font-semibold">Preventive Action</td>
                            </tr>
                            <template x-for="i in 4">
                                <tr>
                                    <td class="border border-black text-center" x-text="i"></td>
                                    <td class="border border-black"><input type="text" class="w-full border-none p-1"
                                            x-model="form['preventive_'+i+'_activity']"></td>
                                    <td class="border border-black"><input type="text" class="w-full border-none p-1"
                                            x-model="form['preventive_'+i+'_pic']"></td>
                                    <td class="border border-black"><input type="date" class="w-full border-none p-1"
                                            x-model="form['preventive_'+i+'_planning']"></td>
                                    <td class="border border-black"><input type="date" class="w-full border-none p-1"
                                            x-model="form['preventive_'+i+'_actual']"></td>
                                </tr>
                            </template>
                        </table>

                        {{-- YOKOTEN --}}
                        <table class="w-full border border-black text-sm mt-2">
                            <tr>
                                <td class="border border-black p-2 w-2/3">
                                    <label>Yokoten?</label>
                                    <label><input type="radio" value="1" x-model="form.yokoten"> Yes</label>
                                    <label><input type="radio" value="0" x-model="form.yokoten"> No</label>

                                </td>
                                <td class="border border-black p-1 font-semibold text-center">Dept. Head</td>
                                <td class="border border-black p-1 font-semibold text-center">Leader/Spv</td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Please specify:</label>
                                    <textarea x-show="form.yokoten == 1" x-model="form.finding_description"
                                        class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
                                </td>
                                {{-- LDR, SPV, DEPT HEAD --}}
                                <td class="border border-black p-2">
                                    <input type="file" @change="uploadSignature($event, 'dept_head_signature')"
                                        accept="image/*" class="text-xs">
                                    <template x-if="form.dept_head_signature">
                                        <img :src="form.dept_head_signature" class="mx-auto mt-1 h-16 object-contain">
                                    </template>
                                </td>
                                <td class="border border-black p-2">
                                    <input type="file" @change="uploadSignature($event, 'ldr_spv_signature')"
                                        accept="image/*" class="text-xs">
                                    <template x-if="form.ldr_spv_signature">
                                        <img :src="form.ldr_spv_signature" class="mx-auto mt-1 h-16 object-contain">
                                    </template>
                                </td>
                            </tr>
                        </table>

                        {{-- AUDITOR VERIFY --}}
                        {{-- TANDA TANGAN --}}
                        <table class="w-full border border-black text-sm mt-2 text-center">
                            <tr>
                                <td class="border border-black p-1 font-semibold">Lead Auditor</td>
                                <td class="border border-black p-1 font-semibold">Auditor</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-2">
                                    <input type="file" @change="uploadSignature($event, 'lead_auditor_signature')"
                                        accept="image/*" class="text-xs">
                                    <template x-if="form.lead_auditor_signature">
                                        <img :src="form.lead_auditor_signature" class="mx-auto mt-1 h-16 object-contain">
                                    </template>
                                </td>
                                <td class="border border-black p-2">
                                    <input type="file" @change="uploadSignature($event, 'auditor_signature')"
                                        accept="image/*" class="text-xs">
                                    <template x-if="form.auditor_signature">
                                        <img :src="form.auditor_signature" class="mx-auto mt-1 h-16 object-contain">
                                    </template>
                                </td>
                            </tr>
                        </table>
                        {{-- END AUDITOR VERIFY --}}

                        {{-- STATUS --}}
                        <div class="flex justify-between mt-3">
                            <div class="text-lg font-bold">
                                Status:
                                <span :class="form.status_id == 1 ? 'text-red-500' : 'text-green-600'">
                                    <span x-text="form.status_id == 1 ? 'OPEN' : 'CLOSE'"></span>
                                </span>
                            </div>
                            <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">
                                Simpan
                            </button>
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
                    klausul_list: []
                },

                get selectedType() {
                    return this.form.audit_type_id ?
                        this.auditTypes.find(a => a.id == this.form.audit_type_id) :
                        null;
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
                    const res = await fetch(`/ftpp/${id}`);
                    this.form = await res.json();
                    this.formLoaded = true;
                },
            }
        }

        function klausulForm({
            klausuls,
            parentForm
        }) {
            return {
                klausuls,
                parentForm,

                // klausul_list akan selalu diambil dari parentForm
                get klausulList() {
                    // pastikan parentForm.klausul_list selalu ada
                    if (!Array.isArray(this.parentForm.klausul_list)) {
                        this.parentForm.klausul_list = [{
                            klausul_id: '',
                            sub_klausul_id: '',
                            filteredSubKlausul: []
                        }];
                    }
                    return this.parentForm.klausul_list;
                },

                // 🔹 Klausul difilter sesuai Audit Type di parent form
                get filteredKlausuls() {
                    const type = Number(this.parentForm.audit_type_id);
                    if (!type) return [];

                    // Mapping: audit type id → klausul id yg boleh muncul
                    switch (type) {
                        case 1: // Sistem Manajemen LK3
                            return this.klausuls.filter(k => [2, 3].includes(k.id));
                        case 2: // Sistem Manajemen Mutu
                            return this.klausuls.filter(k => k.id === 1);
                        default:
                            return [];
                    }
                },

                // 🔹 Update sub klausul berdasarkan klausul yang dipilih
                updateSubKlausul(index) {
                    const klausulId = this.klausulList[index].klausul_id;
                    const selected = this.klausuls.find(k => k.id == klausulId);

                    if (selected && selected.head_klausul) {
                        this.klausulList[index].filteredSubKlausul =
                            selected.head_klausul.flatMap(h => h.sub_klausul || []);
                    } else {
                        this.klausulList[index].filteredSubKlausul = [];
                    }

                    this.klausulList[index].sub_klausul_id = '';
                },

                addRow() {
                    this.klausulList.push({
                        klausul_id: '',
                        sub_klausul_id: '',
                        filteredSubKlausul: [],
                    });
                },

                removeRow(index) {
                    this.klausulList.splice(index, 1);
                },

                init() {
                    // 🔄 reset klausul_list setiap kali audit type berubah
                    this.$watch('parentForm.audit_type_id', () => {
                        this.parentForm.klausul_list = [{
                            klausul_id: '',
                            sub_klausul_id: '',
                            filteredSubKlausul: []
                        }];
                    });
                }
            };
        }
    </script>
@endpush
