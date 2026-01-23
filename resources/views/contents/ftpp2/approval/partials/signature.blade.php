@php
    $finding = $findings->first();
    $action = $finding?->auditeeAction;
    $actionId = $action?->id ?? 'null';
@endphp

{{-- Dept Head --}}
<td class="border border-black p-2 text-center">

    <!-- Jika sudah approve -->
    <template x-if="form.dept_head_signature">
        <img :src="form.dept_head_signature_url" class="mx-auto h-24">
    </template>

    <!-- Jika belum approve -->
    <template x-if="!form.dept_head_signature">
        <button type="button" :disabled="form.status_id != 8 || (userRoles.includes('dept head') === false && userRoles.includes('admin') === false)"
            :class="form.status_id != 8 || (userRoles.includes('dept head') === false && userRoles.includes('admin') === false) ? 'opacity-40 cursor-not-allowed' : ''"
            class="px-3 py-1 bg-blue-600 text-white text-xs rounded" @click="approveDeptHead(form.auditee_action_id)">
            Approve
        </button>
    </template>

    <div>
        <span class="font-semibold my-1">Dept. Head</span>
        <div class="text-center text-gray-700" x-text="form.auditeeAction?.deptHead?.name || '{{ $finding->auditeeAction->deptHead->name ?? '-' }}'">{{ $finding->auditeeAction->deptHead->name ?? '-' }}</div>
    </div>
</td>


{{-- Leader / SPV --}}
<td class="border border-black p-2 text-center">

    <template x-if="form.ldr_spv_signature">
        <img :src="form.ldr_spv_signature_url" class="mx-auto h-24">
    </template>

    <div>
        <span class="font-semibold my-1">Leader/Spv</span>
        <div class="text-center text-gray-700" x-text="form.auditeeAction?.user?.name || '{{ $finding->auditeeAction->user->name ?? '-' }}'">{{ $finding->auditeeAction->user->name ?? '-' }}</div>
    </div>
</td>

<script>
    async function approveDeptHead(id) {
        const aid = id || document.getElementById('auditee_action_id')?.value;

        if (!aid) {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'auditee_action_id tidak ditemukan!'
            });
            return;
        }

        const result = await Swal.fire({
            title: 'Confirm Approval',
            text: 'Are you sure you want to approve this finding?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch('/approval/dept-head-sign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: aid
                })
            });

            const data = await res.json();

            if (data && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Approved',
                    text: 'The finding was approved.',
                    timer: 1200,
                    showConfirmButton: false
                });
                // reload automatically
                location.reload();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Approval failed',
                    text: data?.message || 'Unknown error'
                });
            }
        } catch (err) {
            console.error(err);
            await Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Network or server error when approving'
            });
        }
    }
</script>
