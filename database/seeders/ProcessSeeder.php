<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        $processes = [
            ['name' => 'injection', 'code' => 'INJ'],
            ['name' => 'painting', 'code' => 'PT'],
            ['name' => 'assembling body', 'code' => 'AS'],
            ['name' => 'assembling unit', 'code' => 'AS'],
            ['name' => 'assembling electric', 'code' => 'AS'],
            ['name' => 'die casting', 'code' => 'DC'],
            ['name' => 'machining', 'code' => 'MA'],
            ['name' => 'mounting', 'code' => 'MT'],
            ['name' => 'inspection', 'code' => 'INSP'],
        ];

        foreach ($processes as $process) {
            Process::create($process);
        }
    }
}
