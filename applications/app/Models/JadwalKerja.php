<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'preson_jadwal_kerja';

    protected $fillable = ['skpd_id', 'periode_awal', 'periode_akhir', 'jam_kerja_group', 'flag_status'];
}
