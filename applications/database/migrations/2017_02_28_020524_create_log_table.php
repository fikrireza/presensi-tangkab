<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_log', function(Blueprint $table){
          $table->increments('id');
          $table->string('mach_id', 10);
          $table->string('fid', 15);
          $table->date('tanggal');
          $table->time('jam_datang');
          $table->time('jam_pulang');
          $table->integer('flag_apel')->default(0)->unsigned();
          $table->string('datetime', 25);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
