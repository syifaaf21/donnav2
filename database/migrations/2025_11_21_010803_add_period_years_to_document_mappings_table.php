<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->unsignedTinyInteger('period_years')->default(1)->after('obsolete_date')
                ->comment('Jumlah tahun periode dokumen sebelum kadaluarsa');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropColumn('period_years');
        });
    }
};
