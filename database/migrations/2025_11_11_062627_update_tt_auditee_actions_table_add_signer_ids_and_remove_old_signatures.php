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
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn(['auditor_signature', 'lead_auditor_signature']);

            // Tambah kolom baru (foreign keys)
            $table->foreignId('ldr_spv_id')->after('ldr_spv_signature')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dept_head_id')->after('dept_head_signature')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('auditor_id')->after('dept_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_auditor_id')->after('auditor_id')->nullable()->constrained('users')->nullOnDelete();

            // Tambah kolom acknowledge boolean
            $table->boolean('acknowledge_by_lead_auditor')->after('verified_by_auditor')->default(false);
        });
    }

    /**
     * Kembalikan perubahan.
     */
    public function down(): void
    {
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            // Kembalikan kolom lama
            $table->string('auditor_signature')->nullable();
            $table->string('lead_auditor_signature')->nullable();

            // Hapus kolom baru
            $table->dropForeign(['ldr_spv_id']);
            $table->dropForeign(['dept_head_id']);
            $table->dropForeign(['auditor_id']);
            $table->dropForeign(['lead_auditor_id']);
            $table->dropColumn([
                'ldr_spv_id',
                'dept_head_id',
                'auditor_id',
                'lead_auditor_id',
                'acknowledge_by_lead_auditor',
            ]);
        });
    }
};
