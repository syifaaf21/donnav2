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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'audit_type_id' => 'required|exists:tm_audit_types,id',
            'klausul_id' => 'required',
            'head_klausul_id' => 'required',
            'head_code' => 'nullable|string|max:100',
            'sub_names' => 'array',
            'sub_codes' => 'array',
            'sub_names.*' => 'nullable|string|max:255',
            'sub_codes.*' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // 1) Klausul: if non-numeric -> create new Klausul and set klausul_id to new id
            $klausulId = $request->input('klausul_id');
            if (!is_numeric($klausulId)) {
                $klausul = Klausul::create([
                    'name' => $klausulId,
                    'audit_type_id' => $request->input('audit_type_id')
                ]);
                $klausulId = $klausul->id;
            }

            // 2) Head Klausul: if non-numeric -> create new head with provided head_code
            $headVal = $request->input('head_klausul_id');
            if (!is_numeric($headVal)) {
                $head = HeadKlausul::create([
                    'klausul_id' => $klausulId,
                    'name' => $headVal,
                    'code' => $request->input('head_code'),
                ]);
                $headId = $head->id;
            } else {
                // existing head: optionally update code if user changed it (if you want)
                $headId = (int) $headVal;
                if ($request->filled('head_code')) {
                    HeadKlausul::where('id', $headId)->update(['code' => $request->input('head_code')]);
                }
            }

            // 3) Create sub klausuls (if any)
            $subNames = $request->input('sub_names', []);
            $subCodes = $request->input('sub_codes', []);

            foreach ($subNames as $i => $name) {
                $name = trim($name ?? '');
                if ($name === '')
                    continue;
                $code = isset($subCodes[$i]) ? trim($subCodes[$i]) : null;
                SubKlausul::create([
                    'head_klausul_id' => $headId,
                    'name' => $name,
                    'code' => $code,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Klausul saved.');
        } catch (\Throwable $e) {
            DB::rollBack();
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
            // Validasi dasar
            $request->validate([
                'head_name' => 'required|string|max:255',
                'sub_codes' => 'array',
                'sub_names' => 'array',
            ]);

            // Ambil data Head Klausul
            $head = HeadKlausul::findOrFail($id);
            $head->update([
                'name' => $request->head_name,
            ]);

            // Hapus semua sub klausul lama (agar tidak ganda)
            SubKlausul::where('head_klausul_id', $id)->delete();

            // Simpan ulang sub klausul baru
            if ($request->sub_codes && $request->sub_names) {
                foreach ($request->sub_codes as $index => $code) {
                    $name = $request->sub_names[$index] ?? null;
                    if ($code || $name) {
                        SubKlausul::create([
                            'head_klausul_id' => $id,
                            'code' => $code,
                            'name' => $name,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Klausul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating klausul: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memperbarui klausul: ' . $e->getMessage());
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
}
