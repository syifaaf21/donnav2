<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->enum('plant', ['ALL', 'Body', 'Electric', 'Unit'])->nullable()->after('code');
        });
    }

    public function down()
    {
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->dropColumn('plant');
        });
    }
};
