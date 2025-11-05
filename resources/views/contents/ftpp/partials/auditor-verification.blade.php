{{-- TANDA TANGAN --}}
<table class="w-full border border-black text-sm mt-2 text-center">
    <tr>
        <td class="border border-black p-1 font-semibold">Lead Auditor</td>
        <td class="border border-black p-1 font-semibold">Auditor</td>
    </tr>
    <tr>
        <td class="border border-black p-2">
            <input type="file" @change="uploadSignature($event, 'lead_auditor_signature')" accept="image/*"
                class="text-xs">
            <template x-if="form.lead_auditor_signature">
                <img :src="form.lead_auditor_signature" class="mx-auto mt-1 h-16 object-contain">
            </template>
        </td>
        <td class="border border-black p-2">
            <input type="file" @change="uploadSignature($event, 'auditor_signature')" accept="image/*"
                class="text-xs">
            <template x-if="form.auditor_signature">
                <img :src="form.auditor_signature" class="mx-auto mt-1 h-16 object-contain">
            </template>
        </td>
    </tr>
</table>
