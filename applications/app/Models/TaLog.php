<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaLog extends Model
{
  protected $table = 'ta_log';

  protected $fillable = ['Fid','Tanggal_Log', 'Jam_Log'];
}
