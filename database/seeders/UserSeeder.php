<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil role
        $adminRole = Role::where('name', 'admin')->first();
        $userRoleId = 3; // user biasa
        $deptHeadRoleId = 5;
        $superAdminRoleId = 1;
        $auditorRoleId = 4;
        $leaderRoleId = 7;
        $spvRoleId = 6;

        // Ambil department berdasarkan nama
        $superAdminDept = Department::where('name', 'Engineering Body')->first();
        $adminDept = Department::where('name', 'Engineering Body')->first();
        $defaultDept = Department::where('name', 'Engineering Body')->first();

        if (!$superAdminDept || !$adminDept || !$defaultDept) {
            $this->command->error("Department Engineering Body tidak ditemukan. Jalankan DepartmentSeeder dulu!");
            return;
        }

        // === SUPER ADMIN ===
        $user = User::create([
            'npk' => '000000',
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('super123'),
        ]);
        // attach role and department via pivot
        $user->roles()->sync([$superAdminRoleId]);
        $user->departments()->sync([$superAdminDept->id]);

        // === ADMIN ===
        $user = User::create([
            'npk' => '111111',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
        ]);
        $user->roles()->sync([$adminRole->id]);
        $user->departments()->sync([$adminDept->id]);

        // === USER BIASA ===
        $user = User::create([
            'npk' => '222222',
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
        ]);
        $user->roles()->sync([$userRoleId]);
        $user->departments()->sync([$defaultDept->id]);

        // === AUDITOR ===
        $user = User::create([
            'npk' => '333332',
            'name' => 'Auditor LK3 User',
            'email' => 'auditor@example.com',
            'password' => Hash::make('audit123'),
            'audit_type_id' => 1,
        ]);
        $user->roles()->sync([$auditorRoleId]);
        $user->departments()->sync([$defaultDept->id]);

        // === AUDITOR ===
        $user = User::create([
            'npk' => '333333',
            'name' => 'Auditor Mutu User',
            'email' => 'auditor@example.com',
            'password' => Hash::make('audit123'),
            'audit_type_id' => 2,
        ]);
        $user->roles()->sync([$auditorRoleId]);
        $user->departments()->sync([$defaultDept->id]);

        // === SUPERVISORS (AUDITEE) ===
        $supervisors = [
            ['name' => 'Yamin Muhaemin', 'dept' => 'Quality Body', 'npk' => 199],
            ['name' => 'Zaenuddin Hafidh Karyana', 'dept' => 'Quality Unit', 'npk' => 119],
            ['name' => 'Endi Ependi', 'dept' => 'Quality Electric', 'npk' => 601],
            ['name' => 'Reza Khaerudin', 'dept' => 'Engineering Body', 'npk' => 543],
            ['name' => 'Ikhsanudin', 'dept' => 'Engineering Unit', 'npk' => 1724],
            ['name' => 'Yakobus Piere Aditya Putra', 'dept' => 'Engineering Electric', 'npk' => 552],
            ['name' => 'Wahyu Bagus Hartanto', 'dept' => 'Maintenance', 'npk' => 118],
            ['name' => 'Angga Saputra', 'dept' => 'Production Unit', 'npk' => 909],
            ['name' => 'M. Saiful Romandhon', 'dept' => 'Production Unit', 'npk' => 554],
            ['name' => 'Indra Muhammad Zulkarnaen', 'dept' => 'Production Electric', 'npk' => 452],
            ['name' => 'Heru Maheko Putra', 'dept' => 'Production System & Development', 'npk' => 76],
            ['name' => 'Imam Mahfud', 'dept' => 'IT Development', 'npk' => 813],
            ['name' => 'Rizal Fahlepi', 'dept' => 'PPIC Receiving', 'npk' => 124],
            ['name' => 'Rio Ibrahim Nasution', 'dept' => 'PPIC Delivery', 'npk' => 549],
            ['name' => 'Saiful Akhmad Safari', 'dept' => 'PPIC Electric', 'npk' => 449],
            ['name' => 'Ardiansyah Yuli Saputro', 'dept' => 'PPIC-PC', 'npk' => 461],
            ['name' => 'Arif Setiyono', 'dept' => 'Management System', 'npk' => 27],
        ];

        foreach ($supervisors as $sup) {
            $department = Department::where('name', $sup['dept'])->first();

            if (!$department) {
                $this->command->warn("Department not found for supervisor: {$sup['dept']}");
                continue;
            }

            $user = User::create([
                'npk' => str_pad($sup['npk'], 6, '0', STR_PAD_LEFT),
                'name' => $sup['name'],
                'email' => null,
                'password' => Hash::make('aiia123'),
                'audit_type_id' => null,
            ]);

            // attach role and department
            $user->roles()->sync([$spvRoleId]);
            $user->departments()->sync([$department->id]);
        }

        // === LEADER ===
        $leaderDept = Department::where('name', 'Maintenance')->first();
        $user = User::create([
            'npk' => '444444',
            'name' => 'Leader User',
            'email' => 'leader@example.com',
            'password' => Hash::make('aiia123'),
            'audit_type_id' => null,
        ]);
        $user->roles()->sync([$leaderRoleId]);
        $user->departments()->sync([$leaderDept ? $leaderDept->id : $defaultDept->id]);

        // === DEPT HEADS ===
        $users = [
            ['name' => 'Joni Fernando', 'departments' => ['Qa Body'], 'npk' => '555551'],
            ['name' => 'Dani Purnomo', 'departments' => ['Qa Unit'], 'npk' => '555552'],
            ['name' => 'Bonifasius Ricky Purwanto', 'departments' => ['Qa Electric', 'Eng Electric'], 'npk' => '555553'],
            ['name' => 'Lutfi Dahlan', 'departments' => ['Eng Body'], 'npk' => '000023'],
            ['name' => 'Ricky Pramuditya', 'departments' => ['Eng Unit'], 'npk' => '100050'],
            ['name' => 'Tegar Avrilla Kharismawan', 'departments' => ['Maintenance'], 'npk' => '000026'],
            ['name' => 'Armendo Rachmawan', 'departments' => ['Prd Body'], 'npk' => '555554'],
            ['name' => 'Cahyana Suherlan', 'departments' => ['Prd Unit'], 'npk' => '555555'],
            ['name' => 'Arif Kurniawan Dwi Haryadi', 'departments' => ['Prd Electric', 'Ppic Electric', 'Psd'], 'npk' => '000019'],
            ['name' => 'Ferry Avianto', 'departments' => ['It Development'], 'npk' => '000020'],
            ['name' => 'Faqih Setyo Aji', 'departments' => ['Ppic'], 'npk' => '555556'],
            ['name' => 'Junjunan Tri Setia', 'departments' => ['Management System'], 'npk' => '000024'],
        ];

        foreach ($users as $userData) {

            // Kumpulkan ID department untuk user ini
            $departmentIds = [];

            foreach ($userData['departments'] as $deptName) {

                $d = strtoupper($deptName);

                $department = match (true) {
                    str_contains($d, 'QA BODY') => Department::where('name', 'Quality Body')->first(),
                    str_contains($d, 'QA UNIT') => Department::where('name', 'Quality Unit')->first(),
                    str_contains($d, 'QA ELECTRIC') => Department::where('name', 'Quality Electric')->first(),
                    str_contains($d, 'ENG BODY') => Department::where('name', 'Engineering Body')->first(),
                    str_contains($d, 'ENG UNIT') => Department::where('name', 'Engineering Unit')->first(),
                    str_contains($d, 'ENG ELECTRIC') => Department::where('name', 'Engineering Electric')->first(),
                    str_contains($d, 'MAINTENANCE') => Department::where('name', 'Maintenance')->first(),
                    str_contains($d, 'PRD BODY') => Department::where('name', 'Production Body')->first(),
                    str_contains($d, 'PRD UNIT') => Department::where('name', 'Production Unit')->first(),
                    str_contains($d, 'PRD ELECTRIC') => Department::where('name', 'Production Electric')->first(),
                    str_contains($d, 'PPIC ELECTRIC') => Department::where('name', 'PPIC Electric')->first(),
                    str_contains($d, 'PPIC') => Department::where('name', 'PPIC-PC')->first(),
                    str_contains($d, 'PSD') => Department::where('name', 'Production System & Development')->first(),
                    str_contains($d, 'IT DEVELOPMENT') => Department::where('name', 'IT Development')->first(),
                    str_contains($d, 'MANAGEMENT SYSTEM') => Department::where('name', 'Management System')->first(),
                    default => null,
                };

                if ($department) {
                    $departmentIds[] = $department->id;
                } else {
                    $this->command->warn("Department not found: {$deptName}");
                }
            }

            if (empty($departmentIds)) {
                $this->command->warn("User {$userData['name']} tidak punya department valid, skip.");
                continue;
            }

            // buat user
            $user = User::create([
                'npk' => $userData['npk'],
                'name' => $userData['name'],
                'email' => null,
                'password' => Hash::make('aiia123'),
                'audit_type_id' => null,
            ]);

            // attach primary role and save all departments to pivot
            $user->roles()->sync([$deptHeadRoleId]);
            $user->departments()->sync($departmentIds);
        }
    }
}
