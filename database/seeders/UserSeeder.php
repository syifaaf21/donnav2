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
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        // Admins
        $adminDept = Department::find(17);

        User::create([
            'npk' => '111111',
            'name' => 'Fina',
            'email' => 'fina@example.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'department_id' => $adminDept->id,
        ]);

        // Guests (now assigned role: user)
        $users = [
            ['npk' => '000001', 'name' => 'Udin', 'department_id' => 1],
            ['npk' => '000002', 'name' => 'Asep', 'department_id' => 2],
            ['npk' => '000003', 'name' => 'Joko', 'department_id' => 3],
            ['npk' => '000004', 'name' => 'Yono', 'department_id' => 4],
            ['npk' => '000005', 'name' => 'Dika', 'department_id' => 5],
            ['npk' => '000006', 'name' => 'Budi', 'department_id' => 6],
            ['npk' => '000007', 'name' => 'Yudi', 'department_id' => 7],
            ['npk' => '000008', 'name' => 'Ikhsan', 'department_id' => 8],
            ['npk' => '000009', 'name' => 'Risky', 'department_id' => 9],
            ['npk' => '000010', 'name' => 'Rates', 'department_id' => 10],
            ['npk' => '000011', 'name' => 'Fabojo', 'department_id' => 11],
            ['npk' => '000012', 'name' => 'Umar', 'department_id' => 12],
            ['npk' => '000013', 'name' => 'Ali', 'department_id' => 13],
            ['npk' => '000014', 'name' => 'Amin', 'department_id' => 14],
            ['npk' => '000015', 'name' => 'Jaka', 'department_id' => 15],
            ['npk' => '000016', 'name' => 'Yanto', 'department_id' => 16],
            ['npk' => '000017', 'name' => 'Iman', 'department_id' => 17],
            ['npk' => '000018', 'name' => 'Aji', 'department_id' => 18],
            ['npk' => '000019', 'name' => 'Fauzan', 'department_id' => 19],
            ['npk' => '000020', 'name' => 'Fauji', 'department_id' => 20],
            ['npk' => '000021', 'name' => 'Surya', 'department_id' => 21],
            ['npk' => '000022', 'name' => 'Hasan', 'department_id' => 22],
            ['npk' => '000023', 'name' => 'Komang', 'department_id' => 23],
        ];

        foreach ($users as $index => $data) {
            $department = Department::find($data['department_id']);

            User::create([
                'npk' => $data['npk'],
                'name' => $data['name'],
                'email' => strtolower($data['name']) . '@example.com', // generate email dummy
                'password' => Hash::make('user123'),
                'role_id' => $userRole->id,
                'department_id' => $department->id,
            ]);
        }
    }
}
