<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\FindingCategory;
use App\Models\Klausul;
use Illuminate\Http\Request;

class FtppMasterController extends Controller
{
    public function index()
    {
        // Load semua data (biar tidak perlu reload ketika ganti tab)
        $audits = Audit::with('subAudit')->get();
        $findingCategories = FindingCategory::all();
        $klausuls = Klausul::with('headKlausul.subKlausul')->get();

        return view('contents.master.ftpp.index', compact('audits', 'findingCategories', 'klausuls'));
    }
}
