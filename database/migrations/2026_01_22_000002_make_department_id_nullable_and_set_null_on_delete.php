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
        // Ubah kolom department_id menjadi nullable
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
        // Drop FK lama dan buat FK baru dengan onDelete set null
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->foreign('department_id')
                ->references('id')->on('tm_departments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')
                ->references('id')->on('tm_departments')
                ->onDelete('cascade'); // default lama, sesuaikan jika perlu
        });
    }
};
