{{-- auditor-input blade --}}
<table class="w-full border border-black text-sm">
    <tr>
        <td class="border border-black p-1 w-1/3 align-top">
            <span class="font-semibold">Audit Type:</span>
            <div class="mt-2 space-y-2">
                @foreach ($auditTypes as $type)
                    <label class="flex items-center gap-2 p-2 rounded border cursor-not-allowed transition-all"
                        :class="String(form.audit_type_id) === '{{ $type->id }}' ? 'bg-blue-50 border-blue-500 text-blue-900 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                        <input type="radio" name="audit_type_id" value="{{ $type->id }}" x-model="form.audit_type_id" disabled
                            class="w-4 h-4 text-blue-600">
                        <span>{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Level 2: Sub Audit Type (muncul hanya jika audit type dipilih) --}}
            <div id="subAuditType" class="mt-2 ml-4 flex flex-wrap gap-2">
                @foreach ($subAudit as $sub)
                    <label class="flex items-center gap-2 p-2 rounded border cursor-not-allowed transition-all"
                        x-show="String(form.audit_type_id) === '{{ $sub->audit_type_id }}'"
                        :class="String(form.sub_audit_type_id) === '{{ $sub->id }}' ? 'bg-blue-50 border-blue-500 text-blue-900 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                        <input type="radio" name="sub_audit_type_id" value="{{ $sub->id }}"
                               x-model="form.sub_audit_type_id" disabled
                               class="w-4 h-4 text-blue-600">
                        <span>{{ $sub->name }}</span>
                    </label>
                @endforeach
            </div>
        </td>

        <td class="border border-black p-1">
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 text-gray-700 font-semibold w-1/3">Department / Process / Product:</td>
                    <td class="py-2">
                        {{-- <button type="button"
                            class="px-3 py-1 border text-gray-400 rounded bg-gray-100 cursor-not-allowed"
                            disabled>
                            Select Department / Process / Product by Plant
                        </button> --}}
                        <input type="hidden" id="selectedPlant" name="plant">
                        <input type="hidden" id="selectedDepartment" name="department_id" x-model="form.department_id">
                        <input type="hidden" id="selectedProcess" name="process_id" x-model="form.process_id">
                        <input type="hidden" id="selectedProduct" name="product_id" x-model="form.product_id">
                        <div id="plantDeptDisplay" x-text="form._plant_display ?? '-'" class="mt-1 text-gray-700"></div>
                    </td>
                </tr>
                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Auditee:</td>
                    <td class="py-2">
                        {{-- <button type="button"
                            class="px-3 py-1 border text-gray-400 rounded bg-gray-100 cursor-not-allowed"
                            disabled>
                            Select Auditee
                        </button> --}}

                        <!-- Hasil pilihan -->
                        <div id="selectedAuditees" x-html="form._auditee_html ?? ''" class="flex flex-wrap gap-2 mt-2">
                        </div>
                        <input type="hidden" id="auditee_ids" name="auditee_ids" x-model="form.auditee_ids">
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Auditor / Inisiator:</td>
                    <td class="py-2">
                        <select name="auditor_id" x-model="form.auditor_id"
                            class="border-b border-gray-400 w-full focus:outline-none bg-gray-100" disabled>
                            <option value="">-- Choose Auditor --</option>
                            @foreach ($auditors as $auditor)
                                <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Date:</td>
                    <td class="py-2">
                        <input type="date" name="created_at" x-model="form.created_at"
                            class="border-b border-gray-400 w-full focus:outline-none bg-gray-100" readonly>
                    </td>
                </tr>

                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Registration Number:</td>
                    <td class="py-2">
                        <input type="text" id="reg_number" name="registration_number"
                            x-model="form.registration_number"
                            class="border-b border-gray-400 w-full focus:outline-none bg-gray-100" readonly>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="border border-black p-1">
            <span class="font-semibold">Finding Category:</span>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach ($findingCategories as $category)
                    <label class="flex items-center gap-2 px-3 py-2 rounded border cursor-not-allowed transition-all"
                        :class="String(form.finding_category_id) === '{{ $category->id }}' ? 'bg-blue-50 border-blue-500 text-blue-900 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                        <input type="radio" name="finding_category_id" x-model="form.finding_category_id"
                            value="{{ $category->id }}" disabled
                            class="w-4 h-4 text-blue-600">
                        <span>{{ ucfirst($category->name) }}</span>
                    </label>
                @endforeach
            </div>
        </td>
    </tr>
</table>

{{-- TEMUAN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold">
        <td class="border border-black p-1">AUDITOR / INISIATOR</td>
    </tr>
    <tr>
        <td class="border border-black p-2">
            <div>
                <label class="font-semibold">Finding / Issue:</label>
                <textarea name="finding_description" x-model="form.finding_description"
                    class="w-full border border-gray-400 rounded p-1 h-24 bg-gray-100" readonly></textarea>
            </div>
            <div class="flex justify-between mt-2">
                <div class="mr-8">
                    <span class="font-semibold">Duedate:</span>
                    <input type="date" name="due_date" x-model="form.due_date" class="border-b border-gray-400 bg-gray-100" readonly>
                </div>
                <div class="mt-2">
                    <div class="text-right">Clauses/Categories: </div>
                    <div class="flex items-end gap-2 mb-1">
                        <!-- Tombol Trigger (disabled view only) -->
                        <div class="ml-auto">
                            {{-- <button type="button" disabled
                                class="px-3 py-1 border text-gray-400 rounded bg-gray-100 cursor-not-allowed">
                                Select Clause
                            </button> --}}
                        </div>
                    </div>
                    <input type="hidden" id="selectedSub" name="sub_klausul_id[]">
                    <div id="selectedSubContainer" class="flex flex-wrap gap-2 mt-2 justify-end"></div>
                </div>
            </div>
        </td>
    </tr>
</table>

<table>
    <tr>
        <td>
            <div>
                <!-- Preview containers (sesuaikan posisi di form) -->
                <div id="previewImageContainer" class="mt-2 flex flex-wrap gap-2"></div>
                <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>

                <!-- Attachment button (disabled in view-only) -->
                <div class="relative inline-block">
                    <button id="attachBtn" type="button"
                        class="flex items-center gap-2 px-3 py-1 border rounded text-gray-400 bg-gray-100 cursor-not-allowed"
                        aria-haspopup="true" aria-expanded="false" title="Attach files" disabled>
                        <i data-feather="paperclip" class="w-4 h-4"></i>
                        <span id="attachCount" class="text-xs text-gray-600 hidden">0</span>
                    </button>

                    <!-- keep menu hidden for view-only -->
                    <div id="attachMenu" class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow z-20">
                    </div>
                </div>

                <!-- Hidden file inputs (disabled) -->
                <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple class="hidden" disabled>
                <input type="file" id="fileInput" name="files[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple
                    class="hidden" disabled>
                <!-- Optional combined input -->
                <input type="file" id="combinedInput" name="attachments[]"
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden" disabled>
            </div>
        </td>
    </tr>
</table>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();
</script>
