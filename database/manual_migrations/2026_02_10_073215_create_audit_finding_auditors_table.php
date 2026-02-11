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
        Schema::create('tt_audit_finding_auditors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_finding_id');
            $table->unsignedBigInteger('auditor_id');

            $table->foreign('audit_finding_id')->references('id')->on('tt_audit_findings')->onDelete('cascade');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['audit_finding_id', 'auditor_id'], 'tt_audit_finding_auditors_unique');
            $table->index('audit_finding_id');
            $table->index('auditor_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_audit_finding_auditors');
    }
};
