<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubCreateGroupsMembersTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
    public function up()
    {
        Schema::create('groups_members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group');
            $table->string('member');
            $table->boolean('is_admin');
            $table->string('created');
            $table->timestamps();
            $table->softDeletes();
        });
    }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
    public function down()
    {
        Schema::drop('groups_members');
    }
}
