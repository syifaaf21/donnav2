<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tm_processes', function (Blueprint $table) {
            $table->enum('plant', ['ALL', 'Body', 'Electric', 'Unit',])->after('code')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tm_processes', function (Blueprint $table) {
            $table->dropColumn('plant');
        });
    }
};
