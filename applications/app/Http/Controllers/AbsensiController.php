<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Intervensi;
use App\Models\HariLibur;


use Auth;
use Validator;
use DB;

class AbsensiController extends Controller
{



    public function index()
    {
      $getSkpd = skpd::select('id', 'nama')->get();

      return view('pages.absensi.index', compact('getSkpd'));
    }


    public function detailPegawai()
    {
      $pegawai_id = pegawai::where('id', Auth::user()->pegawai_id)->select('fid')->first();

      $month = date('m');
      $year = "2016";

      $start_date = "01-".$month."-".$year;
      $start_time = strtotime($start_date);

      $end_time = strtotime("+1 month", $start_time);
      for($i=$start_time; $i<$end_time; $i+=86400)
      {
        $tanggalBulan[] = date('d/m/Y', $i);
        $tanggalini = date('d/m/Y', $i);
        $list[] = DB::select("SELECT c.nama AS skpd, b.id as pegawai_id, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                  and Fid = '$pegawai_id->fid') as Jam_Datang,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                  and Fid = '$pegawai_id->fid') as Jam_Pulang
                              FROM ta_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.Fid = b.fid
                              AND a.Fid = '$pegawai_id->fid'
                              AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                              LIMIT 1");
      }

      $absensi = collect($list);

      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                              ->select('preson_pegawais.id as pegawai_id', 'preson_intervensis.tanggal_mulai', 'preson_intervensis.jumlah_hari', 'preson_intervensis.tanggal_akhir', 'preson_intervensis.deskripsi')
                              ->where('preson_pegawais.id', $pegawai_id)
                              ->where('preson_intervensis.tanggal_mulai', 'LIKE', '%'.$month.'%')
                              ->where('preson_intervensis.flag_status', 1)
                              ->get();

      $hariLibur = hariLibur::where('libur', 'LIKE', '%'.$month.'%')->get();

      return view('pages.absensi.absensiPegawai', compact('absensi', 'tanggalBulan', 'intervensi', 'hariLibur'));
    }

    public function filterMonth(Request $request)
    {
      $pegawai_id = pegawai::where('id', Auth::user()->pegawai_id)->select('fid')->first();

      $month = $request->pilih_bulan;
      $year = "2016";

      $start_date = "01-".$month."-".$year;
      $start_time = strtotime($start_date);

      $end_time = strtotime("+1 month", $start_time);
      for($i=$start_time; $i<$end_time; $i+=86400)
      {
        $tanggalBulan[] = date('d/m/Y', $i);
        $tanggalini = date('d/m/Y', $i);
        $list[] = DB::select("SELECT c.nama AS skpd, b.id as pegawai_id, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                  and Fid = '$pegawai_id->fid') as Jam_Datang,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                  and Fid = '$pegawai_id->fid') as Jam_Pulang
                              FROM ta_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.Fid = b.fid
                              AND a.Fid = '$pegawai_id->fid'
                              AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                              LIMIT 1");
      }

      $absensi = collect($list);

      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                              ->select('preson_pegawais.id as pegawai_id', 'preson_intervensis.tanggal_mulai', 'preson_intervensis.jumlah_hari', 'preson_intervensis.tanggal_akhir', 'preson_intervensis.deskripsi')
                              ->where('preson_pegawais.id', $pegawai_id)
                              ->where('preson_intervensis.tanggal_mulai', 'LIKE', '%'.$month.'%')
                              ->where('preson_intervensis.flag_status', 1)
                              ->get();

      $hariLibur = hariLibur::where('libur', 'LIKE', '%'.$month.'%')->get();

      return view('pages.absensi.absensiPegawaiFilter', compact('absensi', 'tanggalBulan', 'intervensi', 'hariLibur', 'month'));
    }

    public function absenSKPD()
    {


      return view('pages.absensi.absensiSKPD');
    }
}
