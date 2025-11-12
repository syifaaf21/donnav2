<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FTPP Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .font-semibold {
            font-weight: bold;
        }

        .text-sm {
            font-size: 11px;
        }

        .bg-gray {
            background-color: #f0f0f0;
        }

        .signature {
            height: 60px;
            object-fit: contain;
            display: block;
            margin: auto;
        }

        .no-border td {
            border: none !important;
        }

        .mt-2 {
            margin-top: 6px;
        }

        .w-50 {
            width: 50%;
        }
    </style>
</head>

<body>

    {{-- ==================== AUDITOR INPUT ==================== --}}
    <table class="text-sm">
        <tr>
            <td class="w-1/3">
                <div><b>Audit Type:</b></div>
                <div>{{ ucfirst(optional($finding->auditType)->name ?? '-') }}</div>
                <div><b>Sub Audit Type:</b></div>
                <div>{{ ucfirst(optional($finding->subAuditType)->name ?? '-') }}</div>
            </td>
            <td>
                <table class="text-sm w-full">
                    <tr>
                        <td class="font-semibold">Department / Process / Product:</td>
                        <td>{{ $finding->department->name ?? '-' }} / {{ $finding->process->name ?? '-' }} /
                            {{ $finding->product->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Auditee:</td>
                        <td>
                            @foreach ($finding->auditees ?? [] as $a)
                                {{ $a->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Auditor / Inisiator:</td>
                        <td>{{ $finding->auditor->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Date:</td>
                        <td>{{ \Carbon\Carbon::parse($finding->created_at)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Registration Number:</td>
                        <td>{{ $finding->registration_number ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>Finding Category:</b> {{ ucfirst(optional($finding->findingCategory)->name ?? '-') }}
            </td>
        </tr>
    </table>

    {{-- ==================== FINDING ==================== --}}
    <table class="text-sm mt-2">
        <tr class="bg-gray">
            <td class="font-semibold">AUDITOR / INISIATOR</td>
        </tr>
        <tr>
            <td>
                <b>Finding / Issue:</b><br>
                {{ $finding->finding_description ?? '-' }}

                {{-- Tampilkan lampiran gambar yang terkait dengan finding ini --}}
                {{-- @foreach ($finding->attachments->where('audit_finding_id', $finding->id)->where('type', 'image') as $image)
                    <div class="mt-2 text-center">
                        <img src="{{ $image->path }}"
                            style="max-width:300px; max-height:200px; display:block; margin:auto;">
                        <div style="font-size:10px; text-align:center;">Lampiran Gambar: {{ basename($image->path) }}
                        </div>
                    </div>
                @endforeach --}}

                <br><br>
                <b>Due Date:</b> {{ \Carbon\Carbon::parse($finding->due_date)->format('d M Y') }}
                <br><br>
                <b>Clause:</b>
                @foreach ($finding->subKlausuls ?? [] as $subKlausul)
                   {{ $subKlausul->code }} {{ $subKlausul->name }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ==================== AUDITEE INPUT ==================== --}}
    <table class="w-full border text-sm mt-2">
        <tr class="bg-gray font-semibold">
            <td>AUDITEE</td>
        </tr>
        <tr>
            <td>
                <div><b>5 Why Analysis:</b></div>
                @for ($i = 1; $i <= 5; $i++)
                    <div class="ml-2 mb-1">
                        Why {{ $i }} (Mengapa): {{ $finding['why_' . $i . '_mengapa'] ?? '-' }}<br>
                        Cause (Karena): {{ $finding['cause_' . $i . '_karena'] ?? '-' }}
                    </div>
                @endfor
                <div class="mt-1"><b>Root Cause:</b> {{ $finding->root_cause ?? '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- ==================== CORRECTIVE & PREVENTIVE ==================== --}}
    <table class="w-full border text-sm mt-2">
        <tr class="bg-gray font-semibold text-center">
            <td>No</td>
            <td>Activity</td>
            <td>PIC</td>
            <td>Planning</td>
            <td>Actual</td>
        </tr>

        {{-- Corrective --}}
        <tr>
            <td colspan="5" class="font-semibold">Corrective Action</td>
        </tr>
        @for ($i = 1; $i <= 4; $i++)
            <tr>
                <td class="text-center">{{ $i }}</td>
                <td>{{ $finding['corrective_' . $i . '_activity'] ?? '' }}</td>
                <td>{{ $finding['corrective_' . $i . '_pic'] ?? '' }}</td>
                <td>{{ $finding['corrective_' . $i . '_planning'] ?? '' }}</td>
                <td>{{ $finding['corrective_' . $i . '_actual'] ?? '' }}</td>
            </tr>
        @endfor

        {{-- Preventive --}}
        <tr>
            <td colspan="5" class="font-semibold">Preventive Action</td>
        </tr>
        @for ($i = 1; $i <= 4; $i++)
            <tr>
                <td class="text-center">{{ $i }}</td>
                <td>{{ $finding['preventive_' . $i . '_activity'] ?? '' }}</td>
                <td>{{ $finding['preventive_' . $i . '_pic'] ?? '' }}</td>
                <td>{{ $finding['preventive_' . $i . '_planning'] ?? '' }}</td>
                <td>{{ $finding['preventive_' . $i . '_actual'] ?? '' }}</td>
            </tr>
        @endfor
    </table>

    {{-- ==================== YOKOTEN ==================== --}}
    <table class="w-full border text-sm mt-2">
        <tr>
            <td class="w-2/3">
                <b>Yokoten?</b> {{ $finding->yokoten ? 'Yes' : 'No' }} <br>
                @if ($finding->yokoten)
                    <b>Area:</b> {{ $finding->yokoten_area ?? '-' }}
                @endif
            </td>
            <td class="text-center font-semibold">Dept. Head</td>
            <td class="text-center font-semibold">Leader/Spv</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-center">
                @if ($finding->dept_head_signature)
                    <img src="{{ $finding->dept_head_signature_url }}" class="signature">
                @endif
            </td>
            <td class="text-center">
                @if ($finding->ldr_spv_signature)
                    <img src="{{ $finding->ldr_spv_signature_url }}" class="signature">
                @endif
            </td>
        </tr>
    </table>

    {{-- ==================== AUDITOR VERIFICATION ==================== --}}
    <table class="w-full border text-sm mt-2 text-center">
        <tr class="font-semibold bg-gray">
            <td>Effectiveness Verification</td>
            <td>Status</td>
            <td>Acknowledge</td>
            <td>Approve</td>
        </tr>
        <tr>
            <td>{{ $finding->effectiveness_verification ?? '-' }}</td>
            <td class="font-bold">
                @switch($finding->status_id)
                    @case(7)
                        <span style="color:red;">OPEN</span>
                    @break

                    @case(8)
                        <span style="color:orange;">SUBMITTED</span>
                    @break

                    @case(10)
                        <span style="color:blue;">CHECKED</span>
                    @break

                    @case(11)
                        <span style="color:green;">CLOSE</span>
                    @break

                    @default
                        <span>-</span>
                @endswitch
            </td>
            <td>
                @if ($finding->lead_auditor_signature)
                    <img src="{{ $finding->lead_auditor_signature_url }}" class="signature"><br>
                @endif
                {{ $finding->lead_auditor_name ?? '-' }}
            </td>
            <td>
                @if ($finding->auditor_signature)
                    <img src="{{ $finding->auditor_signature_url }}" class="signature"><br>
                @endif
                {{ $finding->auditor_name ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- ==================== ATTACHMENTS ==================== --}}
    {{-- LAMPIRAN FILE --}}
    {{-- @if ($finding->auditeeAction->attachments->where('type', 'file')->count())
        <div class="page-break"></div>
        <h4>Lampiran File</h4>
        <ul>
            @foreach ($finding->auditeeAction->attachments->where('type', 'file') as $file)
                <li>
                    {{ basename($file->path) }}
                    @if ($file->audit_finding_id)
                        - Lampiran untuk Finding ID {{ $file->audit_finding_id }}
                    @endif
                </li>
            @endforeach
        </ul>

    @endif --}}
</body>

</html>
