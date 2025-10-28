<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FindingCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_finding_categories')->insert([
            ['name' => 'Major'],
            ['name' => 'Minor'],
            ['name' => 'Observasion'],
        ]);
    }
}
