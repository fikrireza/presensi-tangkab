<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;

use Auth;
use DB;
use Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $hari = date('Y-m-d');
        $waktu = date('H:i:s');
        $terlambat = '08:00:00';
        $pulcep = '16:00:00';

        // dd($hari, $waktu, $terlambat, $pulcep);

        if(session('status') == 'admin')
        {
          $absensi = talog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'ta_log.Fid')
                      ->select('preson_pegawais.nama','ta_log.Tanggal_Log', 'ta_log.Jam_Log')
                      ->where('preson_pegawais.skpd_id', '=', Auth::user()->skpd_id)
                      ->groupBy('ta_log.Tanggal_Log')
                      ->get();


            // $output = [];
            // foreach($data as $entry) {
            //     $output[$entry->day] = $entry->count;
            // }

        }else{
          $absensi = talog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'ta_log.Fid')
                      ->select('preson_pegawais.nama','ta_log.Tanggal_Log', 'ta_log.Jam_Log')
                      ->where('preson_pegawais.id', Auth::user()->pegawai_id)
                      ->orderBy('ta_log.Tanggal_Log', 'desc')
                      ->get();
          // dd($absensi)
        }

        return view('home', compact('absensi'));
    }
    
}
