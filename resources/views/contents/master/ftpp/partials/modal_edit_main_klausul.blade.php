{{-- Modal Edit Klausul --}}
<div class="modal fade" id="modalEditMainKlausul" tabindex="-1" aria-labelledby="modalEditMainKlausulLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form-edit-main-klausul" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditMainKlausulLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Main Klausul
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
                        <label for="edit-audit-type" class="form-label fw-semibold">Audit Type <span
                                class="text-danger">*</span></label>
                        <select id="edit-audit-type" name="audit_type_id"
                            class="form-select @error('audit_type_id') is-invalid @enderror" required>
                            <option value="" disabled>-- Select Audit Type --</option>
                            @foreach ($audits as $audit)
                                <option value="{{ $audit->id }}">{{ $audit->name }}</option>
                            @endforeach
                        </select>
                        @error('audit_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Name --}}
                    <div class="col-md-12">
                        <label for="edit-name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-name" name="name"
                            class="form-control @error('name') is-invalid @enderror" 
                            placeholder="Enter name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                    class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('modalEditMainKlausul');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                const form = document.getElementById('form-edit-main-klausul');
                if (form) {
                    form.reset();
                }
            });
        }
    });
</script>
