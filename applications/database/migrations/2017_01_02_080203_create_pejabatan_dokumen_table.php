<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePejabatanDokumenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_pejabat_dokumen', function(Blueprint $table){
          $table->increments('id');
          $table->integer('pegawai_id')->unsigned()->nullable();
          // Posisi 1 = kanan; 2 = kiri;
          $table->integer('posisi_ttd')->unsigned()->nullable();
          $table->string('pangkat', 250);
          $table->string('jabatan', 250);
          $table->integer('flag_status')->unsigned();
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
