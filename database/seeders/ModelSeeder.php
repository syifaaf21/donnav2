<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            ['name' => '5D45W', 'plant' => 'Body'],
            ['name' => '5H45', 'plant' => 'Body'],
            ['name' => '655B', 'plant' => 'Body'],
            ['name' => 'D30D', 'plant' => 'Body'],
            ['name' => 'D23H', 'plant' => 'Body'],

            ['name' => 'D98E', 'plant' => 'Unit'],
            ['name' => '889F', 'plant' => 'Unit'],
            ['name' => 'D72F', 'plant' => 'Unit'],
            ['name' => 'D05E', 'plant' => 'Unit'],
            ['name' => '4A91', 'plant' => 'Unit'],

            ['name' => '4WD 5F00', 'plant' => 'Electric'],
            ['name' => 'EF160 105E', 'plant' => 'Electric'],
            ['name' => 'GA35', 'plant' => 'Electric'],
            ['name' => 'T431', 'plant' => 'Electric'],
            ['name' => '4WD IMV', 'plant' => 'Electric'],
        ];

        foreach ($models as $model) {
            ProductModel::create($model);
        }
    }
}
