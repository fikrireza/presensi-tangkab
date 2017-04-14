<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'preson_jurnal';

    protected $fillable = ['skpd_id','bulan','tahun','jumlah_tpp','flag_sesuai'];
}
