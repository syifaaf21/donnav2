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
        if (Schema::hasTable('tt_audit_findings')) {
            Schema::table('tt_audit_findings', function (Blueprint $table) {
                if (!Schema::hasColumn('tt_audit_findings', 'is_sent_to_auditee')) {
                    $table->boolean('is_sent_to_auditee')->default(false)->after('status_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tt_audit_findings')) {
            Schema::table('tt_audit_findings', function (Blueprint $table) {
                if (Schema::hasColumn('tt_audit_findings', 'is_sent_to_auditee')) {
                    $table->dropColumn('is_sent_to_auditee');
                }
            });
        }
    }
};
