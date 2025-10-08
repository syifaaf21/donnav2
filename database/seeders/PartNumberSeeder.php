<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Model as CarModel; // Rename to avoid conflict with base PHP Model
use Illuminate\Support\Str;

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
            'WP' => [
                'models' => ['NR', '1SZ', 'K3'],
                'processes' => ['assembling unit'],
            ],
            'OP' => [
                'models' => ['1SZ', '3SZ'],
                'processes' => ['assembling unit'],
            ],
            'Handle1' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'painting', 'assembling body'],
            ],
            'Handle2' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'assembling body'],
            ],
            'CAP' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'painting', 'assembling body'],
            ],
            'Frame' => [
                'models' => ['4L45W', 'YHA', '660A', '230B', '560B', 'YL8', 'IMV', '810A', '700A', 'YTB', '4J45', '5D45W', '5H45', '655B'],
                'processes' => ['injection', 'assembling body'],
            ],
            'Antenna' => [
                'models' => ['4WD 5F00', 'EF160 105E', 'EF160 123E', 'EF160 Z12E', 'GA35', 'T431', '4WD IMV', 'PBD', 'ANTENNA ASSY',],
                'processes' => ['mounting', 'assembling electric', 'inspection'],
            ]
        ];

        foreach ($data as $productName => $info) {
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                echo "Product not found: $productName\n";
                continue;
            }

            foreach ($info['models'] as $modelName) {
                $model = ProductModel::where('name', $modelName)->first();

                if (!$model) {
                    echo "Model not found: $modelName\n";
                    continue;
                }

                foreach ($info['processes'] as $process) {
                    // cari plant berdasarkan process
                    $plant = null;
                    foreach ($plantMapping as $plantName => $processes) {
                        if (in_array($process, $processes)) {
                            $plant = $plantName;
                            break;
                        }
                    }

                    // Kalau plant tidak ditemukan, bisa kasih nilai default atau skip
                    if (!$plant) {
                        echo "Plant not found for process: $process\n";
                        continue;
                    }

                    DB::table('part_numbers')->insert([
                        'part_number' => strtoupper(Str::random(10)),
                        'product_id' => $product->id,
                        'model_id' => $model->id,
                        'process' => $process,
                        'plant' => $plant,  // <- tambah kolom plant di sini
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
