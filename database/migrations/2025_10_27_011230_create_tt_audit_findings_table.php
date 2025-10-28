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
        Schema::create('tt_audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('tm_audits')->onDelete('cascade');
            $table->foreignId('sub_audit_id')->constrained('tm_sub_audits')->onDelete('cascade')->nullable();
            $table->foreignId('finding_category_id')->constrained('tm_finding_categories')->onDelete('cascade');
            $table->foreignId('sub_klausul_id')->constrained('tm_sub_klausuls')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('tm_departments')->onDelete('cascade');
            $table->foreignId('process_id')->constrained('tm_processes')->onDelete('cascade');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('auditee_id')->constrained('users')->onDelete('cascade');
            $table->string('registration_number');
            $table->text('finding_description');
            $table->foreignId('status_id')->constrained('tm_statuses')->onDelete('cascade');
            $table->dateTime('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_audit_findings');
    }
};
