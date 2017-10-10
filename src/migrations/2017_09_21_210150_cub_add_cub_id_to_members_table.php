<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubAddCubIdToMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = App::make(Config::get('cub::config.maps.cub_member.model'))->table;
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('cub_id')->after('id')->default('');
            });

            DB::statement('update '.$tableName.' set cub_id = id');

            Schema::table($tableName, function (Blueprint $table) {
                $table->unique('cub_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = App::make(Config::get('cub::config.maps.cub_member.model'))->table;
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('cub_id');
            });
        }
    }
}
