<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'preson_pegawais';

    protected $fillable = ['nama','nip_sapk','nip_lm','tanggal_lahir',
                          'tempat_lahir','pendidikan_terakhir','alamat','status','actor'];
}
