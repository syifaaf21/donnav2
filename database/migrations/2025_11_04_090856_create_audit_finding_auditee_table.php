<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tt_audit_finding_auditee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_finding_id')->constrained('tt_audit_findings')->onDelete('cascade');
            $table->foreignId('auditee_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_audit_finding_auditee');
    }
};
