<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervensi extends Model
{
    protected $table = 'preson_intervensis';

    protected $fillable = ['pegawai_id','jenis_intervensi','jumlah_hari','tanggal_mulai','tanggal_akhir','deskripsi'];
}
