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
        return view('contents.master.ftpp.header');
    }

    public function loadSection($section)
    {
        $views = [
            'audit' => 'contents.master.ftpp.partials.audit',
            'finding_category' => 'contents.master.ftpp.partials.finding_category',
            'klausul' => 'contents.master.ftpp.partials.klausul',
        ];

        if (!isset($views[$section])) {
            abort(404);
        }

        switch ($section) {
            case 'audit':
                $data = ['audits' => Audit::with('subAudit')->get()];
                break;
            case 'finding_category':
                $data = ['findingCategories' => FindingCategory::all()];
                break;
            case 'klausul':
                $data = ['klausuls' => Klausul::with(['headKlausul.subKlausul'])->get()];
                break;
            default:
                $data = [];
        }

        return view($views[$section], $data);
    }

}
