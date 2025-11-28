<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FTPP Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
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

        .header-table {
            border: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-logo {
            width: 20%;
            /* lebih kecil dari sebelumnya */
            text-align: center;
            vertical-align: middle;
            border-right: 1px solid #000;
            padding: 4px;
        }

        .header-logo img {
            width: 64px;
            /* atur ukuran logo di sini */
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 0.5px;
            vertical-align: middle;
            padding: 6px;
        }

        .header-title h3 {
            margin: 0;
            font-size: 13px;
        }

        .image-row {
            display: flex;
            flex-direction: row;
            gap: 10px;
            /* jarak antar gambar */
            justify-content: center;
            /* atau start sesuai kebutuhan */
            flex-wrap: wrap;
            /* jika gambar banyak agar tidak keluar layar */
        }

        .note {
            font-size: 10px;
            /* lebih kecil dari text-sm */
            color: #888888;
            /* abu-abu lebih terang */
        }
    </style>
</head>

<body>
    <!-- ======== HEADER ======== -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/logo-aiia.png') }}" alt="AISIN Logo">
            </td>
            <td class="header-title">
                <h3>
                    FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                </h3>
            </td>
        </tr>
    </table>
    {{-- ==================== AUDITOR INPUT ==================== --}}
    <table class="text-sm">
        <tr>
            <td class="w-1/3">
                <div><b>Audit Type:</b></div>
                <div>{{ ucfirst(optional($finding->audit)->name ?? '-') }}</div>
                <div><b>Sub Audit Type:</b></div>
                <div>{{ ucfirst(optional($finding->subAudit)->name ?? '-') }}</div>
            </td>
            <td>
                <table class="text-sm w-full">
                    <tr>
                        <td class="font-semibold">Department / Process / Product:</td>
                        <td>
                            {{ \Illuminate\Support\Str::title($finding->department->name ?? '-') .
                                ' / ' .
                                \Illuminate\Support\Str::title($finding->process->name ?? '-') .
                                ' / ' .
                                \Illuminate\Support\Str::title($finding->product->name ?? '-') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Auditee:</td>
                        <td>
                            @foreach ($finding->auditee ?? [] as $auditee)
                                {{ $auditee->name }}{{ !$loop->last ? ', ' : '' }}
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
                @foreach ($finding->file ?? [] as $image)
                    @php
                        $ext = strtolower(
                            pathinfo($image->file_path ?? ($image->original_name ?? ''), PATHINFO_EXTENSION),
                        );
                    @endphp

                    @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                        <div class="image-row mt-2 text-center">
                            <img src="{{ $image->full_url }}"
                                style="max-width: 64px; max-height: 64px; display:block; margin:auto;">
                            <div style="font-size:8px; text-align:center;">
                                Lampiran Gambar: {{ $image->original_name ?? basename($image->file_path ?? '') }}
                            </div>
                        </div>
                    @endif
                @endforeach

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
            <td colspan="2">AUDITEE</td>
        </tr>
        <tr>
            <td colspan="2">
                <b>5 Why Analysis:</b>
            </td>
        </tr>
        @foreach ($finding->auditeeAction->whyCauses ?? [] as $index => $why)
            <tr>
                <td style="width: 25%;">Why {{ $index + 1 }} (Mengapa)</td>
                <td>{{ $why->why_description ?? '-' }}</td>
            </tr>
            <tr>
                <td>Cause (Karena)</td>
                <td>{{ $why->cause_description ?? '-' }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="font-semibold">Root Cause</td>
            <td>{{ $finding->auditeeAction->root_cause ?? '-' }}</td>
        </tr>
    </table>

    {{-- ==================== CORRECTIVE & PREVENTIVE ==================== --}}
    <table class="w-full border text-sm mt-2">
        <tr class="bg-gray font-semibold text-center">
            <td>No</td>
            <td style="width: 50%">Activity</td>
            <td>PIC</td>
            <td>Planning</td>
            <td>Actual</td>
        </tr>

        {{-- Corrective --}}
        <tr>
            <td colspan="5" class="font-semibold">Corrective Action</td>
        </tr>
        @foreach ($finding->auditeeAction->correctiveActions ?? [] as $index => $action)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $action->activity ?? '-' }}</td>
                <td class="text-center">{{ $action->pic ?? '-' }}</td>
                <td class="text-center">
                    {{ $action->planning_date ? \Carbon\Carbon::parse($action->planning_date)->format('d/m/Y') : '-' }}
                </td>
                <td class="text-center">
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
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $action->activity ?? '-' }}</td>
                <td class="text-center">{{ $action->pic ?? '-' }}</td>
                <td class="text-center">
                    {{ $action->planning_date ? \Carbon\Carbon::parse($action->planning_date)->format('d/m/Y') : '-' }}
                </td>
                <td class="text-center">
                    {{ $action->actual_date ? \Carbon\Carbon::parse($action->actual_date)->format('d/m/Y') : '-' }}
                </td>
            </tr>
        @endforeach
    </table>

    {{-- ==================== YOKOTEN ==================== --}}
    <table class="w-full border text-sm mt-2">
        <tr>
            <td class="w-2/3">
                <b>Yokoten?</b> {{ $finding->auditeeAction->yokoten ? 'Yes' : 'No' }} <br>
            </td>
            <td class="text-center font-semibold">Checked</td>
            <td class="text-center font-semibold">Created</td>
        </tr>
        <tr>
            <td>
                @if ($finding->auditeeAction->yokoten)
                    <b>Area:</b> {{ $finding->auditeeAction->yokoten_area ?? '-' }}
                @endif
            </td>
            <td class="text-center">
                @if (!empty($finding->dept_head_signature_url))
                    <img src="{{ $finding->dept_head_signature_url }}" class="signature" alt="Dept Head Approved">
                @elseif (isset($finding->auditeeAction->dept_head_signature) && $finding->auditeeAction->dept_head_signature == 1)
                    <img src="{{ public_path('images/mgr-approve.png') }}" class="signature" alt="Dept Head Approved">
                @endif
                <div>
                    <div>Dept. Head</div>
                    {{ $finding->auditeeAction->deptHead->name ?? '-' }}
                </div>

            </td>
            <td class="text-center">
                @if (!empty($finding->ldr_spv_signature_url))
                    <img src="{{ $finding->ldr_spv_signature_url }}" class="signature" alt="Leader/Spv Approved">
                @elseif (isset($finding->auditeeAction->ldr_spv_signature) && $finding->auditeeAction->ldr_spv_signature == 1)
                    <img src="{{ public_path('images/usr-approve.png') }}" class="signature" alt="Leader/Spv Approved">
                @endif
                <div>
                    <div>Leader/Spv</div>
                    {{ $finding->auditeeAction->user->name ?? '-' }}
                </div>
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
            <td>{{ $finding->auditeeAction->effectiveness_verification ?? '-' }}</td>
            <td class="font-bold">
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
            <td>
                @if (!empty($finding->acknowledge_by_lead_auditor_url))
                    <img src="{{ $finding->acknowledge_by_lead_auditor_url }}" class="signature"><br>
                @elseif ($finding->auditeeAction->lead_auditor_id == 1)
                    <img src="/images/stamp-lead-auditor.png" class="signature"><br>
                @endif
                <div>
                    <div>Lead Auditor</div>
                    {{ $finding->auditeeAction->leadAuditor->name ?? '-' }}
                </div>
            </td>
            <td>
                @if (!empty($finding->verified_by_auditor_url))
                    <img src="{{ $finding->verified_by_auditor_url }}" class="signature"><br>
                @elseif ($finding->auditeeAction->auditor_id == 1)
                    <img src="/images/stamp-internal-auditor.png" class="signature"><br>
                @endif
                <div>
                    <div>Auditor</div>
                    {{ $finding->auditeeAction->auditor->name ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <p class="note">Note : 1 Lembar form untuk satu temuan, tambahkan lampiran jika diperlukan</p>
    <p class="note">No Form : FRM-MR-M4-001-05</p>

    {{-- ==================== ATTACHMENTS ================== --}}
    {{-- LAMPIRAN FILE --}}
    @if ($finding->auditeeAction && $finding->auditeeAction->file->count())
        <div class="page-break">
            <div class="font-semibold text-md">Attachment and Evidence: </div>

            @foreach ($finding->file as $file)
                @php
                    $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                @endphp

                @if (in_array($ext, ['pdf']))
                    <p class="text-sm">- {{ $file->original_name ?? basename($file->file_path) }}</p>
                @endif
            @endforeach

            @foreach ($finding->auditeeAction->file as $file)
                @php
                    $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                @endphp

                @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                    <div class="mt-2 text-center">
                        <img src="{{ $file->full_url }}"
                            style="max-width:400px; max-height:250px; display:block; margin:auto;">
                        <div class="text-sm" style="font-size:10px; text-align:center;">
                            {{ $file->original_name ?? basename($file->file_path) }}</div>
                    </div>
                @else
                    <p class="text-sm">- {{ $file->original_name ?? basename($file->file_path) }}</p>
                @endif
            @endforeach
        </div>
    @endif
</body>

</html>
