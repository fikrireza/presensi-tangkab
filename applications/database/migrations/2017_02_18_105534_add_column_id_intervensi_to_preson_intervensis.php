<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIdIntervensiToPresonIntervensis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('preson_intervensis', function($table){
        $table->integer('id_intervensi')->after('pegawai_id')->nullable()->unsigned();
        $table->foreign('id_intervensi')->references('id')->on('preson_master_intervensis');
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
