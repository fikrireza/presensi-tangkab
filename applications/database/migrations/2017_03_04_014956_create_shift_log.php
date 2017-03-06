<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_shift_log', function(Blueprint $table){
          $table->increments('id');
          $table->string('fid', 15);
          $table->date('tanggal');
          $table->integer('shift_id')->unsigned();
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
