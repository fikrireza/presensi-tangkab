<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'preson_pegawais';

    protected $fillable = ['nama','nip_sapk','nip_lm', 'fid', 'tanggal_lahir', 'jabatan',
                          'tempat_lahir','pendidikan_terakhir','alamat','status','actor'];
}
