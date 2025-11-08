<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            // Tambah kolom baru
            $table->unsignedBigInteger('audit_finding_id')->nullable()->after('document_mapping_id');
            $table->unsignedBigInteger('auditee_action_id')->nullable()->after('audit_finding_id');

            // Tambah foreign key manual
            $table->foreign('audit_finding_id')
                ->references('id')->on('tt_audit_findings')
                ->onDelete('cascade');

            $table->foreign('auditee_action_id')
                ->references('id')->on('tt_auditee_actions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tt_document_files', function (Blueprint $table) {
            $table->dropForeign(['audit_finding_id']);
            $table->dropForeign(['auditee_action_id']);
            $table->dropColumn(['audit_finding_id', 'auditee_action_id']);
        });
    }

};
