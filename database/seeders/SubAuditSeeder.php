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
        DB::table('tm_sub_audit_types')->insert([
            ['audit_type_id' => 2, 'name' => 'Product'],
            ['audit_type_id' => 2, 'name' => 'Process'],
            ['audit_type_id' => 2, 'name' => 'System'],
            ['audit_type_id' => 2, 'name' => 'Kalibrasi'],
        ]);
    }
}
