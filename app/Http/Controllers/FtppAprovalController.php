<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditeeAction;
use App\Models\AuditFinding;
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

class FtppAprovalController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $subAudit = SubAudit::all();

        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with([
            'auditee',
            'auditor',
            'findingCategory',
            'department',   // 👈 tambahkan ini
            'status',        // 👈 dan ini
            'auditeeAction',
        ])
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        return view('contents.ftpp2.approval.index', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit'));
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

    public function edit($id)
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
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file',
        ])->findOrFail($id);

        // Cek apakah auditeeAction ada dan tanda tangan ada
        $finding->dept_head_signature = null;
        $finding->ldr_spv_signature = null;

        if ($finding->auditeeAction) {
            $finding->dept_head_signature = $finding->auditeeAction->dept_head_signature
                ? asset('storage/' . $finding->auditeeAction->dept_head_signature)
                : null;

            $finding->ldr_spv_signature = $finding->auditeeAction->ldr_spv_signature
                ? asset('storage/' . $finding->auditeeAction->ldr_spv_signature)
                : null;
        }

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
                    'status_id' => 7,
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
                        // Get the original file name and extension
                        $originalName = $photo->getClientOriginalName();
                        $extension = $photo->getClientOriginalExtension();

                        // Get the current date in 'Y-m-d' format
                        $date = now()->format('Y-m-d');

                        // Generate the new file name: original_name_date.extension
                        $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;

                        // Store the file with the new name
                        $path = $photo->storeAs('ftpp/audit_finding_attachments', $newFileName, 'public');

                        // Save to the database
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $originalName,
                        ]);
                    }
                }

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        // Get the original file name and extension
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();

                        // Get the current date in 'Y-m-d' format
                        $date = now()->format('Y-m-d');

                        // Generate the new file name: original_name_date.extension
                        $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;

                        // Store the file with the new name
                        $path = $file->storeAs('ftpp/audit_finding_attachments', $newFileName, 'public');

                        // Save to the database
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $originalName,
                        ]);
                    }
                }

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        // Get the original file name and extension
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();

                        // Get the current date in 'Y-m-d' format
                        $date = now()->format('Y-m-d');

                        // Generate the new file name: original_name_date.extension
                        $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;

                        // Store the file with the new name
                        $path = $file->storeAs('ftpp/audit_finding_attachments', $newFileName, 'public');

                        // Save to the database
                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $originalName,
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
                                'pic*' => $pic,
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
                                'pic*' => $pic,
                                'activity' => $activity,
                                'planning_date' => $plan,
                                'actual_date' => $actual,
                            ]);
                        }
                    }

                    // 6️⃣ Upload Attachments
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $file) {
                            // Ambil nama asli dan ekstensi
                            $originalName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();

                            // Ambil tanggal sekarang
                            $date = now()->format('Y-m-d');

                            // Buat nama file baru: originalname_date.extension
                            $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;

                            // Simpan file dengan nama baru
                            $path = $file->storeAs('ftpp/auditee_action_attachments', $newFileName, 'public');

                            // Simpan ke database
                            DocumentFile::create([
                                'auditee_action_id' => $auditeeAction->id,
                                'file_path' => $path,
                                'original_name' => $originalName,
                            ]);
                        }
                    }

                    $auditFinding = AuditFinding::find($validated['audit_finding_id']);
                    if ($auditFinding) {
                        $auditFinding->update(['status_id' => 8]);
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

    public function ldrSpvSign(Request $request)
    {
        $request->validate([
            'auditee_action_id' => 'required|exists:tt_auditee_actions,id',
        ]);

        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $action->ldr_spv_signature = true;
        $action->save();

        return response()->json([
            'success' => true,
            'message' => 'Leader/SPV approved'
        ]);
    }

    public function deptheadSign(Request $request)
    {
        $request->validate([
            'auditee_action_id' => 'required|exists:tt_auditee_actions,id',
        ]);

        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $action->dept_head_signature = true;
        $action->save();

        // update status
        $finding = AuditFinding::find($action->audit_finding_id);
        if ($finding)
            $finding->update(['status_id' => 9]);

        return response()->json([
            'success' => true,
            'auditee_action_id' => $action->id
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
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
                'auditee_ids.*' => 'exists:users,id',
                'registration_number' => 'nullable|string|max:100',
                'finding_description' => 'required|string',
                'due_date' => 'required|date',
            ]);

            $auditFinding = AuditFinding::findOrFail($id);

            // 🔹 Update record utama
            $auditFinding->update([
                'audit_type_id' => $validated['audit_type_id'],
                'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
                'finding_category_id' => $validated['finding_category_id'],
                'department_id' => $validated['department_id'],
                'process_id' => $validated['process_id'] ?? null,
                'product_id' => $validated['product_id'] ?? null,
                'auditor_id' => $validated['auditor_id'],
                'registration_number' => $validated['registration_number'] ?? null,
                'finding_description' => $validated['finding_description'],
                'due_date' => $validated['due_date'],
            ]);

            // 🔹 Update pivot auditee (hapus dulu, lalu attach ulang)
            $auditFinding->auditee()->sync($validated['auditee_ids']);

            // 🔹 Update sub klausul (hapus & insert ulang)
            AuditFindingSubKlausul::where('audit_finding_id', $auditFinding->id)->delete();
            foreach ($validated['sub_klausul_id'] as $subId) {
                AuditFindingSubKlausul::create([
                    'audit_finding_id' => $auditFinding->id,
                    'sub_klausul_id' => $subId,
                ]);
            }

            // 🔹 File upload optional
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ftpp/audit_finding_attachments', 'public');
                    DocumentFile::create([
                        'audit_finding_id' => $auditFinding->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FTPP updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function auditorVerify(Request $request)
    {
        $request->validate([
            'effectiveness_verification' => 'required|string',
        ]);

        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $finding = AuditFinding::findOrFail($action->audit_finding_id);

        // ✅ Simpan effectiveness verification
        $action->effectiveness_verification = $request->effectiveness_verification;
        $action->verified_by_auditor = true;
        $action->save();

        // ✅ Update status finding
        $finding->status_id = 10; // Approved by auditor
        $finding->save();

        return response()->json(['success' => true]);
    }

    public function auditorReturn(Request $request)
    {
        $request->validate([
            'auditee_action_id' => 'required|exists:tt_auditee_actions,id',
            'status_id' => 'required|integer',
            'effectiveness_verification' => 'required|string',
        ]);

        // 1️⃣ Ambil action berdasarkan auditee_action_id
        $auditeeAction = AuditeeAction::findOrFail($request->auditee_action_id);

        // 2️⃣ Ambil audit finding yang menjadi induknya
        $finding = AuditFinding::findOrFail($auditeeAction->audit_finding_id);

        // 3️⃣ Update status di table "tt_audit_findings"
        $finding->status_id = $request->status_id;
        $finding->save();

        // 4️⃣ Reset flag auditor supaya bisa verify lagi
        $auditeeAction->verified_by_auditor = false;
        $auditeeAction->effectiveness_verification = $request->effectiveness_verification;
        $auditeeAction->dept_head_signature = 0; // reset tanda tangan dept head
        $auditeeAction->save();

        return response()->json([
            'success' => true,
            'message' => 'FTPP returned to user for revision'
        ]);
    }

    public function leadAuditorAcknowledge(Request $request)
    {
        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $finding = AuditFinding::findOrFail($action->audit_finding_id);

        $action->acknowledge_by_lead_auditor = true;
        $action->save();

        $finding->status_id = 11; // closed
        $finding->save();

        return response()->json(['success' => true]);
    }
}
