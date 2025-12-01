<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Obsolete'],
            ['name' => 'Active'],
            ['name' => 'Approved'],
            ['name' => 'Rejected'],
            ['name' => 'Need Review'],
            ['name' => 'Uncomplete'],
            ['name' => 'Need Assign'],
            ['name' => 'Need Check'],
            ['name' => 'Need Approval by Auditor'],
            ['name' => 'Need Approval by Lead Auditor'],
            ['name' => 'Close'],
            ['name' => 'Need Revision'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status['name']]);
        }
    }
}
