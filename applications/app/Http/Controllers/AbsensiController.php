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

      // Mencari Hari Libur Dalam Periode Tertentu
      $potongHariLibur = harilibur::select('libur')->whereBetween('libur', array($start_date, $end_date))->get();
      $potongHariLibur->pluck('libur');
      if($potongHariLibur->isEmpty()){
        $hariLibur = array();
      }else{
        foreach ($potongHariLibur as $liburs) {
          $hariLibur[] = $liburs->libur;
        }
      }

      // Menghitung Jumlah Hadir dalam Periode dan Mencari Tanggal Intervesi
      $potongIntervensi = DB::select("SELECT pegawai.id, pegawai.nip_sapk, pegawai.nama as nama_pegawai, Tanggal_Mulai, Tanggal_Akhir, Jumlah_Masuk
                                    	FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

                                    	LEFT OUTER JOIN (select b.id as pegawai_id, a.tanggal_mulai as Tanggal_Mulai, a.tanggal_akhir as Tanggal_Akhir
                                    									  from preson_intervensis a, preson_pegawais b
                                    										where b.id = a.pegawai_id
                                    										and a.tanggal_mulai >= '$start_date'
                                    										and a.tanggal_akhir <= '$end_date'
                                    										and a.flag_status = 1) as tabel_Hari_Intervensi
                                    	ON pegawai.id = tabel_Hari_Intervensi.pegawai_id

                                    	LEFT OUTER JOIN(SELECT b.id as pegawai_id, b.nip_sapk, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
                                    										FROM ta_log a, preson_pegawais b
                                    										WHERE a.Fid = b.fid
                                    										AND b.skpd_id = '$skpd_id'
                                    										AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										group By b.id) as tabel_Jumlah_Masuk
                                    	ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");

      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai,
                                            pegawai.struktural,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, preson_pegawais.nama, fid, preson_strukturals.nama as struktural from preson_pegawais, preson_strukturals where preson_pegawais.skpd_id = '$skpd_id' and preson_pegawais.struktural_id = preson_strukturals.id) as pegawai

                                    	LEFT OUTER JOIN (select b.fid, b.nama, b.skpd_id, count(a.Jam_Log) as Jumlah_Terlambat
                                    										from ta_log a, preson_pegawais b
                                    										where a.Fid = b.fid
                                    										and b.skpd_id = '$skpd_id'
                                    										and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										and TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                    										and TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '08:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Datang_Terlambat
                                    	ON pegawai.fid = tabel_Jam_Datang_Terlambat.Fid
                                    	LEFT OUTER JOIN (select b.fid, b.nama, b.skpd_id, count(a.Jam_Log) as Jumlah_Pulcep
                                    										from ta_log a, preson_pegawais b
                                    										where a.Fid = b.fid
                                    										and b.skpd_id = '$skpd_id'
                                    										and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '16:00:00'
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai
                                      ORDER BY struktural asc");

      return view('pages.absensi.index', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'rekapAbsenPeriode', 'potongIntervensi', 'hariLibur', 'start_date', 'end_date'));
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


      return view('pages.absensi.absensiSKPD');
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

      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai, pegawai.struktural,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, preson_pegawais.nama, fid, preson_strukturals.nama as struktural from preson_pegawais, preson_strukturals where preson_pegawais.skpd_id = '$skpd_id' and preson_pegawais.struktural_id = preson_strukturals.id) as pegawai

                                    	LEFT OUTER JOIN (select b.fid, b.nama, b.skpd_id, count(a.Jam_Log) as Jumlah_Terlambat
                                    										from ta_log a, preson_pegawais b
                                    										where a.Fid = b.fid
                                    										and b.skpd_id = '$skpd_id'
                                    										and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										and TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                    										and TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '08:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Datang_Terlambat
                                    	ON pegawai.fid = tabel_Jam_Datang_Terlambat.Fid
                                    	LEFT OUTER JOIN (select b.fid, b.nama, b.skpd_id, count(a.Jam_Log) as Jumlah_Pulcep
                                    										from ta_log a, preson_pegawais b
                                    										where a.Fid = b.fid
                                    										and b.skpd_id = '$skpd_id'
                                    										and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '16:00:00'
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai
                                      ORDER BY struktural asc ");

      return view('pages.absensi.absensiSKPD', compact('start_dateR', 'end_dateR', 'rekapAbsenPeriode'));
    }
}
