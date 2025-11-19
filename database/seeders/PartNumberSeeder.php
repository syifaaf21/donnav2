<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Process;

class PartNumberSeeder extends Seeder
{
    public function run(): void
    {
        /* ============================================================
         * PLANT UNIT — FIXED PART NUMBERS
         * ============================================================ */
        $unitPartNumbers = [

            'Timing Chain Cover' => [
                'D98E' => [
                    'die casting'      => '212111-31900-04',
                    'machining'        => '212111-31900',
                    'assembling unit'  => '212110-34010',
                ],
                '889F' => [
                    'die casting'      => '212111-31930-04',
                    'machining'        => '212111-31930',
                    'assembling unit'  => '212110-34040',
                ],
                'D72F' => [
                    'die casting'      => '212111-34020-04',
                    'machining'        => '212111-34020',
                    'assembling unit'  => '212110-34140',
                ],
                'D18E' => [
                    'die casting'      => '212111-34110-04',
                    'machining'        => '212111-34110',
                    'assembling unit'  => '212110-34270',
                ],
                'D05E' => [
                    'die casting'      => '212111-34130-04',
                    'machining'        => '212111-34130',
                    'assembling unit'  => '212110-34300',
                ],
                '4A91' => [
                    'die casting'      => '212111-21350',
                    'machining'        => '212104-21030',
                    'assembling unit'  => '212130-21250',
                ],
                'D41E' => [
                    'die casting'      => '212111-34171-04',
                    'machining'        => '212111-34171',
                    'assembling unit'  => '212110-34341',
                ],
                'D13E' => [
                    'die casting'      => '212111-21360-04',
                    'machining'        => '212111-21360',
                    'assembling unit'  => '212130-21260',
                ],
            ],

            'Oil Pan' => [
                '889F' => [
                    'die casting' => '243212-10980-04',
                    'machining'   => '12101-0Y040',
                ],
                '922F' => [
                    'die casting' => '243212-11020-04',
                    'machining'   => '243202-10680',
                ],
                'D72F' => [
                    'die casting' => '243212-11040-04',
                    'machining'   => '243202-10710',
                ],
                'D41E' => [
                    'die casting' => '243212-11030-04',
                ],
            ],

            'Camshaft Housing' => [
                'D98E' => [
                    'die casting' => '243131-10260',
                ],
                'D05E' => [
                    'die casting' => '243131-10490',
                ],
            ],

            'Water Pump' => [
                'K3' => [
                    'assembling unit' => '213100-13551',
                ],
                'NR' => [
                    'assembling unit' => '213100-14200',
                ],
                'SZ' => [
                    'assembling unit' => '213100-13531',
                ],
            ],

            'Oil Pump' => [
                '1SZ' => [
                    'assembling unit' => '223100-41090',
                ],
                '3SZ' => [
                    'assembling unit' => '223100-41110',
                ],
            ],
        ];

        /* ============================================================
         * INSERT DATA FOR UNIT PLANT
         * ============================================================ */
        foreach ($unitPartNumbers as $productName => $models) {

            $product = Product::where('name', $productName)
                ->where('plant', 'unit')
                ->first();

            if (!$product) continue;

            foreach ($models as $modelName => $processes) {

                $model = ProductModel::where('name', $modelName)
                    ->where('plant', 'unit')
                    ->first();

                if (!$model) continue;

                foreach ($processes as $processName => $partNumber) {

                    $process = Process::where('name', $processName)->first();
                    if (!$process) continue;

                    DB::table('tm_part_numbers')->insert([
                        'part_number' => $partNumber,
                        'product_id'  => $product->id,
                        'model_id'    => $model->id,
                        'process_id'  => $process->id,
                        'plant'       => 'unit',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }
}
