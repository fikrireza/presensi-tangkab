<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesinApel extends Model
{
    protected $table = 'preson_mesinapel';

    protected $fillable = ['mach_id','catatan','flag_status','actor'];
}
