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
        Schema::create('tt_document_mapping_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_mapping_id')->constrained('tt_document_mappings')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('tm_statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tt_document_mapping_status_histories');
    }
};
