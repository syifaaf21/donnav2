<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProcessColumnFromPartNumbersTable extends Migration
{
    public function up()
    {
        Schema::table('part_numbers', function (Blueprint $table) {
            $table->dropColumn('process');
        });
    }

    public function down()
    {
        Schema::table('part_numbers', function (Blueprint $table) {
            // Kalau rollback, kita tambahkan kolom process lagi (sesuaikan tipe data)
            $table->string('process')->nullable();
        });
    }
}
