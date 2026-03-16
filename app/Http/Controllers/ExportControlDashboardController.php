<?php

namespace App\Http\Controllers;

use App\Models\DocumentMapping;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportControlDashboardController extends Controller
{
    /**
     * Download Document Control dashboard summary using template Excel.
     */
    public function download(Request $request)
    {
        $roleName = strtolower(trim((string) (auth()->user()?->roles?->pluck('name')->first() ?? '')));
        if (!in_array($roleName, ['admin', 'super admin'])) {
            abort(403, 'Unauthorized access to export document control dashboard.');
        }

        $template = public_path('template_excel/Document-Control.xlsx');

        if (!file_exists($template)) {
            abort(404, 'Template file not found: ' . $template);
        }

        $statusObsoleteId = Status::where('name', 'Obsolete')->value('id');

        $totalDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->count();

        $activeDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->count();

        $obsoleteDocuments = DocumentMapping::with(['document:id,name', 'department:id,name', 'status:id,name'])
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->where('status_id', $statusObsoleteId)
            ->orderBy('obsolete_date', 'desc')
            ->get();

        // Tabel detail berisi semua dokumen control untuk semua status.
        $allControlDocuments = DocumentMapping::with(['document:id,name', 'department:id,name', 'status:id,name'])
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->orderBy('id', 'desc')
            ->get();

        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        // Header summary values based on template coordinates.
        // Template uses a merged title row for period date with B3 as the master cell.
        $sheet->setCellValue('B3', 'Periode Date: ' . now()->format('d-m-Y'));
        $sheet->setCellValue('D6', $totalDocuments);
        $sheet->setCellValue('D7', $activeDocuments);
        $sheet->setCellValue('D8', $obsoleteDocuments->count());

        // Detail document list starts at row 12 (row 11 is reserved in current template).
        $startRow = 12;
        $row = $startRow;
        $no = 1;

        foreach ($allControlDocuments as $doc) {
            $sheet->setCellValue('B' . $row, $no);
            $sheet->setCellValue('C' . $row, $doc->document?->name ?? '-');
            $sheet->setCellValue('D' . $row, $doc->department?->name ?? '-');

            $obsoleteDate = '-';
            if (!empty($doc->obsolete_date)) {
                try {
                    $obsoleteDate = Carbon::parse($doc->obsolete_date)->format('d-m-Y');
                } catch (\Exception $e) {
                    $obsoleteDate = (string) $doc->obsolete_date;
                }
            }

            $sheet->setCellValue('E' . $row, $obsoleteDate);
            $sheet->setCellValue('F' . $row, $doc->status?->name ?? 'Obsolete');

            $row++;
            $no++;
        }

        $filename = 'Document_Control_Dashboard_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return Response::streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
