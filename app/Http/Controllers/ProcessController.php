<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller
{
    /**
     * Display a listing of the processes.
     */
    public function index(Request $request)
    {
        $query = Process::query();

        // Handle search
        if ($request->has('search') && $request->search !== null) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%')
                ->orWhere('plant', 'like', '%' . $request->search . '%');
        }

        $processes = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());


        return view('contents.master.process', compact('processes'));
    }



    /**
     * Store a newly created process.
     */

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tm_processes,name',
            'code' => 'required|string|max:50',
            'plant' => 'required|in:Body,Unit,Electric',
        ]);


        Process::create($validated);

        return redirect()->back()->with('success', 'Process created successfully.');
    }

    /**
     * Update the specified process.
     */
    public function update(Request $request, Process $process)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tm_processes,name,' . $process->id,
            'code' => 'required|string|max:50',
            'plant' => 'required|in:Body,Unit,Electric',
        ]);

         if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('edit_modal', $process->id); // <<< penting
    }

        $process->update($validator->validated());

        return redirect()->back()->with('success', 'Process updated successfully.');
    }

    /**
     * Remove the specified process.
     */
    public function destroy(Process $process)
    {
        $process->delete();

        return redirect()->back()->with('success', 'Process deleted successfully.');
    }
}