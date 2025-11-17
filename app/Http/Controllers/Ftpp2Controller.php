<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditFinding;
use App\Models\Status;
use App\Models\Department;
use App\Models\User;

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
        return view('contents.ftpp2.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
}
