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
            ['name' => 'Human Resource Development (AII)', 'code' => 'AII', 'plant' => 'All'],
            ['name' => 'Human Resource Development (AIIA)', 'code' => 'HRD', 'plant' => 'All'],
            ['name' => 'IRL-GA (AII)', 'code' => 'AII', 'plant' => 'All'],
            ['name' => 'Marketing (AII)', 'code' => 'AII', 'plant' => 'All'],
            ['name' => 'Purchasing Group (AII)', 'code' => 'AII', 'plant' => 'All'],
            ['name' => 'Quality Body', 'code' => 'QAS', 'plant' => 'Body'],
            ['name' => 'Quality Unit', 'code' => 'QAS', 'plant' => 'Unit'],
            ['name' => 'Quality Electric', 'code' => 'QAS', 'plant' => 'Electric'],
            ['name' => 'PPIC Receiving', 'code' => 'PPIC', 'plant' => 'Body'],
            ['name' => 'PPIC Delivery', 'code' => 'PPIC', 'plant' => 'Unit'],
            ['name' => 'PPIC Electric', 'code' => 'PPIC', 'plant' => 'Electric'],
            ['name' => 'PPIC-PC', 'code' => 'PPIC', 'plant' => 'All'],
            ['name' => 'Engineering Body', 'code' => 'ENG', 'plant' => 'Body'],
            ['name' => 'Engineering Unit', 'code' => 'ENG', 'plant' => 'Unit'],
            ['name' => 'Engineering Electric', 'code' => 'ENG', 'plant' => 'Electric'],
            ['name' => 'Maintenance Body', 'code' => 'MTE', 'plant' => 'Body'],
            ['name' => 'Maintenance Unit', 'code' => 'MTE', 'plant' => 'Unit'],
            ['name' => 'Maintenance Electric', 'code' => 'MTE', 'plant' => 'Electric'],
            ['name' => 'Production Unit', 'code' => 'PRD', 'plant' => 'Unit'],
            ['name' => 'Production Body', 'code' => 'PRD', 'plant' => 'Body'],
            ['name' => 'Production Electric', 'code' => 'PRD', 'plant' => 'Electric'],
            ['name' => 'Production System & Development', 'code' => 'PSD', 'plant' => 'All'],
            ['name' => 'IT Development', 'code' => 'ITD', 'plant' => 'All'],
            ['name' => 'Management System', 'code' => 'MS', 'plant' => 'All'],
            ['name' => 'Management Representative', 'code' => 'MR', 'plant' => 'All'],
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
