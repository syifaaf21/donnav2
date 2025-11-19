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
        <button type="button" class="px-3 py-1 bg-blue-600 text-white text-xs rounded"
            @click="approveDeptHead(form.auditee_action_id)">
            Approve
        </button>
    </template>

    <div>
        <span class="font-semibold my-1">Dept. Head</span>
        <input type="text" class="text-center" value="{{ auth()->user()->name }}">
    </div>
</td>


{{-- Leader / SPV --}}
<td class="border border-black p-2 text-center">

    <template x-if="form.ldr_spv_signature">
        <img :src="form.ldr_spv_signature_url" class="mx-auto h-24">
    </template>

    <div>
        <span class="font-semibold my-1">Leader/Spv</span>
        <input type="text" class="text-center" value="{{ auth()->user()->name }}">
    </div>
</td>



<script>
    function approveDeptHead() {
        const id = document.getElementById('auditee_action_id')?.value;

        if (!id) {
            alert("❌ Error: auditee_action_id tidak ditemukan!");
            return;
        }

        fetch('/approval/dept-head-sign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: id
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log("Approved:", data);
            })
            .then(data => {
                console.log(data);
                if (data.success) {
                    location.reload(); // <= Refresh halaman agar Blade fetch data terbaru
                }
            })
            .catch(err => console.error(err));

    }
</script>
