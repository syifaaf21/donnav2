<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = User::with(['role', 'department']);

        if($search){
            $query->where(function ($q) use ($search){
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('npk', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(10)->appends($request->query());
        $roles = Role::all();
        $departments = Department::all();
        return view('contents.master.user', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'npk' => 'required|digits:6|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required',
            'department_id' => 'required',
        ], [
            // Optional: custom error messages in English
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        User::create([
            'name' => $request->name,
            'npk' => $request->npk,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
        ]);
        return redirect()->route('users.index')->with('success', 'User successfully added.');
    }

    public function edit(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {

        $rules = [
            'name' => 'required',
            'npk' => 'required|digits:6|numeric|unique:users,npk,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required',
            'department_id' => 'required',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|min:6|confirmed';
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('edit_modal', $user->id);
        }

        $data = $request->only(['name', 'npk', 'email', 'role_id', 'department_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
