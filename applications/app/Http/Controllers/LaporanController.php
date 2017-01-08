<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Intervensi;
use App\Models\HariLibur;
use App\Models\PejabatDokumen;


use Auth;
use Validator;
use DB;
use PDF;

class LaporanController extends Controller
{


    public function laporanAdministrator(){
      $getSkpd = skpd::select('id', 'nama')->get();

      return view('pages.laporan.laporanAdministrator', compact('getSkpd'));
    }

    public function laporanAdministratorStore(Request $request)
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
                                    	FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = $skpd_id) as pegawai

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
                                    										AND b.skpd_id = $skpd_id
                                    										AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										group By b.id) as tabel_Jumlah_Masuk
                                    	ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");

      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai, pegawai.tpp_dibayarkan,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid, tpp_dibayarkan from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai ");

      return view('pages.laporan.laporanAdministrator', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'rekapAbsenPeriode', 'potongIntervensi', 'hariLibur', 'start_date', 'end_date'));
    }

    public function cetakAdministrator(Request $request)
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

                      	LEFT OUTER JOIN (SELECT b.id as pegawai_id, b.nip_sapk, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
                      										FROM ta_log a, preson_pegawais b
                      										WHERE a.Fid = b.fid
                      										AND b.skpd_id = '$skpd_id'
                      										AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                      										group By b.id) as tabel_Jumlah_Masuk
                      	ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");

      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai, pegawai.tpp_dibayarkan,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid, tpp_dibayarkan from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai ");

      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      view()->share('getSkpd', $getSkpd);
      view()->share('skpd_id', $skpd_id);
      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('rekapAbsenPeriode', $rekapAbsenPeriode);
      view()->share('potongIntervensi', $potongIntervensi);
      view()->share('hariLibur', $hariLibur);
      view()->share('start_date', $start_date);
      view()->share('end_date', $end_date);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('nama_skpd', $nama_skpd);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdministrator')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.'.pdf');
      }

      return view('pages.laporan.cetakAdministrator');
    }


    public function laporanAdmin()
    {


      return view('pages.laporan.laporanAdmin');
    }

    public function laporanAdminStore(Request $request)
    {
      $skpd_id = Auth::user()->skpd_id;
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
                                    	FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = $skpd_id) as pegawai

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
                                    										AND b.skpd_id = 15
                                    										AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										group By b.id) as tabel_Jumlah_Masuk
                                    	ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");


      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai, pegawai.tpp_dibayarkan,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid, tpp_dibayarkan from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai ");

      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();

      return view('pages.laporan.laporanAdmin', compact('start_dateR', 'end_dateR', 'rekapAbsenPeriode', 'potongIntervensi', 'hariLibur', 'start_date', 'end_date', 'pejabatDokumen'));
    }

    public function cetakAdmin(Request $request)
    {
      $skpd_id = Auth::user()->skpd_id;
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
                                    	FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = $skpd_id) as pegawai

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
                                    										AND b.skpd_id = 15
                                    										AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                                    										group By b.id) as tabel_Jumlah_Masuk
                                    	ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");


      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai, pegawai.tpp_dibayarkan,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid, tpp_dibayarkan from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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
                                    										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '15:00:00'
                                    										GROUP BY a.Fid) as tabel_Jam_Pulang_Cepat
                                    	ON pegawai.fid = tabel_Jam_Pulang_Cepat.Fid
                                    	GROUP BY nama_pegawai ");

      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('rekapAbsenPeriode', $rekapAbsenPeriode);
      view()->share('potongIntervensi', $potongIntervensi);
      view()->share('hariLibur', $hariLibur);
      view()->share('start_date', $start_date);
      view()->share('end_date', $end_date);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('nama_skpd', $nama_skpd);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdmin')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.'.pdf');
      }

      return view('pages.laporan.cetakAdmin');
    }

    public function laporanPegawai()
    {
      return view('pages.laporan.laporanPegawai');
    }

    public function laporanPegawaiStore(Request $request)
    {
      $nip_sapk = $request->nip_sapk;
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      // Mencari jadwal intervensi pegawai dalam periode tertentu
      $intervensi = DB::select("select a.tanggal_mulai, a.tanggal_akhir, a.deskripsi
                                from preson_intervensis a, preson_pegawais b
                                where a.pegawai_id = b.id
                                and b.nip_sapk = '$nip_sapk'
                                and a.flag_status = 1");

      // Mencari Hari Libur Dalam Periode Tertentu
      $hariLibur = harilibur::select('libur', 'keterangan')->whereBetween('libur', array($start_date, $end_date))->get();

      // Mengambil data Absen Pegawai per Periode
      $rekapAbsenPeriode = DB::select("select b.nip_sapk, b.nama, a.Tanggal_Log, a.Jam_Log, a.DateTime
                                        from ta_log a, preson_pegawais b
                                        where a.Fid = b.fid
                                        and b.nip_sapk = '$nip_sapk'
                                        and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'");

      return view('pages.laporan.laporanPegawai', compact('start_dateR', 'end_dateR', 'intervensi', 'rekapAbsenPeriode', 'hariLibur', 'start_date', 'end_date', 'nip_sapk'));
    }

    public function cetakPegawai(Request $request)
    {
      $nip_sapk = $request->nip_sapk;
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      // Mencari jadwal intervensi pegawai dalam periode tertentu
      $intervensi = DB::select("select a.tanggal_mulai, a.tanggal_akhir, a.deskripsi
                                from preson_intervensis a, preson_pegawais b
                                where a.pegawai_id = b.id
                                and b.nip_sapk = '$nip_sapk'
                                and a.flag_status = 1");

      // Mencari Hari Libur Dalam Periode Tertentu
      $hariLibur = harilibur::select('libur', 'keterangan')->whereBetween('libur', array($start_date, $end_date))->get();

      // Mengambil data Absen Pegawai per Periode
      $rekapAbsenPeriode = DB::select("select b.nip_sapk, b.nama, a.Tanggal_Log, a.Jam_Log, a.DateTime
                                        from ta_log a, preson_pegawais b
                                        where a.Fid = b.fid
                                        and b.nip_sapk = '$nip_sapk'
                                        and str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'");

      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('rekapAbsenPeriode', $rekapAbsenPeriode);
      view()->share('intervensi', $intervensi);
      view()->share('hariLibur', $hariLibur);
      view()->share('start_date', $start_date);
      view()->share('end_date', $end_date);
      view()->share('nip_sapk', $nip_sapk);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakPegawai')->setPaper('a4', 'potrait');
        return $pdf->download('Presensi Online - '.$nip_sapk.'.pdf');
      }

      return view('pages.laporan.cetakPegawai');
    }
}
