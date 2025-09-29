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
            ['name' => 'Approve'],
            ['name' => 'Reject'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status['name']]);
        }
    }
}
