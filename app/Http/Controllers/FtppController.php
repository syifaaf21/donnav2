<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\Department;
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

        return response()->json([
            'reg_number' => $code,
            'sub_audit' => $auditType->subAudit,
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
        $departments = Department::when($plant !== 'All', function ($q) use ($plant) {
            $q->where('plant', $plant)
                ->orWhere('plant', 'All');
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
            $processes = Process::when($plant !== 'All', function ($q) use ($plant) {
                $q->where('plant', $plant)
                    ->orWhere('plant', 'All');
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
        $products = Product::when($plant !== 'All', function ($q) use ($plant) {
            $q->where('plant', $plant)
                ->orWhere('plant', 'All');
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
