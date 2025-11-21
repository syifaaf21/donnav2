{{-- ✅ Modal Edit Document Review (Modern + Department Dropdown) --}}
<div class="modal fade" id="editModal{{ $mapping->id }}" data-mapping-id="{{ $mapping->id }}" tabindex="-1"
    aria-labelledby="editModalLabel{{ $mapping->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('master.document-control.update', $mapping->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 rounded-4 shadow-lg">

                {{-- Modal Header --}}
                <div class="modal-header bg-light text-dark rounded-top-4">
                    <h5 class="modal-title fw-semibold" style="font-family: 'Inter', sans-serif;">
                        <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Metadata Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-3">

                        {{-- Document Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Document Name <span class="text-danger">*</span></label>
                            <input type="text" name="document_name" class="form-control border-1 shadow-sm"
                                value="{{ session('editOldInputs.' . $mapping->id . '.document_name', $mapping->document->name ?? '') }}"
                                required>
                            @error('document_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select tomselect border-1 shadow-sm" required>
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

                        @php
                            $today = now()->format('Y-m-d');
                        @endphp

                        {{-- Reminder Date --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Reminder Date <span class="text-danger">*</span></label>
                            <input type="date" name="reminder_date" class="form-control border-1 shadow-sm"
                                value="{{ session('editOldInputs.' . $mapping->id . '.reminder_date', \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d')) }}"
                                min="{{ $today }}"required>
                            @error('reminder_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Obsolete --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Obsolete Date <span class="text-danger">*</span></label>
                            <input type="date" name="obsolete_date" class="form-control border-1 shadow-sm"
                                value="{{ session('editOldInputs.' . $mapping->id . '.obsolete_date', \Carbon\Carbon::parse($mapping->obsolete_date)->format('Y-m-d')) }}"
                                min="{{ $today }}"required>
                            @error('obsolete_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Period (Years) --}}
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Period (Years) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="period_years" min="1"
                                class="form-control border-1 shadow-sm"
                                value="{{ session('editOldInputs.' . $mapping->id . '.period_years', $mapping->period_years) }}"
                                @if (strtolower($mapping->status->name) === 'active') disabled @endif required>
                            @error('period_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes (Quill editable) --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-medium">Notes <span class="text-danger">*</span></label>
                            <input type="hidden" name="notes" id="notes_input_edit{{ $mapping->id }}"
                                value="{{ session('editOldInputs.' . $mapping->id . '.notes', $mapping->notes) }}">
                            <div id="quill_editor_edit{{ $mapping->id }}" class="bg-white border-1 shadow-sm rounded"
                                style="min-height: 100px; max-height: 130px; overflow-y: auto;"></div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">You can format your notes with bold, italic, colors, and
                                more.</small>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                        Save Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
