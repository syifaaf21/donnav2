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
use App\Notifications\FtppActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\AuditeeAssignedNotification;
use App\Notifications\DeptHeadCheckedNotification;
use App\Notifications\LeadAuditorApprovedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class FtppApprovalController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('roles', fn($q) => $q->where('name', 'Auditor'))
            ->select('id', 'name')->get();

        $leadAuditors = User::whereHas(
            'roles',
            fn($q) =>
            $q->whereIn('name', ['Lead Auditor', 'Admin', 'Super Admin'])
        )->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();
        $subAudit = SubAudit::all();
        $findingCategories = FindingCategory::all();
        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();

        $user = auth()->user();

        $userDeptIds = $user->departments->pluck('id')->toArray();
        $userAuditTypeIds = $user->auditTypes->pluck('id')->toArray();

        if (empty($userDeptIds) && !empty($user->department_id)) {
            $userDeptIds = [(int) $user->department_id];
        }

        $userRoles = $user->roles
            ->pluck('name')
            ->map(fn($r) => strtolower($r))
            ->toArray();

        $isFullyPrivileged = in_array('admin', $userRoles)
            || in_array('super admin', $userRoles);

        $query = AuditFinding::with([
            'audit',
            'auditee',
            'auditors',
            'findingCategory',
            'department',
            'status',
            'auditeeAction',
            'auditeeAction.deptHead',
            'auditeeAction.auditor',
            'auditeeAction.leadAuditor',
        ])->orderByDesc('created_at');

        // ADMIN / SUPER ADMIN: show lead-auditor queue (unchanged)
        if ($isFullyPrivileged) {
            $query->whereHas('status', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['need approval by lead auditor']);
            });
        } else {
            // Build an OR-based set of filters so users with multiple roles
            // see the union of findings related to each role.
            $query->where(function ($q) use ($user, $userRoles, $userDeptIds, $userAuditTypeIds) {
                // Lead Auditor: need approval by lead auditor + matching audit types
                if (in_array('lead auditor', $userRoles) && !empty($userAuditTypeIds)) {
                    $q->orWhere(function ($qq) use ($userAuditTypeIds) {
                        $qq->whereHas('status', function ($qs) {
                            $qs->whereRaw('LOWER(name) = ?', ['need approval by lead auditor']);
                        })->whereIn('audit_type_id', $userAuditTypeIds);
                    });
                }

                // Dept Head: need check + matching department(s)
                if (in_array('dept head', $userRoles) && !empty($userDeptIds)) {
                    $q->orWhere(function ($qq) use ($userDeptIds) {
                        $qq->whereHas('status', function ($qs) {
                            $qs->whereRaw('LOWER(name) = ?', ['need check']);
                        })->whereIn('department_id', $userDeptIds);
                    });
                }

                // Auditor: need approval by auditor + assigned as auditor
                if (in_array('auditor', $userRoles)) {
                    $q->orWhere(function ($qq) use ($user) {
                        $qq->whereHas('status', function ($qs) {
                            $qs->whereRaw('LOWER(name) = ?', ['need approval by auditor']);
                        })->whereHas('auditors', function ($qa) use ($user) {
                            $qa->where('users.id', $user->id);
                        });
                    });
                }

                // Fallback: if the user has none of these roles (edge case), restrict to user's departments
                if (!in_array('lead auditor', $userRoles) && !in_array('dept head', $userRoles) && !in_array('auditor', $userRoles)) {
                    if (!empty($userDeptIds)) {
                        $q->whereIn('department_id', $userDeptIds);
                    } else {
                        $q->whereRaw('0 = 1');
                    }
                }
            });
        }

        $findings = $query->get();

        return view('contents.ftpp2.approval.index', compact(
            'findings',
            'departments',
            'processes',
            'products',
            'auditors',
            'leadAuditors',
            'klausuls',
            'auditTypes',
            'findingCategories',
            'user',
            'subAudit'
        ));
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

        $auditors = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['auditor']))
            ->whereHas('auditTypes', fn($q) => $q->where('tm_audit_types.id', $auditTypeId))
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

                // send notification to auditees and auditor
                try {
                    $recipients = $auditFinding->auditee()->get();
                    if ($auditFinding->auditor) {
                        $recipients->push($auditFinding->auditor);
                    }
                    $recipients = $recipients->unique('id')->filter()->values();
                    if ($recipients->isNotEmpty()) {
                        $this->safeNotify($recipients, new AuditeeAssignedNotification($auditFinding));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Notify auditee assigned (approval controller) failed: ' . $e->getMessage());
                }

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

    public function deptheadSign(Request $request)
    {
        $request->validate([
            'auditee_action_id' => 'required|exists:tt_auditee_actions,id',
        ]);

        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $action->dept_head_signature = true;
        $action->dept_head_id = auth()->id();
        $action->save();

        // Remove effectiveness verification if present and reset auditor flags
        if (!empty($action->effectiveness_verification) || $action->verified_by_auditor) {
            $action->effectiveness_verification = null;
            $action->verified_by_auditor = false;
            $action->auditor_id = null;
            $action->save();
        }

        // update status
        $finding = AuditFinding::find($action->audit_finding_id);
        if ($finding)
            $finding->update(['status_id' => 9]);

        // notify auditor and auditees that dept head checked
            try {
                $recipients = collect();
                if ($finding->auditor)
                    $recipients->push($finding->auditor);
                $recipients = $recipients->merge($finding->auditee()->get());
                $recipients = $recipients->unique('id')->filter()->values();

                if ($recipients->isNotEmpty()) {
                    $this->safeNotify(
                        $recipients,
                        new FtppActionNotification(
                            $finding,
                            'dept_head_checked',   // action: assigned
                            auth()->user()?->name
                        )
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('FtppActionNotification (dept_head_checked) failed: ' . $e->getMessage());
            }

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
        $action->auditor_id = auth()->id();
        $action->save();

        // ✅ Update status finding
        $finding->status_id = 10; // Approved by auditor
        $finding->save();

        try {
            $recipients = collect();

            // auditees
            $recipients = $recipients->merge($finding->auditee()->get());

            // dept head if present on auditee action
            if (!empty($action->dept_head_id)) {
                $dh = User::find($action->dept_head_id);
                if ($dh)
                    $recipients->push($dh);
            }

            // include lead auditors (role-based)
            $leadAuditors = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
            if ($leadAuditors->isNotEmpty()) {
                $recipients = $recipients->merge($leadAuditors);
            }

            $recipients = $recipients->unique('id')->filter()->values();

            if ($recipients->isNotEmpty()) {
                $this->safeNotify(
                    $recipients,
                    new FtppActionNotification(
                        $finding,
                        'auditor_approved',
                        auth()->user()?->name
                    )
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('FtppActionNotification (auditor_approved) failed: ' . $e->getMessage());
        }

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
        $auditeeAction->dept_head_id = null;
        $auditeeAction->save();

        // --- Kirim notifikasi bahwa auditor mengembalikan (return) untuk revisi ---
        try {
            // Hanya kirim notifikasi ke auditee yang tertera di FTPP
            $recipients = $finding->auditee()->get()->unique('id')->filter()->values();

                if ($recipients->isNotEmpty()) {
                    $by = auth()->user()?->name ?? 'Auditor';
                    $reg = $finding->registration_number ?? '-';
                    $reason = $auditeeAction->effectiveness_verification ?? $request->effectiveness_verification;
                    $customMessage = "{$by} has returned the finding (Registration No: {$reg}) for revision. Note: {$reason}";

                    $this->safeNotify(
                        $recipients,
                        new FtppActionNotification(
                            $finding,
                            'auditor_return',
                            auth()->user()?->name,
                            $customMessage
                        )
                    );
                }
        } catch (\Throwable $e) {
            \Log::warning('FtppActionNotification (auditor_return) failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'FTPP returned to user for revision'
        ]);
    }

    public function leadAuditorAcknowledge(Request $request)
    {
        $request->validate([
            'auditee_action_id' => 'required|exists:tt_auditee_actions,id',
            'lead_auditor_id' => 'required|exists:users,id',
        ]);

        $action = AuditeeAction::findOrFail($request->auditee_action_id);
        $finding = AuditFinding::findOrFail($action->audit_finding_id);

        $action->acknowledge_by_lead_auditor = true;
        $action->lead_auditor_id = $request->lead_auditor_id;
        $action->save();

        $finding->status_id = 11; // closed
        $finding->save();

        // notify auditee, auditor and dept head
        try {
            $recipients = collect();
            $recipients = $recipients->merge($finding->auditee()->get());
            if ($finding->auditor) {
                $recipients->push($finding->auditor);
            }
            if ($action->dept_head_id) {
                $dh = User::find($action->dept_head_id);
                if ($dh)
                    $recipients->push($dh);
            }
            $recipients = $recipients->unique('id')->filter()->values();
            if ($recipients->isNotEmpty()) {
                $this->safeNotify(
                    $recipients,
                    new FtppActionNotification(
                        $finding,
                        'lead_approved',
                        auth()->user()?->name
                    )
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('FtppActionNotification (lead_approved) failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Helper to safely send notifications: always send DB notification to all recipients,
     * then attempt to send mail only to valid email addresses and catch SMTP errors.
     */
    private function safeNotify($recipients, $notification)
    {
        try {
            $recipients = collect($recipients)->filter();
            if ($recipients->isEmpty()) return;

            // send database notification for all recipients
            Notification::send($recipients, $notification);

            // prepare mail recipients: users with valid emails and not reserved domains
            $mailRecipients = $recipients->filter(fn($u) => !empty($u->email))->values();
            $reserved = ['example.com', 'example.org', 'example.net'];
            $validMailRecipients = $mailRecipients->filter(function ($u) use ($reserved) {
                $email = trim(strtolower($u->email ?? ''));
                if (empty($email)) return false;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
                $parts = explode('@', $email);
                if (count($parts) !== 2) return false;
                $domain = $parts[1];
                if (in_array($domain, $reserved)) return false;
                return true;
            })->values();

            if ($validMailRecipients->isNotEmpty()) {
                try {
                    Notification::send($validMailRecipients, $notification);
                } catch (\Throwable $e) {
                    Log::warning('safeNotify mail send failed: ' . $e->getMessage());
                }
            } else {
                Log::warning('safeNotify: no valid mail recipients after filtering');
            }
        } catch (\Throwable $e) {
            Log::warning('safeNotify failed: ' . $e->getMessage());
        }
    }
}
