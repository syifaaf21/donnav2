<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tab = $request->input('tab', 'all');
        $perPage = $request->input('per_page', 10); // default 10 per page

        $query = User::with(['role', 'department']);

        // Filter berdasarkan tab
        switch ($tab) {
            case 'depthead':
                $query->whereHas('role', fn($q) => $q->where('name', 'Dept Head'));
                $view = 'contents.master.user.partials.dept-head';
                break;

            case 'auditor':
                $query->whereHas('role', fn($q) => $q->where('name', 'Auditor'));
                $view = 'contents.master.user.partials.auditor';
                break;

            default:
                $view = 'contents.master.user.partials.all';
                break;
        }

        // Filter pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('npk', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('role', fn($r) => $r->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        $users = $query->orderBy('created_at', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $roles = Role::all();
        $departments = Department::all();

        // AJAX → partial
        if ($request->ajax()) {
            $html = view($view, compact('users'))->render();
            return response($html);
        }

        // Non-AJAX → full view
        return view('contents.master.user.index', compact('users', 'roles', 'departments'));
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
            'email' => 'nullable|email|unique:users',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'role_id' => 'required',
            'department_id' => 'required',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        // Cek Role: apakah existing ID atau teks baru
        $roleId = is_numeric($request->role_id)
            ? $request->role_id
            : Role::firstOrCreate(['name' => $request->role_id])->id;

        // Cek Department: apakah existing ID atau teks baru
        $departmentId = is_numeric($request->department_id)
            ? $request->department_id
            : Department::firstOrCreate(['name' => $request->department_id])->id;

        // Simpan user
        User::create([
            'name' => $request->name,
            'npk' => $request->npk,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'department_id' => $departmentId,
        ]);

        return redirect()->route('master.users.index')->with('success', 'User successfully added.');
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
            'email' => 'nullable|email|unique:users,email,' . $user->id,
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
        return redirect()->route('master.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('master.users.index')->with('success', 'User deleted successfully.');
    }

    public function profile()
    {
        return view('contents.profile');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
