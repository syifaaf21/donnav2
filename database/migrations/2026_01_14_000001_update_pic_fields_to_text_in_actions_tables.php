<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change PIC fields from VARCHAR(255) to TEXT to support rich text content
     */
    public function up(): void
    {
        // Update tt_corrective_actions table
        Schema::table('tt_corrective_actions', function (Blueprint $table) {
            $table->text('pic')->change();
        });

        // Update tt_preventive_actions table
        Schema::table('tt_preventive_actions', function (Blueprint $table) {
            $table->text('pic')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to VARCHAR(255)
        Schema::table('tt_corrective_actions', function (Blueprint $table) {
            $table->string('pic')->change();
        });

        Schema::table('tt_preventive_actions', function (Blueprint $table) {
            $table->string('pic')->change();
        });
    }
};
