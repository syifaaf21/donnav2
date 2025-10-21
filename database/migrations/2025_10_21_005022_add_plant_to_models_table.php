<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->enum('plant', ['Body', 'Unit', 'Electric'])->after('name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('plant');
        });
    }
};
