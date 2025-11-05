{{-- 5 WHY --}}

<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold">
        <td class="border border-black p-1">AUDITEE</td>
    </tr>
    <tr>
        <td class="border border-black p-1">
            <div class="font-semibold mb-1">Issue Causes (5 Why)</div>
            <template x-for="i in 5">
                <div class="ml-2 mb-1">
                    <label>Why-<span x-text="i"></span> (Mengapa):</label>
                    <input type="text" class="w-full border-b border-gray-400 p-1" x-model="form['why_'+i+'_mengapa']">
                    <label>Cause (Karena):</label>
                    <input type="text" class="w-full border-b border-gray-400 p-1" x-model="form['why_'+i+'_karena']">
                </div>
            </template>
            <div class="mt-1">
                Root Cause: <span class="text-danger">*</span>
                <textarea x-model="form.root_cause" class="w-full border border-gray-400 rounded p-1"></textarea>
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
        <tr>
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black"><input type="text" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_activity']"></td>
            <td class="border border-black"><input type="text" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_pic']"></td>
            <td class="border border-black"><input type="date" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_planning']"></td>
            <td class="border border-black"><input type="date" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_actual']"></td>
        </tr>
    </template>

    {{-- Perbaikan --}}
    <tr>
        <td colspan="5" class="border border-black p-1 font-semibold">Preventive Action</td>
    </tr>
    <template x-for="i in 4">
        <tr>
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black"><input type="text" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_activity']"></td>
            <td class="border border-black"><input type="text" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_pic']"></td>
            <td class="border border-black"><input type="date" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_planning']"></td>
            <td class="border border-black"><input type="date" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_actual']"></td>
        </tr>
    </template>
</table>

{{-- YOKOTEN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr>
        <td class="border border-black p-2 w-2/3">
            <label>Yokoten?<span class="text-danger">*</span></label>
            <label><input type="radio" value="1" x-model="form.yokoten"> Yes</label>
            <label><input type="radio" value="0" x-model="form.yokoten"> No</label>

        </td>
        <td class="border border-black p-1 font-semibold text-center">Dept. Head</td>
        <td class="border border-black p-1 font-semibold text-center">Leader/Spv</td>
    </tr>
    <tr>
        <td>
            <label>Please specify:</label>
            <textarea x-show="form.yokoten == 1" x-model="form.finding_description"
                class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
        </td>
        {{-- LDR, SPV, DEPT HEAD --}}
        <td class="border border-black p-2">
            <input type="file" @change="uploadSignature($event, 'dept_head_signature')" accept="image/*"
                class="text-xs">
            <template x-if="form.dept_head_signature">
                <img :src="form.dept_head_signature" class="mx-auto mt-1 h-16 object-contain">
            </template>
        </td>
        <td class="border border-black p-2">
            <input type="file" @change="uploadSignature($event, 'ldr_spv_signature')" accept="image/*"
                class="text-xs">
            <template x-if="form.ldr_spv_signature">
                <img :src="form.ldr_spv_signature" class="mx-auto mt-1 h-16 object-contain">
            </template>
        </td>
    </tr>
</table>
