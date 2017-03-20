<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFlagMassalToPresonIntervensisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_intervensis', function (Blueprint $table) {
            // 0: bukan dari revisi massal ; 1: dari revisi massal;
            $table->integer('flag_massal')->default(0)->after('flag_view');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('preson_intervensis', function (Blueprint $table) {
        //     //
        // });
    }
}
