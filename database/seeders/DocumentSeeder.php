<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Document
        $fmea = Document::create([
            'name' => 'FMEA',
            'parent_id' => null,
            'type' => 'review',
        ]);

        // Child of FMEA
        $qcpc = Document::create([
            'name' => 'QCPC',
            'parent_id' => $fmea->id,
            'type' => 'review',
        ]);

        // Children of QCPC
        $qcw = Document::create([
            'name' => 'QCWIS',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $qualityComponent = Document::create([
            'name' => 'Quality Component',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $checksheetProduction = Document::create([
            'name' => 'Checksheet Production',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $wisProduction = Document::create([
            'name' => 'WIS Production',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $pis = Document::create([
            'name' => 'PIS',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        // Children of QCWIS
        Document::create([
            'name' => 'Checksheet QCWIS',
            'parent_id' => $qcw->id,
            'type' => 'review',
        ]);

        // Children of Quality Component
        Document::create([
            'name' => 'Checksheet Parameter',
            'parent_id' => $qualityComponent->id,
            'type' => 'review',
        ]);
    }
}
