<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJamKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('preson_jam_kerja', function(Blueprint $table){
        $table->increments('id');
        $table->string('nama_jam_kerja', 50);
        $table->time('jam_masuk');
        $table->time('jam_masuk_awal');
        $table->time('jam_masuk_akhir');
        $table->time('jam_pulang');
        $table->time('jam_pulang_awal');
        $table->time('jam_pulang_akhir');
        $table->string('toleransi_terlambat', 3);
        $table->string('toleransi_pulcep', 3);
        $table->integer('flag_status')->length(1)->unsigned();
        $table->timestamps();
        // $table->unique(['jam_masuk', 'jam_masuk_awal', 'jam_masuk_akhir', 'jam_pulang', 'jam_pulang_awal', 'jam_pulang_akhir'],'unique_attributevalue_per_jam');
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
