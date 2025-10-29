<!-- Modal Edit Klausul -->
<div class="modal fade" id="modalEditKlausul" tabindex="-1" aria-labelledby="modalEditKlausulLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="form-edit-klausul" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditKlausulLabel">Edit Klausul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Head Klausul -->
                    <div class="mb-3">
                        <label class="form-label">Head Klausul</label>
                        <input type="text" name="head_name" id="edit-head-name" class="form-control" required>
                    </div>

                    <!-- Sub Klausul -->
                    <div class="mb-3">
                        <label class="form-label">Sub Klausul</label>
                        <div id="edit-sub-list" class="d-flex flex-column gap-2">
                            <!-- dynamic items here -->
                        </div>
                        <button type="button" id="btn-add-sub-edit" class="btn btn-link p-0 mt-1">+ Add
                            another</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
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
