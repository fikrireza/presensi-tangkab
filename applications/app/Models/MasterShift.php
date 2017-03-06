<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterShift extends Model
{
    protected $table = 'preson_master_shift';

    protected $fillable = ['nama_shift', 'jam_masuk', 'jam_pulang', 'keterangan'];

    public function shift()
    {
      return $this->hasMany('App\Models\Shift');
    }
}
