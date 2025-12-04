{{-- Audit type table --}}
<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-700">Audit Type</h2>
    <button id="btn-add"
        class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
        data-bs-toggle="modal" data-bs-target="#modalAddAudit">
        <i class="bi bi-plus"></i> Add Audit
    </button>
</div>

<div
    class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
    <table class="min-w-full text-sm text-gray-700">
        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
            <tr>
                <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Audit Type</th>
                <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Sub Audit Type</th>
                <th class="px-4 py-3 border-r border-gray-200 text-center text-sm font-bold uppercase tracking-wider" style="color: #1e2b50; letter-spacing: 0.5px;">Action
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($audits as $index => $audit)
                <tr class="hover:bg-gray-50 transition-all duration-150">
                    <td class="px-4 py-3 border-r border-gray-200 text-sm">
                        {{ $index + 1 }}</td>
                    <td class="py-2 px-3 border-r border-gray-200 text-sm font-semibold">
                        {{ $audit->name }}</td>
                    <td class="py-2 px-3 border-r border-gray-200 text-sm">
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
                    <td class="px-3 py-2 border-r border-gray-200">
                        <div class="flex justify-center gap-2">
                            <button data-id="{{ $audit->id }}" data-name="{{ $audit->name }}"
                                class="btn-edit w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                <i data-feather="edit" class="w-4 h-4"></i>
                            </button>
                            |
                            <form action="{{ route('master.ftpp.audit.destroy', $audit->id) }}" method="POST"
                                class="inline-block delete-form"
                                onsubmit="return confirm('Are you sure you want to delete this data?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
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
        <form action="{{ route('master.ftpp.audit.store') }}" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalAddAuditLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Add Audit Type
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-4">
                    {{-- Audit Type --}}
                    <div class="col-md-12">
                        <label for="audit_name" class="form-label fw-semibold">Audit Type <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="audit_name" required placeholder="Enter audit type"
                            class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sub Audit Type (Dynamic Input) --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Sub Audit Type</label>

                        <div id="sub-audit-container" class="space-y-2 mt-2">
                            <div class="d-flex gap-2 sub-audit-item">
                                <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                                    class="flex-grow-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <button type="button" class="btn-remove-sub btn btn-sm btn-danger d-none"
                                    title="Remove Sub Audit">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="btn-add-sub"
                            class="mt-2 d-flex align-items-center gap-1 text-primary fw-semibold btn btn-link p-0"
                            style="font-size: 0.875rem;">
                            <i class="bi bi-plus-circle"></i> Add Sub Audit
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal"
                    style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">

                    Submit
                </button>
            </div>
        </form>
    </div>
</div>



{{-- MODAL EDIT AUDIT --}}
<div class="modal fade" id="modalEditAudit" tabindex="-1" aria-labelledby="modalEditAuditLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditAudit" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditAuditLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Audit Type
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-4">
                    {{-- Audit Type --}}
                    <div class="col-md-12">
                        <label for="edit_audit_name" class="form-label fw-semibold">Audit Type <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_audit_name" required
                            class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sub Audit Type (Dynamic Input) --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Sub Audit Type</label>

                        <div id="edit-sub-audit-container" class="space-y-2 mt-2">
                            {{-- akan diisi via JavaScript --}}
                        </div>

                        <button type="button" id="btn-edit-add-sub"
                            class="mt-2 d-flex align-items-center gap-1 text-primary fw-semibold btn btn-link p-0"
                            style="font-size: 0.875rem;">
                            <i class="bi bi-plus-circle"></i> Add Sub Audit
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                    data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editButtons = document.querySelectorAll('#section-audit .btn-edit');
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

            document.querySelectorAll('#section-audit .btn-edit').forEach(btn => {
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
                try {
                    setTimeout(() => {
                        if (document.querySelectorAll('.modal.show').length === 0) {
                            document.querySelectorAll('.modal-backdrop').forEach(backdrop =>
                                backdrop.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                    }, 50);
                } catch (e) {
                    console.warn('Backdrop cleanup skipped:', e);
                }
            });
        });
    </script>
@endpush
