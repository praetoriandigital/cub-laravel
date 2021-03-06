<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubAddCubIdToOrganizationsTable extends Migration
{
    const CUB_ID = 'cub_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($modelName = config('cub.maps.organization.model')) {
            $tableName = app()->make($modelName)->getTable();
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, self::CUB_ID)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string(self::CUB_ID)->after('id')->default('');
                });

                DB::statement('update '.$tableName.' set '.self::CUB_ID.' = id');

                Schema::table($tableName, function (Blueprint $table) {
                    $table->unique(self::CUB_ID);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($modelName = config('cub.maps.organization.model')) {
            $tableName = app()->make($modelName)->getTable();
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, self::CUB_ID)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(self::CUB_ID);
                });
            }
        }
    }
}
