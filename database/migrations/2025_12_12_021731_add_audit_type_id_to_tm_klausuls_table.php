<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tm_klausuls', function (Blueprint $table) {
            $table->foreignId('audit_type_id')
                ->nullable()
                ->constrained('tm_audit_types')
                ->nullOnDelete()
                ->after('nama_kolom_sebelumnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_klausuls', function (Blueprint $table) {
            $table->dropForeign(['audit_type_id']);
            $table->dropColumn('audit_type_id');
        });
    }
};
