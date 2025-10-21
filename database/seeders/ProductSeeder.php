<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Timing Chain Cover',         'code' => 'TCC', 'plant' => 'Unit'],
            ['name' => 'Camshaft Housing',           'code' => 'CSH', 'plant' => 'Unit'],
            ['name' => 'Oil Pan',                    'code' => 'OPN', 'plant' => 'Unit'],
            ['name' => 'Oil Pump',                   'code' => 'OLP', 'plant' => 'Unit'], // OP → OLP
            ['name' => 'Water Pump',                 'code' => 'WP',  'plant' => 'Unit'],
            ['name' => 'Inside Handle',              'code' => 'IHL', 'plant' => 'Body'],
            ['name' => 'Outside Handle',             'code' => 'OHL', 'plant' => 'Body'],
            ['name' => 'Frame Handle',               'code' => 'FRM', 'plant' => 'Body'],
            ['name' => 'Cap Handle',                 'code' => 'CAP', 'plant' => 'Body'],
            ['name' => 'Power Slide Door',           'code' => 'PSD', 'plant' => 'Body'],
            ['name' => 'Half Open Stopper',          'code' => 'HOS', 'plant' => 'Body'],
            ['name' => 'Half Open Control',          'code' => 'HOC', 'plant' => 'Body'],
            ['name' => 'Garnish',                    'code' => 'GRS', 'plant' => 'Body'],
            ['name' => 'Back Door Handle',           'code' => 'BDH', 'plant' => 'Body'],
            ['name' => 'Seat Motor',                 'code' => 'STM', 'plant' => 'Electric'],
            ['name' => 'Electronic Control Units',   'code' => 'ECU', 'plant' => 'Electric'],

            // Tambahan dari data lama yang belum ada code-nya
            ['name' => 'Engine Front Module',        'code' => 'EFM', 'plant' => 'Unit'],
            ['name' => 'ECU Case Assy',              'code' => 'ECA', 'plant' => 'Electric'],
            ['name' => 'Center Pillar Garnish',      'code' => 'CPG', 'plant' => 'Body'],
            ['name' => 'Powerseat Motor Assembly',   'code' => 'PSM', 'plant' => 'Electric'],
            ['name' => 'ECU Board',                  'code' => 'ECB', 'plant' => 'Electric'],
            ['name' => 'Handle',                     'code' => 'HND', 'plant' => 'Body'],
            ['name' => 'Handle1',                    'code' => 'HN1', 'plant' => 'Body'],
            ['name' => 'Handle2',                    'code' => 'HN2', 'plant' => 'Body'],
            ['name' => 'Frame',                      'code' => 'FRM2','plant' => 'Body'], // FRM dipakai, ini alternatif
            ['name' => 'Back D',                     'code' => 'BKD', 'plant' => 'Body'],
            ['name' => 'Antenna',                    'code' => 'ANT', 'plant' => 'Electric'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],  // Unik berdasarkan code
                $product
            );
        }
    }
}
