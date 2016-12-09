<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'preson_jabatans';

    protected $fillable = ['nama','status','actor'];
}
