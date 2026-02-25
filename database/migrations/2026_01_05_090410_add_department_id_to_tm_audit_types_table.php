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
        Schema::table('tm_audit_types', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('tm_departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_audit_types', function (Blueprint $table) {
            $table->dropForeignIdFor('tm_departments');
        });
    }
};
