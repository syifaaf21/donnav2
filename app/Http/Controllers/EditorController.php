<?php

namespace App\Http\Controllers;

use App\Models\DocumentFile;
use App\Services\DocSpaceService;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    public function __construct(protected DocSpaceService $docSpace) {}

    public function index(Request $request)
    {
        $search = $request->input('search');

        $files = DocumentFile::active()
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
            return response()->json(['success' => true, 'message' => 'File berhasil disinkronkan ke Laravel']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reupload(DocumentFile $file)
    {
        abort_if(!$file->is_active, 404);

        try {
            if ($file->docspace_file_id) {
                $this->docSpace->deleteFile($file->docspace_file_id);
            }
            $result = $this->docSpace->uploadFile($file->file_path, $file->display_name);
            $file->update([
                'docspace_file_id'   => $result['file_id'],
                'docspace_folder_id' => $result['folder_id'],
            ]);
            return response()->json(['success' => true, 'file_id' => $result['file_id']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
