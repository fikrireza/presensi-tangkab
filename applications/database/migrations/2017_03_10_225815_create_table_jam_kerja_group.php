<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJamKerjaGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('preson_jam_kerja_group', function(Blueprint $table){
        $table->increments('id');
        $table->string('nama_group', 50);
        $table->integer('group_id')->unsigned();
        $table->integer('jam_kerja_id')->unsigned();
        $table->integer('flag_status')->length(1)->unsigned();
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
