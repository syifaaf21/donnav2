<?php

namespace App\Console\Commands;

use App\Models\UserAuditType;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUserAuditTypeRole extends Command
{
    protected $signature = 'users:sync-audit-type-role
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Populate user_role_id, is_auditor, and is_lead_auditor for existing tt_user_audit_type records that are missing user_role_id.';

    public function handle(): int
    {
        // Drop the old UNIQUE(user_id, audit_id) constraint if it still exists.
        // One user can hold both Auditor and Lead Auditor roles, so the same
        // audit_type must be allowed to appear multiple times (once per role).
        $this->dropLegacyUniqueIfExists();

        $dryRun = $this->option('dry-run');

        $records = UserAuditType::whereNull('user_role_id')->get();

        if ($records->isEmpty()) {
            $this->info('No records need updating. All tt_user_audit_type rows already have user_role_id.');
            return self::SUCCESS;
        }

        $this->info("Found {$records->count()} record(s) with missing user_role_id.");
        if ($dryRun) {
            $this->warn('[DRY-RUN] No changes will be written.');
        }

        $updated  = 0;
        $skipped  = 0;
        $warnings = [];

        // Group records per user to minimise queries
        $byUser = $records->groupBy('user_id');

        foreach ($byUser as $userId => $userRecords) {
            // Fetch all auditor/lead-auditor UserRole rows for this user
            $auditorUserRoles = UserRole::with('role')
                ->where('user_id', $userId)
                ->get()
                ->filter(fn($ur) => $ur->role && stripos($ur->role->name, 'auditor') !== false)
                ->values();

            if ($auditorUserRoles->isEmpty()) {
                $warnings[] = "User {$userId}: no auditor role found — skipping {$userRecords->count()} record(s).";
                $skipped += $userRecords->count();
                continue;
            }

            if ($auditorUserRoles->count() > 1) {
                $roleNames = $auditorUserRoles->map(fn($ur) => $ur->role->name)->join(', ');
                $warnings[] = "User {$userId}: multiple auditor roles ({$roleNames}) — duplicating each audit-type entry per role.";
            }

            foreach ($userRecords as $record) {
                $firstRole = true;

                foreach ($auditorUserRoles as $ur) {
                    $isAuditor     = stripos($ur->role->name, 'auditor') !== false
                                     && stripos($ur->role->name, 'lead') === false;
                    $isLeadAuditor = stripos($ur->role->name, 'lead auditor') !== false;

                    $this->line(sprintf(
                        "  user_id=%-4s  audit_id=%-4s  user_role_id=%-4s  is_auditor=%s  is_lead_auditor=%s%s",
                        $userId,
                        $record->audit_id,
                        $ur->id,
                        $isAuditor ? '1' : '0',
                        $isLeadAuditor ? '1' : '0',
                        $dryRun ? '  [DRY-RUN]' : ''
                    ));

                    if ($dryRun) {
                        $updated++;
                        $firstRole = false;
                        continue;
                    }

                    if ($firstRole) {
                        // Update the existing null record in place
                        $record->update([
                            'user_role_id'    => $ur->id,
                            'is_auditor'      => $isAuditor,
                            'is_lead_auditor' => $isLeadAuditor,
                        ]);
                        $firstRole = false;
                    } else {
                        // Insert an additional row for each extra auditor role,
                        // but only if that combination doesn't already exist.
                        $exists = UserAuditType::where('user_id', $userId)
                            ->where('audit_id', $record->audit_id)
                            ->where('user_role_id', $ur->id)
                            ->exists();

                        if (!$exists) {
                            UserAuditType::create([
                                'user_id'         => $userId,
                                'audit_id'        => $record->audit_id,
                                'user_role_id'    => $ur->id,
                                'is_auditor'      => $isAuditor,
                                'is_lead_auditor' => $isLeadAuditor,
                            ]);
                        }
                    }
                    $updated++;
                }
            }
        }

        foreach ($warnings as $w) {
            $this->warn($w);
        }

        $this->info($dryRun
            ? "[DRY-RUN] Would have updated/created {$updated} row(s). Skipped {$skipped} record(s)."
            : "Done. Updated/created {$updated} row(s). Skipped {$skipped} record(s)."
        );

        return self::SUCCESS;
    }

    /**
     * Drop the legacy UNIQUE(user_id, audit_id) index from tt_user_audit_type
     * if it is still present on the database.
     *
     * MySQL refuses to drop an index that is the sole covering index for a
     * foreign key column. We add plain single-column indexes on user_id and
     * audit_id first (if they don't already exist), so the foreign keys have
     * a supporting index and MySQL allows the unique index to be dropped.
     */
    private function dropLegacyUniqueIfExists(): void
    {
        $indexes = DB::select(
            "SHOW INDEX FROM tt_user_audit_type WHERE Key_name = 'tt_user_audit_type_user_id_audit_id_unique'"
        );

        if (empty($indexes)) {
            return;
        }

        // Ensure plain index on user_id exists
        $hasUserIdx = DB::select(
            "SHOW INDEX FROM tt_user_audit_type WHERE Key_name = 'tt_user_audit_type_user_id_index'"
        );
        if (empty($hasUserIdx)) {
            DB::statement('ALTER TABLE tt_user_audit_type ADD INDEX tt_user_audit_type_user_id_index (user_id)');
        }

        // Ensure plain index on audit_id exists
        $hasAuditIdx = DB::select(
            "SHOW INDEX FROM tt_user_audit_type WHERE Key_name = 'tt_user_audit_type_audit_id_index'"
        );
        if (empty($hasAuditIdx)) {
            DB::statement('ALTER TABLE tt_user_audit_type ADD INDEX tt_user_audit_type_audit_id_index (audit_id)');
        }

        // Now safe to drop the unique index
        DB::statement('ALTER TABLE tt_user_audit_type DROP INDEX tt_user_audit_type_user_id_audit_id_unique');
        $this->warn('Dropped legacy UNIQUE(user_id, audit_id) constraint from tt_user_audit_type.');
    }
}
