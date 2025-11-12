{{-- TANDA TANGAN --}}
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">
<table class="w-full border border-black text-sm mt-2 text-center">
    <tr>
        <td class="border border-black p-1 font-semibold">Effectiveness Verification</td>
        <td class="border border-black p-1 font-semibold">Status</td>
        <td class="border border-black p-1 font-semibold">Acknowledge</td>
        <td class="border border-black p-1 font-semibold">Approve</td>
    </tr>
    <tr>
        <td class="border border-black">
            <textarea name="effectiveness_verification" x-model="form.effectiveness_verification" class="w-full p-1"></textarea>
        </td>
        <td class="border border-black p-2">
            <div class="text-lg font-bold">
                Status:
                <span
                    :class="{
                        'text-red-500': form.status_id == 7,
                        'text-green-600': form.status_id == 11,
                        'text-yellow-500': form.status_id != 7 && form.status_id != 11
                    }"
                    x-text="form.status_id == 7 ? 'OPEN' : (form.status_id == 11 ? 'CLOSE' : (form.status_id == 8 ? 'SUBMITTED' : 'CHECKED BY DEPT HEAD'))">
                </span>
            </div>
        </td>
        <td class="border border-black">
            <div class="my-4">
                <template x-if="!form.lead_auditor_ack">
                    <button type="button" onclick="verifyLeadAuditor()">Verify</button>
                </template>
                <template x-if="form.lead_auditor_signature">
                    <img :src="form.lead_auditor_signature_url" class="mx-auto mt-1 h-16 object-contain">
                </template>
            </div>
            <table class="w-full h-1/4">
                <tr>
                    <td class="border border-black">Lead Auditor</td>
                </tr>
                <tr>
                    <td>
                        <input type="text" x-model="form.lead_auditor_name">
                    </td>
                </tr>
            </table>
        </td>
        <td class="border border-black">
            <div class="my-4">
                <template x-if="!form.auditor_verified">
                    <button type="button" onclick="verifyAuditor()">Verify</button>
                </template>
                <template x-if="form.auditor_signature">
                    <img :src="form.auditor_signature_url" class="mx-auto mt-1 h-16 object-contain">
                </template>
            </div>
            <table class="w-full h-1/4">
                <tr>
                    <td class="border border-black">Auditor</td>
                </tr>
                <tr>
                    <td>
                        <input type="text" x-model="form.auditor_name">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<script>
    async function verifyAuditor() {
        const auditeeActionId = document.getElementById('auditee_action_id')?.value;
        const alpineEl = document.querySelector('[x-data="ftppApp()"]');
        const alpineComponent = Alpine.$data(alpineEl);

        // 🔍 Pastikan effectiveness_verification diisi dulu
        const verification = alpineComponent.form.effectiveness_verification?.trim();
        if (!verification) {
            alert('Please fill in Effectiveness Verification before verifying.');
            return;
        }

        if (!auditeeActionId) {
            alert('No auditee action ID found');
            return;
        }

        try {
            const res = await fetch(`/ftpp/auditor-verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: auditeeActionId,
                    effectiveness_verification: verification // ✅ kirim ke backend
                })
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error('Failed to verify Auditor');
            }

            const data = await res.json();

            if (data.success) {
                alpineComponent.form.status_id = 10; // Approved by Auditor
                alpineComponent.form.auditor_signature = true;
                alpineComponent.form.auditor_signature_url = `/images/stamp-internal-auditor.png`;

                alert('Status updated to Approved by Auditor');
            } else {
                alert('Failed to verify Auditor');
            }

        } catch (error) {
            console.error(error);
            alert('Error verifying Auditor. Check console for details.');
        }
    }

    async function verifyLeadAuditor() {
        const auditeeActionId = document.getElementById('auditee_action_id')?.value;
        if (!auditeeActionId) return alert('No auditee action ID found');

        try {
            const res = await fetch(`/ftpp/lead-auditor-acknowledge`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: auditeeActionId
                })
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error('Failed to verify Auditor');
            }

            const data = await res.json();

            if (data.success) {
                // ✅ Get the Alpine component
                const alpineEl = document.querySelector('[x-data="ftppApp()"]');
                const alpineComponent = Alpine.$data(alpineEl);
                alpineComponent.form.status_id = 10; // Approved by Auditor
                alpineComponent.form.auditor_signature = true;
                alpineComponent.form.auditor_signature_url = `/images/stamp-lead-auditor.png`;

                alert('Status updated to closed');
            } else {
                alert('Failed to verify Auditor');
            }

        } catch (error) {
            console.error(error);
            alert('Error verifying Auditor. Check console for details.');
        }
    }
</script>
