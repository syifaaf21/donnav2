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
        Schema::create('tt_why_causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_action_id')->constrained('tt_auditee_actions')->onDelete('cascade');
            $table->string('why_description');
            $table->string('cause_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_why_causes');
    }
};
