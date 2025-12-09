<!-- CARD WRAPPER -->
<div @if ($readonly) class="opacity-90 pointer-events-none select-none" @endif>
    <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <th class="w-56 px-4 py-2 font-semibold bg-gray-50">Audit Type</th>
                        <td class="px-4 py-2">
                            @foreach ($auditTypes as $type)
                                <template x-if="form.audit_type_id == {{ $type->id }}">
                                    <span>{{ $type->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Sub Audit Type</th>
                        <td class="px-4 py-2">
                            @foreach ($subAudit as $sub)
                                <template x-if="form.sub_audit_type_id == {{ $sub->id }}">
                                    <span>{{ $sub->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Department / Process / Product</th>
                        <td class="px-4 py-2" x-text="form._plant_display ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Auditee</th>
                        <td class="px-4 py-2">
                            <div id="selectedAuditees" x-html="form._auditee_html ?? '-'" class="flex flex-wrap gap-2">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Auditor / Inisiator</th>
                        <td class="px-4 py-2">
                            @foreach ($auditors as $auditor)
                                <template x-if="form.auditor_id == {{ $auditor->id }}">
                                    <span>{{ $auditor->name }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Date</th>
                        <td class="px-4 py-2" x-text="form.created_at ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Registration Number</th>
                        <td class="px-4 py-2" x-text="form.registration_number ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Finding Category</th>
                        <td class="px-4 py-2">
                            @foreach ($findingCategories as $category)
                                <template x-if="form.finding_category_id == {{ $category->id }}">
                                    <span>{{ ucfirst($category->name) }}</span>
                                </template>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Finding / Issue</th>
                        <td class="px-4 py-2 whitespace-pre-line" x-text="form.finding_description ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50">Duedate</th>
                        <td class="px-4 py-2" x-text="form.due_date ?? '-'"></td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Clauses</th>
                        <td class="px-4 py-2">
                            <div id="selectedSubContainer" class="flex flex-wrap gap-2"></div>
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold bg-gray-50 align-top">Attachments</th>
                        <td class="px-4 py-2">
                            <div id="previewImageContainer" class="flex flex-wrap gap-2"></div>
                            <div id="previewFileContainer" class="mt-2 flex flex-col gap-1"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- icon feather init --}}
<script>
    // Inisialisasi Feather Icons
    feather.replace();
</script>
