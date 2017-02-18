<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagMutasiToPresonPegawaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_pegawais', function (Blueprint $table) {
            $table->integer('flag_mutasi')->default(0)->after('actor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('preson_pegawais', function (Blueprint $table) {
        //     //
        // });
    }
}
