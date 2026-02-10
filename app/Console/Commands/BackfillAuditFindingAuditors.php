<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAuditFindingAuditors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:backfill-auditors {--chunk=200 : Number of rows to process per chunk} {--dry : Do not perform inserts, only show counts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill existing tt_audit_findings.auditor_id values into tt_audit_finding_auditors pivot table (idempotent)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $dry = (bool) $this->option('dry');

        $total = DB::table('tt_audit_findings')->whereNotNull('auditor_id')->count();
        if ($total === 0) {
            $this->info('No rows with auditor_id to backfill.');
            return 0;
        }

        $this->info("Total rows to process: {$total}");
        if ($dry) {
            $this->info('Dry run enabled — no inserts will be performed.');
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::table('tt_audit_findings')
            ->select('id', 'auditor_id')
            ->whereNotNull('auditor_id')
            ->orderBy('id')
            ->chunk($chunk, function ($rows) use (&$bar, $dry) {
                $now = now();
                $insert = [];
                foreach ($rows as $r) {
                    if (empty($r->auditor_id)) {
                        continue;
                    }
                    $insert[] = [
                        'audit_finding_id' => $r->id,
                        'auditor_id' => $r->auditor_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($insert)) {
                    if ($dry) {
                        // do nothing
                    } else {
                        // idempotent upsert based on unique keys
                        DB::table('tt_audit_finding_auditors')->upsert(
                            $insert,
                            ['audit_finding_id', 'auditor_id'],
                            ['updated_at']
                        );
                    }
                }

                $bar->advance(count($rows));
            });

        $bar->finish();
        $this->newLine(2);
        $this->info('Backfill completed.');

        return 0;
    }
}
