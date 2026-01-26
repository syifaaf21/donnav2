<?php

namespace App\Http\Controllers;

use App\Models\Audit;
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

        $query = User::with(['roles', 'departments', 'auditTypes']);

        // Filter berdasarkan tab
        switch ($tab) {
            case 'depthead':
                $query->whereHas('roles', fn($q) => $q->where('name', 'Dept Head'));
                $view = 'contents.master.user.partials.dept-head';
                break;

            case 'auditor':
                $query->whereHas('roles', fn($q) => $q->where('name', 'Auditor'));
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
                    ->orWhereHas('roles', fn($r) => $r->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('departments', fn($d) => $d->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('auditTypes', fn($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        $users = $query->orderBy('name', 'asc')
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
            'role_ids' => 'required|array|min:1',
            'department_ids' => 'required|array|min:1',
            'audit_type_ids' => 'nullable|array',
            'audit_type_ids.*' => 'exists:tm_audit_types,id',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        // Resolve role ids (allow passing either existing ids or new names)
        $rawRoles = (array) $request->input('role_ids', []);
        $roleIds = [];
        foreach ($rawRoles as $r) {
            if (is_numeric($r)) {
                $roleIds[] = (int) $r;
            } elseif (!empty($r)) {
                $roleIds[] = Role::firstOrCreate(['name' => $r])->id;
            }
        }

        // Resolve department ids (allow either existing ids or new names)
        $rawDeps = (array) $request->input('department_ids', []);
        $departmentIds = [];
        foreach ($rawDeps as $d) {
            if (is_numeric($d)) {
                $departmentIds[] = (int) $d;
            } elseif (!empty($d)) {
                $departmentIds[] = Department::firstOrCreate(['name' => $d])->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'npk' => $request->npk,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (!empty($roleIds)) {
            $user->roles()->sync(array_values($roleIds));
        }

        if (!empty($departmentIds)) {
            $user->departments()->sync(array_values($departmentIds));
        }

        // Sync audit types
        if ($request->has('audit_type_ids') && is_array($request->audit_type_ids)) {
            $user->auditTypes()->sync($request->audit_type_ids);
        }

        return redirect()->route('master.users.index')->with('success', 'User successfully added.');
    }



    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $departments = Department::all();
        $auditTypes = Audit::all();

        // Kembalikan partial Blade khusus isi modal
        return view('contents.master.user.partials.form-edit', compact('user', 'roles', 'departments', 'auditTypes'));
    }

    public function update(Request $request, User $user)
    {


        // Cek apakah salah satu role yang dipilih adalah dept head
        $rawRoles = (array) $request->input('role_ids', []);
        $isDeptHead = false;
        foreach ($rawRoles as $roleId) {
            $roleObj = is_numeric($roleId)
                ? Role::find($roleId)
                : Role::where('name', $roleId)->first();
            if ($roleObj && stripos($roleObj->name, 'dept head') !== false) {
                $isDeptHead = true;
                break;
            }
        }

        $rules = [
            'name' => 'required',
            'npk' => 'required|digits:6|numeric|unique:users,npk,' . $user->id,
            'email' => ($isDeptHead ? 'required' : 'nullable') . '|email|unique:users,email,' . $user->id,
            'role_ids' => 'required|array|min:1',
            'department_ids' => 'required|array|min:1',
            'audit_type_ids' => 'nullable|array',
            'audit_type_ids.*' => 'exists:tm_audit_types,id',
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

        // Resolve multiple roles & departments (allow new names)
        $rawRoles = (array) $request->input('role_ids', []);
        $roleIds = [];
        foreach ($rawRoles as $r) {
            if (is_numeric($r)) {
                $roleIds[] = (int) $r;
            } elseif (!empty($r)) {
                $roleIds[] = Role::firstOrCreate(['name' => $r])->id;
            }
        }

        $rawDeps = (array) $request->input('department_ids', []);
        $departmentIds = [];
        foreach ($rawDeps as $d) {
            if (is_numeric($d)) {
                $departmentIds[] = (int) $d;
            } elseif (!empty($d)) {
                $departmentIds[] = Department::firstOrCreate(['name' => $d])->id;
            }
        }

        $data = $request->only(['name', 'npk', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if (!empty($departmentIds)) {
            $user->departments()->sync(array_values($departmentIds));
        }

        if (!empty($roleIds)) {
            $user->roles()->sync(array_values($roleIds));
        }

        // Sync audit types
        if ($request->has('audit_type_ids') && is_array($request->audit_type_ids)) {
            $user->auditTypes()->sync($request->audit_type_ids);
        } else {
            $user->auditTypes()->detach();
        }

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
