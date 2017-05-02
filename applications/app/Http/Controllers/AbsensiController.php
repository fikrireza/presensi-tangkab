<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Intervensi;
use App\Models\HariLibur;
use App\Models\Apel;
use App\Models\PresonLog;


use Auth;
use Validator;
use DB;

class AbsensiController extends Controller
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

    public function index()
    {
      $getSkpd = skpd::select('id', 'nama')->get();

      return view('pages.absensi.index', compact('getSkpd'));
    }

    public function filterAdministrator(Request $request)
    {
      $getSkpd = skpd::select('id', 'nama')->get();
      $skpd_id = $request->skpd_id;
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      // Get Data Pegawai berdasarkan SKPD
      $pegawainya = pegawai::join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                            ->select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')->where('skpd_id', $skpd_id)
                            ->orderby('preson_strukturals.nama', 'asc')->get();

      $absensi = DB::select("select a.id, a.fid, nama, tanggal_log, jam_log
                            from (select id, fid, nama from preson_pegawais where skpd_id = '$skpd_id') as a
                            left join ta_log b on a.fid = b.fid
                            where str_to_date(b.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = a.id and flag_status = 1)");

      // START = Menghitung Total Datang Terlambat dan Pulang Cepat
      $date_from = strtotime($start_date);
      $date_to = strtotime($end_date);
      $jam_masuk = array();
      $jam_pulang = array();
      foreach ($pegawainya as $pegawai) {
        for ($i=$date_from; $i<=$date_to; $i+=86400) {
          $tanggalini = date('d/m/Y', $i);

          foreach ($absensi as $key) {
            if($tanggalini == $key->tanggal_log){
              if ($pegawai->fid == $key->fid) {
                $jammasuk1 = 80000;
                $jammasuk2 = 100000;
                $jamlog = (int) str_replace(':','',$key->jam_log);
                if( ($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
                  $jam_masuk[] = $key->fid.'-'.$tanggalini;
                }
              }
            }
          }

          foreach ($absensi as $key) {
            if($tanggalini == $key->tanggal_log){
              if ($pegawai->fid == $key->fid) {
                $jampulang1 = 140000;
                $jampulang2 = 160000;
                $jamlog = (int) str_replace(':','',$key->jam_log);
                if(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
                  $jam_pulang[] = $key->fid.'-'.$tanggalini;
                }
              }
            }
          }
        }
      }

      $jam_masuk = array_unique($jam_masuk);
      $jam_pulang = array_unique($jam_pulang);

      if(($jam_masuk==null) && ($jam_pulang==null)){
        $total_telat_dan_pulcep = '';
        $total_telat_dan_pulcep = collect($total_telat_dan_pulcep);
      }else{
        $total_telat_dan_pulcep = array_intersect($jam_masuk,$jam_pulang);
        $total_telat_dan_pulcep = collect(array_unique($total_telat_dan_pulcep));
      }
      // END = Menghitung Total Datang Terlambat dan Pulang Cepat


      // START = Mencari Hari Libur Dalam Periode Tertentu
      $potongHariLibur = harilibur::select('libur')->whereBetween('libur', array($start_date, $end_date))->get();
      if($potongHariLibur->isEmpty()){
        $hariLibur = array();
      }else{
        foreach ($potongHariLibur as $liburs) {
          $hariLibur[] = $liburs->libur;
        }
      }
      // END = Mencari Hari Libur Dalam Periode Tertentu

      // START = Mencari Hari Apel Dalam Periode Tertentu
      $potongApel = apel::select('tanggal_apel')->whereBetween('tanggal_apel', array($start_date, $end_date))->get();
      if($potongApel->isEmpty()){
        $hariApel = array();
      }else{
        foreach ($potongApel as $apel) {
          $hariApel[] = $apel->tanggal_apel;
        }
      }
      // END = Mencari Hari Apel Dalam Periode Tertentu

      // START =  Menghitung Jumlah Hadir dalam Periode
      $jumlahMasuk = DB::select("SELECT pegawai.id as pegawai_id, pegawai.nip_sapk, pegawai.nama as nama_pegawai, Jumlah_Masuk
                                FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

                                LEFT OUTER JOIN(SELECT b.id as pegawai_id, b.nip_sapk, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
                                    FROM ta_log a, preson_pegawais b
                                    WHERE a.Fid = b.fid
                                    AND b.skpd_id = '$skpd_id'
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') <= '10:00:00'
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                                    group By b.id) as tabel_Jumlah_Masuk
                                ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");
      // END =  Menghitung Jumlah Hadir dalam Periode


      // START = Get Data Intervensi
      $intervensi = intervensi::select('pegawai_id', 'tanggal_mulai', 'tanggal_akhir')->whereBetween('tanggal_akhir', array($start_date, $end_date))->where('flag_status', 1)->get();
      // END = Get Data Intervensi


      return view('pages.absensi.index', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'pegawainya', 'absensi', 'total_telat_dan_pulcep', 'start_date', 'end_date', 'hariLibur', 'hariApel', 'jumlahMasuk', 'intervensi', 'pejabatDokumen'));
    }


    public function detailPegawai()
    {
      $pegawai_id = pegawai::where('id', Auth::user()->pegawai_id)->select('fid', 'id')->first();

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
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
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
                              ->where('preson_pegawais.id', $pegawai_id->id)
                              ->where('preson_intervensis.tanggal_mulai', 'LIKE', '%'.$month.'%')
                              ->where('preson_intervensis.flag_status', 1)
                              ->get();

      $hariLibur = hariLibur::where('libur', 'LIKE', '____-'.$month.'-__')->get();

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
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
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
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.absensi.absensiSKPD')->with('getunreadintervensi', $getunreadintervensi);
    }

    public function filterAdmin(Request $request)
    {
      $skpd_id = Auth::user()->skpd_id;
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      // Get Data Pegawai berdasarkan SKPD
      $pegawainya = pegawai::join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                            ->select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')->where('skpd_id', $skpd_id)
                            ->orderby('preson_strukturals.nama', 'asc')->get();

      $absensi = DB::select("select a.id, a.fid, nama, tanggal_log, jam_log
                            from (select id, fid, nama from preson_pegawais where skpd_id = '$skpd_id') as a
                            left join ta_log b on a.fid = b.fid
                            where str_to_date(b.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = a.id and flag_status = 1)");

      // START = Menghitung Total Datang Terlambat dan Pulang Cepat
      $date_from = strtotime($start_date);
      $date_to = strtotime($end_date);
      $jam_masuk = array();
      $jam_pulang = array();
      foreach ($pegawainya as $pegawai) {
        for ($i=$date_from; $i<=$date_to; $i+=86400) {
          $tanggalini = date('d/m/Y', $i);

          foreach ($absensi as $key) {
            if($tanggalini == $key->tanggal_log){
              if ($pegawai->fid == $key->fid) {
                $jammasuk1 = 80000;
                $jammasuk2 = 100000;
                $jamlog = (int) str_replace(':','',$key->jam_log);
                if( ($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
                  $jam_masuk[] = $key->fid.'-'.$tanggalini;
                }
              }
            }
          }

          foreach ($absensi as $key) {
            if($tanggalini == $key->tanggal_log){
              if ($pegawai->fid == $key->fid) {
                $jampulang1 = 140000;
                $jampulang2 = 160000;
                $jamlog = (int) str_replace(':','',$key->jam_log);
                if(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
                  $jam_pulang[] = $key->fid.'-'.$tanggalini;
                }
              }
            }
          }
        }
      }

      $jam_masuk = array_unique($jam_masuk);
      $jam_pulang = array_unique($jam_pulang);

      if(($jam_masuk==null) && ($jam_pulang==null)){
        $total_telat_dan_pulcep = '';
        $total_telat_dan_pulcep = collect($total_telat_dan_pulcep);
      }else{
        $total_telat_dan_pulcep = array_intersect($jam_masuk,$jam_pulang);
        $total_telat_dan_pulcep = collect(array_unique($total_telat_dan_pulcep));
      }
      // END = Menghitung Total Datang Terlambat dan Pulang Cepat


      // START = Mencari Hari Libur Dalam Periode Tertentu
      $potongHariLibur = harilibur::select('libur')->whereBetween('libur', array($start_date, $end_date))->get();
      if($potongHariLibur->isEmpty()){
        $hariLibur = array();
      }else{
        foreach ($potongHariLibur as $liburs) {
          $hariLibur[] = $liburs->libur;
        }
      }
      // END = Mencari Hari Libur Dalam Periode Tertentu

      // START = Mencari Hari Apel Dalam Periode Tertentu
      $potongApel = apel::select('tanggal_apel')->whereBetween('tanggal_apel', array($start_date, $end_date))->get();
      if($potongApel->isEmpty()){
        $hariApel = array();
      }else{
        foreach ($potongApel as $apel) {
          $hariApel[] = $apel->tanggal_apel;
        }
      }
      // END = Mencari Hari Apel Dalam Periode Tertentu

      // START =  Menghitung Jumlah Hadir dalam Periode
      $jumlahMasuk = DB::select("SELECT pegawai.id as pegawai_id, pegawai.nip_sapk, pegawai.nama as nama_pegawai, Jumlah_Masuk
                                FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

                                LEFT OUTER JOIN(SELECT b.id as pegawai_id, b.nip_sapk, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
                                    FROM ta_log a, preson_pegawais b
                                    WHERE a.Fid = b.fid
                                    AND b.skpd_id = '$skpd_id'
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') <= '10:00:00'
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                                    group By b.id) as tabel_Jumlah_Masuk
                                ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");
      // END =  Menghitung Jumlah Hadir dalam Periode


      // START = Get Data Intervensi
      $intervensi = intervensi::select('pegawai_id', 'tanggal_mulai', 'tanggal_akhir')->whereBetween('tanggal_akhir', array($start_date, $end_date))->where('flag_status', 1)->get();
      // END = Get Data Intervensi

      return view('pages.absensi.absensiSKPD', compact('start_dateR', 'end_dateR', 'pegawainya', 'absensi', 'total_telat_dan_pulcep', 'start_date', 'end_date', 'hariLibur', 'hariApel', 'jumlahMasuk', 'intervensi', 'pejabatDokumen'));
    }

    public function absenHariSKPD()
    {
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.absensi.absenHariSKPD')->with('getunreadintervensi', $getunreadintervensi);
    }

    public function absenHariSKPDStore(Request $request)
    {
      $getskpd = Auth::user()->skpd_id;
      if($getskpd == null){
        abort(404);
      }

      $tanggalini = $request->start_date;
      $pegawainya = pegawai::join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                              ->select('preson_pegawais.*')
                              ->where('skpd_id', $getskpd)
                              ->where('preson_pegawais.status', 1)
                              ->orderby('preson_strukturals.nama', 'asc')
                              ->get();


      $absensi = PresonLog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'preson_log.fid')
                          ->select('preson_log.*', 'preson_pegawais.nama as nama_pegawai')
                          ->where('tanggal', $tanggalini)
                          ->where('preson_pegawais.skpd_id', $getskpd)
                          ->get();

      return view('pages.absensi.absenHariSKPD', compact('absensi', 'pegawainya', 'tanggalini'));
    }

    public function absenHariAdministrator()
    {
      $getSkpd = skpd::all();

      return view('pages.absensi.absenHariAdministrator', compact('getSkpd'));
    }

    public function absenHariAdministratorStore(Request $request)
    {
      $getSkpd = skpd::all();

      $skpd_id = skpd::where('id', $request->skpd_id)->first();
      $skpd_id = $skpd_id->id;

      if($getSkpd == null){
        abort(404);
      }

      $tanggalini = $request->start_date;
      $pegawainya = pegawai::join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                              ->select('preson_pegawais.*')
                              ->where('skpd_id', $skpd_id)
                              ->where('preson_pegawais.status', 1)
                              ->orderby('preson_strukturals.nama', 'asc')
                              ->get();

      $absensi = PresonLog::join('preson_pegawais', 'preson_pegawais.fid', '=', 'preson_log.fid')
                          ->select('preson_log.*', 'preson_pegawais.nama as nama_pegawai')
                          ->where('tanggal', $tanggalini)
                          ->where('preson_pegawais.skpd_id', $skpd_id)
                          ->get();

      return view('pages.absensi.absenHariAdministrator', compact('getSkpd', 'absensi', 'tanggalini', 'pegawainya', 'skpd_id', 'data'));
    }


}
