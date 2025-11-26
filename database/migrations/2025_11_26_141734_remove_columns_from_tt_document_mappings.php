<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {

            // Hapus foreign key part_number_id
            if (Schema::hasColumn('tt_document_mappings', 'part_number_id')) {
                $table->dropForeign(['part_number_id']);
                $table->dropColumn('part_number_id');
            }

            // Hapus foreign key model_id
            if (Schema::hasColumn('tt_document_mappings', 'model_id')) {
                $table->dropForeign(['model_id']);
                $table->dropColumn('model_id');
            }

            // Hapus foreign key product_id
            if (Schema::hasColumn('tt_document_mappings', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }

            // Hapus kolom process_id (biasa enum/varchar)
            if (Schema::hasColumn('tt_document_mappings', 'process_id')) {
                $table->dropForeign('tt_document_mappings_process_id_foreign');
                $table->dropColumn('process_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            // Tambahkan kembali kolom (jika rollback)
            $table->foreignId('part_number_id')->nullable()->constrained('tm_part_numbers');
            $table->foreignId('model_id')->nullable()->constrained('tm_model');
            $table->foreignId('product_id')->nullable()->constrained('tm_product');
            $table->string('process_id')->nullable(); // atau enum sesuai aslinya
        });
    }
};