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

        return view('contents.ftpp2.create', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit'));
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
        $fileName = time() . '-' . $file->getClientOriginalName();
        $path = $file->storeAs('ftpp/audit_finding_attachments', $fileName, 'public');

        DocumentFile::create([
            'audit_finding_id' => $auditFinding->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Store a newly created auditee action in storage.
     */
    public function storeAuditeeAction(Request $request)
    {
        //
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
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $subAudit = SubAudit::all();

        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();

        return view('contents.ftpp2.edit', compact('finding', 'departments', 'processes', 'products', 'auditors', 'auditTypes', 'subAudit', 'findingCategories', 'klausuls'));
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
            } elseif ($action === 'update_auditee_action') {
                $validated = $request->validate([
                    'audit_finding_id' => 'required|exists:tt_audit_findings,id',
                    'root_cause' => 'required|string',
                    'pic' => 'nullable|string|max:100',
                    'yokoten' => 'required|boolean',
                    'yokoten_area' => 'nullable|string',
                    'ldr_spv_signature' => 'nullable|boolean',
                    'attachments.*' => 'nullable|file|max:5120',
                ]);
                try {
                    // 1️⃣ Simpan tt_auditee_actions
                    $auditeeAction = AuditeeAction::updateOrCreate([
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
                            WhyCauses::updateOrCreate([
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
                            CorrectiveAction::updateOrCreate([
                                'auditee_action_id' => $auditeeAction->id,
                                'pic' => $pic,
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
                            PreventiveAction::updateOrCreate([
                                'auditee_action_id' => $auditeeAction->id,
                                'pic' => $pic,
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
                            DocumentFile::updateOrCreate([
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

                    if ($request->has('approve_ldr_spv') && $request->approve_ldr_spv == 1) {
                        $auditeeAction->update([
                            'ldr_spv_signature' => 1,
                            'ldr_spv_id' => auth()->user()->id
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Updated successfully',
                    ]);
                } catch (\Exception $e) {
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
