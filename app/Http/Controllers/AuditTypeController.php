<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\SubKlausul;
use Illuminate\Http\Request;

class AuditTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   

        $search = request('search');
        $audits = Audit::with('subAudit')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('prefix_code', 'like', "%$search%")
                    ->orWhere('registration_number_format', 'like', "%$search%")
                    ->orWhereHas('subAudit', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%") ;
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();
        return view('contents.master.ftpp.audit.index', compact('audits', 'search'));
    }

    public function show($id)
    {
        $audit = Audit::with('subAudit')->findOrFail($id);

        return response()->json([
            'id' => $audit->id,
            'name' => $audit->name,
            'prefix_code' => $audit->prefix_code,
            'registration_number_format' => $audit->registration_number_format,
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
        $audit = Audit::create([
            'name' => $request->name,
            'prefix_code' => $request->prefix_code,
            'registration_number_format' => $request->registration_number_format,
        ]);

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
        $audit->update([
            'name' => $request->name,
            'prefix_code' => $request->prefix_code,
            'registration_number_format' => $request->registration_number_format,
        ]);

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
