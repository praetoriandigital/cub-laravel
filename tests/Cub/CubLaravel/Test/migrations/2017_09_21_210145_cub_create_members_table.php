<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubCreateMembersTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('organization')->nullable();
            $table->string('organization_id')->nullable();
            $table->string('user')->nullable();
            $table->string('user_id')->nullable();
            $table->string('invitation')->nullable();
            $table->string('personal_id')->nullable();
            $table->string('post_id')->nullable();
            $table->string('notes')->nullable();
            $table->string('is_active')->nullable();
            $table->string('is_admin')->nullable();
            $table->string('positions')->nullable();
            $table->string('group_membership')->nullable();
            $table->string('created')->nullable();
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
        Schema::drop('members');
    }
}
