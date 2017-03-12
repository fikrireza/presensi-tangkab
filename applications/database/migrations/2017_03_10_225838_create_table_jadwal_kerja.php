<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJadwalKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_jadwal_kerja', function(Blueprint $table){
          $table->increments('id');
          $table->integer('skpd_id')->unsigned();
          $table->date('periode_awal');
          $table->date('periode_akhir');
          $table->string('jam_kerja_group');
          $table->integer('flag_status')->length(10)->unsigned();
          $table->timestamps();
        });

        Schema::table('preson_jadwal_kerja', function($table) {
          $table->foreign('skpd_id')->references('id')->on('preson_skpd');
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
