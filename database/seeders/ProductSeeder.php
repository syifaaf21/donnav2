<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            'Timming Chain Cover',
            'Engine Front Module',
            'Camshaft Housing',
            'Oil Pan',
            'Oil/ Water Pump',
            'ECU Case Assy',
            'Center Pillar Garnish',
            'Power Slide Door',
            'Outside Handle',
            'Powerseat Motor Assembly',
            'ECU Board',
        ];

        foreach ($products as $name) {
            Product::create(['name' => $name]);
        }
    }
}
