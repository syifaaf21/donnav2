<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil role
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $deptHeadRoleId = 5; // Dept Head
        $superAdminRoleId = 1;
        $userRoleId = 3;
        $auditorRoleId = 4;
        $leaderRoleId = 6;
        $spvRoleId = 7;

        // Ambil satu department default untuk user global (misal Engineering Body)
        $defaultDept = Department::find(24); // sesuaikan dengan ID dept yang aman

        // === SUPER ADMIN ===
        User::create([
            'npk' => '000000',
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('super123'),
            'role_id' => $superAdminRoleId,
            'department_id' => 23,
        ]);

        // === ADMIN ===
        $adminDept = Department::find(24);
        User::create([
            'npk' => '111111',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'department_id' => $adminDept->id,
        ]);

        // === USER BIASA ===
        User::create([
            'npk' => '222222',
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'role_id' => $userRoleId,
            'department_id' => $defaultDept->id,
        ]);

        // === AUDITOR ===
        User::create([
            'npk' => '333332',
            'name' => 'Auditor LK3 User',
            'email' => 'auditor@example.com',
            'password' => Hash::make('audit123'),
            'role_id' => $auditorRoleId,
            'department_id' => $defaultDept->id,
            'audit_type_id' => 1,
        ]);

        // === AUDITOR ===
        User::create([
            'npk' => '333333',
            'name' => 'Auditor Mutu User',
            'email' => 'auditor@example.com',
            'password' => Hash::make('audit123'),
            'role_id' => $auditorRoleId,
            'department_id' => $defaultDept->id,
            'audit_type_id' => 2,
        ]);

        // === SUPERVISOR ===
        User::create([
            'npk' => '444444',
            'name' => 'Supervisor User',
            'email' => 'spv@example.com',
            'password' => Hash::make('aiia123'),
            'role_id' => $spvRoleId,
            'department_id' => 20,
        ]);

        // === LEADER ===
        User::create([
            'npk' => '555555',
            'name' => 'Leader User',
            'email' => 'leader@example.com',
            'password' => Hash::make('aiia123'),
            'role_id' => $leaderRoleId,
            'department_id' => 20,
        ]);

        // === DEPT HEADS ===
        $users = [
            ['name' => 'JONI FERNANDO', 'departments' => ['QA BODY']],
            ['name' => 'DANI PURNOMO', 'departments' => ['QA UNIT']],
            ['name' => 'BONIFASIUS RICKY PURWANTO', 'departments' => ['QA ELECTRIC', 'ENG ELECTRIC']],
            ['name' => 'LUTFI DAHLAN', 'departments' => ['ENG BODY']],
            ['name' => 'RICKY PRAMUDITYA', 'departments' => ['ENG UNIT']],
            ['name' => 'TEGAR AVRILLA KHARISMAWAN', 'departments' => ['MAINTENANCE']],
            ['name' => 'ARMENDO RACHMAWAN', 'departments' => ['PRD BODY']],
            ['name' => 'CAHYANA SUHERLAN', 'departments' => ['PRD UNIT']],
            ['name' => 'ARIF KURNIAWAN DWI HARYADI', 'departments' => ['PRD ELECTRIC', 'PPIC ELECTRIC', 'PSD']],
            ['name' => 'FERRY AVIANTO', 'departments' => ['IT DEVELOPMENT']],
            ['name' => 'FAQIH SETYO AJI', 'departments' => ['PPIC']],
            ['name' => 'JUNJUNAN TRI SETIA', 'departments' => ['MANAGEMENT SYSTEM']],
        ];

        $npk = 100001;

        foreach ($users as $userData) {
            foreach ($userData['departments'] as $deptName) {

                // Map nama ke department_id
                $department = match (true) {
                    str_contains($deptName, 'QA BODY') => Department::where('name', 'Quality Body')->first(),
                    str_contains($deptName, 'QA UNIT') => Department::where('name', 'Quality Unit')->first(),
                    str_contains($deptName, 'QA ELECTRIC') => Department::where('name', 'Quality Electric')->first(),
                    str_contains($deptName, 'ENG BODY') => Department::where('name', 'Engineering Body')->first(),
                    str_contains($deptName, 'ENG UNIT') => Department::where('name', 'Engineering Unit')->first(),
                    str_contains($deptName, 'ENG ELECTRIC') => Department::where('name', 'Engineering Electric')->first(),
                    str_contains($deptName, 'MAINTENANCE') => Department::where('name', 'Maintenance Body')->first(),
                    str_contains($deptName, 'PRD BODY') => Department::where('name', 'Production Body')->first(),
                    str_contains($deptName, 'PRD UNIT') => Department::where('name', 'Production Unit')->first(),
                    str_contains($deptName, 'PRD ELECTRIC') => Department::where('name', 'Production Electric')->first(),
                    str_contains($deptName, 'PPIC ELECTRIC') => Department::where('name', 'PPIC Electric')->first(),
                    str_contains($deptName, 'PPIC') => Department::where('name', 'PPIC-PC')->first(),
                    str_contains($deptName, 'PSD') => Department::where('name', 'Production System & Development')->first(),
                    str_contains($deptName, 'IT DEVELOPMENT') => Department::where('name', 'IT Development')->first(),
                    str_contains($deptName, 'MANAGEMENT SYSTEM') => Department::where('name', 'Management System')->first(),
                    default => null
                };

                if (!$department) {
                    $this->command->warn("Department not found for {$deptName}");
                    continue;
                }

                User::create([
                    'npk' => $npk++,
                    'name' => $userData['name'],
                    'email' => null,
                    'password' => Hash::make('aiia123'),
                    'role_id' => $deptHeadRoleId,
                    'department_id' => $department->id,
                ]);
            }
        }
    }
}
