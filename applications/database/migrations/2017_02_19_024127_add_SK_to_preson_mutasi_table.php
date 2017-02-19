<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSKToPresonMutasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preson_mutasi', function (Blueprint $table) {
            $table->string('nomor_sk',255)->after('tpp_dibayarkan')->nullable();
            $table->date('tanggal_sk')->after('nomor_sk')->nullable();
            $table->longText('upload_sk')->after('tanggal_sk')->nullable();
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
