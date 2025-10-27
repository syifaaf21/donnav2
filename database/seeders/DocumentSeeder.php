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
            'name' => 'Failure Mode Effect Analysis',
            'code' => 'FMEA',
            'parent_id' => null,
            'type' => 'review',
        ]);

        // Child of FMEA
        $qcpc = Document::create([
            'name' => 'Quality Control Process Chart',
            'code' => 'QCPC',
            'parent_id' => $fmea->id,
            'type' => 'review',
        ]);

        // Children of QCPC
        $qcw = Document::create([
            'name' => 'Quality Control Work Instruction Sheet',
            'code' => 'QCWIS',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $qualityComponent = Document::create([
            'name' => 'Quality Component',
            'code' => 'Q-COMPO',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $checksheetProduction = Document::create([
            'name' => 'Checksheet Production',
            'code' => 'C/S PRD',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $wisProduction = Document::create([
            'name' => 'Work Instruction Sheet',
            'code' => 'WIS',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        $pis = Document::create([
            'name' => 'Product Instruction Sheet',
            'code' => 'PIS',
            'parent_id' => $qcpc->id,
            'type' => 'review',
        ]);

        // Children of QCWIS
        Document::create([
            'name' => 'Checksheet QCWIS',
            'code' => 'C/S QCWIS',
            'parent_id' => $qcw->id,
            'type' => 'review',
        ]);

        // Children of Quality Component
        Document::create([
            'name' => 'Checksheet Parameter',
            'code' => 'C/S Parameter',
            'parent_id' => $qualityComponent->id,
            'type' => 'review',
        ]);
    }
}
