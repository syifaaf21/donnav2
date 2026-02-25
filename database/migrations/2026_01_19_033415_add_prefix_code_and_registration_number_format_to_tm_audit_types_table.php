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
        Schema::table('tm_audit_types', function (Blueprint $table) {
            $table->string('prefix_code', 10)->nullable()->after('name');
            $table->string('registration_number_format', 100)->nullable()->after('prefix_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_audit_types', function (Blueprint $table) {
            $table->dropColumn(['prefix_code', 'registration_number_format']);
        });
    }
};
