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
        Schema::table('tt_user_audit_type', function (Blueprint $table) {
            // $table->dropUnique(['user_id', 'audit_id']); // Drop legacy unique constraint
            $table->unsignedBigInteger('user_role_id')->nullable()->after('user_id');
            $table->boolean('is_auditor')->default(false)->after('user_role_id');
            $table->boolean('is_lead_auditor')->default(false)->after('is_auditor');

            $table->foreign('user_role_id')->references('id')->on('tt_user_role')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_user_audit_type', function (Blueprint $table) {
            // $table->unique(['user_id', 'audit_id']);
            $table->dropForeign(['user_role_id']);
            $table->dropColumn(['user_role_id', 'is_auditor', 'is_lead_auditor']);
        });
    }
};
