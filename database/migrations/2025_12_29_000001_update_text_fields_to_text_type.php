<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah VARCHAR(255) menjadi TEXT untuk field yang bisa berisi teks panjang
     */
    public function up(): void
    {
        // Update tt_auditee_actions table
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->text('root_cause')->change();
            $table->text('yokoten_area')->nullable()->change();
        });

        // Update tt_why_causes table
        Schema::table('tt_why_causes', function (Blueprint $table) {
            $table->text('why_description')->change();
            $table->text('cause_description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke VARCHAR(255)
        Schema::table('tt_auditee_actions', function (Blueprint $table) {
            $table->string('root_cause')->change();
            $table->string('yokoten_area')->nullable()->change();
        });

        Schema::table('tt_why_causes', function (Blueprint $table) {
            $table->string('why_description')->change();
            $table->string('cause_description')->change();
        });
    }
};
