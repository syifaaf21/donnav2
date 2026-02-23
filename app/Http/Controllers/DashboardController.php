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
        // Hitung total dokumen (exclude mappings marked for deletion)
        $totalDocuments = DocumentMapping::whereNull('marked_for_deletion_at')->count();
        $totalFtpp = AuditFinding::count();

        $ftpp = AuditFinding::count();

        $activeDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Active');
        })->count();

        // Hitung berdasarkan status
        $documentControls = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', function ($q) {
                $q->where('type', 'control');
            })->count();

        $documentReviews = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', function ($q) {
                $q->where('type', 'review');
            })->count();

        // Hitung user
        $totalUsers = User::count();

        // Jumlah dokumen control per department keyed by department_id (exclude marked for deletion)
        $controlDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Semua department untuk Control Documents (filtered to only those with control docs)
        $allDepartments = Department::pluck('name', 'id')->toArray();

        // Include documents without department as 'Unknown'
        $unknownControlCount = DocumentMapping::whereNull('department_id')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->count();
        if ($unknownControlCount > 0) {
            $controlDocuments['unknown'] = $unknownControlCount;
            $allDepartments['unknown'] = 'Unknown';
        }
        // Filter departments to only those that have control documents
        $departments = array_filter($allDepartments, function ($name, $id) use ($controlDocuments) {
            return isset($controlDocuments[$id]) && (int) $controlDocuments[$id] > 0;
        }, ARRAY_FILTER_USE_BOTH);

        // Hanya department dengan plant Body, Unit, Electric untuk Review Documents
        $allDepartmentsReview = Department::whereIn('plant', ['Body', 'Unit', 'Electric'])
            ->pluck('name', 'id')->toArray();

        // Jumlah dokumen review per department keyed by department_id (exclude marked for deletion)
        $reviewDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Include review docs without department as 'Unknown'
        $unknownReviewCount = DocumentMapping::whereNull('department_id')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->count();
        if ($unknownReviewCount > 0) {
            $reviewDocuments['unknown'] = $unknownReviewCount;
            $allDepartmentsReview['unknown'] = 'Unknown';
        }

        // Filter review departments to only those that have review documents
        $departmentsReview = array_filter($allDepartmentsReview, function ($name, $id) use ($reviewDocuments) {
            return isset($reviewDocuments[$id]) && (int) $reviewDocuments[$id] > 0;
        }, ARRAY_FILTER_USE_BOTH);


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

        // Data tambahan per department untuk tooltip (exclude mappings marked for deletion)
        $controlExtraData = [];
        foreach ($departments as $id => $name) {
            if ($id === 'unknown') {
                $activeCount = DocumentMapping::whereNull('department_id')
                    ->whereNull('marked_for_deletion_at')
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Active'))
                    ->count();

                $rejectedCount = DocumentMapping::whereNull('department_id')
                    ->whereNull('marked_for_deletion_at')
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count();

                $controlExtraData[$id] = [
                    'active' => $activeCount,
                    'obsolete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                        ->count(),
                    'needReview' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),
                    'uncomplete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                    'rejected' => $rejectedCount,
                    'reject' => $rejectedCount,
                ];
            } else {
                $activeCount = DocumentMapping::where('department_id', $id)
                    ->whereNull('marked_for_deletion_at')
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Active'))
                    ->count();

                $rejectedCount = DocumentMapping::where('department_id', $id)
                    ->whereNull('marked_for_deletion_at')
                    ->whereHas('document', fn($q) => $q->where('type', 'control'))
                    ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                    ->count();

                $controlExtraData[$id] = [
                    'active' => $activeCount,
                    'obsolete' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                        ->count(),
                    'needReview' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),
                    'uncomplete' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                    'rejected' => $rejectedCount,
                    'reject' => $rejectedCount,
                ];
            }
        }

        $reviewStatusData = [];

        foreach ($departmentsReview as $deptId => $deptName) {
            if ($deptId === 'unknown') {
                $reviewStatusData[$deptId] = [
                    'need_review' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),

                    'approved' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                        ->count(),

                    'rejected' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),

                    'uncomplete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                ];
            } else {
                $reviewStatusData[$deptId] = [
                    'need_review' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),

                    'approved' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                        ->count(),

                    'rejected' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),

                    'uncomplete' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                ];
            }
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
        $totalDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->count();

        $activeDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->count();

        $statusObsoleteId = Status::where('name', 'Obsolete')->value('id');
        $obsoleteDocuments = DocumentMapping::with(['document:id,name', 'department:id,name'])
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->where('status_id', $statusObsoleteId)
            ->orderBy('obsolete_date', 'desc')
            ->get();

        $uncompleteDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
            ->count();

        $rejectedDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
            ->count();

        // Jumlah dokumen control per department
        $controlDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Semua department untuk Control Documents (filter hanya yang punya dokumen)
        $allDepartments = Department::pluck('name', 'id')->toArray();

        // Include unknown (no department) if present
        $unknownControlCount = DocumentMapping::whereNull('department_id')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->count();
        if ($unknownControlCount > 0) {
            $controlDocuments['unknown'] = $unknownControlCount;
            $allDepartments['unknown'] = 'Unknown';
        }

        $departments = array_filter($allDepartments, function ($name, $id) use ($controlDocuments) {
            return isset($controlDocuments[$id]) && (int) $controlDocuments[$id] > 0;
        }, ARRAY_FILTER_USE_BOTH);

        // Data tambahan per department untuk tooltip (hanya untuk departments yang dipakai)
        $controlExtraData = [];
        foreach ($departments as $id => $name) {
            if ($id === 'unknown') {
                $controlExtraData[$id] = [
                    'obsolete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                        ->count(),
                    'active' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Active'))
                        ->count(),
                    'needReview' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),
                    'uncomplete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                    'rejected' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),
                ];
            } else {
                $controlExtraData[$id] = [
                    'obsolete' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                        ->count(),
                    'active' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Active'))
                        ->count(),
                    'needReview' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),
                    'uncomplete' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                    'rejected' => DocumentMapping::where('department_id', $id)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'control'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),
                ];
            }
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
        // Data spesifik untuk Document Review (exclude mappings marked for deletion)
        $totalDocuments = DocumentMapping::whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->count();

        // Hanya department dengan plant Body, Unit, Electric
        $departmentsReview = Department::whereIn('plant', ['Body', 'Unit', 'Electric'])
            ->pluck('name', 'id')->toArray();

        // Jumlah dokumen review per department
        $reviewDocuments = DocumentMapping::selectRaw('department_id, COUNT(*) as total')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id')
            ->toArray();

        // Include unknown (no department) if present
        $unknownReviewCount = DocumentMapping::whereNull('department_id')
            ->whereNull('marked_for_deletion_at')
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->count();
        if ($unknownReviewCount > 0) {
            $reviewDocuments['unknown'] = $unknownReviewCount;
            $departmentsReview['unknown'] = 'Unknown';
        }

        $reviewStatusData = [];
        foreach ($departmentsReview as $deptId => $deptName) {
            if ($deptId === 'unknown') {
                $reviewStatusData[$deptId] = [
                    'need_review' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),

                    'approved' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                        ->count(),

                    'rejected' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),

                    'uncomplete' => DocumentMapping::whereNull('department_id')
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                ];
            } else {
                $reviewStatusData[$deptId] = [
                    'need_review' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                        ->count(),

                    'approved' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
                        ->count(),

                    'rejected' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Rejected'))
                        ->count(),

                    'uncomplete' => DocumentMapping::where('department_id', $deptId)
                        ->whereNull('marked_for_deletion_at')
                        ->whereHas('document', fn($q) => $q->where('type', 'review'))
                        ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                        ->count(),
                ];
            }
        }

        // Status breakdown untuk pie chart
        $statusBreakdown = DocumentMapping::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_document_mappings.status_id')
            ->whereNull('marked_for_deletion_at')
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

    public function ftppDashboard()
    {
        // Data spesifik untuk FTPP
        $totalFtpp = AuditFinding::count();

        // Chart data berdasarkan status
        $chartData = AuditFinding::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status');

        // Findings per department
        $findingsPerDepartment = Department::withCount('auditFindings')->get();
        $deptLabels = $findingsPerDepartment->pluck('name');
        $deptTotals = $findingsPerDepartment->pluck('audit_findings_count');

        // Recent findings (10 terbaru)
        $recentFindings = AuditFinding::with(['department', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Status breakdown untuk distribusi
        $statusBreakdown = $chartData->toArray();

        // Departments list (id => name)
        $departments = Department::pluck('name', 'id')->toArray();

        // Build per-department status matrix, excluding statuses that contain 'draft'
        $deptStatusMatrix = [];
        foreach ($departments as $deptId => $deptName) {
            $rows = AuditFinding::selectRaw('tm_statuses.name as status, COUNT(*) as total')
                ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
                ->where('tt_audit_findings.department_id', $deptId)
                ->groupBy('tm_statuses.name')
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

        return view('contents.ftpp-dashboard', compact(
            'totalFtpp',
            'chartData',
            'deptLabels',
            'deptTotals',
            'recentFindings',
            'statusBreakdown',
            'deptStatusMatrix'
        ));
    }
}
