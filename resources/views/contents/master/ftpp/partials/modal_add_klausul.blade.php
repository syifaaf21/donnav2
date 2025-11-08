<!-- Modal Add Klausul (final) -->
<div class="modal fade" id="modalAddKlausul" tabindex="-1" aria-labelledby="modalAddKlausulLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="form-add-klausul" action="{{ route('master.ftpp.klausul.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Klausul & Head</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Klausul -->
                    <div class="mb-3">
                        <label class="form-label">Klausul</label>
                        <select id="select-klausul" name="klausul_id" class="form-select" required></select>
                    </div>

                    <!-- Head Klausul -->
                    <div class="mb-3">
                        <label class="form-label">Head Klausul</label>
                        <select id="select-head" name="head_klausul_id" class="form-select" required></select>
                        <div class="form-text">Type to search or type a new head to create one.</div>
                    </div>

                    <!-- Code untuk Head Klausul (shown when head selected OR new created) -->
                    <div class="mb-3 d-none" id="group-head-code">
                        <label class="form-label">Head Code</label>
                        <input type="text" id="input-head-code" name="head_code" class="form-control"
                            placeholder="Enter code for this head...">
                    </div>

                    <!-- Sub Klausul repeater -->
                    <div class="mb-3">
                        <label class="form-label">Sub Klausul</label>
                        <div id="sub-klausul-list" class="d-flex flex-column gap-2">
                            <div class="d-flex gap-2 align-items-center sub-row">
                                <input type="text" name="sub_codes[]" class="form-control w-25" placeholder="Code">
                                <input type="text" name="sub_names[]" class="form-control"
                                    placeholder="Sub klausul name">
                                <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-remove-sub-input">×</button>
                            </div>
                        </div>
                        <button type="button" id="btn-add-sub" class="btn btn-link p-0 mt-2">+ Add another</button>
                        <div class="form-text">You can add many sub klausul rows (each has code + name).</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // === 1. Inisialisasi TomSelect ===
        const klausulSelect = new TomSelect("#select-klausul", {
            valueField: "id",
            labelField: "name",
            searchField: "name",
            preload: true,
            create: true,
            load: (query, callback) => {
                fetch(`/api/klausuls?q=${encodeURIComponent(query || "")}`)
                    .then(r => r.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });

        const headSelect = new TomSelect("#select-head", {
            valueField: "id",
            labelField: "name",
            searchField: "name",
            preload: false,
            create: true,
            options: []
        });

        const headCodeGroup = document.getElementById("group-head-code");
        const headCodeInput = document.getElementById("input-head-code");

        function isNumericId(val) {
            return /^[0-9]+$/.test(String(val || ""));
        }

        // === 2. Event untuk Klausul dan Head ===
        klausulSelect.on("change", (value) => {
            headSelect.clearOptions();
            headSelect.disable();
            headCodeGroup.classList.add("d-none");
            headCodeInput.value = "";

            if (!value) return;

            fetch(`/api/head-klausuls?klausul_id=${encodeURIComponent(value)}`)
                .then(r => r.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        headSelect.addOptions(data.map(d => ({
                            id: String(d.id),
                            name: d.name,
                            code: d.code || ""
                        })));
                    }
                    headSelect.enable();
                })
                .catch(() => headSelect.enable());
        });

        headSelect.on("change", (value) => {
            headCodeGroup.classList.add("d-none");
            headCodeInput.value = "";

            if (!value) return;

            if (isNumericId(value)) {
                const opt = headSelect.options?.[value] ?? null;
                headCodeInput.value = opt?.code || "";
                headCodeInput.readOnly = true;
            } else {
                headCodeInput.value = "";
                headCodeInput.readOnly = false;
            }
            headCodeGroup.classList.remove("d-none");
        });

        // === 3. Repeater Functionality ===
        const subContainer = document.getElementById("sub-klausul-list");

        // Fungsi pembuat row baru
        function createSubRow(code = "", name = "") {
            const row = document.createElement("div");
            row.className = "d-flex gap-2 align-items-center sub-row";
            row.innerHTML = `
            <input type="text" name="sub_codes[]" class="form-control w-25" placeholder="Code" value="${code}">
            <input type="text" name="sub_names[]" class="form-control" placeholder="Sub klausul name" value="${name}">
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-sub-input">×</button>
        `;
            return row;
        }

        // 🔹 Gunakan delegasi klik supaya event tetap jalan meskipun modal di-reload
        document.addEventListener("click", (e) => {
            // Klik tombol "Add another"
            if (e.target.matches("#btn-add-sub") || e.target.closest("#btn-add-sub")) {
                e.preventDefault();
                subContainer.appendChild(createSubRow());
            }

            // Klik tombol hapus baris sub klausul
            if (e.target.classList.contains("btn-remove-sub-input")) {
                e.preventDefault();
                e.target.closest(".sub-row")?.remove();
            }
        });

        // === 4. Reset Modal ===
        const modalEl = document.getElementById("modalAddKlausul");
        modalEl.addEventListener("show.bs.modal", () => {
            const form = document.getElementById("form-add-klausul");
            form.reset();

            klausulSelect.clear(true);
            headSelect.clearOptions();
            headSelect.disable();
            headCodeGroup.classList.add("d-none");
            headCodeInput.value = "";

            subContainer.innerHTML = "";
            subContainer.appendChild(createSubRow());
        });
    });
</script>
