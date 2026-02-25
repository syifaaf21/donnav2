<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class AddDraftStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::firstOrCreate(['name' => 'Draft']);
    }
}
