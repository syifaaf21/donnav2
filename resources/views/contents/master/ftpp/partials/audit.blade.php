{{-- Audit type table --}}
<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-700">Audit Type</h2>
    <button id="btn-add"
        class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-3 py-1.5 rounded-md transition"
        data-bs-toggle="modal" data-bs-target="#modalAddAudit">
        <i class="bi bi-plus"></i> Add Audit
    </button>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="w-12 text-center border-b border-gray-200 py-2 px-3">No</th>
                <th class="border-b border-gray-200 py-2 px-3 text-left">Audit Type</th>
                <th class="border-b border-gray-200 py-2 px-3 text-left">Sub Audit Type</th>
                <th class="w-48 text-center border-b border-gray-200 py-2 px-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($audits as $index => $audit)
                <tr class="hover:bg-gray-50">
                    <td class="text-center py-2 px-3 border-b border-gray-200 text-gray-600">
                        {{ $index + 1 }}</td>
                    <td class="py-2 px-3 border-b border-gray-200 font-medium text-gray-800">
                        {{ $audit->name }}</td>
                    <td class="py-2 px-3 border-b border-gray-200 text-gray-700">
                        @if ($audit->subAudit->isNotEmpty())
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($audit->subAudit as $sub)
                                    <li>{{ $sub->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 border-b text-center">
                        <div class="flex justify-center gap-2">
                        <button data-id="{{ $audit->id }}" data-name="{{ $audit->name }}"
                            class="bg-blue-600 text-white hover:bg-blue-700 p-2 rounded">
                            <i data-feather="edit" class="w-4 h-4"></i>
                        </button>
                        |
                        <button data-id="{{ $audit->id }}"
                            class="bg-red-600 text-white hover:bg-red-700 p-2 rounded">
                            <i data-feather="trash-2" class="w-4 h-4"></i>
                        </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-gray-400 py-4">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- MODAL ADD AUDIT --}}
<div class="modal fade" id="modalAddAudit" tabindex="-1" aria-labelledby="modalAddAuditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header bg-blue-500 text-white rounded-t-xl">
                <h5 class="modal-title text-sm font-medium" id="modalAddAuditLabel">
                    <i class="bi bi-plus-circle me-1"></i> Add Audit Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form action="{{ route('master.ftpp.audit.store') }}" method="POST">
                @csrf
                <div class="modal-body space-y-4">

                    {{-- Audit Type --}}
                    <div>
                        <label for="audit_name" class="text-sm font-medium text-gray-700">Audit Type</label>
                        <input type="text" name="name" id="audit_name" required
                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    {{-- Sub Audit Type (Dynamic Input) --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Sub Audit Type</label>

                        <div id="sub-audit-container" class="space-y-2 mt-2">
                            <div class="flex gap-2 sub-audit-item">
                                <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <button type="button"
                                    class="btn-remove-sub bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-md text-sm hidden">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="btn-add-sub"
                            class="mt-2 flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="bi bi-plus-circle"></i> Add Sub Audit
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-t">
                    <button type="button" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT AUDIT --}}
<div class="modal fade" id="modalEditAudit" tabindex="-1" aria-labelledby="modalEditAuditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header bg-blue-500 text-white rounded-t-xl">
                <h5 class="modal-title text-sm font-medium" id="modalEditAuditLabel">
                    <i class="bi bi-pencil-square me-1"></i> Edit Audit Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="formEditAudit" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body space-y-4">
                    {{-- Audit Type --}}
                    <div>
                        <label for="edit_audit_name" class="text-sm font-medium text-gray-700">Audit Type</label>
                        <input type="text" name="name" id="edit_audit_name" required
                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    {{-- Sub Audit Type (Dynamic Input) --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Sub Audit Type</label>

                        <div id="edit-sub-audit-container" class="space-y-2 mt-2">
                            {{-- akan diisi via JavaScript --}}
                        </div>

                        <button type="button" id="btn-edit-add-sub"
                            class="mt-2 flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="bi bi-plus-circle"></i> Add Sub Audit
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-t">
                    <button type="button" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Hidden Delete Form --}}
<form id="form-delete" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editButtons = document.querySelectorAll('.btn-edit');
            const editForm = document.getElementById('formEditAudit');
            const nameInput = document.getElementById('edit_audit_name');

            editButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');

                    // isi field
                    nameInput.value = name;

                    // ubah action form
                    editForm.action = `/master/ftpp/audit/update/${id}`;

                    // buka modal
                    const editModal = new bootstrap.Modal(document.getElementById(
                        'modalEditAudit'));
                    editModal.show();
                });
            });
        });

        // add handler
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('sub-audit-container');
            const addBtn = document.getElementById('btn-add-sub');

            addBtn.addEventListener('click', () => {
                const newField = document.createElement('div');
                newField.classList.add('flex', 'gap-2', 'sub-audit-item');

                newField.innerHTML = `
                <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                <button type="button"
                    class="btn-remove-sub bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-md text-sm">
                    <i class="bi bi-x"></i>
                </button>
            `;

                container.appendChild(newField);

                // event untuk tombol hapus
                const removeBtn = newField.querySelector('.btn-remove-sub');
                removeBtn.addEventListener('click', () => {
                    newField.remove();
                });

                // tampilkan tombol remove di semua field kecuali pertama
                document.querySelectorAll('.btn-remove-sub').forEach(btn => btn.classList.remove('hidden'));
            });
        });

        // edit handler
        document.addEventListener('DOMContentLoaded', () => {
            const editContainer = document.getElementById('edit-sub-audit-container');
            const btnAddEditSub = document.getElementById('btn-edit-add-sub');

            // tombol tambah sub audit di modal edit
            btnAddEditSub.addEventListener('click', () => {
                const field = document.createElement('div');
                field.classList.add('flex', 'gap-2', 'sub-audit-item');
                field.innerHTML = `
                <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                <button type="button"
                    class="btn-remove-sub bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-md text-sm">
                    <i class="bi bi-x"></i>
                </button>
            `;
                editContainer.appendChild(field);
                field.querySelector('.btn-remove-sub').addEventListener('click', () => field.remove());
            });

            const editModalEl = document.getElementById('modalEditAudit');
            const editModal = new bootstrap.Modal(editModalEl); // buat sekali

            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-id');
                    const response = await fetch(`/master/ftpp/audit/${id}`);
                    const data = await response.json();

                    document.getElementById('edit_audit_name').value = data.name;
                    document.getElementById('formEditAudit').action =
                        `/master/ftpp/audit/update/${id}`;
                    const container = document.getElementById('edit-sub-audit-container');
                    container.innerHTML = '';

                    if (data.sub_audit?.length) {
                        data.sub_audit.forEach(sub => {
                            const field = document.createElement('div');
                            field.classList.add('flex', 'gap-2', 'sub-audit-item');
                            field.innerHTML = `
                    <input type="text" name="sub_audit_existing[${sub.id}]" value="${sub.name}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <button type="button"
                        class="btn-remove-sub bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-md text-sm">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                            field.querySelector('.btn-remove-sub').addEventListener(
                                'click', () => field.remove());
                            container.appendChild(field);
                        });
                    }

                    editModal.show();
                });
            });

            document.getElementById('modalEditAudit').addEventListener('hidden.bs.modal', function() {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });

        // delete handler
        document.addEventListener("DOMContentLoaded", function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const formDelete = document.getElementById('form-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;

                    if (confirm('Apakah kamu yakin ingin menghapus data ini?')) {
                        // Set action URL ke form
                        formDelete.action = `/master/ftpp/audit/${id}`;
                        formDelete.submit();
                    }
                });
            });
        });
    </script>
@endpush
