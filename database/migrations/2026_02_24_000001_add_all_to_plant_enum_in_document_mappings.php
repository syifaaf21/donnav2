<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'all' to the plant enum values. Keep column nullable.
        DB::statement("ALTER TABLE `tt_document_mappings` MODIFY `plant` ENUM('body','unit','electric','all') DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Convert any 'all' values to NULL first to avoid invalid enum values.
        DB::statement("UPDATE `tt_document_mappings` SET `plant` = NULL WHERE `plant` = 'all';");
        DB::statement("ALTER TABLE `tt_document_mappings` MODIFY `plant` ENUM('body','unit','electric') DEFAULT NULL;");
    }
};
