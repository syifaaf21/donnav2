<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        $processes = [
            // Unit Plant
            ['name' => 'die casting', 'code' => 'DC', 'plant' => 'Unit'],
            ['name' => 'machining', 'code' => 'MA', 'plant' => 'Unit'],
            ['name' => 'assembling unit', 'code' => 'AS', 'plant' => 'Unit'],

            // Body Plant
            ['name' => 'injection', 'code' => 'INJ', 'plant' => 'Body'],
            ['name' => 'assembling body', 'code' => 'AS', 'plant' => 'Body'],
            ['name' => 'painting', 'code' => 'PT', 'plant' => 'Body'],

            // Electric Plant
            ['name' => 'antenna', 'code' => 'ANT', 'plant' => 'Electric'],
            ['name' => 'mounting', 'code' => 'MT', 'plant' => 'Electric'],
            ['name' => 'inspection', 'code' => 'INSP', 'plant' => 'Electric'],
            ['name' => 'vacuum', 'code' => 'VAC', 'plant' => 'Electric'],
        ];

        foreach ($processes as $process) {
            Process::create($process);
        }
    }
}
