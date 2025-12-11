<div class="modal fade" id="editModal{{ $mapping->id }}" data-mapping-id="{{ $mapping->id }}" tabindex="-1"
    aria-labelledby="editModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('master.document-control.update', $mapping->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                {{-- Modal Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">

                    <h5 class="modal-title fw-semibold text-dark text-center"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;"><i class="bi bi-pencil-square me-2 text-primary"></i>
                        Edit Metadata Document
                    </h5>

                    {{-- Close button: white background with subtle border, circle --}}
                    <button type="button"
                        class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="width: 36px; height: 36px; border: 1px solid #ddd;">
                        <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-5 bg-gray-50" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-4">

                        {{-- Document Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Document Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="document_name" class="form-control border-0 shadow-sm rounded-3"
                                value="{{ session('editOldInputs.' . $mapping->id . '.document_name', $mapping->document->name ?? '') }}"
                                required>
                            @error('document_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select tomselect border-0 shadow-sm rounded-3"
                                required>
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ session('editOldInputs.' . $mapping->id . '.department_id', $mapping->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php $today = now()->format('Y-m-d'); @endphp


                        {{-- Obsolete --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Obsolete Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="obsolete_date" class="form-control border-0 shadow-sm rounded-3"
                                value="{{ session('editOldInputs.' . $mapping->id . '.obsolete_date', \Carbon\Carbon::parse($mapping->obsolete_date)->format('Y-m-d')) }}"
                                min="{{ $today }}" required>
                            @error('obsolete_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Reminder Date --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reminder Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="reminder_date" class="form-control border-0 shadow-sm rounded-3"
                                value="{{ session('editOldInputs.' . $mapping->id . '.reminder_date', \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d')) }}"
                                min="{{ $today }}" required>
                            @error('reminder_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Period (Years) --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Period (Years) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="period_years" min="1"
                                class="form-control border-0 shadow-sm rounded-3"
                                value="{{ session('editOldInputs.' . $mapping->id . '.period_years', $mapping->period_years) }}"
                                @if (strtolower($mapping->status->name) === 'active') readonly @endif required>
                            @error('period_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes <span class="text-danger">*</span></label>
                            <input type="hidden" name="notes" id="notes_input_edit{{ $mapping->id }}"
                                value="{{ session('editOldInputs.' . $mapping->id . '.notes', $mapping->notes) }}">
                            <div id="quill_editor_edit{{ $mapping->id }}" class="bg-white rounded-3 shadow-sm p-2"
                                style="min-height: 120px; max-height: 160px; overflow-y: auto; border: 1px solid #e2e8f0;">
                            </div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format your notes with bold, italic, colors, and more.</small>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-4 justify-content-between bg-white">
                    <button type="button"
                        class="btn btn-link text-secondary fw-semibold px-4 py-2"
                        data-bs-dismiss="modal"
                        style="text-decoration: none; transition: background-color 0.3s ease;">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
