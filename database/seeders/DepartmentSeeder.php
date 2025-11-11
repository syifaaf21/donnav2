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
            ['name' => 'Human Resource Development (AII)', 'code' => 'AII', 'plant' => 'ALL'],
            ['name' => 'Human Resource Development (AIIA)', 'code' => 'HRD', 'plant' => 'ALL'],
            ['name' => 'IRL-GA (AII)', 'code' => 'AII', 'plant' => 'ALL'],
            ['name' => 'Marketing (AII)', 'code' => 'AII', 'plant' => 'ALL'],
            ['name' => 'Purchasing Group (AII)', 'code' => 'AII', 'plant' => 'ALL'],
            ['name' => 'Quality Body', 'code' => 'QAS', 'plant' => 'Body'],
            ['name' => 'Quality Unit', 'code' => 'QAS', 'plant' => 'Unit'],
            ['name' => 'Quality Electric', 'code' => 'QAS', 'plant' => 'Electric'],
            ['name' => 'PPIC Receiving', 'code' => 'PPIC', 'plant' => 'Body'],
            ['name' => 'PPIC Delivery', 'code' => 'PPIC', 'plant' => 'Unit'],
            ['name' => 'PPIC Electric', 'code' => 'PPIC', 'plant' => 'Electric'],
            ['name' => 'PPIC-PC', 'code' => 'PPIC', 'plant' => 'ALL'],
            ['name' => 'Engineering Body', 'code' => 'ENG', 'plant' => 'Body'],
            ['name' => 'Engineering Unit', 'code' => 'ENG', 'plant' => 'Unit'],
            ['name' => 'Engineering Electric', 'code' => 'ENG', 'plant' => 'Electric'],
            ['name' => 'Maintenance', 'code' => 'MTE', 'plant' => 'ALL'],
            ['name' => 'Production Unit', 'code' => 'PRD', 'plant' => 'Unit'],
            ['name' => 'Production Body', 'code' => 'PRD', 'plant' => 'Body'],
            ['name' => 'Production Electric', 'code' => 'PRD', 'plant' => 'Electric'],
            ['name' => 'Production System & Development', 'code' => 'PSD', 'plant' => 'ALL'],
            ['name' => 'IT Development', 'code' => 'ITD', 'plant' => 'ALL'],
            ['name' => 'Management System', 'code' => 'MS', 'plant' => 'ALL'],
            ['name' => 'Management Representative', 'code' => 'MR', 'plant' => 'ALL'],
        ];

        foreach ($departments as $department) {
            Department::create([
                'name'       => $department['name'],
                'code'       => $department['code'],
                'plant'      => $department['plant'],  // tambah plant disini
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
