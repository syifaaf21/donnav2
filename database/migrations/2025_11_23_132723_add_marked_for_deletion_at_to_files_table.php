<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Kolom penanda kapan file ini harus dihapus permanen
            $table->timestamp('marked_for_deletion_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->dropColumn('marked_for_deletion_at');
        });
    }
};