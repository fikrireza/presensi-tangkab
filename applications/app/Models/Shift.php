<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'preson_shift_log';

    protected $fillable = ['fid', 'tanggal', 'shift_id', 'keterangan'];

    public function shift()
    {
      return $this->belongsTo('App\Models\MasterShift', 'shift_id');
    }
}
