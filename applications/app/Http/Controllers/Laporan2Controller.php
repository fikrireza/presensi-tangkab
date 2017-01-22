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
use App\Models\Apel;


use Auth;
use Validator;
use DB;
use PDF;

class Laporan2Controller extends Controller
{

    public function laporan2()
    {
      $getSkpd = skpd::select('id', 'nama')->get();

      return view('pages.laporan2.index', compact('getSkpd'));
    }

    public function laporan2Store(Request $request)
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
      $pegawainya = pegawai::select('id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'nama')->where('skpd_id', $skpd_id)->orderby('nama', 'asc')->get();

      $absensi = DB::select("select a.fid, nama, tanggal_log, jam_log
                            from (select fid, nama from preson_pegawais where skpd_id = '$skpd_id') as a
                            left join ta_log b on a.fid = b.fid
                            where str_to_date(b.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis)");
      // dd($absensi);

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
      // dd($hariLibur);
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
      // dd($hariApel);
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
      // dd($jumlahMasuk);
      // END =  Menghitung Jumlah Hadir dalam Periode


      // START = Get Data Intervensi
      $intervensi = intervensi::select('pegawai_id', 'tanggal_mulai', 'tanggal_akhir')->whereBetween('tanggal_akhir', array($start_date, $end_date))->where('flag_status', 1)->get();
      // dd($intervensi);
      // END = Get Data Intervensi


      // START = Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', '$skpd_id')
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // dd($pejabatDokumen);
      // END = Pejabat Dokumen Jika Login sebagai admin skpd


      return view('pages.laporan2.index', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'pegawainya', 'absensi', 'total_telat_dan_pulcep', 'start_date', 'end_date', 'hariLibur', 'hariApel', 'jumlahMasuk', 'intervensi', 'pejabatDokumen'));
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
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$start_date.' - '.$end_date.'.pdf');
      }

      return view('pages.laporan.cetakAdmin');
    }

}
