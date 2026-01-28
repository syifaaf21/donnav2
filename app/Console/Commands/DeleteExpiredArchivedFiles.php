<?php

namespace App\Console\Commands;

// Ganti App\Models\File dengan Model yang Anda gunakan
use App\Models\DocumentFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredArchivedFiles extends Command
{
    protected $signature = 'archive:delete-expired';
    protected $description = 'Performs hard delete on files that have passed the 1-year archival limit.';

    public function handle()
    {
        $expiredFiles = DocumentFile::withTrashed()
                    ->whereNotNull('marked_for_deletion_at')
                    ->where('marked_for_deletion_at', '<=', now())
                    ->get();

        $count = 0;
        foreach ($expiredFiles as $file) {
            // Hapus dari Storage (PENTING: ini Hard Delete)
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Hapus dari Database (PENTING: ini Hard Delete)
            $file->forceDelete();
            $count++;
        }

        $this->info("Successfully hard-deleted {$count} expired archived files.");
        return Command::SUCCESS;
    }
}
