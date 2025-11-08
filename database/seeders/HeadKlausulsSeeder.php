<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadKlausulsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_head_klausuls')->insert([
            // ISO 9001 / IATF 16949
            ['klausul_id' => 1, 'code' => '4', 'name' => 'Context of the Organization'],
            ['klausul_id' => 1, 'code' => '5', 'name' => 'Leadership'],
            ['klausul_id' => 1, 'code' => '6', 'name' => 'Planning'],
            ['klausul_id' => 1, 'code' => '7', 'name' => 'Support'],
            ['klausul_id' => 1, 'code' => '8', 'name' => 'Operation'],
            ['klausul_id' => 1, 'code' => '9', 'name' => 'Performance Evaluation'],
            ['klausul_id' => 1, 'code' => '10', 'name' => 'Improvement'],

            // ISO 14001:2015
            ['klausul_id' => 2, 'code' => '4', 'name' => 'Konteks Organisasi'],
            ['klausul_id' => 2, 'code' => '5', 'name' => 'Kepemimpinan'],
            ['klausul_id' => 2, 'code' => '6', 'name' => 'Perencanaan'],
            ['klausul_id' => 2, 'code' => '7', 'name' => 'Dukungan'],
            ['klausul_id' => 2, 'code' => '8', 'name' => 'Operasi'],
            ['klausul_id' => 2, 'code' => '9', 'name' => 'Evaluasi Kinerja'],
            ['klausul_id' => 2, 'code' => '10', 'name' => 'Peningkatan'],

            // ISO 45001:2018
            ['klausul_id' => 3, 'code' => '4', 'name' => 'Konteks Organisasi'],
            ['klausul_id' => 3, 'code' => '5', 'name' => 'Kepemimpinan dan Partisipasi Pekerja'],
            ['klausul_id' => 3, 'code' => '6', 'name' => 'Perencanaan'],
            ['klausul_id' => 3, 'code' => '7', 'name' => 'Dukungan'],
            ['klausul_id' => 3, 'code' => '8', 'name' => 'Operasi'],
            ['klausul_id' => 3, 'code' => '9', 'name' => 'Evaluasi Kinerja'],
            ['klausul_id' => 3, 'code' => '10', 'name' => 'Peningkatan'],
        ]);
    }
}
