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
        // Step 1: Hapus kolom lama
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->dropColumn('ldr_spv_signature');
            $table->dropColumn('dept_head_signature');
        });

        // Step 2: Tambahkan kolom boolean
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->boolean('ldr_spv_signature')->after('effectiveness_verification')->default(false);
            $table->boolean('dept_head_signature')->after('ldr_spv_signature')->default(false);
        });
    }

    public function down(): void
    {
        // Hapus kolom boolean
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->dropColumn('ldr_spv_signature');
            $table->dropColumn('dept_head_signature');
        });

        // Tambahkan kembali sebagai string
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->string('ldr_spv_signature')->after('effectiveness_verification')->nullable();
            $table->string('dept_head_signature')->after('ldr_spv_signature')->nullable();
        });
    }
};
