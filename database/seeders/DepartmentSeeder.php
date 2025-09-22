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
            'ENGINEERING BODY',
            'ENGINEERING UNIT',
            'MAINTENANCE',
            'QA UNIT',
            'QA BODY',
            'OMD, TPS, 3 PILLAR',
            'PRO ENGINE GROUP (DC,MA,AS)',
            'PRODUCTION BODY',
            'ENGINEERING & QC ELECTRIC',
            'PROJECT CONTROL',
            'MANAGEMENT SYSTEM',
            'PRODUCTION & PPIC ELECTRIC',
            'IT DEVELOPMENT',
            'HRD',
            'COMMITE',
            'PPIC',
            'IRL & GA',
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
