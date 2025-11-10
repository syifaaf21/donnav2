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
use App\Models\SubAudit;
use App\Models\SubKlausul;
use App\Models\User;
use App\Models\WhyCauses;
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

        $user = auth()->user();

        return view('contents.ftpp.index', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user'));
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

    public function create(AuditFinding $audit_finding)
    {
        return view('auditee.action-form', [
            'auditFinding' => $audit_finding
        ]);
    }

    public function show($id)
    {
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
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
        ])->findOrFail($id);

        return response()->json($finding);
    }

    public function store(Request $request)
    {
        $action = $request->action;
        DB::beginTransaction();

        try {
            if ($action === 'save_header') {

                // 🔹 Ubah auditee_ids dari string menjadi array sebelum validasi
                // $auditeeIds = json_decode($request->auditee_ids, true) ?? [];
                // $request->merge(['auditee_id' => $auditeeIds]); // agar validasi berjalan

                $validated = $request->validate([
                    'audit_type_id' => 'required|exists:tm_audit_types,id',
                    'sub_audit_type_id' => 'nullable|exists:tm_sub_audit_types,id',
                    'finding_category_id' => 'required|exists:tm_finding_categories,id',
                    'sub_klausul_id' => 'required|array',
                    'sub_klausul_id.*' => 'exists:tm_sub_klausuls,id',
                    'department_id' => 'required|exists:tm_departments,id',
                    'process_id' => 'nullable|exists:tm_processes,id',
                    'product_id' => 'nullable|exists:tm_products,id',
                    'auditor_id' => 'required|exists:users,id',
                    'auditee_ids' => 'required|array',
                    'auditee_ids.*' => 'exists:users,id', // validasi sekarang sudah pakai array
                    'registration_number' => 'nullable|string|max:100',
                    'finding_description' => 'required|string',
                    'due_date' => 'required|date',
                    'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);

                $auditFinding = AuditFinding::create([
                    'audit_type_id' => $validated['audit_type_id'],
                    'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
                    'finding_category_id' => $validated['finding_category_id'],
                    'department_id' => $validated['department_id'],
                    'process_id' => $validated['process_id'] ?? null,
                    'product_id' => $validated['product_id'] ?? null,
                    'auditor_id' => $validated['auditor_id'],
                    'registration_number' => $validated['registration_number'] ?? null,
                    'finding_description' => $validated['finding_description'],
                    'status_id' => 6,
                    'due_date' => $validated['due_date'],
                ]);

                // 🔹 Simpan auditee ke pivot
                $auditFinding->auditee()->attach($validated['auditee_ids']);

                foreach ($validated['sub_klausul_id'] as $subId) {
                    AuditFindingSubKlausul::create([
                        'audit_finding_id' => $auditFinding->id,
                        'sub_klausul_id' => $subId,
                    ]);
                }

                // === Upload attachments ===
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $path = $photo->store('audit_finding_files', 'public');
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $photo->getClientOriginalName(),
                        ]);
                    }
                }

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $path = $file->store('audit_finding_files', 'public');
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('audit_finding_files', 'public');
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }

                DB::commit();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Header saved successfully',
                        'id' => $auditFinding->id,
                    ]);
                }

                return back()->with('success', 'Audit Finding berhasil disimpan!');
            } elseif ($action === 'save_auditee_action') {
                $validated = $request->validate([
                    'audit_finding_id' => 'required|exists:tt_audit_findings,id',
                    'root_cause' => 'required|string',
                    'pic' => 'nullable|string|max:100',
                    'yokoten' => 'required|boolean',
                    'yokoten_area' => 'nullable|string',
                    'dept_head_signature' => 'nullable|file|image|max:2048',
                    'ldr_spv_signature' => 'nullable|file|image|max:2048',
                    'attachments.*' => 'nullable|file|max:5120',
                ]);
                try {
                    // 1️⃣ Simpan tt_auditee_actions
                    $auditeeAction = AuditeeAction::create([
                        'audit_finding_id' => $validated['audit_finding_id'],
                        'pic' => $validated['pic'] ?? '-',
                        'root_cause' => $validated['root_cause'],
                        'yokoten' => $validated['yokoten'],
                        'yokoten_area' => $validated['yokoten_area'] ?? null,
                    ]);

                    // 2️⃣ Simpan file signature (jika ada)
                    if ($request->hasFile('dept_head_signature')) {
                        $path = $request->file('dept_head_signature')->store('signatures', 'public');
                        $auditeeAction->update(['dept_head_signature' => $path]);
                    }

                    if ($request->hasFile('ldr_spv_signature')) {
                        $path = $request->file('ldr_spv_signature')->store('signatures', 'public');
                        $auditeeAction->update(['ldr_spv_signature' => $path]);
                    }

                    // 3️⃣ Simpan Why (5 Why)
                    for ($i = 1; $i <= 5; $i++) {
                        $why = $request->input('why_' . $i . '_mengapa');
                        $cause = $request->input('cause_' . $i . '_karena');
                        if ($why || $cause) {
                            WhyCauses::create([
                                'auditee_action_id' => $auditeeAction->id,
                                'why_description' => $why ?? '',
                                'cause_description' => $cause ?? '',
                            ]);
                        }
                    }

                    // 4️⃣ Simpan Corrective Action
                    for ($i = 1; $i <= 4; $i++) {
                        $activity = $request->input('corrective_' . $i . '_activity');
                        $pic = $request->input('corrective_' . $i . '_pic');
                        $plan = $request->input('corrective_' . $i . '_planning');
                        $actual = $request->input('corrective_' . $i . '_actual');
                        if ($activity) {
                            CorrectiveAction::create([
                                'auditee_action_id' => $auditeeAction->id,
                                'pic' => $pic,
                                'activity' => $activity,
                                'planning_date' => $plan,
                                'actual_date' => $actual,
                            ]);
                        }
                    }

                    // 5️⃣ Simpan Preventive Action
                    for ($i = 1; $i <= 4; $i++) {
                        $activity = $request->input('preventive_' . $i . '_activity');
                        $pic = $request->input('preventive_' . $i . '_pic');
                        $plan = $request->input('preventive_' . $i . '_planning');
                        $actual = $request->input('preventive_' . $i . '_actual');
                        if ($activity) {
                            PreventiveAction::create([
                                'auditee_action_id' => $auditeeAction->id,
                                'pic' => $pic,
                                'activity' => $activity,
                                'planning_date' => $plan,
                                'actual_date' => $actual,
                            ]);
                        }
                    }

                    // 6️⃣ Upload Attachments
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $file) {
                            $path = $file->store('auditee_attachments', 'public');
                            DocumentFile::create([
                                'auditee_action_id' => $auditeeAction->id,
                                'file_path' => $path,
                                'original_name' => $file->getClientOriginalName(),
                            ]);
                        }
                    }

                    $auditFinding = AuditFinding::find($validated['audit_finding_id']);
                    if ($auditFinding) {
                        $auditFinding->update(['status_id' => 7]);
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Auditee Action saved successfully',
                        'auditee_action_id' => $auditeeAction->id
                    ]);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
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
}
