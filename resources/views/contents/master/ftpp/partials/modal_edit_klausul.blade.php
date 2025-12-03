{{-- Modal Edit Klausul --}}
<div class="modal fade" id="modalEditKlausul" tabindex="-1" aria-labelledby="modalEditKlausulLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form-edit-klausul" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditKlausulLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Klausul
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
                    {{-- Head Klausul --}}
                    <div class="col-md-12">
                        <label for="edit-head-name" class="form-label fw-semibold">Head Klausul <span
                                class="text-danger">*</span></label>
                        <input type="text" name="head_name" id="edit-head-name"
                            class="form-control @error('head_name') is-invalid @enderror" required>
                        @error('head_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sub Klausul --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Sub Klausul</label>
                        <div id="edit-sub-list" class="d-flex flex-column gap-2">
                            {{-- dynamic items will be injected via JS --}}
                        </div>
                        <button type="button" id="btn-add-sub-edit" class="btn btn-link p-0 mt-2">+ Add
                            another</button>
                        <div class="form-text">You can add many sub klausul rows (each has code + name).</div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal"
                    style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit"
                    class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditKlausul'));
        const formEdit = document.getElementById('form-edit-klausul');
        const headInput = document.getElementById('edit-head-name');
        const subContainer = document.getElementById('edit-sub-list');

        // --- Klik tombol edit ---
        document.querySelectorAll('.btn-edit-klausul').forEach(btn => {
            btn.addEventListener('click', () => {
                const headId = btn.dataset.headId;
                const headName = btn.dataset.headName;
                const subKlausuls = JSON.parse(btn.dataset.sub || '[]');

                // Set form action
                formEdit.action = `/master/ftpp/klausul/update/${headId}`;

                // Isi field head name
                headInput.value = headName;

                // Render sub klausul
                subContainer.innerHTML = '';
                if (subKlausuls.length > 0) {
                    subKlausuls.forEach(sub => {
                        const row = document.createElement('div');
                        row.className = 'd-flex gap-2 align-items-center';
                        row.innerHTML = `
                        <input type="text" name="sub_codes[]" value="${sub.code || ''}" class="form-control w-25" placeholder="Code">
                        <input type="text" name="sub_names[]" value="${sub.name || ''}" class="form-control" placeholder="Sub klausul name">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-sub">×</button>
                    `;
                        subContainer.appendChild(row);
                    });
                } else {
                    // Jika belum ada sub klausul
                    const empty = document.createElement('div');
                    empty.className = 'd-flex gap-2 align-items-center';
                    empty.innerHTML = `
                    <input type="text" name="sub_codes[]" class="form-control w-25" placeholder="Code">
                    <input type="text" name="sub_names[]" class="form-control" placeholder="Sub klausul name">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-sub">×</button>
                `;
                    subContainer.appendChild(empty);
                }

                modalEdit.show();
            });
        });

        // --- Tambah sub klausul ---
        document.getElementById('btn-add-sub-edit').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'd-flex gap-2 align-items-center';
            row.innerHTML = `
            <input type="text" name="sub_codes[]" class="form-control w-25" placeholder="Code">
            <input type="text" name="sub_names[]" class="form-control" placeholder="Sub klausul name">
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-sub">×</button>
        `;
            subContainer.appendChild(row);
        });

        // --- Hapus sub klausul ---
        document.addEventListener('click', e => {
            if (e.target.classList.contains('btn-remove-sub')) {
                e.target.closest('.d-flex').remove();
            }
        });
    });
</script>
