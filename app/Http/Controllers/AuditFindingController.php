<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditFindingSubKlausul;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\Process;
use App\Models\Product;
use App\Models\SubAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuditFindingController extends Controller
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
    public function store(Request $request)
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
            'photos.*' => 'file|mimes:jpg,jpeg,png,pdf',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
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

        // Return response: JSON for AJAX, redirect to index for normal requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Audit Finding saved successfully!',
            ]);
        }

        return redirect('/ftpp')->with('success', 'Audit Finding saved successfully!');
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
            'department',
            'status',
            'auditeeAction',
        ])
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        $finding = AuditFinding::with(['auditee', 'subKlausuls', 'file', 'department', 'process', 'product'])
            ->findOrFail($id);

        return view('contents.ftpp2.audit-finding.edit', compact('findings', 'departments', 'processes', 'products', 'auditors', 'klausuls', 'auditTypes', 'findingCategories', 'user', 'subAudit', 'finding'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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
            'photos.*' => 'file|mimes:jpg,jpeg,png,pdf',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf',
            'existing_file_delete' => 'nullable|array',
            'existing_file_delete.*' => 'integer',
        ]);

        $auditFinding = AuditFinding::findOrFail($id);

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

        // sync auditee relationship
        $auditFinding->auditee()->sync($validated['auditee_ids']);

        // replace sub klausul relationships
        AuditFindingSubKlausul::where('audit_finding_id', $auditFinding->id)->delete();
        foreach ($validated['sub_klausul_id'] as $subId) {
            AuditFindingSubKlausul::create([
                'audit_finding_id' => $auditFinding->id,
                'sub_klausul_id' => $subId,
            ]);
        }

        // handle deletion of existing attachments if requested
        if ($request->filled('existing_file_delete')) {
            $toDelete = $request->input('existing_file_delete', []);
            foreach ($toDelete as $fileId) {
                $df = DocumentFile::where('audit_finding_id', $auditFinding->id)->where('id', $fileId)->first();
                if ($df) {
                    if (Storage::disk('public')->exists($df->file_path)) {
                        Storage::disk('public')->delete($df->file_path);
                    }
                    $df->delete();
                }
            }
        }

        // Handle new uploads
        $this->handleFileUploads($request, $auditFinding);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Audit Finding updated successfully!']);
        }

        return redirect('/ftpp')->with('success', 'Audit Finding updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Detach an auditee from an audit finding (AJAX)
     */
    public function destroyAuditee(string $id, string $auditee)
    {
        $finding = AuditFinding::findOrFail($id);
        // detach the auditee (if exists)
        $finding->auditee()->detach($auditee);

        return response()->json(['success' => true, 'message' => 'Auditee removed from finding']);
    }

    /**
     * Remove a sub-klausul association from an audit finding (AJAX)
     */
    public function destroySubKlausul(string $id, string $sub)
    {
        $row = AuditFindingSubKlausul::where('audit_finding_id', $id)
            ->where('sub_klausul_id', $sub)
            ->first();

        if ($row) {
            $row->delete();
            return response()->json(['success' => true, 'message' => 'Sub-klausul removed']);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    /**
     * Delete an attachment (DocumentFile) by id (AJAX)
     */
    public function destroyAttachment(string $id)
    {
        $df = DocumentFile::find($id);
        if (!$df) {
            return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
        }

        // delete file from storage if exists
        if ($df->file_path && Storage::disk('public')->exists($df->file_path)) {
            Storage::disk('public')->delete($df->file_path);
        }

        $df->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted']);
    }
}
