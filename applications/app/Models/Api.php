<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $table = 'preson_api';

    protected $fillable = ['api_key', 'deskripsi'];
}
