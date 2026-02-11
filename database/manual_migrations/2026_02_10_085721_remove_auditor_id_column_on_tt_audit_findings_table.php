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
        // Safely drop foreign key and auditor_id column if exists
        if (Schema::hasTable('tt_audit_findings')) {
            Schema::table('tt_audit_findings', function (Blueprint $table) {
                // drop foreign key if exists (Laravel will resolve by column name)
                try {
                    $table->dropForeign(['auditor_id']);
                } catch (\Throwable $e) {
                    // ignore if constraint does not exist
                }

                if (Schema::hasColumn('tt_audit_findings', 'auditor_id')) {
                    $table->dropColumn('auditor_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore auditor_id column (nullable) and FK to users
        if (Schema::hasTable('tt_audit_findings')) {
            Schema::table('tt_audit_findings', function (Blueprint $table) {
                if (!Schema::hasColumn('tt_audit_findings', 'auditor_id')) {
                    $table->unsignedBigInteger('auditor_id')->nullable()->after('product_id');
                    $table->foreign('auditor_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }
};
