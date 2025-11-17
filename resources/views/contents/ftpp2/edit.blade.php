@extends('layouts.app')
@section('title', 'Edit-FTPP')

@php
    $role = strtolower(auth()->user()->role->name);
@endphp
@section('content')
    <div class="container mx-auto my-2 px-4">
        <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm space-y-6 mt-2">
            {{-- Back button --}}
            <div class="mb-3">
                <a href="{{ route('ftpp2.index') }}"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded hover:bg-gray-200 text-sm text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="ml-2">Back to index</span>
                </a>
            </div>
            <div x-data="editFtppApp()" x-init="init()">
                <form action="{{ route('ftpp2.update', $finding->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    {{-- Show create-audit-finding for: super admin, admin, auditor --}}
                    @php
                        $canEditAuditFinding = in_array($role, ['super admin', 'admin', 'auditor']);
                    @endphp

                    @include('contents.ftpp2.partials.edit-audit-finding', [
                        'readonly' => !$canEditAuditFinding,
                    ])

                    {{-- Show create-auditee-action for: super admin, admin, user --}}
                    @if (in_array($role, ['super admin', 'admin', 'user']))
                        @include('contents.ftpp2.partials.auditee-action')
                    @endif
                </form>
            </div>

        </div>
    </div>
@endsection
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

                // convert tanggal
                this.form.created_at = this.form.created_at?.substring(0, 10);
                this.form.due_date = this.form.due_date?.substring(0, 10);

                // tampilkan plant
                this.form._plant_display = [this.form.department?.name, this.form.process?.name, this.form.product
                        ?.name]
                    .filter(Boolean)
                    .join(" / ");

                // Auditee
                this.form.auditee_ids = (this.form.auditee ?? []).map(a => a.id).join(",");

                this.form._auditee_html = (this.form.auditee ?? [])
                    .map(a => `<span class='bg-blue-100 px-2 py-1 rounded'>${a.name}</span>`)
                    .join("");

                // Sub Audit
                this.loadSubAudit();

                // Sub Klausul
                this.loadSubKlausul();
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
                const list = this.form.sub_klausul ?? [];
                const container = document.getElementById('selectedSubContainer');

                if (!container) return;

                container.innerHTML = "";
                list.forEach(s => {
                    container.insertAdjacentHTML('beforeend', `
                    <span class="bg-blue-100 px-2 py-1 rounded">
                        ${s.code ?? ''} - ${s.name}
                    </span>
                `);
                });
            },
        }
    }
</script>
