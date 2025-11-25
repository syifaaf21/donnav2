<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropRoleIdAndDepartmentIdFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign and column for role_id if present
            if (Schema::hasColumn('users', 'role_id')) {
                try {
                    $table->dropForeign(['role_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key does not exist or name differs
                }
                $table->dropColumn('role_id');
            }

            // Drop foreign and column for department_id if present
            if (Schema::hasColumn('users', 'department_id')) {
                try {
                    $table->dropForeign(['department_id']);
                } catch (\Exception $e) {
                    // ignore
                }
                $table->dropColumn('department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
            }
        });
    }
}
