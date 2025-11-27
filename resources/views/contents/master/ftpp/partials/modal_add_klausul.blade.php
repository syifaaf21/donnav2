{{-- Modal Add Klausul (final) --}}
<div class="modal fade" id="modalAddKlausul" tabindex="-1" aria-labelledby="modalAddKlausulLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form-add-klausul" action="{{ route('master.ftpp.klausul.store') }}" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4" style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalAddKlausulLabel" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Add Klausul & Head
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
                    {{-- Klausul --}}
                    <div class="col-md-6">
                        <label for="select-klausul" class="form-label fw-semibold">Klausul <span class="text-danger">*</span></label>
                        <select id="select-klausul" name="klausul_id" class="form-select @error('klausul_id') is-invalid @enderror" required></select>
                        @error('klausul_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Head Klausul --}}
                    <div class="col-md-6">
                        <label for="select-head" class="form-label fw-semibold">Head Klausul <span class="text-danger">*</span></label>
                        <select id="select-head" name="head_klausul_id" class="form-select @error('head_klausul_id') is-invalid @enderror" required></select>
                        <div class="form-text">Type to search or type a new head to create one.</div>
                        @error('head_klausul_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Head Code --}}
                    <div class="col-md-12 d-none" id="group-head-code">
                        <label for="input-head-code" class="form-label fw-semibold">Head Code</label>
                        <input type="text" id="input-head-code" name="head_code" class="form-control" placeholder="Enter code for this head...">
                    </div>

                    {{-- Sub Klausul Repeater --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Sub Klausul</label>
                        <div id="sub-klausul-list" class="d-flex flex-column gap-2">
                            <div class="d-flex gap-2 align-items-center sub-row">
                                <input type="text" name="sub_codes[]" class="form-control w-25" placeholder="Code">
                                <input type="text" name="sub_names[]" class="form-control" placeholder="Sub klausul name">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-sub-input">×</button>
                            </div>
                        </div>
                        <button type="button" id="btn-add-sub" class="btn btn-link p-0 mt-2">+ Add another</button>
                        <div class="form-text">You can add many sub klausul rows (each has code + name).</div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-5 py-2 rounded-3 fw-semibold" style="background-color: #3b82f6; border: 1px solid #3b82f6; color: white; transition: background-color 0.3s ease;">
                    Submit
                </button>
            </div>
        </form>
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
            placeholder: "Select or type to add klausul...",
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
