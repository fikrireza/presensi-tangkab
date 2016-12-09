<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Golongan extends Model
{
    protected $table = 'preson_golongans';

    protected $fillable = ['nama','status','actor'];
}
