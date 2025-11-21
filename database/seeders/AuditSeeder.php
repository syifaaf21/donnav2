<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_audit_types')->insert([
            ['name' => 'System Management LK3 (ISO 14001 & ISO 45001)'],
            ['name' => 'System Management Mutu (IATF 16949)'],
        ]);
    }
}
