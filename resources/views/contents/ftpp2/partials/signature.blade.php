{{-- ==================== AUDITOR VERIFICATION ==================== --}}
<table class="w-full border p-1 text-sm mt-2 text-center">
    <tr class="font-semibold bg-gray">
        <td class="border p-1">Effectiveness Verification</td>
        <td class="border p-1">Status</td>
        <td class="border p-1">Acknowledge</td>
        <td class="border p-1">Approve</td>
    </tr>
    <tr>
        <td class="border p-1">{{ $finding->auditeeAction->effectiveness_verification ?? '-' }}</td>
        <td class="border p-1 font-bold">
            @switch($finding->status_id)
                @case(7)
                    <span style="color:red;">OPEN</span>
                @break

                @case(8)
                    <span style="color:orange;">SUBMITTED</span>
                @break

                @case(10)
                    <span style="color:blue;">CHECKED BY DEPT HEAD</span>
                @break

                @case(11)
                    <span style="color:green;">CLOSE</span>
                @break

                @default
                    <span>-</span>
            @endswitch
        </td>
        <td class="border p-1 text-center">
            @if ($finding->auditeeAction->acknowledge_by_lead_auditor == 1)
                <img src="{{ asset('images/stamp-lead-auditor.png') }}" class="signature mx-auto"
                    style="max-width:120px; height:auto;">
                <br>
            @endif
            {{ $finding->auditeeAction->lead_auditor_id ?? '-' }}
        </td>

        <td class="border p-1 text-center">
            @if ($finding->auditeeAction->verified_by_auditor == 1)
                <img src="{{ asset('images/stamp-internal-auditor.png') }}" class="signature mx-auto"
                    style="max-width:120px; height:auto;">
                <br>
            @endif
            {{ $finding->auditeeAction->auditor_id ?? '-' }}
        </td>
    </tr>
</table>
