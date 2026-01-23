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
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['document_mapping_id']);
            // Re-add with set null on delete
            $table->foreign('document_mapping_id')
                ->references('id')->on('tt_document_mappings')
                ->nullOnDelete(); // set null instead of cascade
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->dropForeign(['document_mapping_id']);
            $table->foreign('document_mapping_id')
                ->references('id')->on('tt_document_mappings')
                ->onDelete('cascade');
        });
    }
};
