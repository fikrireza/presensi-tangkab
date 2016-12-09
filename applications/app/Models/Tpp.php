<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tpp extends Model
{
    protected $table = 'preson_tpps';

    protected $fillable = ['pegawai_id','awal_periode','akhir_periode','terlambat','terlambat_potongan',
                            'pulcep','pulcep_potongan','terlambat_pulcep','terlambat_pulcep_potongan','tanpaketerangan',
                            'tanpaketerangan_potongan','tidak_apel','tidak_apel_potongan','tidak_apel_4','tidak_apel_4_potongan',
                            'status','actor'];
}
