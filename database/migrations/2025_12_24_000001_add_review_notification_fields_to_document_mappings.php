<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->timestamp('last_approved_at')->nullable()->after('last_reminder_date');
            $table->timestamp('review_notified_at')->nullable()->after('last_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropColumn(['last_approved_at', 'review_notified_at']);
        });
    }
};
