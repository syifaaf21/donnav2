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
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->nullable()->after('part_number_id');
            $table->unsignedBigInteger('product_id')->nullable()->after('model_id');
            $table->unsignedBigInteger('process_id')->nullable()->after('product_id');

            $table->foreign('model_id')->references('id')->on('tm_models')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('tm_products')->onDelete('set null');
            $table->foreign('process_id')->references('id')->on('tm_processes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['process_id']);
            $table->dropColumn(['model_id', 'product_id', 'process_id']);
        });
    }
};
