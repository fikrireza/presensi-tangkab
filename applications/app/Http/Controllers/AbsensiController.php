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
      // $absensi = talog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'ta_log.Fid')
      //               ->select('ta_log.Tanggal_Log', 'ta_log.Jam_Log')
      //               ->where('preson_pegawais.id', Auth::user()->pegawai_id)
      //               ->orderBy('ta_log.DateTime', 'desc')
      //               ->get();


      $pegawai_id = Auth::user()->pegawai_id;

      $tpp = pegawai::where('id', $pegawai_id)->select('tpp_dibayarkan', 'fid')->first();

      $month = "04";
      $year = "2016";

      $start_date = "01-".$month."-".$year;
      $start_time = strtotime($start_date);

      $end_time = strtotime("+1 month", $start_time);

      for($i=$start_time; $i<$end_time; $i+=86400)
      {
        $tanggalini = date('d/m/Y', $i);
        $list[] = DB::select("SELECT c.nama AS skpd, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime, DATE_FORMAT(CURDATE(),  '%d/%m/%Y') as hari_ini,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                  and Fid = '$tpp->fid') as Jam_Datang,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                  and Fid = '$tpp->fid') as Jam_Pulang
                              FROM ta_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.Fid = b.fid
                              AND a.Fid = '$tpp->fid'
                              AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                              LIMIT 1");
      }
      $absensi = collect($list);

      return view('pages.absensi.index', compact('absensi'));
    }
}
