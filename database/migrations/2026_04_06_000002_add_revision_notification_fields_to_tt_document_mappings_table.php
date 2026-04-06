<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->json('revision_notification_department_ids')->nullable()->after('review_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_mappings', function (Blueprint $table) {
            $table->dropColumn(['revision_notification_department_ids']);
        });
    }
};