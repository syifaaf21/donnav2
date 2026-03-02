<?php

namespace App\Http\Controllers;

use App\Models\DocumentFile;
use App\Models\Status;
use App\Services\DocSpaceService;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    public function __construct(protected DocSpaceService $docSpace) {}

    public function index(Request $request)
    {
        $search = $request->input('search');

        $files = DocumentFile::active()
            // Hanya ambil file yang terhubung ke mapping yang belum ditandai untuk dihapus
            // dan terkait ke document dengan type = 'review'
            ->whereHas('mapping', fn($q) => $q->whereNull('marked_for_deletion_at')
                ->whereHas('document', fn($q) => $q->where('type', 'review')))
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                    ->orWhere('file_path', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('onlyoffice.index', compact('files', 'search'));
    }

    public function editor(DocumentFile $file)
    {
        abort_if(!$file->is_active, 404);

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
        $docEditorUrl = "{$docspaceUrl}/doceditor?fileId={$file->docspace_file_id}&editorType=desktop&editorGoBack=false";

        try {
            $token    = $this->docSpace->getToken();
            $loginUrl = "{$docspaceUrl}/login?token=" . urlencode($token);
        } catch (\Exception $e) {
            $token    = null;
            $loginUrl = null;
        }

        return view('onlyoffice.editor', compact('file', 'docEditorUrl', 'loginUrl', 'docspaceUrl'));
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

    public function sync(DocumentFile $file)
    {
        abort_if(!$file->docspace_file_id, 404, 'File belum di-upload ke DocSpace');

        try {
            $this->docSpace->downloadAndSave($file->docspace_file_id, $file->file_path);
            $file->touch();
            // Jika file terkait ke sebuah mapping, set status mapping menjadi "Need Review"
            if ($file->document_mapping_id) {
                $mapping = $file->mapping()->first();
                if ($mapping) {
                    $needReview = Status::where('name', 'Need Review')->first();
                    if ($needReview && $mapping->status_id !== $needReview->id) {
                        $mapping->update([
                            'status_id' => $needReview->id,
                            'review_notified_at' => null,
                        ]);
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
                    $needReview = Status::where('name', 'Need Review')->first();
                    if ($needReview && $mapping->status_id !== $needReview->id) {
                        $mapping->update([
                            'status_id' => $needReview->id,
                            'review_notified_at' => null,
                        ]);
                    }
                }
            }

            return response()->json(['success' => true, 'file_id' => $result['file_id'], 'new_file_id' => $newFile->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
