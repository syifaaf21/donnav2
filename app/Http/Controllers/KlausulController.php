<?php

namespace App\Http\Controllers;

use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\SubKlausul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KlausulController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $klausuls = Klausul::with('headKlausuls.subKlausuls')->orderBy('created_at', 'asc')->get();
        return view('contents.master.ftpp.clause.index', compact('klausuls'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // If Add Head Klausul modal is used
        if ($request->has('head_name')) {
            $request->validate([
                'klausul_id' => 'required|exists:tm_klausuls,id',
                'head_name' => 'required|string|max:255',
                'head_code' => 'nullable|string|max:100',
                'sub_names' => 'array',
                'sub_codes' => 'array',
            ]);
            DB::beginTransaction();
            try {
                $head = HeadKlausul::create([
                    'klausul_id' => $request->input('klausul_id'),
                    'name' => $request->input('head_name'),
                    'code' => $request->input('head_code'),
                ]);
                $subNames = $request->input('sub_names', []);
                $subCodes = $request->input('sub_codes', []);
                foreach ($subNames as $i => $name) {
                    SubKlausul::create([
                        'head_klausul_id' => $head->id,
                        'name' => $name,
                        'code' => $subCodes[$i] ?? null,
                    ]);
                }
                DB::commit();
                return redirect()->back()->with('success', 'Head Klausul berhasil ditambahkan.');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Klausul store error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Save failed: ' . $e->getMessage());
            }
        }

        // If Add Klausul (main) modal is used
        $request->validate([
            'audit_type_id' => 'required|exists:tm_audit_types,id',
            'klausul_name' => 'required|string|max:255',
            'heads' => 'required|array|min:1',
            'heads.*.name' => 'required|string|max:255',
            'heads.*.code' => 'nullable|string|max:100',
            'heads.*.subs' => 'nullable|array',
            'heads.*.subs.*.name' => 'required|string|max:255',
            'heads.*.subs.*.code' => 'nullable|string|max:100',
        ]);
        DB::beginTransaction();
        try {
            // 1) Create Klausul
            $klausul = Klausul::create([
                'name' => $request->input('klausul_name'),
                'audit_type_id' => $request->input('audit_type_id')
            ]);

            // 2) Create Head Klausuls with Subs
            foreach ($request->input('heads', []) as $headData) {
                $head = HeadKlausul::create([
                    'klausul_id' => $klausul->id,
                    'name' => $headData['name'],
                    'code' => $headData['code'] ?? null,
                ]);

                // 3) Create Sub Klausuls for this head
                if (isset($headData['subs']) && is_array($headData['subs'])) {
                    foreach ($headData['subs'] as $subData) {
                        SubKlausul::create([
                            'head_klausul_id' => $head->id,
                            'name' => $subData['name'],
                            'code' => $subData['code'] ?? null,
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Klausul with multiple heads and subs created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Klausul store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update main klausul (the klausul itself, not head klausul)
     */
    public function updateMain(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'audit_type_id' => 'required|exists:tm_audit_types,id',
                'name' => 'required|string|max:255',
            ]);

            $klausul = Klausul::findOrFail($id);
            $klausul->update([
                'name' => $request->name,
                'audit_type_id' => $request->audit_type_id,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Main Klausul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating main klausul: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memperbarui Main Klausul: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'head_name' => 'required|string|max:255',
                'head_code' => 'nullable|string|max:100',
                'sub_names' => 'array',
                'sub_codes' => 'array',
            ]);

            $head = HeadKlausul::find($id);
            if (!$head) {
                // Tambah head baru jika tidak ada (fallback, seharusnya edit head selalu ada)
                $klausulId = $request->input('klausul_id');
                if (!$klausulId) {
                    throw new \Exception('Klausul ID is required to add new Head Klausul.');
                }
                $head = HeadKlausul::create([
                    'klausul_id' => $klausulId,
                    'name' => $request->head_name,
                    'code' => $request->head_code,
                ]);
            } else {
                $head->update([
                    'name' => $request->head_name,
                    'code' => $request->head_code !== null ? $request->head_code : '',
                ]);
                // Hapus semua sub klausul lama (agar tidak ganda)
                SubKlausul::where('head_klausul_id', $head->id)->delete();
            }

            // Validasi sub klausul (jika ada)
            $subNames = $request->input('sub_names', []);
            $subCodes = $request->input('sub_codes', []);
            foreach ($subNames as $i => $name) {
                if (trim($name) !== '') {
                    SubKlausul::create([
                        'head_klausul_id' => $head->id,
                        'name' => $name,
                        'code' => $subCodes[$i] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Head Klausul & Sub Klausul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating klausul: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui/menambah Head Klausul: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Pastikan head klausul ada
            $head = HeadKlausul::findOrFail($id);

            // Hapus semua sub klausul yang terhubung
            SubKlausul::where('head_klausul_id', $id)->delete();

            // Hapus head klausulnya
            $head->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Head Klausul dan semua Sub Klausul berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting klausul: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus Klausul: ' . $e->getMessage());
        }
    }

    /**
     * Delete main klausul (the klausul itself, including all head klausul and sub klausul)
     */
    public function destroyMain($id)
    {
        DB::beginTransaction();

        try {
            $klausul = Klausul::findOrFail($id);

            // Hapus semua head klausul yang terhubung
            $headKlausuls = HeadKlausul::where('klausul_id', $id)->get();
            foreach ($headKlausuls as $head) {
                // Hapus semua sub klausul yang terhubung dengan head
                SubKlausul::where('head_klausul_id', $head->id)->delete();
                // Hapus head klausulnya
                $head->delete();
            }

            // Hapus klausul utama
            $klausul->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Main Klausul dan semua data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting main klausul: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus Main Klausul: ' . $e->getMessage());
        }
    }
}
