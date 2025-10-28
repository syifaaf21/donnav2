<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->string('plant')->nullable()->after('code');  // kolom plant nullable tanpa default, di posisi setelah code
        });
    }

    public function down()
    {
        Schema::table('tm_departments', function (Blueprint $table) {
            $table->dropColumn('plant');
        });
    }
};
