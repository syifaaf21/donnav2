<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\AuditFindingSubKlausul;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\SubKlausul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FtppController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with(['auditee', 'auditor', 'findingCategory'])
            ->orderByDesc('created_at')
            ->get();


        return view('contents.ftpp.index', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories'));
    }

    public function getData($auditTypeId)
    {
        $auditType = Audit::with('subAudit')->findOrFail($auditTypeId);

        $year = now()->year;
        $lastCount = AuditFinding::whereYear('created_at', $year)->count() + 1;
        $findingNumber = str_pad($lastCount, 3, '0', STR_PAD_LEFT);
        $revisionNuber = str_pad($lastCount, 2, '0', STR_PAD_LEFT);
        $prefix = ($auditTypeId == 1) ? 'MR' : 'MS'; // sesuaikan id audit
        $code = "{$prefix}/FTPP/{$year}/{$findingNumber}/{$revisionNuber}";

        $auditors = User::where('role_id', 4) // Role auditor
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
        $auditees = User::where('department_id', $departmentId)->get(['id', 'name']);

        return response()->json($auditees);
    }

    public function store(Request $request)
    {
        $action = $request->action;
        DB::beginTransaction();
        try {

            if ($action === 'save_header') {
                $validated = $request->validate([
                    'audit_type_id' => 'required|exists:tm_audit_types,id',
                    'sub_audit_type_id' => 'nullable|exists:tm_sub_audit_types,id',
                    'finding_category_id' => 'required|exists:tm_finding_categories,id',
                    'sub_klausul_id' => 'required|array',           // <- multiple
                    'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
                    'department_id' => 'required|exists:tm_departments,id',
                    'process_id' => 'nullable|exists:tm_processes,id',
                    'auditor_id' => 'required|exists:users,id',
                    'auditee_id' => 'required|exists:users,id',
                    'registration_number' => 'nullable|string|max:100',
                    'finding_description' => 'required|string',
                    'due_date' => 'required|date',
                    'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);

                // 1. Simpan ke tt_audit_findings
                $auditFinding = AuditFinding::create([
                    'audit_type_id' => $validated['audit_type_id'],
                    'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
                    'finding_category_id' => $validated['finding_category_id'],
                    'department_id' => $validated['department_id'],
                    'process_id' => $validated['process_id'] ?? null,
                    'auditor_id' => $validated['auditor_id'],
                    'auditee_id' => $validated['auditee_id'],
                    'registration_number' => $validated['registration_number'] ?? null,
                    'finding_description' => $validated['finding_description'],
                    'status_id' => 6, // ✅ Default OPEN
                    'due_date' => $validated['due_date'],
                ]);

                // 2. Simpan Sub Klausul ke pivot tt_audit_finding_sub_klausul
                foreach ($validated['sub_klausul_id'] as $subId) {
                    AuditFindingSubKlausul::create([
                        'audit_finding_id' => $auditFinding->id,
                        'sub_klausul_id' => $subId,
                    ]);
                }

                // 3. Upload file jika ada
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $path = $file->store('audit_finding_files', 'public');

                    DocumentFile::create([
                        'audit_finding_id' => $auditFinding->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }
            DB::commit();
            return back()->with('success', 'Audit Finding berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function auditeeActionStore()
    {
        //
    }

    public function ldrSpvSign()
    {
        //
    }

    public function deptheadSign()
    {
        //
    }

    public function auditorVerify()
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function updateAuditeeAction(Request $request, $id)
    {
        //
    }
}
