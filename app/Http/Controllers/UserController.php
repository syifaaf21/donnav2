<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'department'])->get();
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
            'npk' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required',
            'department_id' => 'required',
        ]);

        User::create([
            'name' => $request->name,
            'npk' => $request->npk,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('contents.master.user.')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'npk' => 'required|unique:users,npk,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required',
            'department_id' => 'required',
        ]);

        $data = $request->only(['name', 'npk', 'email', 'role_id', 'department_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }


}
