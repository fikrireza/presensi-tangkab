<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apel extends Model
{
  protected $table = 'preson_apel';

  protected $fillable = ['tanggal_apel','keterangan','actor'];
}
