<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
  protected $table = 'preson_harilibur';

  protected $fillable = ['libur','keterangan'];
}
