<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PurgeSoftDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ftpp:purge-soft-deleted {--days=0 : Age in days to keep soft deleted records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted FTTP records older than configured age';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = \Carbon\Carbon::now()->subDays($days);

        $this->info('Carbon now: ' . \Carbon\Carbon::now());
        $this->info('Threshold: ' . $threshold);

        $models = [
            \App\Models\AuditFinding::class,
            \App\Models\AuditeeAction::class,
            \App\Models\CorrectiveAction::class,
            \App\Models\PreventiveAction::class,
            \App\Models\DocumentFile::class,
            \App\Models\WhyCauses::class,
            \App\Models\AuditFindingAuditee::class,
            \App\Models\AuditFindingSubKlausul::class,
        ];

        foreach ($models as $modelClass) {
            try {
                $modelInstance = new $modelClass;
                $deletedAtCol = $modelInstance->getDeletedAtColumn();

                $query = $modelClass::withoutGlobalScopes()
                    ->whereNotNull($deletedAtCol)
                    ->whereDate($deletedAtCol, '<=', $threshold->toDateString());

                $count = $query->count();
                if ($count > 0) {
                    $this->info("Purging {$count} records from {$modelClass} (column: {$deletedAtCol})");
                    $query->forceDelete();
                } else {
                    $this->info("No records to purge for {$modelClass}");
                }
            } catch (\Throwable $e) {
                \Log::error('PurgeSoftDeleted error for ' . $modelClass . ': ' . $e->getMessage());
                $this->error('Error purging ' . $modelClass . ': ' . $e->getMessage());
            }
        }

        $this->info('Purge completed');
        return 0;
    }
}
