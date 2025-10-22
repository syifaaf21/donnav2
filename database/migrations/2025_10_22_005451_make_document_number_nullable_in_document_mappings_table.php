<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('document_mappings', function (Blueprint $table) {
            $table->string('document_number', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_mappings', function (Blueprint $table) {
            $table->string('document_number', 100)->nullable(false)->change();
        });
    }
};
