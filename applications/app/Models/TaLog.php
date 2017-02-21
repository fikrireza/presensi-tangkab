<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaLog extends Model
{
  protected $table = 'ta_log';

  public $timestamps = false;

  protected $fillable = ['id', 'Mach_id', 'Fid', 'Nama_Staff', 'Kondisi', 'Verifikasi', 'In_out', 'Tanggal_Log', 'Jam_Log',
                          'tgl_input', 'user_input', 'TA_MarkSMS', 'Pilih', 'DateTime'];
}
