<?php

namespace Database\Seeders;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Quality Body', 'code' => 'QAS'],
            ['name' => 'Quality Unit', 'code' => 'QAS'],
            ['name' => 'Quality Electric', 'code' => 'QAS'],
            ['name' => 'PPIC Receiving', 'code' => 'PPIC'],
            ['name' => 'PPIC Delivery', 'code' => 'PPIC'],
            ['name' => 'PPIC Electric', 'code' => 'PPIC'],
            ['name' => 'Engineering Body', 'code' => 'ENG'],
            ['name' => 'Engineering Unit', 'code' => 'ENG'],
            ['name' => 'Engineering Electric', 'code' => 'ENG'],
            ['name' => 'Maintenance Body', 'code' => 'MTE'], // Custom
            ['name' => 'Maintenance Unit', 'code' => 'MTE'], // Custom
            ['name' => 'Maintenance Electric', 'code' => 'MTE'], // Custom
            ['name' => 'Production Unit', 'code' => 'PRD'],
            ['name' => 'Production Body', 'code' => 'PRD'],
            ['name' => 'Production Electric', 'code' => 'PRD'],
            ['name' => 'Production System & Development', 'code' => 'PSD'],
            ['name' => 'IT Development', 'code' => 'ITD'],
            ['name' => 'Management System', 'code' => 'MS'],
            ['name' => 'Management Representative', 'code' => 'MR'],
            ['name' => 'Human Resource Development', 'code' => 'HRD'],
        ];

        foreach ($departments as $department) {
            Department::create([
                'name'       => $department['name'],
                'code'       => $department['code'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
