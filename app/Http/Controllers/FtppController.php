<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\Department;
use App\Models\FindingCategory;
use App\Models\Klausul;
use App\Models\Process;
use App\Models\SubAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FtppController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name')->get();
        $processes = Process::select('id', 'name')->get();

        // ambil auditee & auditor dari role user
        $auditees = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['User', 'Leader', 'Supervisor']);
        })
            ->select('id', 'name')
            ->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();
        $subAuditTypes = SubAudit::all();
        $findingCategories = FindingCategory::all();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with(['auditee', 'auditor', 'findingCategory'])
            ->orderByDesc('created_at')
            ->get();


        return view('contents.ftpp.index', compact('findings', 'departments', 'processes', 'auditees', 'auditors', 'klausuls', 'auditTypes', 'subAuditTypes', 'findingCategories'));
    }

    public function storeHeader(Request $request)
    {
        // 🔹 1. Validasi input
        $validated = $request->validate([
            'audit_type_id' => 'required|integer',
            'sub_audit_type_id' => 'nullable|integer',
            'department_id' => 'required|integer',
            'process_id' => 'nullable|integer',
            'auditee_id' => 'required|integer',
            'auditor_id' => 'required|integer',
            'finding_category_id' => 'required|string|in:major,minor,observation',
            'finding_description' => 'required|string|max:255',
            'sub_klausul_id' => 'required|integer',
            'due_date' => 'nullable|date',
        ]);

        // 🔹 2. Generate nomor temuan (berdasarkan urutan ID)
        $last = AuditFinding::latest('id')->first();
        $nextNumber = $last ? $last->id + 1 : 1;
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT); // contoh: 001

        // 🔹 3. Tentukan prefix berdasarkan audit type
        $prefix = match ($validated['audit_type_id']) {
            1 => 'MS', // Management LK3
            2 => 'MR', // Management Mutu
            default => 'XX', // default fallback
        };

        // 🔹 4. Format nomor revisi (default: 00)
        $revision = '00';

        // 🔹 5. Bentuk nomor registrasi akhir
        // contoh: MS/FTPP/2025/001/00
        $regNumber = sprintf(
            '%s/FTPP/%s/%s/%s',
            $prefix,
            now()->format('Y'),
            $formattedNumber,
            $revision
        );

        // 🔹 6. Simpan data
        $finding = AuditFinding::create([
            'registration_number' => $regNumber,
            'audit_type_id' => $validated['audit_type_id'],
            'sub_audit_type_id' => $validated['sub_audit_type_id'] ?? null,
            'department_id' => $validated['department_id'],
            'process_id' => $validated['process_id'] ?? null,
            'auditee_id' => $validated['auditee_id'],
            'auditor_id' => $validated['auditor_id'],
            'finding_category_id' => $validated['finding_category_id'],
            'finding_description',
            'sub_klausul_id',
            'due_date' => $validated['due_date'] ?? null,
            'status_id' => 1, // default OPEN
        ]);

        // 🔹 7. Return response ke Alpine.js
        return response()->json([
            'success' => true,
            'message' => 'Header FTPP berhasil disimpan.',
            'data' => $finding,
        ]);
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
