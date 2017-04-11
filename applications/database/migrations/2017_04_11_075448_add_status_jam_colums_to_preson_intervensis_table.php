<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusJamColumsToPresonIntervensisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_intervensis', function (Blueprint $table) {
            //0 untuk tidak ceklis dan 1 untuk ceklis
            $table->integer('status_jam_datang')->after('flag_massal')->default(0)->unsigned();
            //0 untuk tidak ceklis dan 1 untuk ceklis
            $table->integer('status_jam_pulang')->after('status_jam_datang')->default(0)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
