<?php

namespace App\Http\Controllers;

use App\Models\DocumentFile;
use App\Models\Department;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentActionNotification;
use App\Services\DocSpaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditorController extends Controller
{
    private function canAccessByDepartment(DocumentFile $file): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $roleNames = $user->roles->pluck('name')->map(fn($role) => strtolower(trim((string) $role)));
        $isAdmin = $roleNames->contains(fn($role) => in_array($role, ['admin', 'super admin'], true));
        if ($isAdmin) {
            return true;
        }

        $mapping = $file->mapping()->first();
        if (!$mapping || !$mapping->department_id) {
            // If no department-bound mapping exists, keep access allowed.
            return true;
        }

        return $user->departments()->where('tm_departments.id', $mapping->department_id)->exists();
    }

    // Endpoint: /editor/{file}/onlyoffice-url
    public function onlyofficeUrl(DocumentFile $file)
    {
        abort_if(!$file->is_active, 404);
        abort_unless($this->canAccessByDepartment($file), 403, 'Unauthorized department access.');

        if (!$file->docspace_file_id) {
            try {
                $result = $this->docSpace->uploadFile(
                    $file->file_path,
                    $file->display_name
                );
                $file->update([
                    'docspace_file_id'   => $result['file_id'],
                    'docspace_folder_id' => $result['folder_id'],
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal upload ke DocSpace: ' . $e->getMessage()], 500);
            }
        }
        $docspaceUrl  = rtrim(config('onlyoffice.docspace_url'), '/');
        $docEditorUrl = "{$docspaceUrl}/doceditor?fileId={$file->docspace_file_id}&editorType=desktop&editorGoBack=false&action=view";
        return response()->json(['url' => $docEditorUrl]);
    }
    public function __construct(protected DocSpaceService $docSpace) {}

    public function index(Request $request)
    {
        $search = $request->input('search');

        $files = DocumentFile::active()
            // Hanya ambil file yang terhubung ke mapping yang belum ditandai untuk dihapus
            // dan terkait ke document dengan type = 'review'
            ->whereHas('mapping', fn($q) => $q->whereNull('marked_for_deletion_at')
                ->whereHas('document', fn($q) => $q->where('type', 'review')))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                        $q->where('original_name', 'like', "%{$search}%")
                        ->orWhere('file_path', 'like', "%{$search}%");
                })
                ->orWhereHas('mapping', function ($m) use ($search) {
                    $m->where(function ($m) use ($search) {
                        $m->where('document_number', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    })->orWhereHas('document', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })->orWhereHas('department', function ($dep) use ($search) {
                        $dep->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('onlyoffice.index', compact('files', 'search'));
    }

    private function getPlantFromMapping($mapping)
    {
        // Prioritize explicit mapping->plant. If it's 'all' treat as 'Others'.
        $mappingPlant = strtolower(trim($mapping->plant ?? ''));
        if ($mappingPlant === 'all') {
            return 'Others';
        }

        // Ambil plant dari partNumber pertama yang ada, kalau nggak ada ambil dari productModel
        $pnPlant = $mapping->partNumber->first()?->plant;
        $pmPlant = $mapping->productModel->first()?->plant;

        $plant = $pnPlant ?? $pmPlant;

        if (!$plant) return 'Others';

        // Normalize to ucfirst format used in tabs (Body/Unit/Electric)
        return ucfirst(strtolower(trim($plant)));
    }

    public function editor(DocumentFile $file)
    {
        abort_if(!$file->is_active, 404);
        abort_unless($this->canAccessByDepartment($file), 403, 'Unauthorized department access.');

        if (!$file->docspace_file_id) {
            try {
                $result = $this->docSpace->uploadFile(
                    $file->file_path,
                    $file->display_name
                );
                $file->update([
                    'docspace_file_id'   => $result['file_id'],
                    'docspace_folder_id' => $result['folder_id'],
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal upload ke DocSpace: ' . $e->getMessage());
            }
        }


        $docspaceUrl  = rtrim(config('onlyoffice.docspace_url'), '/');
        // Cek apakah request preview (readonly)
        $isViewOnly = request()->query('view') == '1';
        $docEditorUrl = "{$docspaceUrl}/doceditor?fileId={$file->docspace_file_id}&editorType=desktop&editorGoBack=false";
        if ($isViewOnly) {
            $docEditorUrl .= "&action=view";
        }

        try {
            $token    = $this->docSpace->getToken();
            $loginUrl = "{$docspaceUrl}/login?token=" . urlencode($token);
        } catch (\Exception $e) {
            $token    = null;
            $loginUrl = null;
        }

        $allDepartments = Department::orderBy('name')->get();

        return view('onlyoffice.editor', compact('file', 'docEditorUrl', 'loginUrl', 'docspaceUrl', 'allDepartments'));
    }

    public function authToken()
    {
        try {
            $token = $this->docSpace->getToken();
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncStatus(DocumentFile $file)
    {
        abort_if(!$file->docspace_file_id, 404, 'File belum di-upload ke DocSpace');

        try {
            $info = $this->docSpace->getFileInfo($file->docspace_file_id);

            $remoteRaw = $info['updated']
                ?? $info['updatedOn']
                ?? $info['modified']
                ?? $info['modifiedOn']
                ?? $info['createOn']
                ?? null;

            if (!$remoteRaw) {
                return response()->json([
                    'success' => true,
                    'hasChanges' => false,
                    'remoteUpdatedAt' => null,
                    'localUpdatedAt' => optional($file->updated_at)->toIso8601String(),
                    'reason' => 'remote_updated_at_missing',
                ]);
            }

            $remoteUpdatedAt = \Illuminate\Support\Carbon::parse($remoteRaw);
            $localUpdatedAt = $file->updated_at;

            // Add small tolerance to avoid flicker due to second-level timestamp precision.
            $hasChanges = $localUpdatedAt
                ? $remoteUpdatedAt->gt($localUpdatedAt->copy()->addSeconds(2))
                : true;

            return response()->json([
                'success' => true,
                'hasChanges' => $hasChanges,
                'remoteUpdatedAt' => $remoteUpdatedAt->toIso8601String(),
                'localUpdatedAt' => optional($localUpdatedAt)->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'hasChanges' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sync(\Illuminate\Http\Request $request, DocumentFile $file)
    {
        abort_if(!$file->docspace_file_id, 404, 'File belum di-upload ke DocSpace');

        try {
            $this->docSpace->downloadAndSave($file->docspace_file_id, $file->file_path);
            $file->touch();
            // Jika file terkait ke sebuah mapping, set status mapping menjadi "Need Review"
            if ($file->document_mapping_id) {
                $mapping = $file->mapping()->first();
                if ($mapping) {
                    /** @var \App\Models\User|null $uploader */
                    $uploader = Auth::user();

                    $requestedDepartmentIds = collect((array) $request->input('department_ids', []))
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->unique()
                        ->values();

                    $allowedDepartmentIds = Department::query()
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->values();

                    $targetDepartmentIds = $requestedDepartmentIds
                        ->intersect($allowedDepartmentIds)
                        ->values();

                    if ($targetDepartmentIds->isEmpty() && $mapping->department_id) {
                        $targetDepartmentIds = collect([(int) $mapping->department_id]);
                    }

                    $mappingPayload = [
                        // Important: keep Updated By in main Document Review in sync with online edits.
                        'user_id' => Auth::id(),
                        'review_notified_at' => null,
                    ];

                    // Simpan catatan revisi: if the frontend submitted the `notes` field
                    // (even if empty) update the mapping. Normalize empty HTML/blank to null
                    // so old notes are cleared when user removes them in the modal.
                    if ($request->exists('notes')) {
                        $notes = trim($request->input('notes'));
                        if ($notes === '' || $notes === '<p><br></p>') {
                            $notes = null;
                        }
                        $mappingPayload['notes'] = $notes;
                    }

                    $isLeaderOfMappingDepartment = $uploader
                        && $mapping->department_id
                        && $uploader->isLeaderOfDepartment($mapping->department_id);

                    $isSupervisorReviewInSameDepartment = $uploader
                        && $mapping->department_id
                        && $uploader->roles()->where('name', 'Supervisor')->exists()
                        && $uploader->departments()->where('tm_departments.id', $mapping->department_id)->exists()
                        && strtolower(trim((string) optional($mapping->status)->name)) === 'need check by supervisor';

                    $targetStatusName = 'Need Review';
                    if ($isLeaderOfMappingDepartment) {
                        $targetStatusName = 'Need Check by Supervisor';
                    } elseif ($isSupervisorReviewInSameDepartment) {
                        // Supervisor reviewing in editor: keep status as "Need Check by Supervisor"
                        // Approval will happen in Approval Queue page with auto-sync
                        $targetStatusName = 'Need Check by Supervisor';
                    }

                    $targetStatus = Status::where('name', $targetStatusName)->first();
                    if (!$targetStatus && $targetStatusName === 'Need Check by Supervisor') {
                        // Fallback to existing flow if new status is not seeded yet.
                        $targetStatus = Status::where('name', 'Need Review')->first();
                    }

                    if ($targetStatus && $mapping->status_id !== $targetStatus->id) {
                        $mappingPayload['status_id'] = $targetStatus->id;
                    }

                    $previousStatusId = (int) $mapping->status_id;
                    $mapping->update($mappingPayload);

                    $statusChangedToNeedCheckBySupervisor = isset($mappingPayload['status_id'])
                        && (int) $mappingPayload['status_id'] !== $previousStatusId
                        && strtolower(trim((string) optional($targetStatus)->name)) === 'need check by supervisor';

                    // Leader edit-online revision that changes status to Need Check by Supervisor:
                    // notify supervisors in the same department (not admins).
                    if ($isLeaderOfMappingDepartment && $statusChangedToNeedCheckBySupervisor) {
                        $products = $mapping->product->pluck('code')->filter();
                        if ($products->isEmpty()) {
                            $products = $mapping->partNumber->map(fn($pn) => $pn->product?->code)->filter();
                        }

                        $models = $mapping->productModel->pluck('name')->filter();
                        if ($models->isEmpty()) {
                            $models = $mapping->partNumber->map(fn($pn) => $pn->productModel?->name)->filter();
                        }

                        $processes = $mapping->process->pluck('code')->filter();
                        if ($processes->isEmpty()) {
                            $processes = $mapping->partNumber->map(fn($pn) => $pn->process?->code)->filter();
                        }

                        $partNumbers = $mapping->partNumber->pluck('part_number')->filter();

                        $emailDetails = [
                            'model' => $models->unique()->values()->join(', '),
                            'product' => $products->unique()->values()->join(', '),
                            'process' => $processes->unique()->values()->join(', '),
                            'part_number' => $partNumbers->unique()->values()->join(', '),
                            'revision_notes' => trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($mapping->notes ?? '')))),
                        ];

                        $supervisors = User::whereHas(
                            'roles',
                            fn($q) => $q->whereRaw('LOWER(name) = ?', ['supervisor'])
                        )
                            ->whereHas('departments', fn($q) => $q->whereIn('tm_departments.id', $targetDepartmentIds->all()))
                            ->where('id', '!=', $uploader?->id)
                            ->get();

                        foreach ($supervisors as $supervisor) {
                            $supervisor->notify(new DocumentActionNotification(
                                action: 'revised',
                                byUser: $uploader->name,
                                documentNumber: $mapping->document_number,
                                documentName: null,
                                url: route('document-review.approval'),
                                departmentName: $mapping->department?->name,
                                details: $emailDetails
                            ));
                        }
                    } else {
                        // Keep existing notification behavior for other non-admin online revisions.
                        $userRole = strtolower($uploader->roles->pluck('name')->first() ?? '');

                        if (!in_array($userRole, ['admin', 'super admin'], true)) {
                            $admins = User::whereHas(
                                'roles',
                                fn($q) => $q->whereIn('name', ['Admin', 'Super Admin'])
                            )->get();

                            foreach ($admins as $admin) {
                                $admin->notify(new DocumentActionNotification(
                                    action: 'revised',
                                    byUser: $uploader->name,
                                    documentNumber: $mapping->document_number,
                                    documentName: null,
                                    url: route('document-review.approval', [
                                        'plant' => $this->getPlantFromMapping($mapping),
                                        'docCode' => base64_encode($mapping->document->code ?? ''),
                                    ]),
                                    departmentName: $mapping->department?->name
                                ));
                            }
                        }
                    }

                }
            }

            return response()->json(['success' => true, 'message' => 'File berhasil disinkronkan ke Laravel']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reupload(\Illuminate\Http\Request $request, DocumentFile $file)
    {
        abort_if(!$file->is_active, 404);

        try {
            // If a replacement file is provided, store it and use it for upload.
            if (! $request->hasFile('replacement_file')) {
                return response()->json(['success' => false, 'message' => 'No replacement file provided'], 422);
            }

            $uploaded = $request->file('replacement_file');

            $request->validate([
                'replacement_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            ]);

            $folder = 'document-reviews';
            $baseName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $uploaded->getClientOriginalExtension();
            $ts = now()->format('Ymd_His');
            $filename = "{$baseName}_reupload_{$ts}.{$ext}";

            $localPath = $uploaded->storeAs($folder, $filename, 'public');

            // Create new DB record for the newly uploaded replacement
            // NOTE: new replacement should NOT be marked as pending approval
            // so that approveWithDates() will only archive the old pending file(s).
            $newFile = DocumentFile::create([
                'document_mapping_id' => $file->document_mapping_id,
                'file_path' => $localPath,
                'original_name' => $uploaded->getClientOriginalName(),
                'is_active' => true,
                'pending_approval' => false,
            ]);

            // Upload stored file to DocSpace
            $result = $this->docSpace->uploadFile($localPath, $uploaded->getClientOriginalName());

            // Persist DocSpace ids on the new file
            $newFile->update([
                'docspace_file_id' => $result['file_id'],
                'docspace_folder_id' => $result['folder_id'] ?? $newFile->docspace_folder_id,
            ]);

            // Update old file to reference this new pending replacement
            $file->update([
                'replaced_by_id' => $newFile->id,
                'pending_approval' => true,
            ]);

            // Jika file terkait mapping, set status mapping ke "Need Review"
            if ($file->document_mapping_id) {
                $mapping = $file->mapping()->first();
                if ($mapping) {
                    $mappingPayload = [
                        // Keep Updated By consistent across editor actions (sync + reupload).
                        'user_id' => Auth::id(),
                        'review_notified_at' => null,
                    ];

                    $needReview = Status::where('name', 'Need Review')->first();
                    if ($needReview && $mapping->status_id !== $needReview->id) {
                        $mappingPayload['status_id'] = $needReview->id;
                    }

                    $mapping->update($mappingPayload);
                     // --- SEND NOTIFICATION TO ADMINS ---
        $uploader = Auth::user();
        $userRole = strtolower($uploader->roles->pluck('name')->first() ?? '');

        if (!in_array($userRole, ['admin', 'super admin'])) {

            $admins = User::whereHas(
                'roles',
                fn($q) =>
                $q->whereIn('name', ['Admin', 'Super Admin'])
            )->get();

            foreach ($admins as $admin) {

                $admin->notify(new DocumentActionNotification(
                    action: 'revised',
                    byUser: $uploader->name,
                    documentNumber: $mapping->document_number, // ← PAKAI DOCUMENT NUMBER 👍
                    documentName: null,                        // ← DI REVIEW TIDAK DIPAKAI
                    url: route('document-review.approval', [
                        'plant' => $this->getPlantFromMapping($mapping),
                        'docCode' => base64_encode($mapping->document->code ?? ''),
                    ]),
                    departmentName: $mapping->department?->name
                ));
            }
        }

                }
            }

            return response()->json(['success' => true, 'file_id' => $result['file_id'], 'new_file_id' => $newFile->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
