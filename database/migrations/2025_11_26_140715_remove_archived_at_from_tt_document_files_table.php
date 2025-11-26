<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghapus kolom 'archived_at'.
     */
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            
            if (Schema::hasColumn('tt_document_files', 'archived_at')) {
                // Kolom 'archived_at' adalah timestamp, tidak memiliki foreign key.
                $table->dropColumn('archived_at');
            }
        });
    }

    /**
     * Mengembalikan kolom 'archived_at' (untuk rollback).
     */
    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Mengembalikan kolom sesuai definisi sebelumnya (timestamp nullable, setelah is_active)
            $table->timestamp('archived_at')->nullable()->after('is_active');
        });
    }
};