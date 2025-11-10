<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('original_name');
            $table->timestamp('archived_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'archived_at']);
        });
    }
};

