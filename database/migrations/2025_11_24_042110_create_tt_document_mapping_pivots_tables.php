<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Model
        Schema::create('tt_document_mapping_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_mapping_id')->constrained('tt_document_mappings')->onDelete('cascade');
            $table->foreignId('model_id')->constrained('tm_models')->onDelete('cascade');
            $table->timestamps();
        });

        // Product
        Schema::create('tt_document_mapping_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_mapping_id')->constrained('tt_document_mappings')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('tm_products')->onDelete('cascade');
            $table->timestamps();
        });

        // Process
        Schema::create('tt_document_mapping_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_mapping_id')->constrained('tt_document_mappings')->onDelete('cascade');
            $table->foreignId('process_id')->constrained('tm_processes')->onDelete('cascade');
            $table->timestamps();
        });

        // Part Number
        Schema::create('tt_document_mapping_part_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_mapping_id')->constrained('tt_document_mappings')->onDelete('cascade');
            $table->foreignId('part_number_id')->constrained('tm_part_numbers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tt_document_mapping_part_numbers');
        Schema::dropIfExists('tt_document_mapping_processes');
        Schema::dropIfExists('tt_document_mapping_products');
        Schema::dropIfExists('tt_document_mapping_models');
    }
};

