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
        Schema::table('tt_download_report', function (Blueprint $table) {
            $table->unsignedBigInteger('document_file_id')->nullable()->after('document_mapping_id');
            $table->foreign('document_file_id')
                ->references('id')
                ->on('tt_document_files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_download_report', function (Blueprint $table) {
            $table->dropForeign(['document_file_id']);
            $table->dropColumn('document_file_id');
        });
    }
};
