<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tm_documents', function (Blueprint $table) {
            $table->timestamp('marked_for_deletion_at')->nullable(); // menambah kolom marked_for_deletion_at
        });
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->timestamp('marked_for_deletion_at')->nullable(); // menambah kolom marked_for_deletion_at
        });
    }

    public function down(): void
    {
        Schema::table('tm_documents', function (Blueprint $table) {
            $table->dropColumn('marked_for_deletion_at');
        });
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropColumn('marked_for_deletion_at');
        });
    }
};
