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
        // Drop foreign key constraints first
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['process_id']);
            $table->dropForeign(['finding_category_id']);
            $table->dropForeign(['audit_type_id']);
            $table->dropForeign(['auditor_id']);
        });
        // Make department_id, product_id, process_id, finding_category_id, audit_type_id, auditor_id nullable
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('process_id')->nullable()->change();
            $table->foreignId('finding_category_id')->nullable()->change();
            $table->foreignId('audit_type_id')->nullable()->change();
            $table->foreignId('auditor_id')->nullable()->change();
        });
        // Add new foreign keys with set null on delete
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')
                ->on('tm_departments')
                ->nullOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('tm_products')
                ->nullOnDelete();
            $table->foreign('process_id')
                ->references('id')
                ->on('tm_processes')
                ->nullOnDelete();
            $table->foreign('finding_category_id')
                ->references('id')
                ->on('tm_finding_categories')
                ->nullOnDelete();
            $table->foreign('audit_type_id')
                ->references('id')
                ->on('tm_audit_types')
                ->nullOnDelete();
            $table->foreign('auditor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the modified foreign keys
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['process_id']);
            $table->dropForeign(['finding_category_id']);
            $table->dropForeign(['audit_type_id']);
            $table->dropForeign(['auditor_id']);
        });
        // Make department_id, product_id, process_id, finding_category_id, audit_type_id, auditor_id not nullable and restore cascade
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable(false)->change();
            $table->foreignId('product_id')->nullable(false)->change();
            $table->foreignId('process_id')->nullable(false)->change();
            $table->foreignId('finding_category_id')->nullable(false)->change();
            $table->foreignId('audit_type_id')->nullable(false)->change();
            $table->foreignId('auditor_id')->nullable(false)->change();
        });
        Schema::table('tt_audit_findings', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')
                ->on('tm_departments')
                ->onDelete('cascade')
                ->change();
            $table->foreign('product_id')
                ->references('id')
                ->on('tm_products')
                ->onDelete('cascade')
                ->change();
            $table->foreign('process_id')
                ->references('id')
                ->on('tm_processes')
                ->onDelete('cascade')
                ->change();
            $table->foreign('finding_category_id')
                ->references('id')
                ->on('tm_finding_categories')
                ->onDelete('cascade')
                ->change();
            $table->foreign('audit_type_id')
                ->references('id')
                ->on('tm_audit_types')
                ->onDelete('cascade')
                ->change();
            $table->foreign('auditor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->change();
        });
    }
};
