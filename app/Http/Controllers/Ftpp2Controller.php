<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditeeAction;
use App\Models\AuditFindingSubKlausul;
use App\Models\CorrectiveAction;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\PreventiveAction;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\WhyCauses;
use Illuminate\Http\Request;
use App\Models\AuditFinding;
use App\Models\Status;
use App\Models\Department;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class Ftpp2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->input('due_date_from'));
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->input('due_date_to'));
        }

        // order and paginate
        $findings = $query->orderBy('due_date')->paginate(15);
        // preserve filters in pagination links
        $findings->appends($request->except('page'));

        // Lists for filters and sidebar (include counts)
        $statuses = Status::withCount('auditFinding')->orderBy('name')->get();
        $totalCount = AuditFinding::count();
        $departments = Department::orderBy('name')->get();
        // auditors: users with role 'auditor' if role relation exists, else all users
        $auditors = User::whereHas('role', function ($q) {
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
            ->orderBy('due_date')
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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

        return view('contents.ftpp2.audit-finding.create', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAuditFinding(Request $request)
    {
        // Validate request
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
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        // Create the audit finding
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

        // Store auditee relationship
        $auditFinding->auditee()->attach($validated['auditee_ids']);

        // Store sub klausul relationships
        foreach ($validated['sub_klausul_id'] as $subId) {
            AuditFindingSubKlausul::create([
                'audit_finding_id' => $auditFinding->id,
                'sub_klausul_id' => $subId,
            ]);
        }

        // Handle file uploads for photos, files, and attachments
        $this->handleFileUploads($request, $auditFinding);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Audit Finding saved successfully!',
        ]);
    }

    private function handleFileUploads(Request $request, $auditFinding)
    {
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $this->storeFile($photo, $auditFinding);
            }
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->storeFile($file, $auditFinding);
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $this->storeFile($attachment, $auditFinding);
            }
        }
    }

    private function storeFile($file, $auditFinding)
    {
        // generate a more unique filename to avoid collisions
        $timestamp = now()->format('YmdHis') . '_' . uniqid();
        $safeOriginal = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
        $fileName = $timestamp . '-' . $safeOriginal;

        $path = $file->storeAs('ftpp/audit_finding_attachments', $fileName, 'public');

        $originalName = $file->getClientOriginalName();

        // prevent duplicate DocumentFile records: check by audit_finding_id + original_name + path
        $exists = DocumentFile::where('audit_finding_id', $auditFinding->id)
            ->where('original_name', $originalName)
            ->where('file_path', $path)
            ->exists();

        if (!$exists) {
            return DocumentFile::create([
                'audit_finding_id' => $auditFinding->id,
                'file_path' => $path,
                'original_name' => $originalName,
            ]);
        }

        // if already exists, return the existing record
        return DocumentFile::where('audit_finding_id', $auditFinding->id)
            ->where('original_name', $originalName)
            ->where('file_path', $path)
            ->first();
    }

    /**
     * Display the specified resource.
     */
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


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $action = $request->action;
        DB::beginTransaction();

        try {
            if ($action === 'update_audit_finding') {

                // 🔹 Validasi request
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
                    'photos.*' => 'nullable|file|image|max:2048',
                    'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                    'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:4096',
                ]);

                // 🔹 Ambil data audit finding yang akan diupdate
                $auditFinding = AuditFinding::findOrFail($id);

                // 🔹 Update data utama
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

                // 🔹 Sync auditee pivot
                $auditFinding->auditee()->sync($validated['auditee_ids']);

                // 🔹 Update sub klausul
                AuditFindingSubKlausul::where('audit_finding_id', $auditFinding->id)->delete();
                foreach ($validated['sub_klausul_id'] as $subId) {
                    AuditFindingSubKlausul::create([
                        'audit_finding_id' => $auditFinding->id,
                        'sub_klausul_id' => $subId,
                    ]);
                }

                // 🔹 Upload attachment function helper
                $uploadFiles = function ($files) use ($auditFinding) {
                    foreach ($files as $file) {
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $date = now()->format('Y-m-d');
                        $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $date . '.' . $extension;
                        $path = $file->storeAs('ftpp/audit_finding_attachments', $newFileName, 'public');

                        DocumentFile::create([
                            'audit_finding_id' => $auditFinding->id,
                            'file_path' => $path,
                            'original_name' => $originalName,
                        ]);
                    }
                };

                // 🔹 Upload photos
                if ($request->hasFile('photos')) {
                    $uploadFiles($request->file('photos'));
                }

                // 🔹 Upload documents
                if ($request->hasFile('files')) {
                    $uploadFiles($request->file('files'));
                }

                // 🔹 Upload combined attachments
                if ($request->hasFile('attachments')) {
                    $uploadFiles($request->file('attachments'));
                }

                DB::commit();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Audit Finding updated successfully',
                        'id' => $auditFinding->id,
                    ]);
                }

                return back()->with('success', 'Audit Finding updated successfully.');

            } elseif ($action === 'update_auditee_action') {
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
            return redirect()->back()->with('error', 'Finding not found');
        }

        try {
            $finding->delete();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Finding deleted successfully']);
            }
            return redirect()->back()->with('success', 'Record deleted.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to delete'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete record.');
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

        // Tambah URL penuh untuk signature
        foreach (['dept_head_signature', 'ldr_spv_signature', 'acknowledge_by_lead_auditor', 'verified_by_auditor'] as $sig) {
            if (!empty($finding->auditeeAction?->$sig)) {
                $finding->{$sig . '_url'} = asset('storage/' . $finding->auditeeAction->$sig);
            }
        }

        // Tambah URL penuh untuk semua lampiran
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $file->full_url = asset('storage/' . $file->file_path);
            }
        }

        $pdf = PDF::loadView('contents.ftpp.pdf', compact('finding'))
            ->setPaper('a4', 'portrait');

        $filename = 'FTPP_Finding_' . preg_replace('/[\/\\\\]/', '_', $finding->registration_number) . '.pdf';

        return $pdf->download($filename);
    }
}
