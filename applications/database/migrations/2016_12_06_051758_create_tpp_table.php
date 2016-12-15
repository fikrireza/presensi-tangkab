<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_tpps', function(Blueprint $table)
        {
          $table->increments('id');
          $table->integer('pegawai_id')->unsigned();
          $table->date('awal_periode');
          $table->date('akhir_periode');
          $table->integer('terlambat')->unsigned()->default(0);
          $table->double('terlambat_potongan', 15);
          $table->integer('pulcep')->unsigned()->default(0);
          $table->double('pulcep_potongan', 15);
          $table->integer('terlambat_pulcep')->unsigned()->default(0);
          $table->double('terlambat_pulcep_potongan', 15);
          $table->integer('tanpaketerangan')->unsigned()->default(0);
          $table->double('tanpaketerangan_potongan', 15);
          $table->integer('tidak_apel')->unsigned()->default(0);
          $table->double('tidak_apel_potongan', 15);
          $table->integer('tidak_apel_4')->unsigned()->default(0);
          $table->double('tidak_apel_4_potongan', 15);
          $table->string('status')->default('1');
          $table->string('actor');
          $table->timestamps();
        });

        Schema::table('preson_tpps', function(Blueprint $table){
          $table->foreign('pegawai_id')->references('id')->on('preson_pegawais');
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
