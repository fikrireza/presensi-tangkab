<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengecualian extends Model
{
    protected $table = 'preson_pengecualian_tpp';

    protected $fillable = ['nip_sapk','catatan','status','actor'];

    
}
