<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagMutasiTpPresonMutasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_mutasi', function (Blueprint $table) {
            // 0 mati dan 1 hidup
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
        Schema::table('preson_mutasi', function (Blueprint $table) {
            //
        });
    }
}
