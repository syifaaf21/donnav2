<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('part_numbers', function (Blueprint $table) {
            $table->enum('process', [
                'injection',
                'painting',
                'assembling body',
                'die casting',
                'machining',
                'assembling unit',
                'mounting',
                'assembling electric',
                'inspection'
            ])->nullable()->after('model_id');
        });
    }

    public function down(): void
    {
        Schema::table('part_numbers', function (Blueprint $table) {
            $table->dropColumn('process');
        });
    }
};

