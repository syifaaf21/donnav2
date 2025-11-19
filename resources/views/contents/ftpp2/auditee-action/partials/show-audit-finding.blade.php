<!-- CARD WRAPPER -->
<div @if ($readonly) class="opacity-70 pointer-events-none select-none" @endif>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
            <!-- AUDIT TYPE -->
            <div>
                <label class="font-semibold block">Audit Type:</label>
                <div class="mt-2 space-y-1">
                    @foreach ($auditTypes as $type)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="audit_type_id" value="{{ $type->id }}"
                                x-model="form.audit_type_id">
                            {{ $type->name }}
                        </label>
                    @endforeach
                </div>

                <!-- SUB AUDIT TYPE -->
                <div id="subAuditType" class="mt-2 ml-4 space-y-1">
                    @foreach ($subAudit as $sub)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="sub_audit_type_id" value="{{ $sub->id }}" disabled>
                            {{ $sub->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- DEPARTMENT / PROCESS / PRODUCT -->
            <div class="space-y-1">
                <label class="font-semibold">Department / Process / Product:</label>

                <button type="button" class="px-3 py-1 border text-gray-600 rounded hover:bg-blue-600 hover:text-white"
                    onclick="openPlantSidebar()">
                    Select Department / Process / Product by Plant
                </button>

                <input type="hidden" id="selectedPlant" name="plant">
                <input type="hidden" id="selectedDepartment" name="department_id" x-model="form.department_id">
                <input type="hidden" id="selectedProcess" name="process_id" x-model="form.process_id">
                <input type="hidden" id="selectedProduct" name="product_id" x-model="form.product_id">

                <div id="plantDeptDisplay" x-text="form._plant_display ?? '-'" class="mt-1 text-gray-700">
                </div>
            </div>

            <!-- AUDITEE -->
            <div class="space-y-1">
                <label class="font-semibold">Auditee:</label>

                <button type="button" onclick="openAuditeeSidebar()"
                    class="px-3 py-1 border text-gray-600 rounded hover:bg-blue-600 hover:text-white">
                    Select Auditee
                </button>

                <div id="selectedAuditees" x-html="form._auditee_html ?? ''" class="flex flex-wrap gap-2 mt-2">
                </div>

                <input type="hidden" id="auditee_ids" name="auditee_ids[]" x-model="form.auditee_ids">
            </div>

            <!-- AUDITOR -->
            <div class="space-y-1">
                <label class="font-semibold">Auditor / Inisiator:</label>
                <select name="auditor_id" x-model="form.auditor_id"
                    class="border-b border-gray-400 w-full focus:outline-none">
                    <option value="">-- Choose Auditor --</option>
                    @foreach ($auditors as $auditor)
                        <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- DATE -->
            <div class="space-y-1">
                <label class="font-semibold">Date:</label>
                <input type="date" name="created_at" x-model="form.created_at"
                    class="border-b border-gray-400 w-full focus:outline-none" value="{{ now()->toDateString() }}">
            </div>

            <!-- REG NUMBER -->
            <div class="space-y-1">
                <label class="font-semibold">Registration Number:</label>
                <input type="text" name="registration_number" id="reg_number" x-model="form.registration_number"
                    class="border-b border-gray-400 w-full focus:outline-none bg-gray-100" readonly>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                <!-- FINDING CATEGORY -->
                <div>
                    <label class="font-semibold">Finding Category:</label>
                    <div class="mt-1">
                        @foreach ($findingCategories as $category)
                            <label class="mr-4">
                                <input type="radio" name="finding_category_id" x-model="form.finding_category_id"
                                    value="{{ $category->id }}">
                                {{ ucfirst($category->name) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <h5 class="font-semibold text-gray-700">AUDITOR / INISIATOR</h5>

                <!-- FINDING -->
                <div>
                    <label class="font-semibold">Finding / Issue:</label>
                    <textarea name="finding_description" x-model="form.finding_description" class="w-full border rounded p-2 h-24" required></textarea>
                </div>

                <div class="flex justify-between items-start">
                    <!-- DUE DATE -->
                    <div>
                        <label class="font-semibold">Duedate:</label>
                        <input type="date" name="due_date" x-model="form.due_date"
                            class="border-b border-gray-400 ml-2" required>
                    </div>

                    <!-- CLAUSE SELECT -->
                    <div class="text-right">
                        <button type="button" onclick="openSidebar()"
                            class="px-3 py-1 border text-gray-600 rounded hover:bg-blue-600 hover:text-white">
                            Select Clause
                        </button>

                        <input type="hidden" id="selectedSub" name="sub_klausul_id[]">

                        <div id="selectedSubContainer" class="flex flex-wrap gap-2 mt-3 justify-end"></div>
                    </div>
                </div>
            </div>

            {{-- ATTACHMENT --}}
            <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                <div class="font-semibold text-lg text-gray-700">Attachments</div>
                <div>
                    <!-- Preview containers (sesuaikan posisi di form) -->
                    <div id="previewImageContainer" class="mt-2 flex flex-wrap gap-2"></div>
                    <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>

                    <!-- Attachment button (paperclip) -->
                    <div class="relative inline-block">
                        @if (in_array(optional(auth()->user()->role)->name, ['Admin', 'Auditor']))
                            <button id="attachBtn" type="button"
                                class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                                aria-haspopup="true" aria-expanded="false" title="Attach files">
                                <i data-feather="paperclip" class="w-4 h-4"></i>
                                <span id="attachCount" class="text-xs text-gray-600 hidden">0</span>
                            </button>
                        @endif

                        <!-- Small menu seperti email (hidden, muncul saat klik) -->
                        <div id="attachMenu"
                            class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow z-20">
                            <button id="attachImages" type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <i data-feather="image" class="w-4 h-4"></i>
                                <span class="text-sm">Upload Images</span>
                            </button>
                            <button id="attachDocs" type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <i data-feather="file-text" class="w-4 h-4"></i>
                                <span class="text-sm">Upload Documents</span>
                            </button>
                            <div class="border-t mt-1"></div>
                            <button id="attachBoth" type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <i data-feather="upload" class="w-4 h-4"></i>
                                <span class="text-sm">Open Combined Picker</span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden file inputs -->
                    <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple class="hidden">
                    <input type="file" id="fileInput" name="files[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple
                        class="hidden">
                    <!-- Optional combined input -->
                    <input type="file" id="combinedInput" name="attachments[]"
                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
                </div>
                {{-- @if (in_array(optional(auth()->user()->role)->name, ['Super Admin', 'Admin', 'Auditor']))
                    <button type="button" onclick="updateAuditFinding()"
                        class="ml-auto mt-2 bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 mb-4">
                        Save changes
                    </button>
                {{-- @endif --}}
            </div>
        </div>
    </div>
</div>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();
</script>
