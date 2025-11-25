<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\AuditFindingSubKlausul;
use App\Models\CorrectiveAction;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\PreventiveAction;
use App\Models\Process;
use App\Models\Product;
use App\Models\Status;
use App\Models\SubAudit;
use App\Models\SubKlausul;
use App\Models\User;
use App\Models\WhyCauses;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;

class FtppController extends Controller
{
    public function index(Request $request)
    {
        // Build base query
        $query = AuditFinding::with(['status', 'department', 'auditor', 'auditee']);

        // Filters
        if ($request->filled('registration_number')) {
            $query->where('registration_number', 'like', '%' . $request->input('registration_number') . '%');
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('auditor_id')) {
            $query->where('auditor_id', $request->input('auditor_id'));
        }

        if ($request->filled('auditee')) {
            $auditee = $request->input('auditee');
            $query->whereHas('auditee', function ($q) use ($auditee) {
                $q->where('name', 'like', '%' . $auditee . '%');
            });
        }

        // order and paginate
        $findings = $query->orderBy('updated_at')->paginate(10);
        // preserve filters in pagination links
        $findings->appends($request->except('page'));

        // Lists for filters and sidebar (include counts)
        $statuses = Status::withCount('auditFinding')->orderBy('name')->get();
        $totalCount = AuditFinding::count();
        $departments = Department::orderBy('name')->get();
        // auditors: users with role 'auditor'
        $auditors = User::whereHas('roles', function ($q) {
            $q->where('name', 'auditor');
        })->orderBy('name')->get();

        return view('contents.ftpp2.index', compact('findings', 'statuses', 'departments', 'auditors', 'totalCount'));
    }

    /**
     * AJAX live search endpoint for a single query input.
     */
    public function search(Request $request)
    {
        $q = $request->input('q');

        $results = AuditFinding::with(['status', 'department', 'auditor', 'auditee'])
            ->when($q, function ($query, $q) {
                $query->where('registration_number', 'like', "%{$q}%")
                    ->orWhereHas('auditee', function ($q2) use ($q) {
                        $q2->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('auditor', function ($q3) use ($q) {
                        $q3->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('department', function ($q4) use ($q) {
                        $q4->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('status', function ($q5) use ($q) {
                        $q5->where('name', 'like', "%{$q}%");
                    });
            })
            ->orderBy('registration_number')
            ->limit(50)
            ->get();

        $payload = $results->map(function ($f) {
            return [
                'id' => $f->id,
                'registration_number' => $f->registration_number,
                'status' => optional($f->status)->name,
                'department' => optional($f->department)->name,
                'auditor' => optional($f->auditor)->name,
                'auditee' => $f->auditee->pluck('name')->join(', '),
                'due_date' => $f->due_date ? \Carbon\Carbon::parse($f->due_date)->format('Y/m/d') : null,
            ];
        });

        return response()->json($payload);
    }

    public function getData($auditTypeId)
    {
        $auditType = Audit::with('subAudit')->findOrFail($auditTypeId);

        $year = now()->year;
        $prefix = ($auditTypeId == 1) ? 'MS' : 'MR'; // sesuaikan id audit

        // Hitung berdasarkan prefix + tahun
        $lastCount = AuditFinding::where('registration_number', 'like', "{$prefix}/FTPP/{$year}/%")
            ->count() + 1;

        // Format nomor 3 digit, misal 001, 002, dst
        $findingNumber = str_pad($lastCount, 3, '0', STR_PAD_LEFT);

        // Generate kode lengkap
        $code = "{$prefix}/FTPP/{$year}/{$findingNumber}/01";

        $auditors = User::whereHas('roles', fn($q) => $q->where('id', 4)) // Role auditor
            ->where('audit_type_id', $auditTypeId)
            ->get();

        return response()->json([
            'reg_number' => $code,
            'sub_audit' => $auditType->subAudit,
            'auditors' => $auditors,
        ]);
    }

    public function filterKlausul($auditType)
    {
        // Contoh mapping manual
        $klausulIds = $auditType == 2
            ? [1]        // Management Mutu
            : [2, 3];    // Management LK3

        $klausuls = Klausul::whereIn('id', $klausulIds)->get();
        return response()->json($klausuls);
    }

    public function getHeadKlausul($klausulId)
    {
        $headKlausuls = HeadKlausul::where('klausul_id', $klausulId)->get();
        return response()->json($headKlausuls);
    }

    public function getSubKlausul($headId)
    {
        $subKlausuls = SubKlausul::where('head_klausul_id', $headId)->get();
        return response()->json($subKlausuls);
    }

    public function getDepartments($plant)
    {
        $departments = Department::when($plant, function ($q) use ($plant) {
            $q->where('plant', $plant);
        }, function ($q) {
            // jika plant == 'All', tampilkan semua department (atau sesuai kebijakan)
            $q;
        })
            ->get(['id', 'name']);

        return response()->json($departments);
    }

    // sebelumnya named getProcess -> sekarang getProcesses
    public function getProcesses($plant)
    {
        try {
            $processes = Process::when($plant, function ($q) use ($plant) {
                $q->where('plant', $plant);
            }, function ($q) {
                $q;
            })
                ->get(['id', 'name']);

            return response()->json($processes);
        } catch (\Exception $e) {
            \Log::error('Error getProcesses: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // sebelumnya getProduct -> sekarang getProducts
    public function getProducts($plant)
    {
        $products = Product::when($plant, function ($q) use ($plant) {
            $q->where('plant', $plant);
        }, function ($q) {
            $q;
        })
            ->get(['id', 'name']);

        return response()->json($products);
    }

    public function getAuditee($departmentId)
    {
        // Ambil auditee berdasarkan department
        $auditees = User::whereHas('departments', fn($q) => $q->where('id', $departmentId))->get(['id', 'name']);

        return response()->json($auditees);
    }

    public function show($id)
    {
        $finding = AuditFinding::with([
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file'
        ])->findOrFail($id);

        return view('contents.ftpp2.partials.detail', compact('finding'));
    }

    public function previewPdf($id)
    {
        $finding = AuditFinding::with(['auditeeAction.file'])->findOrFail($id);

        // Generate main PDF
        $mainPdf = PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('contents.ftpp2.pdf', compact('finding'))->output();

        $merger = new \setasign\Fpdi\Fpdi();

        // Simpan sementara main PDF
        $tmpMain = storage_path('app/temp_main.pdf');
        file_put_contents($tmpMain, $mainPdf);

        $pageCount = $merger->setSourceFile($tmpMain);
        for ($i = 1; $i <= $pageCount; $i++) {
            $merger->AddPage();
            $merger->importPage($i);
        }

        // Tambahkan file attachment AuditFinding
        foreach ($finding->attachments ?? [] as $file) {
            $path = storage_path('app/public/ftpp/audit_finding_attachments/' . $file->filename);
            if (file_exists($path)) {
                $pages = $merger->setSourceFile($path);
                for ($i = 1; $i <= $pages; $i++) {
                    $merger->AddPage();
                    $merger->importPage($i);
                }
            }
        }

        // Tambahkan file attachment AuditeeAction
        if ($finding->auditeeAction) {
            $auditeeFiles = $finding->auditeeAction->file ?? [];
            foreach ($auditeeFiles as $file) {
                if (!empty($file->filename)) {
                    $path = storage_path('app/public/ftpp/auditee_action_attachments/' . $file->filename);
                    if (file_exists($path)) {
                        $pages = $merger->setSourceFile($path);
                        for ($i = 1; $i <= $pages; $i++) {
                            $merger->AddPage();
                            $merger->importPage($i);
                        }
                    }
                }
            }
        }

        // Simpan file sementara untuk preview
        $tmpMerged = storage_path('app/temp_merged.pdf');
        $merger->Output($tmpMerged, 'F');

        return response()->file($tmpMerged); // iframe bisa load PDF merge
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $finding = AuditFinding::find($id);
        if (!$finding) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Finding not found'], 404);
            }
            return redirect('/ftpp')->with('error', 'Finding not found');
        }

        try {
            $finding->delete();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Finding deleted successfully']);
            }
            return redirect('/ftpp')->with('success', 'Record deleted.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to delete'], 500);
            }
            return redirect('/ftpp')->with('error', 'Failed to delete record.');
        }
    }

    /**
     * Download PDF Finding
     */
    public function download($id)
    {
        // Ambil data finding beserta relasi
        $finding = AuditFinding::with([
            'audit',
            'subAudit',
            'findingCategory',
            'auditor',
            'auditee',
            'department',
            'process',
            'product',
            'subKlausuls',
            'file',
            'status',
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file', // lampiran
        ])->findOrFail($id);

        // Set stamp image URLs when signature/approval flags are set (value == 1)
        if ($finding->auditeeAction) {
            // Dept head signature (show manager approval image)
            if (!empty($finding->auditeeAction->dept_head_signature) && $finding->auditeeAction->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }

            // Leader / Supervisor signature (show user approval image)
            if (!empty($finding->auditeeAction->ldr_spv_signature) && $finding->auditeeAction->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }

            // Acknowledge by lead auditor: if flag present on finding or auditeeAction, show lead auditor stamp
            if (
                (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1)
                || (!empty($finding->auditeeAction->acknowledge_by_lead_auditor) && $finding->auditeeAction->acknowledge_by_lead_auditor == 1)
            ) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }

            // Verified by auditor: if flag present on finding or auditeeAction, show internal auditor stamp
            if (
                (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1)
                || (!empty($finding->auditeeAction->verified_by_auditor) && $finding->auditeeAction->verified_by_auditor == 1)
            ) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        } else {
            // In case auditeeAction is absent but flags are on the finding
            if (!empty($finding->dept_head_signature) && $finding->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }
            if (!empty($finding->ldr_spv_signature) && $finding->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }
            if (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }
            if (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        }

        // Tambah filesystem path untuk semua lampiran pada audit finding (images/files) so DomPDF can embed them
        if ($finding->file) {
            foreach ($finding->file as $file) {
                $diskPath = storage_path('app/public/' . $file->file_path);
                if (file_exists($diskPath)) {
                    $file->full_url = $diskPath;
                } else {
                    $file->full_url = public_path('storage/' . $file->file_path);
                }
            }
        }

        // Tambah filesystem path untuk semua lampiran pada auditeeAction (lampiran auditee) so DomPDF can embed them
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $diskPath = storage_path('app/public/' . $file->file_path);
                if (file_exists($diskPath)) {
                    $file->full_url = $diskPath;
                } else {
                    $file->full_url = public_path('storage/' . $file->file_path);
                }
            }
        }

        // Generate main PDF content
        $pdf = PDF::loadView('contents.ftpp2.pdf', compact('finding'))
            ->setPaper('a4', 'portrait');

        $mainPdfContent = $pdf->output();

        // Attempt to merge attached PDFs (from finding->file and auditeeAction->file)
        $tempDir = sys_get_temp_dir();
        $mainTempPath = $tempDir . DIRECTORY_SEPARATOR . 'ftpp_main_' . uniqid() . '.pdf';
        file_put_contents($mainTempPath, $mainPdfContent);

        // Collect attachment PDF paths
        $pdfFilesToMerge = [];

        if ($finding->file) {
            foreach ($finding->file as $file) {
                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $diskPath = storage_path('app/public/' . $file->file_path);
                    if (file_exists($diskPath)) {
                        $pdfFilesToMerge[] = $diskPath;
                    }
                }
            }
        }

        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $diskPath = storage_path('app/public/' . $file->file_path);
                    if (file_exists($diskPath)) {
                        $pdfFilesToMerge[] = $diskPath;
                    }
                }
            }
        }

        $finalFilename = 'FTPP_Finding_' . preg_replace('/[\/\\\\]/', '_', $finding->registration_number) . '.pdf';

        if (empty($pdfFilesToMerge)) {
            // No other PDFs to merge — return main PDF
            // Clean up temp
            @unlink($mainTempPath);
            return response($mainPdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
            ]);
        }

        // Merge using FPDI if available. If not installed, log a hint and return main PDF.
        if (!empty($pdfFilesToMerge)) {
            if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
                \Log::warning('FPDI not available. Install it with: composer require setasign/fpdi-fpdf');
                @unlink($mainTempPath);
                return response($mainPdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
                ]);
            }

            try {
                $merger = new \setasign\Fpdi\Fpdi();

                // import main PDF
                $pageCount = $merger->setSourceFile($mainTempPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tplId = $merger->importPage($pageNo);
                    $size = $merger->getTemplateSize($tplId);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $merger->AddPage($orientation, [$size['width'], $size['height']]);
                    $merger->useTemplate($tplId);
                }

                // append other pdfs
                foreach ($pdfFilesToMerge as $pf) {
                    $pc = $merger->setSourceFile($pf);
                    for ($p = 1; $p <= $pc; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $merger->AddPage($orientation, [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                }

                // Output merged PDF as string
                $mergedPdfString = $merger->Output('', 'S');

                // cleanup temp
                @unlink($mainTempPath);

                return response($mergedPdfString, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
                ]);
            } catch (\Throwable $e) {
                // If merging fails, fall back to main PDF
                \Log::error('PDF merge failed: ' . $e->getMessage());
                @unlink($mainTempPath);
                return response($mainPdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
                ]);
            }
        }
    }

}
