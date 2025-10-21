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
        Schema::table('part_numbers', function (Blueprint $table) {
            $table->unsignedBigInteger('process_id')->nullable()->after('model_id');

            // Optional: tambahkan constraint FK
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('part_numbers', function (Blueprint $table) {
            $table->dropForeign(['process_id']);
            $table->dropColumn('process_id');
        });
    }
};
