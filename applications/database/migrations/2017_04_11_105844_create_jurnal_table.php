<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJurnalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_jurnal', function(Blueprint $table){
          $table->increments('id');
          $table->integer('skpd_id')->unsigned();
          $table->string('bulan', 2);
          $table->string('tahun', 4);
          $table->string('jumlah_tpp');
          // Sesuai = 1 adalah jika jumlah tpp skpd telah sesuai dan tidak dapat diedit lagi
          $table->integer('flag_sesuai')->unsigned()->default(0);
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
