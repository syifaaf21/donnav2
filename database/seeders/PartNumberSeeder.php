<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Process;

class PartNumberSeeder extends Seeder
{
    public function run(): void
    {
        $plantMapping = [
            'body' => ['injection', 'painting', 'assembling body'],
            'unit' => ['die casting', 'machining', 'assembling unit'],
            'electric' => ['mounting', 'assembling electric', 'inspection'],
        ];

        $data = [
            'Timing Chain Cover' => [
                'models' => ['D98E', '889F/D81F', 'D72F/D73F', 'D05E', '4A91', 'D18E', 'D41E', 'D13E'],
                'processes' => ['die casting', 'machining', 'assembling unit'],
            ],
            'Camshaft Housing' => [
                'models' => ['D98E', 'D05E'],
                'processes' => ['die casting', 'machining', 'assembling unit'],
            ],
            'Oil Pan' => [
                'models' => ['889F/D81F', 'D72F/D73F', 'D41E', '922F'],
                'processes' => ['die casting', 'machining'],
            ],
            'Handle1' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'painting', 'assembling body'],
            ],
            'Handle2' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'assembling body'],
            ],
            'Frame' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'assembling body'],
            ],
            'Antenna' => [
                'models' => ['4WD 5F00', 'EF160 105E', 'EF160 123E', 'EF160 Z12E', 'GA35', 'T431', '4WD IMV', 'PBD', 'ANTENNA ASSY'],
                'processes' => ['mounting', 'assembling electric', 'inspection'],
            ],
        ];

        foreach ($data as $productName => $info) {
            $product = Product::where('name', $productName)->first();
            if (!$product) continue;

            foreach ($info['models'] as $modelName) {
                $model = ProductModel::where('name', $modelName)->first();
                if (!$model) continue;

                foreach ($info['processes'] as $processName) {
                    // Cek apakah proses ada
                    $process = Process::where('name', $processName)->first();
                    if (!$process) continue;

                    // Tentukan plant berdasarkan process
                    $plant = null;
                    foreach ($plantMapping as $plantName => $processes) {
                        if (in_array($processName, $processes)) {
                            $plant = $plantName;
                            break;
                        }
                    }

                    if (!$plant) continue;

                    DB::table('tm_part_numbers')->insert([
                        'part_number' => strtoupper(Str::random(10)),
                        'product_id' => $product->id,
                        'model_id' => $model->id,
                        'process_id' => $process->id,
                        'plant' => $plant,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
