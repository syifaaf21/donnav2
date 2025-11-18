{{-- ==================== CORRECTIVE & PREVENTIVE ==================== --}}
<table class="w-full border p-1 text-sm mt-2">
    <tr class="bg-gray border p-1 font-semibold text-center">
        <td class="border p-1">No</td>
        <td class="border p-1" style="width: 50%">Activity</td>
        <td class="border p-1">PIC</td>
        <td class="border p-1">Planning</td>
        <td class="border p-1">Actual</td>
    </tr>

    {{-- Corrective --}}
    <tr>
        <td colspan="5" class="font-semibold">Corrective Action</td>
    </tr>
    @foreach ($finding->auditeeAction->correctiveActions ?? [] as $index => $action)
        <tr class="border p-1 w-ful">
            <td class="border p-1 text-center">{{ $index + 1 }}</td>
            <td class="border p-1">{{ $action->activity ?? '-' }}</td>
            <td class="border p-1 text-center">{{ $action->pic ?? '-' }}</td>
            <td class="border p-1 text-center">
                {{ $action->planning_date ? \Carbon\Carbon::parse($action->planning_date)->format('d/m/Y') : '-' }}
            </td>
            <td class="border p-1 text-center">
                {{ $action->actual_date ? \Carbon\Carbon::parse($action->actual_date)->format('d/m/Y') : '-' }}
            </td>
        </tr>
    @endforeach

    {{-- Preventive --}}
    <tr>
        <td colspan="5" class="font-semibold">Preventive Action</td>
    </tr>
    @foreach ($finding->auditeeAction->preventiveActions ?? [] as $index => $action)
        <tr>
            <td class="border p-1 text-center">{{ $index + 1 }}</td>
            <td class="border p-1">{{ $action->activity ?? '-' }}</td>
            <td class="border p-1 text-center">{{ $action->pic ?? '-' }}</td>
            <td class="border p-1 text-center">
                {{ $action->planning_date ? \Carbon\Carbon::parse($action->planning_date)->format('d/m/Y') : '-' }}
            </td>
            <td class="border p-1 text-center">
                {{ $action->actual_date ? \Carbon\Carbon::parse($action->actual_date)->format('d/m/Y') : '-' }}
            </td>
        </tr>
    @endforeach
</table>
