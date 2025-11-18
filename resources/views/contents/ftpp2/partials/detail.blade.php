<div class="space-y-1">

    {{-- HEADER --}}
    <table class="header-table border w-full">
        <tr>
            <td class="w-1/4">
                <img src="{{ asset('images/logo-aiia.png') }}" class="w-24" alt="AIIA Logo">
            </td>
            <td class="text-center font-bold text-lg">
                FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
            </td>
        </tr>
    </table>

    {{-- ==================== AUDITOR INPUT ==================== --}}
    <table class="border p-1 w-full text-sm">
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
                        <td>{{ $finding->department->name ?? '-' }} / {{ $finding->process->name ?? '-' }} /
                            {{ $finding->product->name ?? '-' }}</td>
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
    <table class="border p-1 w-full text-sm mt-2">
        <tr class="bg-gray">
            <td class="font-semibold">AUDITOR / INISIATOR</td>
        </tr>
        <tr>
            <td>
                <b>Finding / Issue:</b><br>
                {{ $finding->finding_description ?? '-' }}

                {{-- Tampilkan lampiran gambar yang terkait dengan finding ini --}}
                @foreach ($finding->file as $image)
                    <div class="mt-2 text-center">
                        <img src="{{ $image->full_url }}"
                            style="max-width:300px; max-height:200px; display:block; margin:auto;">
                        <div style="font-size:10px; text-align:center;">
                            Lampiran Gambar: {{ basename($image->file_path) }}
                        </div>
                    </div>
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

    {{-- WHY CAUSE, CORRECTIVE, PREVENTIVE --}}
    @include('contents.ftpp2.partials.why')
    @include('contents.ftpp2.partials.corrective-preventive')
    {{-- ==================== YOKOTEN ==================== --}}
    <table class="w-full border p-1 text-sm mt-2">
        <tr>
            <td class="w-2/3">
                <b>Yokoten?</b> {{ $finding->auditeeAction->yokoten ? 'Yes' : 'No' }} <br>
            </td>
            <td class="border p-1 text-center font-semibold">Dept. Head</td>
            <td class="border p-1 text-center font-semibold">Leader/Spv</td>
        </tr>
        <tr>
            <td class="border p-1">
                @if ($finding->auditeeAction->yokoten)
                    <b>Area:</b> {{ $finding->auditeeAction->yokoten_area ?? '-' }}
                @endif
            </td>
            <td class="border p-1 text-center">
                @if (isset($finding->auditeeAction->dept_head_signature) && $finding->auditeeAction->dept_head_signature == 1)
                    <img src="/images/mgr-approve.png" class="signature" alt="Dept Head Approved">
                @endif
            </td>
            <td class="border p-1 text-center">
                @if (isset($finding->auditeeAction->ldr_spv_signature) && $finding->auditeeAction->ldr_spv_signature == 1)
                    <img src="/images/usr-approve.png" class="signature" alt="Leader/Spv Approved">
                @endif
            </td>

        </tr>
    </table>

    {{-- SIGNATURE --}}
    @include('contents.ftpp2.partials.signature')

    {{-- ==================== ATTACHMENTS ==================== --}}
    {{-- LAMPIRAN FILE --}}
    @if ($finding->auditeeAction && $finding->auditeeAction->file->count())
        <div class="page-break"></div>
        <h4>Lampiran</h4>

        @foreach ($finding->auditeeAction->file as $file)
            @php
                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
            @endphp

            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                <div class="mt-2 text-center">
                    <img src="{{ $file->full_url }}"
                        style="max-width:400px; max-height:250px; display:block; margin:auto;">
                    <div style="font-size:10px; text-align:center;">
                        {{ $file->original_name ?? basename($file->file_path) }}</div>
                </div>
            @else
                <p>- {{ $file->original_name ?? basename($file->file_path) }}</p>
            @endif
        @endforeach
    @endif

</div>
