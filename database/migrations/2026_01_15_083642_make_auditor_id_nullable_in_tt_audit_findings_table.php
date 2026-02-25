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
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->unsignedBigInteger('auditor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->unsignedBigInteger('auditor_id')->nullable(false)->change();
        });
    }
};
