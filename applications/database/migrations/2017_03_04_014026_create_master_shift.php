<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterShift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_master_shift', function(Blueprint $table){
          $table->increments('id');
          $table->string('nama_shift', 50);
          $table->time('jam_masuk');
          $table->time('jam_pulang');
          $table->text('keterangan');
          $table->integer('actor')->unsigned();
          $table->timestamps();
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
