<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Struktural extends Model
{
    protected $table = 'preson_strukturals';

    protected $fillable = ['nama','status','actor'];
}
