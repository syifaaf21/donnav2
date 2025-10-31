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
        Schema::create('tt_auditee_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_finding_id')->constrained('tt_audit_findings')->onDelete('cascade');
            $table->string('pic');
            $table->string('root_cause');
            $table->boolean('yokoten')->default(false);
            $table->string('yokoten_area')->nullable();
            $table->boolean('verified_by_auditor')->default(false);
            $table->string('ldr_spv_signature')->nullable();
            $table->string('dept_head_signature')->nullable();
            $table->string('auditor_signature')->nullable();
            $table->string('lead_auditor_signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_auditee_actions');
    }
};
