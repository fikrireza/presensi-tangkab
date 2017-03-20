<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    protected $table = 'preson_jam_kerja';

    protected $fillable = ['nama_jam_kerja', 'jam_masuk', 'jam_masuk_awal', 'jam_masuk_akhir', 'jam_pulang', 'jam_pulang_awal', 'jam_pulang_akhir', 'toleransi_terlambat', 'toleransi_pulcep'];

    public function groupJamKerja()
    {
      return $this->hasMany('App\Models\JamKerjaGroup', 'jam_kerja_id');
    }
}
