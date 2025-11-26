<?php

namespace App\Http\Controllers;

use App\Models\DocumentMapping;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        // ================================
        // 1. CONTROL ARCHIVE (new logic)
        // ================================
        $controlQuery = DocumentMapping::with([
            'document',
            'department',
            'status',
            'files' => function ($q) {
                $q->where('is_active', false); // ambil semua file non aktif
            }
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas(
                'files',
                fn($q2) =>
                $q2->where('is_active', false)
                    ->where('marked_for_deletion_at', '>', now())
            );

        // Filter department (non admin)
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();

            $controlQuery->whereHas(
                'department',
                fn($q) =>
                $q->whereIn('tm_departments.id', $userDeptIds)
            );
        }

        // Search filter
        if ($request->filled('search_control')) {
            $search = $request->search_control;

            $controlQuery->where(function ($q) use ($search) {
                $q->whereHas('document', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('department', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('files', function ($q2) use ($search) {
                        $q2->where('original_name', 'like', "%$search%")
                            ->where('is_active', false)
                            ->where('marked_for_deletion_at', '>', now());
                    });
            });
        }

        $controlDocuments = $controlQuery->paginate(10, ['*'], 'page_control');


        // ================================
        // 2. REVIEW ARCHIVE (same as before)
        // ================================
        $reviewCollection = DocumentMapping::with([
            'files' => fn($q) => $q->where('is_active', 0),
            'document',
            'department'
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->get()
            ->filter(fn($mapping) => $mapping->files->isNotEmpty());

        // Manual pagination
        $page = $request->input('page_review', 1);
        $perPage = 10;

        $reviewDocuments = new \Illuminate\Pagination\LengthAwarePaginator(
            $reviewCollection->forPage($page, $perPage),
            $reviewCollection->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'pageName' => 'page_review'
            ]
        );

        // Departments for dropdown
        $departments = Department::all();

        return view('contents.archive.index', compact(
            'controlDocuments',
            'reviewDocuments',
            'departments'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        // ================================
        // CONTROL ARCHIVE SEARCH
        // ================================
        $controlQuery = DocumentMapping::with([
            'document',
            'department',
            'status',
            'files' => function ($q) {
                $q->where('is_active', false);
            }
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'control'))
            ->whereHas(
                'files',
                fn($q2) =>
                $q2->where('is_active', false)
                    ->where('marked_for_deletion_at', '>', now())
            );

        // Filter department (non admin)
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();

            $controlQuery->whereHas(
                'department',
                fn($q) =>
                $q->whereIn('tm_departments.id', $userDeptIds)
            );
        }

        // Search filter for control
        if (!empty($query)) {
            $controlQuery->where(function ($q) use ($query) {
                $q->whereHas('document', fn($q2) => $q2->where('name', 'like', "%$query%"))
                    ->orWhereHas('department', fn($q2) => $q2->where('name', 'like', "%$query%"))
                    ->orWhereHas('files', function ($q2) use ($query) {
                        $q2->where('original_name', 'like', "%$query%")
                            ->where('is_active', false)
                            ->where('marked_for_deletion_at', '>', now());
                    });
            });
        }

        $controlDocuments = $controlQuery->paginate(10);

        // ================================
        // REVIEW ARCHIVE SEARCH
        // ================================
        $reviewCollection = DocumentMapping::with([
            'files' => fn($q) => $q->where('is_active', 0),
            'document',
            'department'
        ])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->get()
            ->filter(fn($mapping) => $mapping->files->isNotEmpty());

        // Search filter for review
        if (!empty($query)) {
            $reviewCollection = $reviewCollection->filter(function ($mapping) use ($query) {
                $query_lower = strtolower($query);

                return
                    str_contains(strtolower($mapping->document_number ?? ''), $query_lower)
                    || str_contains(strtolower($mapping->department?->name ?? ''), $query_lower)
                    || $mapping->files->contains(fn($f) => str_contains(strtolower($f->original_name ?? ''), $query_lower));
            });
        }

        // Manual pagination for review
        $page = $request->input('page_review', 1);
        $perPage = 10;

        $reviewDocuments = new \Illuminate\Pagination\LengthAwarePaginator(
            $reviewCollection->forPage($page, $perPage),
            $reviewCollection->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'pageName' => 'page_review'
            ]
        );

        return response()->json([
            'success' => true,
            'control' => [
                'html' => view('contents.archive.partials.control-archive', [
                    'controlDocuments' => $controlDocuments,
                ])->render(),
            ],
            'review' => [
                'html' => view('contents.archive.partials.review-archive', [
                    'reviewDocuments' => $reviewDocuments,
                ])->render(),
            ],
        ]);
    }
}
