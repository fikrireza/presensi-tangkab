<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\TaLog;
use App\Models\Skpd;
use App\Models\Log;


class ConvertController extends Controller
{
    public function log_to_preson_log()
    {

      $get_Ta_Log = TaLog::paginate(50);
      dd($get_Ta_Log);
    }


}
