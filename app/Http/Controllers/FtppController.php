<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\Department;
use App\Models\Klausul;
use App\Models\Process;
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
        $auditees = User::whereHas('role', fn($q) => $q->where('name', 'auditee'))
            ->select('id', 'name')->get();

        $auditors = User::whereHas('role', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name')->get();

        $auditTypes = Audit::with('subAudit')->get();

        $klausuls = Klausul::with(['headKlausul.subKlausul'])->get();
        $findings = AuditFinding::with(['auditee', 'auditor', 'findingCategory'])
            ->orderByDesc('created_at')
            ->get();

        return view('contents.ftpp.index', compact('findings', 'departments', 'processes', 'auditees', 'auditors', 'klausuls', 'auditTypes'));
    }

    public function getAll()
    {
        return response()->json(
            Klausul::with('headKlausul.subKlausul')->get()
        );
    }

    public function store(Request $request)
    {
        //
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
}
