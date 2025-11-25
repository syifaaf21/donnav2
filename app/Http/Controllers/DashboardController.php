<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use App\Models\DocumentMapping;
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

        // Semua department untuk Control Documents
        $departments = Department::pluck('name', 'id')->toArray();

        // Hanya department dengan plant Body, Unit, Electric untuk Review Documents
        $departmentsReview = Department::whereIn('plant', ['Body', 'Unit', 'Electric'])
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


        $obsoleteDocuments = DocumentMapping::whereHas('status', function ($q) {
            $q->where('name', 'Obsolete');
        })->get();


        $chartData = AuditFinding::selectRaw('tm_statuses.name as status, COUNT(*) as total')
            ->join('tm_statuses', 'tm_statuses.id', '=', 'tt_audit_findings.status_id')
            ->whereIn('tm_statuses.name', [
                'Open',
                'Submitted',
                'Checked by Dept Head',
                'Approved by Auditor',
                'Need Revision',
                'Close'
            ])
            ->groupBy('tm_statuses.name')
            ->pluck('total', 'status');

        // Data tambahan per department untuk tooltip
        $controlExtraData = [];
        foreach ($departments as $id => $name) {
            $controlExtraData[$id] = [
                'obsolete'   => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Obsolete'))
                    ->count(),
                'needReview' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Need Review'))
                    ->count(),
                'uncomplete' => DocumentMapping::where('department_id', $id)
                    ->whereHas('status', fn($q) => $q->where('name', 'Uncomplete'))
                    ->count(),
            ];
        }

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
        ));
    }
}
