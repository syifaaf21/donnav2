<?php

namespace App\Http\Controllers;

use App\Models\DocumentMapping;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecycleBinController extends Controller
{
    protected function authorizeSuper()
    {
        $role = Auth::user()->roles->pluck('name')->first() ?? '';
        if (strtolower($role) !== 'super admin') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuper();

        $resource = $request->input('resource', 'all');

        if ($resource === 'ftpp') {
            // AuditFinding uses SoftDeletes on `marked_for_deletion_at` — fetch trashed findings
            $query = \App\Models\AuditFinding::with(['auditor', 'department', 'file'])
                ->onlyTrashed()
                ->orderBy('marked_for_deletion_at', 'desc');
            $docs = $query->paginate(20)->appends($request->query());

            return view('contents.recycle.index', compact('docs', 'resource'));
        }

        if ($resource === 'mapping') {
            $query = DocumentMapping::with(['document', 'department', 'files'])
                ->whereNotNull('marked_for_deletion_at')
                ->orderBy('marked_for_deletion_at', 'desc');

            $docs = $query->paginate(20)->appends($request->query());

            return view('contents.recycle.index', compact('docs', 'resource'));
        }

        // resource === 'all' -> merge both types into a unified paginated list
        $mappingItems = DocumentMapping::with(['document', 'department'])
            ->whereNotNull('marked_for_deletion_at')
            ->get()
            ->map(function ($m) {
                return (object) [
                    'id' => $m->id,
                    'resource' => 'mapping',
                    'document' => $m->document ?? null,
                    'document_number' => $m->document_number ?? null,
                    'registration_number' => null,
                    'department' => $m->department ?? null,
                    'marked_for_deletion_at' => $m->marked_for_deletion_at,
                ];
            });

        $ftppItems = \App\Models\AuditFinding::withTrashed()->with(['department'])
            ->onlyTrashed()
            ->get()
            ->map(function ($f) {
                return (object) [
                    'id' => $f->id,
                    'resource' => 'ftpp',
                    'document' => null,
                    'document_number' => null,
                    'registration_number' => $f->registration_number ?? null,
                    'department' => $f->department ?? null,
                    'marked_for_deletion_at' => $f->marked_for_deletion_at,
                ];
            });

        // ensure we work with base Collections (not Eloquent collections) to avoid model-specific methods
        $all = collect($mappingItems)->merge(collect($ftppItems))->sortByDesc('marked_for_deletion_at')->values();

        // manual pagination
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $itemsForCurrentPage = $all->slice(($page - 1) * $perPage, $perPage)->values();
        $docs = new LengthAwarePaginator($itemsForCurrentPage, $all->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('contents.recycle.index', compact('docs', 'resource'));
    }

    public function restore(Request $request, $id)
    {
        $this->authorizeSuper();

        $resource = $request->input('resource', 'mapping');

        if ($resource === 'ftpp') {
            $finding = \App\Models\AuditFinding::withTrashed()->where('id', $id)->firstOrFail();

            // Restoring the finding triggers model restoring cascades (auditeeAction, files, pivots)
            if (method_exists($finding, 'restore')) {
                $finding->restore();
            } else {
                $finding->marked_for_deletion_at = null;
                $finding->save();
            }

            return redirect()->back()->with('success', 'FTPP finding restored successfully.');
        }

        // mapping (document control)
        // include trashed files because DocumentFile uses SoftDeletes (marked_for_deletion_at)
        $mapping = DocumentMapping::with(['document', 'files' => function ($q) {
            $q->withTrashed();
        }])->where('id', $id)->whereNotNull('marked_for_deletion_at')->firstOrFail();

        // restore mapping
        $mapping->update(['marked_for_deletion_at' => null]);

        // restore files (they may be soft-deleted). Prefer using model restore() if available.
        foreach ($mapping->files as $file) {
            if (method_exists($file, 'restore')) {
                $file->restore();
            } else {
                $file->update(['marked_for_deletion_at' => null]);
            }
        }

        // If the parent document was marked for deletion, restore it as well (since mapping is back)
        if ($mapping->document && $mapping->document->marked_for_deletion_at) {
            $mapping->document->update(['marked_for_deletion_at' => null]);
        }

        return redirect()->back()->with('success', 'Item restored successfully.');
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeSuper();

        $resource = $request->input('resource', 'mapping');

        if ($resource === 'ftpp') {
            $finding = \App\Models\AuditFinding::withTrashed()->where('id', $id)->firstOrFail();

            // delete related files (including trashed)
            $files = \App\Models\DocumentFile::withTrashed()->where('audit_finding_id', $finding->id)->get();
            foreach ($files as $file) {
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
                if (method_exists($file, 'forceDelete')) {
                    $file->forceDelete();
                } else {
                    $file->delete();
                }
            }

            // delete auditee action and its children
            $auditee = \App\Models\AuditeeAction::withTrashed()->where('audit_finding_id', $finding->id)->first();
            if ($auditee) {
                \App\Models\WhyCauses::withTrashed()->where('auditee_action_id', $auditee->id)->forceDelete();
                \App\Models\CorrectiveAction::withTrashed()->where('auditee_action_id', $auditee->id)->forceDelete();
                \App\Models\PreventiveAction::withTrashed()->where('auditee_action_id', $auditee->id)->forceDelete();
                \App\Models\DocumentFile::withTrashed()->where('auditee_action_id', $auditee->id)->forceDelete();
                $auditee->forceDelete();
            }

            // delete pivot records
            \App\Models\AuditFindingAuditee::withTrashed()->where('audit_finding_id', $finding->id)->forceDelete();
            \App\Models\AuditFindingSubKlausul::withTrashed()->where('audit_finding_id', $finding->id)->forceDelete();

            // finally force delete the finding
            $finding->forceDelete();

            return redirect()->back()->with('success', 'FTPP finding permanently deleted.');
        }

        // mapping (document control)
        $mapping = DocumentMapping::with(['files' => function ($q) { $q->withTrashed(); }, 'partNumber', 'productModel', 'product', 'process', 'document'])->where('id', $id)->firstOrFail();

        // mark immediate deletion timestamp
        $mapping->update(['marked_for_deletion_at' => now()]);

        // delete files from storage and force delete
        foreach ($mapping->files as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            // force delete record
            if (method_exists($file, 'forceDelete')) {
                $file->forceDelete();
            } else {
                $file->delete();
            }
        }

        // detach pivots
        $mapping->partNumber()->detach();
        $mapping->productModel()->detach();
        $mapping->product()->detach();
        $mapping->process()->detach();

        // delete mapping
        $mapping->delete();

        // If the parent document has no mappings left and was marked for deletion,
        // do NOT remove the master `Document` record automatically. Removing the
        // Document here causes master hierarchy entries to disappear unexpectedly
        // (e.g. when a mapping scheduled the document for deletion). Instead,
        // clear the deletion flag so the master document remains available.
        if ($mapping->document) {
            $remaining = DocumentMapping::where('document_id', $mapping->document->id)->exists();
            if (!$remaining && $mapping->document->marked_for_deletion_at) {
                $mapping->document->update(['marked_for_deletion_at' => null]);
            }
        }

        return redirect()->back()->with('success', 'Item permanently deleted.');
    }
}
