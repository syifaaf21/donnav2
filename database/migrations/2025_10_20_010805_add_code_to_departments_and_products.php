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
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });

        Schema::table('tm_products', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('tm_products', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
