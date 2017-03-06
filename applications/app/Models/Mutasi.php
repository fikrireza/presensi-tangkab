<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    protected $table = 'preson_mutasi';

    protected $fillable = ['pegawai_id','skpd_id_old','skpd_id_new','tanggal_mutasi','keterangan','tpp_dibayarkan','nomor_sk','tanggal_sk','upload_sk','actor','flag_mutasi'];

    public function pegawai()
	{
  		return $this->belongsTo('App\Models\Pegawai', 'pegawai_id');
	}

	public function skpd_old()
	{
  		return $this->belongsTo('App\Models\Skpd', 'skpd_id_old');
	}

	public function skpd_new()
	{
  		return $this->belongsTo('App\Models\Skpd', 'skpd_id_new');
	}
}
