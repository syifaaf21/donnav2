<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentMapping;
use App\Models\Document;
use App\Models\PartNumber;
use App\Models\User;
use App\Models\Status;

class DocumentMappingSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data relasi yang sudah ada
        $documents = Document::where('type', 'review')->take(3)->with('department')->get();
        $partNumbers = PartNumber::take(3)->get();
        $status = Status::where('name', 'Active')->first() ?? Status::first();
        $user = User::first();

        // Cek dulu biar gak error kalau data belum ada
        if ($documents->isEmpty() || $partNumbers->isEmpty() || !$status || !$user) {
            $this->command->warn('⚠️ Data relasi belum lengkap (cek Document, PartNumber, Status, atau User).');
            return;
        }

        // Buat mapping data secara berurutan
        foreach ($documents as $index => $doc) {
            // pastikan dokumen punya department
            if (!$doc->department) {
                $this->command->warn("⚠️ Document {$doc->id} belum punya department, dilewati.");
                continue;
            }

            $mappingData = [
                'document_id' => $doc->id,
                'part_number_id' => $partNumbers[$index % $partNumbers->count()]->id,
                'status_id' => $status->id,
                'document_number' => 'DOC-00' . ($index + 1),
                'version' => '1.' . $index,
                'file_path' => 'docs/sample-' . ($index + 1) . '.pdf',
                'notes' => 'Auto seeded review document #' . ($index + 1),
                'obsolete_date' => now()->addMonths(6),
                'reminder_date' => now()->addWeeks(2),
                'deadline' => now()->addWeeks(4),
                'user_id' => $user->id,
            ];

            DocumentMapping::firstOrCreate(
                ['document_number' => $mappingData['document_number']],
                $mappingData
            );
        }

        $this->command->info('✅ Document mappings berhasil dibuat dari document tabel!');
    }
}
