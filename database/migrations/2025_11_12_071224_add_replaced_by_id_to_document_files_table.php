<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Tambahkan kolom replaced_by_id
            $table->unsignedBigInteger('replaced_by_id')->nullable()->after('original_name');

            // Tambahkan foreign key
            $table->foreign('replaced_by_id')
                  ->references('id')
                  ->on('tt_document_files')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Drop foreign key dulu
            $table->dropForeign(['replaced_by_id']);

            // Drop kolom
            $table->dropColumn('replaced_by_id');
        });
    }
};
