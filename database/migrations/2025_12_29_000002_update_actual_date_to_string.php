<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah actual_date dari dateTime menjadi string agar bisa diisi dengan tanggal atau "-"
     */
    public function up(): void
    {
        // Update tt_corrective_actions table
        Schema::table('tt_corrective_actions', function (Blueprint $table) {
            $table->string('actual_date')->nullable()->change();
        });

        // Update tt_preventive_actions table
        Schema::table('tt_preventive_actions', function (Blueprint $table) {
            $table->string('actual_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke dateTime
        Schema::table('tt_corrective_actions', function (Blueprint $table) {
            $table->dateTime('actual_date')->nullable()->change();
        });

        Schema::table('tt_preventive_actions', function (Blueprint $table) {
            $table->dateTime('actual_date')->nullable()->change();
        });
    }
};
