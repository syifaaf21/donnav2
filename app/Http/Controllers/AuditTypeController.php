<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\SubKlausul;
use Illuminate\Http\Request;

class AuditTypeController extends Controller
{

    public function show($id)
    {
        $audit = Audit::with('subAudit')->findOrFail($id);

        return response()->json([
            'id' => $audit->id,
            'name' => $audit->name,
            'sub_audit' => $audit->subAudit->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->name
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $audit = Audit::create(['name' => $request->name]);

        if ($request->has('sub_audit')) {
            foreach ($request->sub_audit as $sub) {
                if (!empty($sub)) {
                    $audit->subAudit()->create(['name' => $sub]);
                }
            }
        }

        return redirect()->back()->with('success', 'Audit type added successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $audit = Audit::findOrFail($id);
        $audit->update(['name' => $request->name]);

        // 🔁 Hapus semua sub audit lama terlebih dahulu
        $audit->subAudit()->delete();

        // 🧩 Gabungkan semua sub audit baru dari existing dan tambahan baru
        $allSubAudits = [];

        // Ambil sub audit existing (yang diubah)
        if ($request->has('sub_audit_existing')) {
            foreach ($request->sub_audit_existing as $subName) {
                if (!empty($subName)) {
                    $allSubAudits[] = $subName;
                }
            }
        }

        // Ambil sub audit baru
        if ($request->has('sub_audit')) {
            foreach ($request->sub_audit as $newSub) {
                if (!empty($newSub)) {
                    $allSubAudits[] = $newSub;
                }
            }
        }

        // 💾 Simpan ulang semua sub audit
        foreach ($allSubAudits as $name) {
            $audit->subAudit()->create(['name' => $name]);
        }

        return redirect()->back()->with('success', 'Audit Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $audit = Audit::with('subAudit')->findOrFail($id);

            // Hapus sub audit terlebih dahulu
            foreach ($audit->subAudit as $sub) {
                $sub->delete();
            }

            // Hapus audit utama
            $audit->delete();

            return redirect()->back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Fail to delete: ' . $e->getMessage());
        }
    }
}
