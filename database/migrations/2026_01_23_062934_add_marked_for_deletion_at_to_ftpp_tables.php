<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'tt_audit_findings',
            'tt_auditee_actions',
            'tt_audit_finding_auditee',
            'tt_audit_finding_sub_klausul',
            'tt_corrective_actions',
            'tt_preventive_actions',
            'tt_why_causes',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (!Schema::hasColumn($tableBlueprint->getTable(), 'marked_for_deletion_at')) {
                    $tableBlueprint->timestamp('marked_for_deletion_at')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tt_audit_findings',
            'tt_auditee_actions',
            'tt_audit_finding_auditee',
            'tt_audit_finding_sub_klausul',
            'tt_corrective_actions',
            'tt_preventive_actions',
            'tt_why_causes',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (Schema::hasColumn($tableBlueprint->getTable(), 'marked_for_deletion_at')) {
                    $tableBlueprint->dropColumn('marked_for_deletion_at');
                }
            });
        }
    }
};
