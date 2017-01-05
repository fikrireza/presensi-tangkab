<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PejabatDokumen extends Model
{
  protected $table = 'preson_pejabat_dokumen';

  protected $fillable = ['pegawai_id', 'posisi_ttd', 'pangkat', 'jabatan', 'flag_status'];
}
