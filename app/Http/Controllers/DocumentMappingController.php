<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentMapping;
use App\Models\PartNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documentMappings = DocumentMapping::with(['document', 'department'])->get();
        $departments = Department::all();
        $documents = Document::all();

        return view('contents.master.document', compact('documentMappings', 'departments', 'documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
       //
    }
}