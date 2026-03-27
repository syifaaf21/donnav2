<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->string('docspace_file_id')->nullable()->after('file_path');
            $table->string('docspace_folder_id')->nullable()->after('docspace_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->dropColumn(['docspace_file_id', 'docspace_folder_id']);
        });
    }
};
