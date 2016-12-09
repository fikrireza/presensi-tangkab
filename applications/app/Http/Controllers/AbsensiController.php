<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;
use App\Intervensi;
use App\HariLibur;

use Auth;
use Validator;
use DB;

class AbsensiController extends Controller
{



    public function index()
    {
      $absensi = talog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'ta_log.Fid')
                    ->select('ta_log.Tanggal_Log', 'ta_log.Jam_Log')
                    ->where('preson_pegawais.id', Auth::user()->pegawai_id)
                    ->orderBy('ta_log.DateTime', 'desc')
                    ->get();

      return view('pages.absensi.index', compact('absensi'));
    }
}
