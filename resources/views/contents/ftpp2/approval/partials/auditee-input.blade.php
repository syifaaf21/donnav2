
{{-- 5 WHY --}}
<input type="hidden" name="audit_finding_id" x-model="selectedId" disabled>
<input type="hidden" name="action" value="save_auditee_action" disabled>
<input type="hidden" name="pic" value="{{ auth()->user()->id }}" disabled>
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id" disabled>

<style>
    .approval-content {
        font-family: inherit;
        font-size: 0.875rem !important; /* text-sm = 14px */
        line-height: 1.25rem;
        color: inherit;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .approval-content * {
        font-size: inherit !important;
        line-height: inherit !important;
    }
    .approval-content ul {
        list-style-type: disc;
        padding-left: 20px;
        margin: 4px 0;
        display: block;
    }
    .approval-content ol {
        list-style-type: decimal;
        padding-left: 20px;
        margin: 4px 0;
        display: block;
    }
    .approval-content li {
        margin: 2px 0;
        display: list-item;
        font-size: inherit !important;
    }
    .approval-content b, .approval-content strong {
        font-weight: bold;
        display: inline;
    }
    .approval-content i, .approval-content em {
        font-style: italic;
        display: inline;
    }
    .approval-content u {
        text-decoration: underline;
        display: inline;
    }
    .approval-content br {
        display: block;
    }
    .approval-content p {
        margin: 4px 0;
        display: block;
    }
    .approval-content div {
        display: block;
    }
</style>

<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold">
        <td class="border border-black p-1">AUDITEE</td>
    </tr>
    <tr>
        <td class="border border-black p-1">
            <div class="font-semibold mb-1">Issue Causes (5 Why)</div>
            <template x-for="i in 5">
                <div class="ml-2 mb-1" x-show="form['why_'+i+'_mengapa'] || form['cause_'+i+'_karena']">
                    <label x-show="form['why_'+i+'_mengapa']">Why-<span x-text="i"></span> (Mengapa):</label>
                    <div class="w-full border-b border-gray-400 p-1 bg-gray-100 approval-content"
                        x-show="form['why_'+i+'_mengapa']"
                        x-html="form['why_'+i+'_mengapa']"></div>
                    <label x-show="form['cause_'+i+'_karena']" class="block mt-2">Cause (Karena):</label>
                    <div class="w-full border-b border-gray-400 p-1 bg-gray-100 approval-content"
                        x-show="form['cause_'+i+'_karena']"
                        x-html="form['cause_'+i+'_karena']"></div>
                </div>
            </template>
            <div class="mt-1">
                Root Cause: <span class="text-danger">*</span>
                <div class="w-full border border-gray-400 rounded p-1 bg-gray-100 approval-content min-h-20"
                    x-html="form.root_cause"></div>
            </div>
        </td>
    </tr>
</table>

{{-- TINDAKAN KOREKSI & PERBAIKAN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold text-center">
        <td class="border border-black p-1 w-8">No</td>
        <td class="border border-black p-1">Activity</td>
        <td class="border border-black p-1 w-32">PIC</td>
        <td class="border border-black p-1 w-28">Planning</td>
        <td class="border border-black p-1 w-28">Actual</td>
    </tr>

    {{-- Koreksi --}}
    <tr>
        <td colspan="5" class="border border-black p-1 font-semibold">Corrective Action</td>
    </tr>
    <template x-for="i in 4">
        <tr class="corrective-row" x-show="form['corrective_'+i+'_activity']">
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black p-1" style="word-wrap: break-word; white-space: normal; display: table-cell; vertical-align: top;">
                <div class="w-full bg-gray-100 p-1 rounded min-h-10 approval-content" x-html="form['corrective_'+i+'_activity']"></div>
            </td>
            <td class="border border-black p-1">
                <div class="w-full bg-gray-100 p-1 rounded min-h-10 approval-content" x-html="form['corrective_'+i+'_pic']"></div>
            </td>
            <td class="border border-black">
                <input name="planning_date[]" type="date" class="w-full border-none p-1 bg-gray-100 rounded min-h-10 flex items-center"
                    x-model="form['corrective_'+i+'_planning']" readonly disabled>
            </td>
            <td class="border border-black text-center">
                <span x-show="form['corrective_'+i+'_actual'] === '-' || !form['corrective_'+i+'_actual']" class="block p-1">-</span>
                <input name="actual_date[]" type="date" class="w-full border-none p-1 bg-gray-100"
                    x-model="form['corrective_'+i+'_actual']" x-show="form['corrective_'+i+'_actual'] && form['corrective_'+i+'_actual'] !== '-'" readonly disabled>
            </td>
        </tr>
    </template>

    {{-- Perbaikan --}}
    <tr>
        <td colspan="5" class="border border-black p-1 font-semibold">Preventive Action</td>
    </tr>
    <template x-for="i in 4">
        <tr class="preventive-row" x-show="form['preventive_'+i+'_activity']">
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black p-1" style="word-wrap: break-word; white-space: normal; display: table-cell; vertical-align: top;">
                <div class="w-full bg-gray-100 p-1 rounded min-h-10 approval-content" x-html="form['preventive_'+i+'_activity']"></div>
            </td>
            <td class="border border-black p-1">
                <div class="w-full bg-gray-100 p-1 rounded min-h-10 approval-content" x-html="form['preventive_'+i+'_pic']"></div>
            </td>
            <td class="border border-black">
                <input name="planning_date[]" type="date" class="w-full border-none p-1 bg-gray-100 rounded min-h-10 flex items-center"
                    x-model="form['preventive_'+i+'_planning']" readonly disabled>
            </td>
            <td class="border border-black text-center">
                <span x-show="form['preventive_'+i+'_actual'] === '-' || !form['preventive_'+i+'_actual']" class="block p-1">-</span>
                <input name="actual_date[]" type="date" class="w-full border-none p-1 bg-gray-100"
                    x-model="form['preventive_'+i+'_actual']" x-show="form['preventive_'+i+'_actual'] && form['preventive_'+i+'_actual'] !== '-'" readonly disabled>
            </td>
        </tr>
    </template>
</table>

{{-- YOKOTEN --}}
<table class="w-full border border-black text-sm mt-2" x-show="form.yokoten !== null && form.yokoten !== undefined && form.yokoten !== ''">
    <tr>
        <td class="border border-black p-2 w-2/3">
            <label class="block mb-2 font-semibold">Yokoten?<span class="text-danger">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 px-4 py-2 rounded border cursor-not-allowed transition-all"
                    :class="String(form.yokoten) === '1' ? 'bg-green-50 border-green-500 text-green-900 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                    <input type="radio" name="yokoten" value="1" x-model="form.yokoten" required disabled
                        class="w-4 h-4 text-green-600">
                    <span>Yes</span>
                </label>
                <label class="flex items-center gap-2 px-4 py-2 rounded border cursor-not-allowed transition-all"
                    :class="String(form.yokoten) === '0' ? 'bg-red-50 border-red-500 text-red-900 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                    <input type="radio" name="yokoten" value="0" x-model="form.yokoten" required disabled
                        class="w-4 h-4 text-red-600">
                    <span>No</span>
                </label>
            </div>
        </td>
        <td class="border border-black p-1 font-semibold text-center">Checked</td>
        <td class="border border-black p-1 font-semibold text-center">Created</td>
    </tr>
    <tr>
        <td>
            <label x-show="form.yokoten == 1" class="block mb-2">Please specify:</label>
            <div x-show="form.yokoten == 1"
                class="w-full border border-gray-400 rounded p-2 h-24 bg-gray-100 approval-content overflow-auto"
                x-html="form.yokoten_area"></div>
        </td>
        @include('contents.ftpp2.approval.partials.signature')
    </tr>
</table>

<table>
    <tr>
        <td>
            <!-- Preview containers (sesuaikan posisi di form) -->
            <div id="previewImageContainer2" class="mt-2 flex flex-wrap gap-2"></div>
            <div id="previewFileContainer2" class="mt-2 flex flex-col gap-1"></div>

            <!-- Attachment button (paperclip) - disabled in view only -->
            <div class="relative inline-block">
                <button id="attachBtn2" type="button"
                    class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 bg-gray-100 cursor-not-allowed"
                    aria-haspopup="true" aria-expanded="false" title="Attach files2" disabled>
                    <i data-feather="paperclip" class="w-4 h-4"></i>
                    <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                </button>

                <!-- Small menu hidden for view-only -->
                <div id="attachMenu2" class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow z-20" aria-hidden="true"></div>
            </div>

            <!-- Hidden file inputs (disabled) -->
            <input type="file" id="photoInput2" name="photos2[]" accept="image/*" multiple class="hidden" disabled>
            <input type="file" id="fileInput2" name="files2[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden" disabled>
            <!-- Optional combined input -->
            <input type="file" id="combinedInput2" name="attachments2[]"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden" disabled>
        </td>
    </tr>
</table>


