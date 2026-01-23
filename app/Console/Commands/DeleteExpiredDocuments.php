<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentMapping;
use Illuminate\Console\Command;

class DeleteExpiredDocuments extends Command
{
    protected $signature = 'documents:delete-expired';
    protected $description = 'Hard delete documents and mappings where marked_for_deletion_at has passed.';

    public function handle()
    {
        // Hapus DocumentMapping yang sudah expired
        $expiredMappings = DocumentMapping::whereNotNull('marked_for_deletion_at')
            ->where('marked_for_deletion_at', '<=', now())
            ->get();
        $mappingCount = 0;
        foreach ($expiredMappings as $mapping) {
            $mapping->delete();
            $mappingCount++;
        }

        // Hapus Document yang sudah expired
        $expiredDocuments = Document::whereNotNull('marked_for_deletion_at')
            ->where('marked_for_deletion_at', '<=', now())
            ->get();
        $docCount = 0;
        foreach ($expiredDocuments as $doc) {
            $doc->delete();
            $docCount++;
        }

        $this->info("Deleted $mappingCount expired document mappings and $docCount expired documents.");
        return Command::SUCCESS;
    }
}
