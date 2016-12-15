<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_users', function (Blueprint $table) {
          $table->increments('id');
    			$table->string('nip_sapk', 30)->unique();
          $table->string('nama', 50);
    			$table->string('email')->unique();
    			$table->string('password', 60);
    			$table->integer('role_id')->unsigned();
          $table->integer('skpd_id')->unsigned();
    			$table->boolean('seen')->default(false);
    			$table->boolean('valid')->default(false);
    			$table->boolean('confirmed')->default(false);
    			$table->string('confirmation_code')->nullable();
    			$table->timestamps();
    			$table->rememberToken();
        });
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
