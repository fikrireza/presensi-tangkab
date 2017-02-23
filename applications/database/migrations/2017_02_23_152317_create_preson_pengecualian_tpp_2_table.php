<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePresonPengecualianTpp2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_pengecualian_tpp', function (Blueprint $table) {
           $table->increments('id');
          $table->string('nip_sapk', 50)->nullable();
          $table->string('catatan')->nullable();
          $table->integer('status')->default(1);
          $table->string('actor')->nullable();
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
        Schema::table('preson_pengecualian_tpp', function (Blueprint $table) {
            //
        });
    }
}
