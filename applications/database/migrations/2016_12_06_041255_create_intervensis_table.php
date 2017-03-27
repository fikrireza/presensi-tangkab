<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntervensisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_intervensis', function(Blueprint $table)
        {
          $table->increments('id');
          $table->integer('pegawai_id')->unsigned();
          $table->string('jenis_intervensi', 50);
          $table->integer('jumlah_hari')->unsigned()->nullable();
          $table->date('tanggal_mulai')->nullable();
          $table->date('tanggal_akhir')->nullable();
          $table->text('deskripsi');
          $table->string('berkas', 150)->nullable();
          // 0 = Belum di approve; 1 = Sudah di Approve; 2 = Tidak di Approve; 3 dibatalkan.
          $table->integer('flag_status')->unsigned();
          $table->integer('actor');
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
