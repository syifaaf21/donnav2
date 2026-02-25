<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class RunCreatePivotMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:create-pivot {--yes : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migration that creates tt_audit_finding_auditors pivot table only';

    public function handle(): int
    {
        $table = 'tt_audit_finding_auditors';
        if (Schema::hasTable($table)) {
            $this->info("Table `{$table}` already exists. Nothing to do.");
            return 0;
        }

        $confirm = $this->option('yes') || $this->confirm('This will run the migration that creates the pivot table `tt_audit_finding_auditors`. Continue?');
        if (! $confirm) {
            $this->info('Aborted.');
            return 1;
        }

        $path = 'database/manual_migrations/2026_02_10_073215_create_audit_finding_auditors_table.php';
        $this->info('Running migration: ' . $path);
        try {
            Artisan::call('migrate', ['--path' => $path, '--force' => true]);
            $this->info(Artisan::output());
            $this->info('Migration completed.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
