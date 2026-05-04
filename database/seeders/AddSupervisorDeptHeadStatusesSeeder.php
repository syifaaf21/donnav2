<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class AddSupervisorDeptHeadStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Need Check by Supervisor',
            'Need Approval by Dept Head',
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status]);
        }
    }
}