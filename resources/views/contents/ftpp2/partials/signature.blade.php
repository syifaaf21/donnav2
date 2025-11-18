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
        <td class="border p-1">
            @if (!empty($finding->acknowledge_by_lead_auditor_url))
                <img src="{{ $finding->acknowledge_by_lead_auditor_url }}" class="signature"><br>
            @endif
            {{ $finding->auditeeAction->lead_auditor_id ?? '-' }}
        </td>
        <td class="border p-1">
            @if (!empty($finding->verified_by_auditor_url))
                <img src="{{ $finding->verified_by_auditor_url }}" class="signature"><br>
            @endif
            {{ $finding->auditeeAction->auditor_id ?? '-' }}
        </td>
    </tr>
</table>
