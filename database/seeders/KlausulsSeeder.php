<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KlausulsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_klausuls')->insert([
            ['name' => 'IATF', 'audit_type_id' => 2],
            ['name' => 'ISO 14001:2015', 'audit_type_id' => 1],
            ['name' => 'ISO 45001:2018', 'audit_type_id' => 1],
        ]);
    }
}
