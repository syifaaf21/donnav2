<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            'D98E',
            '889F/D81F',
            'D72F/D73F',
            'D05E',
            '4A91',
            'D18E',
            'D41E',
            'D13E',
            '922F',
            'NR',
            '1SZ',
            '3SZ',
            'K3',
            '4L45W',
            'YHA',
            '660A',
            '800A',
            '230B',
            '560B',
            'YL8',
            'IMV',
            '810A',
            '700A',
            '913L',
            'YTB',
            '4J45',
            '5D45W',
            '5H45',
            '655B',
            'D30D',
            'D23H',
            '640A',
            '4WD 5F00',
            'EF160 105E',
            'EF160 123E',
            'EF160 Z12E',
            'GA35',
            'T431',
            '4WD IMV',
            'PBD',
            'ANTENNA ASSY',
        ];

        foreach ($models as $model) {
            ProductModel::create([
                'name' => $model
            ]);
        }
    }
}
