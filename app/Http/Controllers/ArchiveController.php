<?php

namespace App\Http\Controllers;

use App\Models\DocumentMapping;
use App\Models\Department;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @var \Illuminate\Pagination\LengthAwarePaginator $controlDocuments */

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        // 1. CONTROL ARCHIVE
        $controlQuery = DocumentFile::query()
            ->with(['mapping.document', 'mapping.department']) // Load relasi ke atas
            ->where('is_active', false)
            ->where('marked_for_deletion_at', '>', now())
            ->where('pending_approval', false)
            ->whereHas('mapping.document', fn($q) => $q->where('type', 'control'));

        // Filter department (non admin)
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();

            // BERUBAH: Filter via relasi mapping
            $controlQuery->whereHas('mapping', function ($q) use ($userDeptIds) {
                $q->whereIn('department_id', $userDeptIds);
            });
        }

        // Search filter
        if ($request->filled('search_control')) {
            $search = $request->search_control;

            $controlQuery->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%$search%") // Cari nama file
                    ->orWhereHas('mapping.document', fn($q2) => $q2->where('name', 'like', "%$search%")) // Cari nama dokumen
                    ->orWhereHas('mapping.department', fn($q2) => $q2->where('name', 'like', "%$search%")); // Cari department
            });
        }

        // BERUBAH: Paginate sekarang menghitung total file (misal: 12 item)
        $controlDocuments = $controlQuery->paginate(10, ['*'], 'page_control');

        // 2. REVIEW ARCHIVE
        $reviewQuery = DocumentFile::query()
            ->with(['mapping.document', 'mapping.department'])
            ->where('is_active', false)
            ->where('marked_for_deletion_at', '>', now()) // tambahkan ini
            ->whereHas('mapping.document', fn($q) => $q->where('type', 'review'));


        $reviewDocuments = $reviewQuery->paginate(10, ['*'], 'page_review');


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

        // CONTROL ARCHIVE SEARCH
        $controlQuery = DocumentFile::query()
            ->with(['mapping.document', 'mapping.department'])
            ->where('is_active', false)
            ->where('marked_for_deletion_at', '>', now())
            ->whereHas('mapping.document', fn($q) => $q->where('type', 'control'));

        // Filter department (non admin)
        if (!in_array(strtolower(Auth::user()->roles->pluck('name')->first() ?? ''), ['admin', 'super admin'])) {
            $userDeptIds = Auth::user()->departments->pluck('id')->toArray();
            $controlQuery->whereHas('mapping', fn($q) => $q->whereIn('department_id', $userDeptIds));
        }

        // Search filter
        if (!empty($query)) {
            $controlQuery->where(function ($q) use ($query) {
                $q->where('original_name', 'like', "%$query%")
                    ->orWhereHas('mapping.document', fn($q2) => $q2->where('name', 'like', "%$query%"))
                    ->orWhereHas('mapping.department', fn($q2) => $q2->where('name', 'like', "%$query%"));
            });
        }

        $controlDocuments = $controlQuery->paginate(10, ['*'], 'page_control');

        // REVIEW ARCHIVE SEARCH
        // 2. REVIEW SEARCH (FIXED)
        $reviewQuery = DocumentFile::query()
            ->with(['mapping.document', 'mapping.department'])
            ->where('is_active', false)
            ->whereHas('mapping.document', fn($q) => $q->where('type', 'review'));

        if (!empty($query)) {
            $reviewQuery->where(function ($q) use ($query) {
                $q->where('original_name', 'like', "%$query%") // Cari Nama File
                    // Cari Document Number (Review biasanya pakai doc number)
                    ->orWhereHas('mapping', fn($q2) => $q2->where('document_number', 'like', "%$query%"))
                    // Cari Dept Name
                    ->orWhereHas('mapping.department', fn($q2) => $q2->where('name', 'like', "%$query%"));
            });
        }

        // Paginasi otomatis, tidak perlu LengthAwarePaginator manual lagi
        $reviewDocuments = $reviewQuery->paginate(10, ['*'], 'page_review');


        return response()->json([
            'success' => true,
            'control' => [
                'html' => view('contents.archive.partials.control-archive', ['controlDocuments' => $controlDocuments])->render(),
            ],
            'review' => [
                'html' => view('contents.archive.partials.review-archive', ['reviewDocuments' => $reviewDocuments])->render(),
            ],
        ]);
    }
}
