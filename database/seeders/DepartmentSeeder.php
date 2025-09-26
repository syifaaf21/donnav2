<?php

namespace Database\Seeders;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run(): void
    {
        $departments = [
            'Quality Body',
            'Quality Unit',
            'Quality Electric',
            'PPIC Receiving',
            'PPIC Delivery',
            'PPIC Electric',
            'Engineering Body',
            'Engineering Unit',
            'Engineering Electric',
            'Maintenance',
            'Maintenance Electric',
            'Production Unit',
            'Production Body',
            'Production Electric',
            'Production System Development',
            'IT Development',
            'Management System',
            'Management Representative',
            'OMD, TPS, 3 Pillar',
            'Human Resource Development',
            'Pro Engine Group (DC,MA,AS)',
            'Project Control',
            'Commite',
        ];


        foreach ($departments as $department) {
            Department::create([
                'name' => $department,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
