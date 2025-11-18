{{-- ==================== AUDITEE INPUT ==================== --}}
<table class="w-full border p-1 text-sm mt-2">
    <tr class="bg-gray border p-1 font-semibold">
        <td colspan="2">AUDITEE</td>
    </tr>
    <tr>
        <td colspan="2">
            <b>5 Why Analysis:</b>
        </td>
    </tr>
    @foreach ($finding->auditeeAction->whyCauses ?? [] as $index => $why)
        <tr>
            <td class="border p-1" style="width: 25%;">Why {{ $index + 1 }} (Mengapa)</td>
            <td class="border p-1">{{ $why->why_description ?? '-' }}</td>
        </tr>
        <tr>
            <td class="border p-1">Cause (Karena)</td>
            <td class="border p-1">{{ $why->cause_description ?? '-' }}</td>
        </tr>
    @endforeach
    <tr>
        <td class="font-semibold border p-1">Root Cause</td>
        <td class="border p-1">{{ $finding->auditeeAction->root_cause ?? '-' }}</td>
    </tr>
</table>
