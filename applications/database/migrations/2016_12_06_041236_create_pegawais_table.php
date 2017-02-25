<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePegawaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preson_pegawais', function(Blueprint $table)
        {
          $table->increments('id');
          $table->string('nama', 50);
          $table->string('nip_sapk', 25)->unique();
          $table->string('nip_lm', 15);
          $table->string('fid', 22)->unique();
          $table->integer('skpd_id')->unsigned();
          $table->integer('golongan_id')->unsigned();
          $table->integer('struktural_id')->unsigned();
          $table->string('jabatan', 200);
          $table->date('tanggal_lahir');
          $table->string('tempat_lahir', 30);
          $table->string('pendidikan_terakhir', 50);
          $table->text('alamat');
          $table->double('tpp_dibayarkan');
          // 1 aktif
          // 2 non aktif
          // 3 pensiun
          // 4 meninggal
          $table->string('status')->default('1');
          $table->string('actor');
          $table->timestamps();
        });

        Schema::table('preson_users', function(Blueprint $table)
        {
          $table->integer('pegawai_id')->after('id')->unsigned();
          $table->foreign('pegawai_id')->references('id')->on('preson_pegawais');
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
