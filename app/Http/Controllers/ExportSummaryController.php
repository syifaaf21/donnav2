<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Department;
use App\Models\Status;
use App\Models\AuditFinding;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ExportSummaryController extends Controller
{
    /**
     * Download FTPP summary using the Excel template in public/template_excel.
     */
    public function download(Request $request)
    {
        $template = public_path('template_excel/FRM-MR-M3-017-02 SUMMARY AUDIT INTERNAL.xlsx');

        if (!file_exists($template)) {
            abort(404, 'Template file not found: ' . $template);
        }

        // Load template
        $spreadsheet = IOFactory::load($template);

        // Prepare sheets mapping. Ensure sheets exist (create if necessary).
        $sheetAll = $spreadsheet->getSheetByName('Summary Audit (ALL)') ?: $spreadsheet->getActiveSheet();
        $sheetAll->setTitle('Summary Audit (ALL)');

        $sheetIso = $spreadsheet->getSheetByName('Summary Audit (ISO)');
        if (!$sheetIso) {
            $sheetIso = $spreadsheet->createSheet();
            $sheetIso->setTitle('Summary Audit (ISO)');
        }

        $sheetIatf = $spreadsheet->getSheetByName('Summary Audit (IATF)');
        if (!$sheetIatf) {
            $sheetIatf = $spreadsheet->createSheet();
            $sheetIatf->setTitle('Summary Audit (IATF)');
        }

        $sheetAeo = $spreadsheet->getSheetByName('Summary Audit (AEO)');
        if (!$sheetAeo) {
            $sheetAeo = $spreadsheet->createSheet();
            $sheetAeo->setTitle('Summary Audit (AEO)');
        }

        // Small header info on each sheet and Periode Date on B4 (centered)
        $nowLabel = 'Exported At: ' . now()->format('Y-m-d H:i');
        $periodLabel = 'Periode Date: ' . now()->format('d/m/Y');

        foreach ([$sheetAll, $sheetIso, $sheetIatf, $sheetAeo] as $sheet) {
            // $sheet->setCellValue('A2', $nowLabel);
            $sheet->setCellValue('B4', $periodLabel);
            $sheet->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Prepare to write detailed findings starting at row 9 according to template mapping
        // Column mapping (row 9 is first data row):
        // C => registration_number
        // D => department
        // E => finding/issue (finding_description)
        // F => klausul (joined subKlausuls names)
        // G => auditor (joined auditors)
        // H => auditee (joined auditees)
        // I => due date
        // J..O => findings category (J major, K minor, L observation, M perubahan, N penambahan, O peningkatan)
        // P => checklist for need statuses; Q => checklist for close

        $startRow = 9;

        $findings = AuditFinding::with(['department', 'subKlausuls', 'auditors', 'auditee', 'findingCategory', 'status', 'audit'])
            ->orderBy('id')
            ->get();

        // maintain row pointers for each sheet
        $rows = [
            'ALL' => $startRow,
            'ISO' => $startRow,
            'IATF' => $startRow,
            'AEO' => $startRow,
        ];

        $writeRow = function ($sheet, $r, AuditFinding $f) {
            // C: registration number
            $sheet->setCellValue('C' . $r, $f->registration_number ?? '');

            // D: department
            $sheet->setCellValue('D' . $r, $f->department?->name ?? '-');

            // E: finding/issue
            $sheet->setCellValue('E' . $r, $f->finding_description ?? '-');

            // F: klausul (join subKlausuls names)
            $klausul = '-';
            if ($f->subKlausuls && $f->subKlausuls->isNotEmpty()) {
                $klausul = $f->subKlausuls->pluck('name')->join(', ');
            }
            $sheet->setCellValue('F' . $r, $klausul);

            // G: auditor (join auditors)
            $auditorNames = '-';
            if ($f->relationLoaded('auditors') || $f->auditors()->exists()) {
                $auditorNames = $f->auditors->pluck('name')->join(', ') ?: '-';
            }
            $sheet->setCellValue('G' . $r, $auditorNames);

            // H: auditee
            $sheet->setCellValue('H' . $r, $f->getAuditeeNamesAttribute() ?? '-');

            // I: due date (format Indonesia: d/m/Y)
            $due = '';
            if ($f->due_date) {
                try {
                    $due = Carbon::parse($f->due_date)->format('d/m/Y');
                } catch (\Exception $e) {
                    $due = (string) $f->due_date;
                }
            }
            $sheet->setCellValue('I' . $r, $due);

            // Findings category -> J..O
            $category = strtolower($f->findingCategory?->name ?? '');
            $sheet->setCellValue('J' . $r, $category === 'major' ? 'X' : '');
            $sheet->setCellValue('K' . $r, $category === 'minor' ? 'X' : '');
            $sheet->setCellValue('L' . $r, $category === 'observasion' ? 'X' : '');
            $sheet->setCellValue('M' . $r, $category === 'perubahan' ? 'X' : '');
            $sheet->setCellValue('N' . $r, $category === 'penambahan' ? 'X' : '');
            $sheet->setCellValue('O' . $r, $category === 'peningkatan' ? 'X' : '');

            // Status -> P or Q
            $statusName = strtolower($f->status?->name ?? '');
            $needStatuses = array_map('strtolower', ['Draft Finding', 'Need Assign', 'Draft', 'Need Check', 'Need Approval by Auditor', 'Need Approval by Lead Auditor', 'Need Revision']);
            if (in_array($statusName, $needStatuses)) {
                $sheet->setCellValue('P' . $r, 'X');
                $sheet->setCellValue('Q' . $r, '');
            } elseif ($statusName === 'close') {
                $sheet->setCellValue('P' . $r, '');
                $sheet->setCellValue('Q' . $r, 'X');
            } else {
                $sheet->setCellValue('P' . $r, '');
                $sheet->setCellValue('Q' . $r, '');
            }
        };

        foreach ($findings as $f) {
            // always write to ALL
            $writeRow($sheetAll, $rows['ALL'], $f);
            $rows['ALL']++;

            // determine audit type and write to specific sheet(s) as requested
            $atypeName = strtolower($f->audit?->name ?? '');

            // ISO: System Management LK3 (ISO 14001 & ISO 45001)
            if (str_contains($atypeName, 'iso 14001') || str_contains($atypeName, 'iso 45001') || str_contains($atypeName, 'lk3') || str_contains($atypeName, 'system management lk3')) {
                $writeRow($sheetIso, $rows['ISO'], $f);
                $rows['ISO']++;
            }

            // IATF: System Management Mutu (IATF 16949)
            if (str_contains($atypeName, 'iatf') || str_contains($atypeName, '16949') || str_contains($atypeName, 'system management mutu')) {
                $writeRow($sheetIatf, $rows['IATF'], $f);
                $rows['IATF']++;
            }

            // AEO: Sistem Authorized Economic Operator (AEO)
            if (str_contains($atypeName, 'aeo') || str_contains($atypeName, 'authorized economic operator') || str_contains($atypeName, 'sistem authorized economic operator')) {
                $writeRow($sheetAeo, $rows['AEO'], $f);
                $rows['AEO']++;
            }
        }

        // Prepare download filename
        $filename = 'FTPP_Summary_' . now()->format('Ymd_His') . '.xlsx';

        // Write and stream
        $writer = new Xlsx($spreadsheet);

        return Response::streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
}
