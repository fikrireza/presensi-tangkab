<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'preson_log';

    protected $fillable = ['mach_id', 'fid', 'tanggal', 'jam_datang', 'jam_pulang', 'flag_apel', 'datetime'];

}
