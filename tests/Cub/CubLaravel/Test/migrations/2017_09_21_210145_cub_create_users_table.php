<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CubCreateUsersTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username');
            $table->string('email');
            $table->timestamp('last_login')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert([
            [
                'id' => 1,
                'first_name' => 'do not remove of modify',
                'last_name' => 'user for tests',
                'username' => 'ivelum',
                'email' => 'support@ivelum.com',
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
        Schema::drop('users');
    }
}
