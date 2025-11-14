@php
    $finding = $findings->first();
    $action = $finding?->auditeeAction;
    $actionId = $action?->id ?? 'null';
@endphp

{{-- Dept Head --}}
<td class="border border-black p-2 text-center">
    @if ($action && $action->dept_head_signature)
        <img src="/images/mgr-approve.png" class="mx-auto h-24">
    @else
        {{-- Tombol muncul bahkan jika $action null --}}
        <button type="button" class="px-3 py-1 bg-blue-600 text-white text-xs rounded"
        @click="approveDeptHead({{ $actionId }})">
            Approve
        </button>
    @endif
    <div>
        <span class="font-semibold my-1">Dept. Head</span>
        <input type="text" name="" id="" class="text-center" value="{{ auth()->user()->name }}">
    </div>
</td>

{{-- Leader / SPV --}}
<td class="border border-black p-2 text-center">
    @if ($action && $action->ldr_spv_signature)
        <img src="/images/usr-approve.png" class="mx-auto h-24">
    @else
        <button type="button" class="px-3 py-1 bg-blue-600 text-white text-xs rounded"
            @click="approveLdrSpv({{ $actionId }})">
            Approve
        </button>
    @endif
    <div>
        <span class="font-semibold my-1">Leader/Spv</span>
        <input type="text" name="" id="" class="text-center" value="{{ auth()->user()->name }}">
    </div>
</td>

<script>
    function approveDeptHead(findingId) {
        if (!confirm("Are you sure?")) return;

        fetch("/ftpp/dept-head-sign", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    auditee_action_id: findingId
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || "Something went wrong");
                }
            });
    }

    function approveLdrSpv(findingId) {
        if (!confirm("Are you sure?")) return;

        fetch("/ftpp/ldr-spv-sign", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    auditee_action_id: findingId
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || "Something went wrong");
                }
            });
    }
</script>
