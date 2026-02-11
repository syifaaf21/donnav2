<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use App\Models\DocumentMapping;
use App\Models\Status;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total dokumen
        $totalDocuments = DocumentMapping::count();
        $totalFtpp = AuditFinding::count();

        $ftpp = AuditFinding::count();

        $activeDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Active');
        })->count();

        // Hitung berdasarkan status
        $documentControls = DocumentMapping::whereHas('document', function ($q) {
            $q->where('type', 'control');
        })->count();

        $documentReviews = DocumentMapping::whereHas('document', function ($q) {
            $q->where('type', 'review');
        })->count();

        // Hitung user
        $totalUsers = User::count();

        // Semua department untuk Control Documents (urut berdasarkan nama)
            $departments = Department::orderBy('name')->pluck('name', 'id')->toArray();

        // Hanya department dengan plant Body, Unit, Electric untuk Review Documents (urut berdasarkan nama)
        $departmentsReview = Department::whereIn('plant', ['Body', 'Unit', 'Electric'])
            ->orderBy('name')
            ->pluck('name', 'id')->toArray();

        // Jumlah dokumen control per department keyed by department_id
        $controlDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Jumlah dokumen review per department keyed by department_id
        $reviewDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();


        $statusObsoleteId = Status::where('name', 'Obsolete')->value('id');

        $obsoleteDocuments = DocumentMapping::with([
            'document:id,name',
            'department:id,name'
        ])
            ->where('status_id', $statusObsoleteId)
            ->orderBy('obsolete_date', 'desc')
            ->get();

        $chartData = AuditFinding::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
            ->whereIn('tm_statuses.name', [
                'Need Assign',
                'Need Check',
                'Need Approval by Auditor',
                'Need Approval by Lead Auditor',
                'Need Revision',
                'Close'
            ])
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status');

        // Data tambahan per department untuk tooltip
        $controlExtraData = [];
        foreach ($departments as $id => $name) {
            $controlExtraData[$id] = [
                'obsolete' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                    ->count(),
                'needReview' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                    ->count(),
                'uncomplete' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                    ->count(),
                'reject' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count(),
            ];
        }

        $reviewStatusData = [];

        foreach ($departmentsReview as $deptId => $deptName) {
            $reviewStatusData[$deptId] = [
                'need_review' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                    ->count(),

                'approved' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                    ->count(),

                'rejected' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count(),

                'uncomplete' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                    ->count(),
            ];
        }


        $findingsPerDepartment = Department::withCount('auditFindings')->get();

        $deptLabels = $findingsPerDepartment->pluck('name');
        $deptTotals = $findingsPerDepartment->pluck('audit_findings_count');


        return view('contents.index', compact(
            'totalDocuments',
            'totalFtpp',
            'departments',
            'departmentsReview',
            'controlDocuments',
            'reviewDocuments',
            'ftpp',
            'documentControls',
            'documentReviews',
            'activeDocuments',
            'totalUsers',
            'obsoleteDocuments',
            'chartData',
            'controlExtraData',
            'reviewStatusData',
            'deptLabels',
            'deptTotals'
        ));
    }

    public function controlDashboard()
    {
        // Data spesifik untuk Document Control
        $totalDocuments = DocumentMapping::whereHas('document', fn($q) => $q->where('type', 'control'))->count();

        $activeDocuments = DocumentMapping::whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->count();

        $statusObsoleteId = Status::where('name', 'Obsolete')->value('id');
        $obsoleteDocuments = DocumentMapping::with(['document:id,name', 'department:id,name'])
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->where('status_id', $statusObsoleteId)
            ->orderBy('obsolete_date', 'desc')
            ->get();

        $uncompleteDocuments = DocumentMapping::whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
            ->count();

        $rejectedDocuments = DocumentMapping::whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
            ->count();

        // Semua department untuk Control Documents
            $departments = Department::orderBy('name')->pluck('name', 'id')->toArray();

        // Jumlah dokumen control per department
        $controlDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Data tambahan per department untuk tooltip
        $controlExtraData = [];
        foreach ($departments as $id => $name) {
            $controlExtraData[$id] = [
                'obsolete' => DocumentMapping::where('department_id', $id)
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                    ->count(),
                'active' => DocumentMapping::where('department_id', $id)
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Active'))
                    ->count(),
                'needReview' => DocumentMapping::where('department_id', $id)
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                    ->count(),
                'uncomplete' => DocumentMapping::where('department_id', $id)
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                    ->count(),
                'rejected' => DocumentMapping::where('department_id', $id)
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count(),
            ];
        }

        // Status breakdown untuk pie chart
        $statusBreakdown = DocumentMapping::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_document_mappings.status_id')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status')
            ->toArray();

        return view('contents.control-dashboard', compact(
            'totalDocuments',
            'activeDocuments',
            'obsoleteDocuments',
            'uncompleteDocuments',
            'rejectedDocuments',
            'departments',
            'controlDocuments',
            'controlExtraData',
            'statusBreakdown'
        ));
    }

    public function reviewDashboard()
    {
        // Data spesifik untuk Document Review
        $totalDocuments = DocumentMapping::whereHas('document', fn($q) => $q->where('type', 'review'))->count();

        // Hanya department dengan plant Body, Unit, Electric (urut berdasarkan nama)
        $departmentsReview = Department::whereIn('plant', ['Body', 'Unit', 'Electric'])
            ->orderBy('name')
            ->pluck('name', 'id')->toArray();

        // Jumlah dokumen review per department
        $reviewDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        $reviewStatusData = [];
        foreach ($departmentsReview as $deptId => $deptName) {
            $reviewStatusData[$deptId] = [
                'need_review' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                    ->count(),
                'approved' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                    ->count(),
                'rejected' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count(),
                'uncomplete' => DocumentMapping::where('department_id', $deptId)
                    ->whereHas('document', fn($q) => $q->where('type', 'review'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                    ->count(),
            ];
        }

        // Status breakdown untuk pie chart
        $statusBreakdown = DocumentMapping::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_document_mappings.status_id')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status')
            ->toArray();

        return view('contents.review-dashboard', compact(
            'totalDocuments',
            'departmentsReview',
            'reviewDocuments',
            'reviewStatusData',
            'statusBreakdown'
        ));
    }

    public function ftppDashboard(Request $request)
    {
        // Data spesifik untuk FTPP
        $selectedAuditTypeId = $request->input('audit_type');

        // base query possibly filtered by audit type
        $baseQuery = AuditFinding::query();
        if (!empty($selectedAuditTypeId)) {
            $baseQuery->where('audit_type_id', $selectedAuditTypeId);
        }

        $totalFtpp = $baseQuery->count();

        // Chart data berdasarkan status
        $chartData = (clone $baseQuery)->selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status');

        // Findings per department
        // Findings per department (respecting audit type filter)
        $findingsPerDepartment = Department::withCount(['auditFindings' => function ($q) use ($selectedAuditTypeId) {
            if (!empty($selectedAuditTypeId)) $q->where('audit_type_id', $selectedAuditTypeId);
        }])->get();
        $deptLabels = $findingsPerDepartment->pluck('name');
        $deptTotals = $findingsPerDepartment->pluck('audit_findings_count');

        // Recent findings (10 terbaru)
        $recentFindings = (clone $baseQuery)->with(['department', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Status breakdown untuk distribusi
        $statusBreakdown = $chartData->toArray();

        // Audit types list for tabs/filters
        $auditTypes = \App\Models\Audit::orderBy('name')->get();
        // Counts per audit type for displaying badges on tabs
        $auditTypeCounts = AuditFinding::selectRaw('audit_type_id, COUNT(*) as total')
            ->groupBy('audit_type_id')
            ->pluck('total', 'audit_type_id')
            ->toArray();

        // Departments list (id => name) - urut berdasarkan nama
        $departments = Department::orderBy('name')->pluck('name', 'id')->toArray();

        // Build per-department status matrix, excluding statuses that contain 'draft'
        $deptStatusMatrix = [];
        foreach ($departments as $deptId => $deptName) {
            $query = AuditFinding::selectRaw('tm_statuses.name as status, COUNT(*) as total')
                ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
                ->where('tt_audit_findings.department_id', $deptId);
            if (!empty($selectedAuditTypeId)) {
                $query->where('tt_audit_findings.audit_type_id', $selectedAuditTypeId);
            }
            $rows = $query->groupBy('tm_statuses.name')
                ->pluck('total', 'status')
                ->toArray();

            // filter out statuses containing 'draft' (case-insensitive)
            $filtered = [];
            foreach ($rows as $s => $count) {
                if (stripos($s, 'draft') !== false) {
                    continue;
                }
                $filtered[$s] = (int) $count;
            }

            // store keyed by department name for display in the view
            $deptStatusMatrix[$deptName] = $filtered;
        }

        // Provide full status list (excluding drafts) so view/chart can show zero-values
        $allStatuses = Status::orderBy('name')->pluck('name')->filter(function ($name) {
            return stripos($name, 'draft') === false;
        })->values()->toArray();

        return view('contents.ftpp-dashboard', compact(
            'totalFtpp',
            'chartData',
            'deptLabels',
            'deptTotals',
            'recentFindings',
            'statusBreakdown',
            'deptStatusMatrix',
            'auditTypes',
            'auditTypeCounts',
            'selectedAuditTypeId'
        ));
    }
}
