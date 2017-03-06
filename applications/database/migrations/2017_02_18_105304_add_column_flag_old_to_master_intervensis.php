<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFlagOldToMasterIntervensis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_master_intervensis', function($table){
          // 1 = intervensi sebelum revisi bkppd
          // 0 = intervensi sesudah revisi bkppd
          $table->integer('flag_old')->after('nama_intervensi');
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
