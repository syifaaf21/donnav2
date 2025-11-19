<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditeeAction;
use App\Models\AuditFinding;
use App\Models\CorrectiveAction;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\PreventiveAction;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\User;
use App\Models\WhyCauses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditeeActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $id)
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
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $subAudit = SubAudit::all();

        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();

        return view('contents.ftpp2.auditee-action.create', compact('finding', 'departments', 'processes', 'products', 'auditors', 'auditTypes', 'subAudit', 'findingCategories', 'klausuls'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_finding_id' => 'required|exists:tt_audit_findings,id',
            'root_cause' => 'required|string',
            'pic' => 'nullable|string|max:100',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',

            // terima file upload
            'attachments.*' => 'nullable|file|max:5120',
            'photos2.*' => 'nullable|file|max:5120',
            'files2.*' => 'nullable|file|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Simpan tt_auditee_actions
            $auditeeAction = AuditeeAction::updateOrCreate(
                ['audit_finding_id' => $validated['audit_finding_id']],
                [
                    'pic' => $validated['pic'] ?? '-',
                    'root_cause' => $validated['root_cause'],
                    'yokoten' => $validated['yokoten'],
                    'yokoten_area' => $validated['yokoten_area'] ?? null,
                ]
            );

            // Hapus child lama agar sync (opsional tapi direkomendasikan saat update)
            if ($auditeeAction && $auditeeAction->id) {
                $aid = $auditeeAction->id;

                if (WhyCauses::where('auditee_action_id', $aid)->exists()) {
                    WhyCauses::where('auditee_action_id', $aid)->delete();
                }

                if (CorrectiveAction::where('auditee_action_id', $aid)->exists()) {
                    CorrectiveAction::where('auditee_action_id', $aid)->delete();
                }

                if (PreventiveAction::where('auditee_action_id', $aid)->exists()) {
                    PreventiveAction::where('auditee_action_id', $aid)->delete();
                }

                if (DocumentFile::where('auditee_action_id', $aid)->exists()) {
                    DocumentFile::where('auditee_action_id', $aid)->delete();
                }
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
                        'pic' => $pic ?: null,
                        'activity' => $activity,
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
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
                        'pic' => $pic ?: null,
                        'activity' => $activity,
                        'planning_date' => $plan ?: null,
                        'actual_date' => $actual ?: null,
                    ]);
                }
            }

            // 6️⃣ Upload Attachments (pastikan form mengirim 'attachments[]')
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $date = now()->format('Y-m-d');
                    $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;
                    $path = $file->storeAs('ftpp/auditee_action_attachments', $newFileName, 'public');

                    DocumentFile::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'file_path' => $path,
                        'original_name' => $originalName,
                    ]);
                }
            }

            // update status finding
            $auditFinding = AuditFinding::find($validated['audit_finding_id']);
            if ($auditFinding) {
                $auditFinding->update(['status_id' => 8]);
            }

            if ($request->has('approve_ldr_spv') && $request->approve_ldr_spv == 1) {
                $auditeeAction->update([
                    'ldr_spv_signature' => 1,
                    'ldr_spv_id' => auth()->user()->id
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auditee Action updated',
                    'id' => $auditeeAction->id
                ]);
            }

            return back()->with('success', 'Auditee Action updated');
        } catch (\Throwable $e) {
            DB::rollBack();
            // log error agar lebih mudah debug
            \Log::error('update_auditee_action error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $finding = AuditFinding::with([
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.whyCauses',
            'auditeeAction.file',
            'auditee',
            'department',
            'process',
            'product'
        ])->findOrFail($id);

        $subAudit = SubAudit::all();

        return view('contents.ftpp2.auditee-action.edit', compact('finding', 'subAudit'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'root_cause' => 'required|string',
            'pic' => 'nullable|string|max:100',
            'yokoten' => 'required|boolean',
            'yokoten_area' => 'nullable|string',
            'ldr_spv_signature' => 'nullable|boolean',

            'attachments.*' => 'nullable|file|max:5120',
            'photos2.*' => 'nullable|file|max:5120',
            'files2.*' => 'nullable|file|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $auditeeAction = AuditeeAction::findOrFail($id);

            // UPDATE parent
            $auditeeAction->update([
                'pic' => $validated['pic'] ?? '-',
                'root_cause' => $validated['root_cause'],
                'yokoten' => $validated['yokoten'],
                'yokoten_area' => $validated['yokoten_area'] ?? null,
            ]);

            /* =====================================================
             * 1️⃣ UPDATE WHY (5 WHY)
             * ===================================================== */
            WhyCauses::where('auditee_action_id', $id)->delete();

            for ($i = 1; $i <= 5; $i++) {
                $why = $request->input("why_{$i}_mengapa");
                $cause = $request->input("cause_{$i}_karena");

                if ($why || $cause) {
                    WhyCauses::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'why_description' => $why ?? '',
                        'cause_description' => $cause ?? '',
                    ]);
                }
            }

            /* =====================================================
             * 2️⃣ UPDATE Corrective Action (hapus & replace)
             * ===================================================== */
            CorrectiveAction::where('auditee_action_id', $id)->delete();

            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input("corrective_{$i}_activity");
                if ($activity) {
                    CorrectiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $request->input("corrective_{$i}_pic"),
                        'planning_date' => $request->input("corrective_{$i}_planning"),
                        'actual_date' => $request->input("corrective_{$i}_actual"),
                    ]);
                }
            }

            /* =====================================================
             * 3️⃣ UPDATE Preventive Action
             * ===================================================== */
            PreventiveAction::where('auditee_action_id', $id)->delete();

            for ($i = 1; $i <= 4; $i++) {
                $activity = $request->input("preventive_{$i}_activity");
                if ($activity) {
                    PreventiveAction::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'activity' => $activity,
                        'pic' => $request->input("preventive_{$i}_pic"),
                        'planning_date' => $request->input("preventive_{$i}_planning"),
                        'actual_date' => $request->input("preventive_{$i}_actual"),
                    ]);
                }
            }

            /* =====================================================
             * 4️⃣ Handle Upload Attachments Baru
             * ===================================================== */
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $date = now()->format('Y-m-d');
                    $newName = pathinfo($originalName, PATHINFO_FILENAME) . "_{$date}.{$extension}";

                    $path = $file->storeAs(
                        'ftpp/auditee_action_attachments',
                        $newName,
                        'public'
                    );

                    DocumentFile::create([
                        'auditee_action_id' => $auditeeAction->id,
                        'file_path' => $path,
                        'original_name' => $originalName,
                    ]);
                }
            }

            /* =====================================================
             * 5️⃣ UPDATE Status Finding
             * ===================================================== */
            $auditFinding = AuditFinding::find($auditeeAction->audit_finding_id);
            if ($auditFinding) {
                $auditFinding->update(['status_id' => 8]);
            }

            /* =====================================================
             * 6️⃣ Approve Ldr/SPV
             * ===================================================== */
            if ($request->approve_ldr_spv == 1) {
                $auditeeAction->update([
                    'ldr_spv_signature' => 1,
                    'ldr_spv_id' => auth()->id(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Auditee Action updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("update_auditee_action: " . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
