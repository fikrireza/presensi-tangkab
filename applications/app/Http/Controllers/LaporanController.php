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


      $absensi = DB::select("select a.id, a.nip_sapk, a.fid, nama, tanggal_log, jam_log, DateTime
                            from (select id, nip_sapk, fid, nama from preson_pegawais where skpd_id = '$skpd_id') as a
                            left join ta_log b on a.fid = b.fid
                            where str_to_date(b.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                            AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = a.id and flag_status = 1)
                            ORDER BY DateTime ASC");
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
                $jampulang1 = 140000;
                $jampulang2 = 155900;
                $jamlog = (int) str_replace(':','',$key->jam_log);
                if(($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
                  $jam_masuk[] = ["fid" => $key->fid, "nip_sapk" => $key->nip_sapk,"tanggal" => $tanggalini, "jam_telat" => $key->jam_log, "jam_pulcep" => ''];
                }elseif(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
                  $jam_pulang[] = array("fid" => $key->fid, "nip_sapk" => $key->nip_sapk,"tanggal" => $tanggalini, "jam_telat" => '', "jam_pulcep" => $key->jam_log);
                }
              }
            }
          }
        }
      };

// return $jam_masuk[0]['jam_pulcep'];
      // $track=0;
      // foreach ($jam_masuk as $masuk) {
      //   foreach ($jam_pulang as $pulang) {
      //     if($masuk['fid'] == $pulang['fid'] && $masuk['tanggal'] == $pulang['tanggal']){
      //       // $masuk[0]['jam_pulcep'] = $pulang[0]['jam_pulcep'];
      //       // $masuk[0]['jam_pulcep'] = $pulang[0]['jam_pulcep'];
      //       $jam_masuk[$track]['jam_pulcep'] = $pulang['jam_pulcep'];
      //       break;
      //     }
      //   }
      //   $track++;
      // }
      $flagpulang=0;
      foreach ($jam_pulang as $pulang) {
        $track=0;
        foreach ($jam_masuk as $masuk) {
          if($pulang['fid'] == $masuk['fid'] && $pulang['tanggal'] == $masuk['tanggal']){
            $jam_masuk[$track]['jam_pulcep'] = $pulang['jam_pulcep'];
            $flagpulang=1;
            break;
          }
          $track++;
        }
        if ($flagpulang==0) {
          $jam_masuk[] = ["fid" => $pulang['fid'], "nip_sapk" => $pulang['nip_sapk'],"tanggal" => $pulang['tanggal'], "jam_telat" => $pulang['jam_telat'], "jam_pulcep" => $pulang['jam_pulcep']];
        }
        $flagpulang=0;
      }
      return response()->json($jam_masuk);
//
//       $array = array_merge($jam_masuk, $jam_pulang);
// return var_dump($array);


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
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = b.id and flag_status = 1)
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                                    group By b.id) as tabel_Jumlah_Masuk
                                ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");
      // dd($jumlahMasuk);
      // END =  Menghitung Jumlah Hadir dalam Periode


      // START = Get Data Intervensi
      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                								->select('pegawai_id', 'tanggal_mulai', 'tanggal_akhir')
                								->whereBetween('tanggal_akhir', array($start_date, $end_date))
                								->where('flag_status', 1)
                								->where('preson_pegawais.skpd_id', $skpd_id)
                								->get();
      //dd($intervensi);
      // END = Get Data Intervensi


      // START = Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // dd($pejabatDokumen);
      // END = Pejabat Dokumen Jika Login sebagai admin skpd

      return view('pages.laporan.laporanAdministrator', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'pegawainya', 'absensi', 'total_telat_dan_pulcep', 'start_date', 'end_date', 'hariLibur', 'hariApel', 'jumlahMasuk', 'intervensi', 'pejabatDokumen'));
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
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // dd($pejabatDokumen);
      // END = Pejabat Dokumen Jika Login sebagai admin skpd

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      view()->share('getSkpd', $getSkpd);
      view()->share('skpd_id', $skpd_id);
      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('pegawainya', $pegawainya);
      view()->share('absensi', $absensi);
      view()->share('total_telat_dan_pulcep', $total_telat_dan_pulcep);
      view()->share('start_date', $start_date);
      view()->share('end_date', $end_date);
      view()->share('hariLibur', $hariLibur);
      view()->share('hariApel', $hariApel);
      view()->share('jumlahMasuk', $jumlahMasuk);
      view()->share('intervensi', $intervensi);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('nama_skpd', $nama_skpd);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdministrator')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$start_date.' - '.$end_date.'.pdf');
      }

      return view('pages.laporan.cetakAdministrator');
    }


    public function laporanAdmin()
    {
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.laporan.laporanAdmin')->with('getunreadintervensi', $getunreadintervensi);
    }

    public function laporanAdminStore(Request $request)
    {
      // get request
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


      // DFA LOGIC BARU
      $datangterlambat = DB::select("select p.fid, telat from
                                      (
                                      	select fid, count(*) as telat from preson_log
                                        where ((jam_datang > '08:01:00' and jam_datang < '09:00:00') or jam_datang is null or jam_datang > '09:00:00')
	                                       and (jam_pulang > '16:00:00' and jam_pulang < '19:00:00')
                                         and (tanggal between '$start_dateR' and '$end_dateR')
                                         and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date'))
                                         and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel between '$start_date' and '$end_date'))
                                      	group by fid
                                      ) s join preson_pegawais p
                                      on p.fid = s.fid where skpd_id = $skpd_id");

      $pulangcepat = DB::select("select p.fid, pulcep from
                                  (
                                  	select fid, count(*) as pulcep from preson_log
                                    where ((jam_pulang > '13:00:00' and jam_pulang < '16:00:00') or jam_pulang is null or jam_pulang < '13:00:00')
	                                   and (jam_datang < '08:00:00' and jam_datang > '07:00:00')
                                     and (tanggal between '$start_dateR' and '$end_dateR')
                                     and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date'))
                                  	group by fid
                                  ) s join preson_pegawais p
                                  on p.fid = s.fid where skpd_id = $skpd_id");

      $dtpc = DB::select("select p.fid, dtpc from
                          (
                          	select fid, count(*) as dtpc from preson_log
                          	where ((jam_datang > '08:00:00' and jam_datang < '09:00:00') or jam_datang is null or jam_datang > '09:00:00')
                              and ((jam_pulang > '15:00:00' and jam_pulang < '16:00:00') or jam_pulang is null or jam_pulang < '13:00:00')
                              and (tanggal between '$start_dateR' and '$end_dateR')
                              and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date'))
                              and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel between '$start_date' and '$end_date'))
                          	group by fid
                          ) s join preson_pegawais p
                          on p.fid = s.fid where skpd_id = $skpd_id");

      $tanggalhadirperskpd = DB::select("select a.fid, tanggal from preson_log a join preson_pegawais b
                                          on a.fid = b.fid
                                          where tanggal between '$start_dateR' and '$end_dateR'
                                          and skpd_id = $skpd_id");

      $intervensiperskpd = DB::select("select fid, tanggal_mulai, tanggal_akhir, id_intervensi
                                        from preson_intervensis a join preson_pegawais b
                                        on a.pegawai_id = b.id where skpd_id = $skpd_id and flag_status = 1");

      $getdatetelat = DB::select("select fid, tanggal from preson_log
                                  where ((jam_datang > '08:01:00' and jam_datang < '09:00:00') or jam_datang is null or jam_datang > '09:00:00')
                                   and (jam_pulang > '16:00:00' and jam_pulang < '19:00:00')
                                   and (tanggal between '$start_dateR' and '$end_dateR')
                                   and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date')
                                   and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel between '$start_date' and '$end_date'))
                                   )");

      $getdatepulcep = DB::select("select fid, tanggal from preson_log
                                   where ((jam_pulang > '13:00:00' and jam_pulang < '16:00:00') or jam_pulang is null or jam_pulang < '13:00:00')
                                    and (jam_datang < '08:00:00' and jam_datang > '07:00:00')
                                    and (tanggal between '$start_dateR' and '$end_dateR')
                                    and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date'))");

      $getdatedtpc = DB::select("select fid, tanggal from preson_log
                                  where ((jam_datang > '08:01:00' and jam_datang < '09:00:00') or jam_datang is null or jam_datang > '09:00:00')
                                    and ((jam_pulang > '15:00:00' and jam_pulang < '16:00:00') or jam_pulang is null or jam_pulang < '13:00:00')
                                    and (tanggal between '$start_dateR' and '$end_dateR')
                                    and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur between '$start_date' and '$end_date'))
                                    and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel between '$start_date' and '$end_date'))
                                    ");

      $tanggalhadirapel = DB::select("select fid, count(*) as jmlapel from (
                                      select * from preson_log
                                      where tanggal in
                                      (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel between '2017-03-01' and '2017-03-14')
                                      and (jam_datang < '08:31:00' and jam_datang > '06:00:00')
                                      ) a
                                      group by fid");

      $gethariapel = DB::select("select tanggal_apel from preson_apel where tanggal_apel between '$start_date' and '$end_date'");

      $getpengecualiantpp = DB::select("select nip_sapk from preson_pengecualian_tpp");

      $arrpengecualian = array();
      foreach ($getpengecualiantpp as $key) {
        $arrpengecualian[] = $key->nip_sapk;
      }

      // get hari libur
      $harilibur = DB::select("select libur from preson_harilibur
                                where libur between '$start_date' and '$end_date'");
      $arrharilibur = array();
      foreach ($harilibur as $hl) {
        $arrharilibur[] = $hl->libur;
      }

      // get tanggal kerja seharusnya (tanpa hari libur)
      $tanggalmulai = $start_date;
      $tanggalakhir = $end_date;

      $dateRange=array();
      $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
      $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

      if ($iDateTo>=$iDateFrom)
      {
          array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
          while ($iDateFrom<$iDateTo)
          {
              $iDateFrom+=86400; // add 24 hours
              array_push($dateRange,date('Y-m-d',$iDateFrom));
          }
      }
      $weekdayDate = array();
      foreach ($dateRange as $key) {
        if ((date('N', strtotime($key)) < 6) && (!in_array($key, $arrharilibur))) {
          $weekdayDate[] = $key;
        }
      }

      // masukin data ke array
      $dataabsensi = array();
      foreach ($pegawainya as $p) {

        $arrayrow = array();
        $arrayrow[] = $p->nip_sapk;
        $arrayrow[] = $p->nama;
        $arrayrow[] = $p->tpp_dibayarkan;


        // itung jumlah telat (berdasarkan range tanggal dan bukan hari libur)
        $jmltelat = 0;
        foreach ($datangterlambat as $dt) {
          if ($p->fid == $dt->fid) {
            $jmltelat = $dt->telat;
            break;
          }
        }
        if ($jmltelat!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid) and $key->id_intervensi==2) {
              $tanggalmulai = $key->tanggal_mulai;
              $tanggalakhir = $key->tanggal_akhir;

              $dateRange=array();
              $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
              $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

              if ($iDateTo>=$iDateFrom)
              {
                  array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
                  while ($iDateFrom<$iDateTo)
                  {
                      $iDateFrom+=86400; // add 24 hours
                      array_push($dateRange,date('Y-m-d',$iDateFrom));
                  }
              }
              foreach ($getdatetelat as $gdt) {
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gdt->fid) {
                    $tglnew = explode('/', $gdt->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                    if ($tglformat==$dr) {
                      $jmltelat--;
                    }
                  }
                }
              }
            }
          }
        }
        $arrayrow[] = $jmltelat;
        $potongtpptelat = ($p->tpp_dibayarkan*60/100)*2/100*$jmltelat;
        $arrayrow[] = $potongtpptelat;


        // itung jumlah pulcep
        $jmlpulcep = 0;
        foreach ($pulangcepat as $pc) {
          if ($p->fid == $pc->fid) {
            $jmlpulcep = $pc->pulcep;
            break;
          }
        }
        if ($jmlpulcep!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid) and $key->id_intervensi==3) {
              $tanggalmulai = $key->tanggal_mulai;
              $tanggalakhir = $key->tanggal_akhir;

              $dateRange=array();
              $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
              $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

              if ($iDateTo>=$iDateFrom)
              {
                  array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
                  while ($iDateFrom<$iDateTo)
                  {
                      $iDateFrom+=86400; // add 24 hours
                      array_push($dateRange,date('Y-m-d',$iDateFrom));
                  }
              }
              foreach ($getdatepulcep as $gdp) {
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gdp->fid) {
                    $tglnew = explode('/', $gdp->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                    if ($tglformat==$dr) {
                      $jmlpulcep--;
                    }
                  }
                }
              }
            }
          }
        }
        $arrayrow[] = $jmlpulcep;
        $potongtpppulcep = ($p->tpp_dibayarkan*60/100)*2/100*$jmlpulcep;
        $arrayrow[] = $potongtpppulcep;


        //itung datang telat dan pulang cepat
        $jmldtpc = 0;
        foreach ($dtpc as $pc) {
          if ($p->fid == $pc->fid) {
            $jmldtpc = $pc->dtpc;
            break;
          }
        }
        if ($jmldtpc!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid) and ($key->id_intervensi==3 or $key->id_intervensi==2)) {
              $tanggalmulai = $key->tanggal_mulai;
              $tanggalakhir = $key->tanggal_akhir;

              $dateRange=array();
              $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
              $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

              if ($iDateTo>=$iDateFrom)
              {
                  array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
                  while ($iDateFrom<$iDateTo)
                  {
                      $iDateFrom+=86400; // add 24 hours
                      array_push($dateRange,date('Y-m-d',$iDateFrom));
                  }
              }

              foreach ($getdatedtpc as $gddtpc) {
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gddtpc->fid) {
                    $tglnew = explode('/', $gddtpc->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                    if ($tglformat==$dr) {
                      $jmldtpc--;
                    }
                  }
                }
              }
            }
          }
        }
        $arrayrow[] = $jmldtpc;
        $potongtppdtpc = ($p->tpp_dibayarkan*60/100)*3/100*$jmldtpc;
        $arrayrow[] = $potongtppdtpc;


        // itung bolos
        $flagtanggal = 0;
        $tanggaltidakhadir = array();
        foreach ($weekdayDate as $keyss) {
          $tglnew = explode('-', $keyss);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          foreach ($tanggalhadirperskpd as $keys) {
            if ($p->fid == $keys->fid) {
              if ($tglformat == $keys->tanggal) {
                $flagtanggal = 1;
                break;
              }
            }
          }
          if ($flagtanggal==0) {
            $tanggaltidakhadir[] = $tglformat;
          }
          $flagtanggal=0;
        }
        $jmltidakhadir = count($tanggaltidakhadir);
        foreach ($intervensiperskpd as $key) {
          if (($key->fid == $p->fid) and ($key->id_intervensi != 2 and $key->id_intervensi != 3)) {
            $tanggalmulai = $key->tanggal_mulai;
            $tanggalakhir = $key->tanggal_akhir;

            $dateRange=array();
            $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
            $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

            if ($iDateTo>=$iDateFrom)
            {
                array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
                while ($iDateFrom<$iDateTo)
                {
                    $iDateFrom+=86400; // add 24 hours
                    array_push($dateRange,date('Y-m-d',$iDateFrom));
                }
            }

            foreach ($tanggaltidakhadir as $tth) {
              foreach ($dateRange as $dr) {
                if ($tth==$dr) {
                  $jmltidakhadir--;
                }
              }
            }
          }
        }
        $arrayrow[] = $jmltidakhadir;
        $potongantppbolos = ($p->tpp_dibayarkan*100/100)*3/100*$jmltidakhadir;
        $arrayrow[] = $potongantppbolos;


        //hitung apel
        $jumlahharushadirapel = count($gethariapel);
        $jumlahhadirapel = 0;
        foreach ($tanggalhadirapel as $key) {
          if ($key->fid == $p->fid) {
            $jumlahhadirapel = $key->jmlapel;
            break;
          }
        }
        $jumlahtidakapel = $jumlahharushadirapel - $jumlahhadirapel;
        $jumlahtidakapelempatkali = 0;

        if ($jumlahtidakapel>=4) {
          $jumlahtidakapelempatkali = floor($jumlahtidakapel / 4);
          $jumlahtidakapel = $jumlahtidakapel % 4;
        }

        $arrayrow[] = $jumlahtidakapel;
        $potongantppapel = ($p->tpp_dibayarkan*60/100)*2.5/100*$jumlahtidakapel;
        $arrayrow[] = $potongantppapel;

        $arrayrow[] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($p->tpp_dibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $arrayrow[] = $potongantppapelempatkali;

        // masukin ke array
        $dataabsensi[] = $arrayrow;
      }

      return view('pages.laporan.laporanAdmin')
        ->with('dataabsensi', $dataabsensi)
        ->with('pengecualian', $arrpengecualian);

      // END OF DFA LOGIC BARU





      //-------------- OLD LOGIC -------------------//
      // $absensi = DB::select("select a.id, a.fid, nama, tanggal_log, jam_log
      //                       from (select id, fid, nama from preson_pegawais where skpd_id = '$skpd_id') as a
      //                       left join ta_log b on a.fid = b.fid
      //                       where str_to_date(b.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
      //                       AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
      //                       AND str_to_date(b.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = a.id and flag_status = 1)");
      //
      // // START = Menghitung Total Datang Terlambat dan Pulang Cepat
      // $date_from = strtotime($start_date);
      // $date_to = strtotime($end_date);
      // $jam_masuk = array();
      // $jam_pulang = array();
      // foreach ($pegawainya as $pegawai) {
      //   for ($i=$date_from; $i<=$date_to; $i+=86400) {
      //     $tanggalini = date('d/m/Y', $i);
      //
      //     foreach ($absensi as $key) {
      //       if($tanggalini == $key->tanggal_log){
      //         if ($pegawai->fid == $key->fid) {
      //           $jammasuk1 = 80000;
      //           $jammasuk2 = 100000;
      //           $jamlog = (int) str_replace(':','',$key->jam_log);
      //           if( ($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
      //             $jam_masuk[] = $key->fid.'-'.$tanggalini;
      //           }
      //         }
      //       }
      //     }
      //
      //     foreach ($absensi as $key) {
      //       if($tanggalini == $key->tanggal_log){
      //         if ($pegawai->fid == $key->fid) {
      //           $jampulang1 = 140000;
      //           $jampulang2 = 160000;
      //           $jamlog = (int) str_replace(':','',$key->jam_log);
      //           if(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
      //             $jam_pulang[] = $key->fid.'-'.$tanggalini;
      //           }
      //         }
      //       }
      //     }
      //   }
      // }
      //
      // $jam_masuk = array_unique($jam_masuk);
      // $jam_pulang = array_unique($jam_pulang);
      //
      // if(($jam_masuk==null) && ($jam_pulang==null)){
      //   $total_telat_dan_pulcep = '';
      //   $total_telat_dan_pulcep = collect($total_telat_dan_pulcep);
      // }else{
      //   $total_telat_dan_pulcep = array_intersect($jam_masuk,$jam_pulang);
      //   $total_telat_dan_pulcep = collect(array_unique($total_telat_dan_pulcep));
      // }
      // // END = Menghitung Total Datang Terlambat dan Pulang Cepat
      //
      //
      // // START = Mencari Hari Libur Dalam Periode Tertentu
      // $potongHariLibur = harilibur::select('libur')->whereBetween('libur', array($start_date, $end_date))->get();
      // if($potongHariLibur->isEmpty()){
      //   $hariLibur = array();
      // }else{
      //   foreach ($potongHariLibur as $liburs) {
      //     $hariLibur[] = $liburs->libur;
      //   }
      // }
      // // END = Mencari Hari Libur Dalam Periode Tertentu
      //
      // // START = Mencari Hari Apel Dalam Periode Tertentu
      // $potongApel = apel::select('tanggal_apel')->whereBetween('tanggal_apel', array($start_date, $end_date))->get();
      // if($potongApel->isEmpty()){
      //   $hariApel = array();
      // }else{
      //   foreach ($potongApel as $apel) {
      //     $hariApel[] = $apel->tanggal_apel;
      //   }
      // }
      // // END = Mencari Hari Apel Dalam Periode Tertentu
      //
      // // START =  Menghitung Jumlah Hadir dalam Periode
      // $jumlahMasuk = DB::select("SELECT pegawai.id as pegawai_id, pegawai.nip_sapk, pegawai.nama as nama_pegawai, Jumlah_Masuk
      //                           FROM (select nama, id, nip_sapk from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai
      //
      //                           LEFT OUTER JOIN(SELECT b.id as pegawai_id, b.nip_sapk, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
      //                               FROM ta_log a, preson_pegawais b
      //                               WHERE a.Fid = b.fid
      //                               AND b.skpd_id = '$skpd_id'
      //                               AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'
      //                               AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') <= '10:00:00'
      //                               AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
      //                               group By b.id) as tabel_Jumlah_Masuk
      //                           ON pegawai.id = tabel_Jumlah_Masuk.pegawai_id");
      // // END =  Menghitung Jumlah Hadir dalam Periode
      //
      //
      // // START = Get Data Intervensi
      // $intervensi = intervensi::select('pegawai_id', 'tanggal_mulai', 'tanggal_akhir')->whereBetween('tanggal_akhir', array($start_date, $end_date))->where('flag_status', 1)->get();
      // // END = Get Data Intervensi
      //
      //
      // // START = Pejabat Dokumen Jika Login sebagai admin skpd
      // $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
      //                                 ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
      //                                 ->where('preson_pegawais.skpd_id', $skpd_id)
      //                                 ->where('preson_pejabat_dokumen.flag_status', 1)
      //                                 ->get();
      // // END = Pejabat Dokumen Jika Login sebagai admin skpd
      //
      // return view('pages.laporan.laporanAdmin', compact('start_dateR', 'end_dateR', 'pegawainya', 'absensi', 'total_telat_dan_pulcep', 'start_date', 'end_date', 'hariLibur', 'hariApel', 'jumlahMasuk', 'intervensi', 'pejabatDokumen'));
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


      // START = Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // END = Pejabat Dokumen Jika Login sebagai admin skpd

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('pegawainya', $pegawainya);
      view()->share('absensi', $absensi);
      view()->share('total_telat_dan_pulcep', $total_telat_dan_pulcep);
      view()->share('start_date', $start_date);
      view()->share('end_date', $end_date);
      view()->share('hariLibur', $hariLibur);
      view()->share('hariApel', $hariApel);
      view()->share('jumlahMasuk', $jumlahMasuk);
      view()->share('intervensi', $intervensi);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('nama_skpd', $nama_skpd);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdmin')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$start_date.' - '.$end_date.'.pdf');
      }

      return view('pages.laporan.cetakAdmin');
    }

    public function laporanPegawai()
    {
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.laporan.laporanPegawai')->with('getunreadintervensi', $getunreadintervensi);
    }

    public function laporanPegawaiStore(Request $request)
    {
      $nip_sapk = $request->nip_sapk;
      $fid = pegawai::select('fid')->where('nip_sapk', $nip_sapk)->first();
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
      $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
      $date_to = strtotime($end_date); // Convert date to a UNIX timestamp

      for ($i=$date_from; $i<=$date_to; $i+=86400) {
        $tanggalBulan[] = date('d/m/Y', $i);
        $tanggalini = date('d/m/Y', $i);
        $list[] = DB::select("SELECT b.id as pegawai_id, a.Tanggal_Log, a.DateTime,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '11:59:00'
                                  and Fid = '$fid->fid'
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = b.id and flag_status = 1)
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)) as Jam_Datang,
                                (select MAX(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '12:01:00'
                                  and Fid = '$fid->fid'
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = b.id and flag_status = 1)
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)) as Jam_Pulang
                              FROM ta_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.Fid = b.fid
                              AND a.Fid = '$fid->fid'
                              AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                              AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                              LIMIT 1");
      }

      $absensi = collect($list);

      return view('pages.laporan.laporanPegawai', compact('start_dateR', 'end_dateR', 'intervensi', 'absensi', 'hariLibur', 'nip_sapk', 'tanggalBulan'));
    }

    public function cetakPegawai(Request $request)
    {
      $nip_sapk = $request->nip_sapk;
      $fid = pegawai::select('fid', 'nama')->where('nip_sapk', $nip_sapk)->first();
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
      $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
      $date_to = strtotime($end_date); // Convert date to a UNIX timestamp

      for ($i=$date_from; $i<=$date_to; $i+=86400) {
        $tanggalBulan[] = date('d/m/Y', $i);
        $tanggalini = date('d/m/Y', $i);
        $list[] = DB::select("SELECT b.id as pegawai_id, a.Tanggal_Log, a.DateTime,
                                (select MIN(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '11:59:00'
                                  and Fid = '$fid->fid'
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = b.id and flag_status = 1)
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)) as Jam_Datang,
                                (select MAX(Jam_Log) from ta_log
                                  where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '12:01:00'
                                  and Fid = '$fid->fid'
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT tanggal_mulai FROM preson_intervensis where pegawai_id = b.id and flag_status = 1)
                                  and str_to_date(Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)) as Jam_Pulang
                              FROM ta_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.Fid = b.fid
                              AND a.Fid = '$fid->fid'
                              AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                              AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                              LIMIT 1");
      }

      $absensi = collect($list);

      view()->share('start_dateR', $start_dateR);
      view()->share('end_dateR', $end_dateR);
      view()->share('absensi', $absensi);
      view()->share('tanggalBulan', $tanggalBulan);
      view()->share('intervensi', $intervensi);
      view()->share('hariLibur', $hariLibur);
      view()->share('nip_sapk', $nip_sapk);
      view()->share('fid', $fid);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakPegawai')->setPaper('a4', 'potrait');
        return $pdf->download('Presensi Online - '.$nip_sapk.' Periode '.$start_date.' - '.$end_date.'.pdf');
      }

      return view('pages.laporan.cetakPegawai');
    }
}
