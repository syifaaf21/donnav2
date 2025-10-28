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
        Schema::create('tt_document_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('tm_documents')->onDelete('cascade');
            $table->foreignId('part_number_id')->nullable()->constrained('tm_part_numbers')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('tm_statuses')->onDelete('cascade');
            $table->string('document_number');
            $table->text('notes')->nullable();
            $table->date('obsolete_date')->nullable();
            $table->date('reminder_date')->nullable();
            $table->date('deadline')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('tm_departments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_document_mappings');
    }
};
