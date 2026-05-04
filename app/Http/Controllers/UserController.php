<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\UserAuditType;
use App\Models\UserRole;
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

        $query = User::with(['roles', 'departments', 'auditTypes', 'userAuditTypes.userRole.role', 'userAuditTypes.audit']);

        // Filter berdasarkan tab
        switch ($tab) {
            case 'depthead':
                $query->whereHas('roles', fn($q) => $q->where('name', 'Dept Head'));
                $view = 'contents.master.user.partials.dept-head';
                break;

            case 'lead-auditor':
                $query->whereHas('roles', fn($q) => $q->where('name', 'Lead Auditor'));
                $view = 'contents.master.user.partials.lead-auditor';
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

        // Jika current user adalah Admin (tetapi bukan Super Admin), sembunyikan role 'Super Admin'
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->roles->pluck('name')->contains('Super Admin');
        $isAdmin = $currentUser->roles->pluck('name')->contains('Admin');

        if ($isAdmin && !$isSuperAdmin) {
            $roles = Role::where('name', '<>', 'Super Admin')->get();
        } else {
            $roles = Role::all();
        }
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
            'audit_type_ids_by_role' => 'nullable|array',
            'audit_type_ids_by_role.*' => 'array',
            'audit_type_ids_by_role.*.*' => 'exists:tm_audit_types,id',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        // Resolve role ids (allow passing either existing ids or new names)
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->roles->pluck('name')->contains('Super Admin');

        $rawRoles = (array) $request->input('role_ids', []);
        $roleIds = [];
        foreach ($rawRoles as $r) {
            if (is_numeric($r)) {
                $roleObj = Role::find($r);
                if (!$roleObj) continue;
                // prevent Admin (non-super) from assigning Super Admin
                if (!$isSuperAdmin && $roleObj->name === 'Super Admin') continue;
                $roleIds[] = (int) $r;
            } elseif (!empty($r)) {
                if (!$isSuperAdmin && strcasecmp($r, 'Super Admin') === 0) continue;
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
            'password_changed_at' => now(),
            'permissions' => $request->input('permissions', []),
        ]);

        if (!empty($roleIds)) {
            $user->roles()->sync(array_values($roleIds));
        }

        if (!empty($departmentIds)) {
            $user->departments()->sync(array_values($departmentIds));
        }

        // Sync audit types per role (Auditor / Lead Auditor)
        $user->userAuditTypes()->delete();
        foreach ((array) $request->input('audit_type_ids_by_role', []) as $roleId => $auditTypeIds) {
            $role = Role::find($roleId);
            if (!$role) continue;
            $isAuditor     = stripos($role->name, 'auditor') !== false && stripos($role->name, 'lead') === false;
            $isLeadAuditor = stripos($role->name, 'lead auditor') !== false;
            $userRoleRecord = UserRole::where('user_id', $user->id)->where('role_id', $roleId)->first();
            if (!$userRoleRecord) continue;
            foreach (array_unique((array) $auditTypeIds) as $auditId) {
                UserAuditType::create([
                    'user_id'         => $user->id,
                    'audit_id'        => $auditId,
                    'user_role_id'    => $userRoleRecord->id,
                    'is_auditor'      => $isAuditor,
                    'is_lead_auditor' => $isLeadAuditor,
                ]);
            }
        }

        return redirect()->route('master.users.index')->with('success', 'User successfully added.');
    }



    public function edit($id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->roles->pluck('name')->contains('Super Admin');
        $isAdmin = $currentUser->roles->pluck('name')->contains('Admin');

        if ($isAdmin && !$isSuperAdmin) {
            $roles = Role::where('name', '<>', 'Super Admin')->get();
        } else {
            $roles = Role::all();
        }
        $departments = Department::all();
        $auditTypes   = Audit::all();

        // Load existing audit type assignments grouped by role_id for pre-selection
        $auditTypeIdsByRole = [];
        foreach (UserAuditType::with('userRole')->where('user_id', $user->id)->get() as $record) {
            $roleId = $record->userRole ? $record->userRole->role_id : null;
            if ($roleId) {
                $auditTypeIdsByRole[$roleId][] = $record->audit_id;
            }
        }

        // Kembalikan partial Blade khusus isi modal
        return view('contents.master.user.partials.form-edit', compact('user', 'roles', 'departments', 'auditTypes', 'auditTypeIdsByRole'));
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
            'audit_type_ids_by_role' => 'nullable|array',
            'audit_type_ids_by_role.*' => 'array',
            'audit_type_ids_by_role.*.*' => 'exists:tm_audit_types,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ];
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
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->roles->pluck('name')->contains('Super Admin');

        $rawRoles = (array) $request->input('role_ids', []);
        $roleIds = [];
        foreach ($rawRoles as $r) {
            if (is_numeric($r)) {
                $roleObj = Role::find($r);
                if (!$roleObj) continue;
                if (!$isSuperAdmin && $roleObj->name === 'Super Admin') continue;
                $roleIds[] = (int) $r;
            } elseif (!empty($r)) {
                if (!$isSuperAdmin && strcasecmp($r, 'Super Admin') === 0) continue;
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
        $data['permissions'] = $request->input('permissions', []);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['password_changed_at'] = now();
        }

        $user->update($data);

        if (!empty($departmentIds)) {
            $user->departments()->sync(array_values($departmentIds));
        }

        if (!empty($roleIds)) {
            $user->roles()->sync(array_values($roleIds));
        }

        // Sync audit types per role (Auditor / Lead Auditor)
        $user->userAuditTypes()->delete();
        foreach ((array) $request->input('audit_type_ids_by_role', []) as $roleId => $auditTypeIds) {
            $role = Role::find($roleId);
            if (!$role) continue;
            $isAuditor     = stripos($role->name, 'auditor') !== false && stripos($role->name, 'lead') === false;
            $isLeadAuditor = stripos($role->name, 'lead auditor') !== false;
            $userRoleRecord = UserRole::where('user_id', $user->id)->where('role_id', $roleId)->first();
            if (!$userRoleRecord) continue;
            foreach (array_unique((array) $auditTypeIds) as $auditId) {
                UserAuditType::create([
                    'user_id'         => $user->id,
                    'audit_id'        => $auditId,
                    'user_role_id'    => $userRoleRecord->id,
                    'is_auditor'      => $isAuditor,
                    'is_lead_auditor' => $isLeadAuditor,
                ]);
            }
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

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ], [
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_changed_at = now();
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
