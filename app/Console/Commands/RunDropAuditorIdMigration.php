<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RunDropAuditorIdMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:drop-auditor-column {--yes : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migration that removes auditor_id column from tt_audit_findings';

    public function handle(): int
    {
        // Verify pivot backfill: ensure pivot has mapping for each existing auditor_id
        $expected = (int) DB::table('tt_audit_findings')->whereNotNull('auditor_id')->count();
        $actual = (int) DB::table('tt_audit_finding_auditors')->distinct()->count('audit_finding_id');

        $this->info("Rows with non-null auditor_id: {$expected}");
        $this->info("Distinct audit_finding_id in pivot: {$actual}");

        if ($actual < $expected) {
            $missing = DB::table('tt_audit_findings')
                ->whereNotNull('auditor_id')
                ->whereNotIn('id', function ($q) {
                    $q->select('audit_finding_id')->from('tt_audit_finding_auditors');
                })
                ->limit(20)
                ->pluck('id')
                ->toArray();

            $this->error('Pivot table appears incomplete: some audit findings with auditor_id are not present in pivot.');
            $this->line('Sample missing audit_finding ids: ' . implode(', ', $missing));
            $this->line('Recommended: run `php artisan audit:backfill-auditors` and re-check before dropping the column.');

            if (! $this->option('yes')) {
                $this->error('Aborting migration. Use --yes to force run despite mismatch.');
                return 1;
            }
            $this->warn('Proceeding because --yes was provided.');
        }

        $confirm = $this->option('yes') || $this->confirm('Are you sure you want to run the migration that will DROP `auditor_id` on `tt_audit_findings`?');
        if (! $confirm) {
            $this->info('Aborted.');
            return 1;
        }

        $path = 'database/manual_migrations/2026_02_10_085721_remove_auditor_id_column_on_tt_audit_findings_table.php';

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
