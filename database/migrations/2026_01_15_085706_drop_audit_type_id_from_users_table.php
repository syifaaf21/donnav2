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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['audit_type_id']);
            $table->dropColumn('audit_type_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('audit_type_id')
                ->nullable()
                ->constrained('tm_audit_types')
                ->cascadeOnDelete();
        });
    }

};
