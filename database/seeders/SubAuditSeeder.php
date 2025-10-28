<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_sub_audits')->insert([
            ['audit_id' => 2, 'name' => 'Product'],
            ['audit_id' => 2, 'name' => 'Process'],
            ['audit_id' => 2, 'name' => 'System'],
            ['audit_id' => 2, 'name' => 'Kalibrasi'],
        ]);
    }
}
