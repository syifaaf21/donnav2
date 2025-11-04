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
            ['name' => 'Open'],
            ['name' => 'Submitted'],
            ['name' => 'Checked by Dept Head'],
            ['name' => 'Approved by Auditor'],
            ['name' => 'Close'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status['name']]);
        }
    }
}
