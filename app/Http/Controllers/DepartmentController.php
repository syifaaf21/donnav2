<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        // Optional: filter by search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%')
                ->orWhere('plant', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $codes = Department::pluck('code')->unique();
        $plants = Department::pluck('plant')->unique();

        return view('contents.master.department', compact('departments', 'codes', 'plants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tm_departments,name',
            'code' => 'required|string|max:10',
            'plant' => 'required|string|max:10',
        ]);

        Department::create($validated);

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tm_departments,name,' . $department->id,
            'code' => 'required|string|max:10',
            'plant' => 'required|string|max:10',
        ]);

         if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('edit_modal', $department->id); // <<< penting
    }

        $department->update($validator->validated());

        session()->flash('edit_modal', $department->id);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }

    public function byPlant($plantId)
    {
        $departments = Department::where('plant_id', $plantId)->get(['id', 'name']);
        return response()->json($departments);
    }
}