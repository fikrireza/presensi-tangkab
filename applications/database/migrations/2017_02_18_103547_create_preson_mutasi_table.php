<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePresonMutasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_mutasi', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pegawai_id')->unsigned()->nullable();
            $table->integer('skpd_id_old')->unsigned()->nullable();
            $table->integer('skpd_id_new')->unsigned()->nullable();
            $table->date('tanggal_mutasi')->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('tpp_dibayarkan');
            $table->string('actor', 255);
            $table->timestamps();
        });

        Schema::table('preson_mutasi', function(Blueprint $table){
            $table->foreign('pegawai_id')->references('id')->on('preson_pegawais');
        });

        Schema::table('preson_mutasi', function(Blueprint $table){
            $table->foreign('skpd_id_old')->references('id')->on('preson_skpd');
        });

        Schema::table('preson_mutasi', function(Blueprint $table){
            $table->foreign('skpd_id_new')->references('id')->on('preson_skpd');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('preson_mutasi', function (Blueprint $table) {
        //     //
        // });
    }
}
