<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubCreateOrganizationsTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('employees')->nullable();
            $table->string('tags')->nullable();
            $table->string('country')->nullable();
            $table->string('country_id')->nullable();
            $table->string('state')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('hr_phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('created')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('organizations')->insert([
        [
        'id' => 1,
        'name' => 'Testy',
        'created_at' => '2013-12-13 15:35:33',
        'updated_at' => '2016-03-21 16:12:18',
        ],
        ]);
    }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
    public function down()
    {
        Schema::drop('organizations');
    }
}
