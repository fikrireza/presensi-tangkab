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
use App\Models\PresonLog;
use App\Models\MesinApel;
use App\Models\Jurnal;

use Auth;
use Validator;
use DB;
use PDF;
use DatePeriod;
use DateTime;
use DateInterval;

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
      // --- GET REQUEST ---
      $bulan = $request->pilih_bulan;
      $bulanexplode = explode("/", $bulan);
      $bulanhitung = $bulanexplode[1]."-".$bulanexplode[0];
      // --- END OF GET REQUEST ---

      // --- GET TANGGAL MULAI & TANGGAL AKHIR ---
      $tanggal_mulai = $bulanhitung."-01";
      $tanggal_akhir = date("Y-m-t", strtotime($tanggal_mulai));
      // --- END OF GET TANGGAL MULAI & TANGGAL AKHIR ---

      // --- GET DATA PEGAWAI BASED ON SKPD ID ---
      $skpd_id = Auth::user()->skpd_id;
      $getpegawai = pegawai::
        select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')
        ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
        ->where('skpd_id', $skpd_id)
        ->orderby('preson_strukturals.nama', 'asc')
        ->orderby('preson_pegawais.nama', 'asc')
        ->get();

      $getidpegawaiperskpd = array();
      foreach ($getpegawai as $key) {
        $getidpegawaiperskpd[] = $key->pegawai_id;
      }
      // --- END OF GET DATA PEGAWAI BASED ON SKPD ID ---


      // --- GET DATA PRESON LOG ---
      $getpresonlog = PresonLog::
        select('preson_log.fid', 'mach_id', 'tanggal', 'jam_datang', 'jam_pulang')
        ->join('preson_pegawais', 'preson_log.fid', '=', 'preson_pegawais.fid')
        ->where('preson_pegawais.skpd_id', $skpd_id)
        ->where('tanggal', 'like', "%$bulan")
        ->orderby('fid')
        ->orderby('tanggal')
        ->get();
      // --- END OF GET DATA PRESON LOG ---

      // --- GET TANGGAL APEL ----
      $getapel = Apel::select('tanggal_apel')->get();
      $tanggalapel = array();
      foreach ($getapel as $key) {
        $tglnew = explode('-', $key->tanggal_apel);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $tanggalapel[] = $tglformat;
      }
      // --- END OF GET TANGGAL APEL ----

      // --- GET MESIN APEL ---
      $getmesinapel = MesinApel::select('mach_id')->where('flag_status', 1)->get();
      $mesinapel = array();
      foreach ($getmesinapel as $key) {
        $mesinapel[] = $key->mach_id;
      }

      // --- GET HARI LIBUR ---
      $getharilibur = HariLibur::select('libur')->where('libur', 'like', "$bulanhitung%")->get();
      $tanggallibur = array();
      foreach ($getharilibur as $key) {
        $tglnew = explode('-', $key->libur);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $tanggallibur[] = $tglformat;
      }
      $tanggalliburformatdash = array();
      foreach ($getharilibur as $key) {
        $tanggalliburformatdash[] = $key->libur;
      }
      // --- END OF GET HARI LIBUR ---

      // --- GET INTERVENSI SKPD ---
      $getintervensi = Intervensi::
        select('fid', 'tanggal_mulai', 'tanggal_akhir', 'preson_pegawais.id as id', 'preson_intervensis.id_intervensi as id_intervensi')
        ->join('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
        ->where('preson_pegawais.skpd_id', $skpd_id)
        ->where('preson_intervensis.flag_status', 1)
        ->whereIn('preson_pegawais.id', $getidpegawaiperskpd)
        ->orderby('fid')
        ->get();
      // ---  END OF GET INTERVENSI SKPD ---

      // --- GET HARI KERJA SEHARUSNYA ---
      $period = new DatePeriod(
           new DateTime("$tanggal_mulai"),
           new DateInterval('P1D'),
           new DateTime("$tanggal_akhir 23:59:59")
      );
      $daterange = array();
      foreach($period as $date) {$daterange[] = $date->format('Y-m-d'); }
      $harikerja = array();
      foreach ($daterange as $key) {
        if ((date('N', strtotime($key)) < 6) && (!in_array($key, $tanggalliburformatdash))) {
          $harikerja[] = $key;
        }
      }
      // --- GET HARI KERJA SEHARUSNYA ---

      // --- GET PENGECUALIAN TPP ---
      $getpengecualiantpp = DB::select("select nip_sapk from preson_pengecualian_tpp");
      $arrpengecualian = array();
      foreach ($getpengecualiantpp as $key) {
        $arrpengecualian[] = $key->nip_sapk;
      }
      // --- END OF GET PENGECUALIAN TPP ---

      // --- LOOP GET PEGAWAI ---
      $rekaptpp = array();
      $grandtotalpotongantpp=0;
      $grandtotaltppdibayarkan=0;
      foreach ($getpegawai as $pegawai) {
        $rowdata = array();
        $rowdata["nip"] = $pegawai->nip_sapk;
        $rowdata["nama"] = $pegawai->nama;
        $rowdata["tpp"] = number_format($pegawai->tpp_dibayarkan, 0, '.', '.');

        // --- INTERVENSI FOR SPECIFIC PEGAWAI
        $dateintervensibebas = array();
        $dateintervensitelat = array();
        $dateintervensipulcep = array();
        foreach ($getintervensi as $intervensi) {
          if ($pegawai->pegawai_id == $intervensi->id) {
            if ($intervensi->id_intervensi==2) {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensitelat[] = $date->format('Y-m-d'); }
            } else if ($intervensi->id_intervensi==3) {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensipulcep[] = $date->format('Y-m-d'); }
            } else {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensibebas[] = $date->format('Y-m-d'); }
            }
          }
        }
        $tanggalintervensitelat = array();
        $unique = array_unique($dateintervensitelat);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensitelat[] = $tglformat;
        }
        $tanggalintervensipulcep = array();
        $unique = array_unique($dateintervensipulcep);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensipulcep[] = $tglformat;
        }
        $tanggalintervensibebas = array();
        $unique = array_unique($dateintervensibebas);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensibebas[] = $tglformat;
        }
        // foreach ($tanggalintervensibebas as $key) {
        //   echo "intervensibebas:".$pegawai->pegawai_id."----".$key."<br>";
        // }
        // --- END OF INTERVENSI FOR SPECIFIC PEGAWAI

        // -- LOOP PRESON LOG
        $dianggapbolos = 0;
        $telat = 0;
        $pulangcepat = 0;
        $telatpulangcepat = 0;
        $tidakapel = 0;
        $tanggalhadir = array();
        foreach ($getpresonlog as $presonlog) {
          // --- MAKE SURE IS NOT HOLIDAY DATE
          if (($pegawai->fid == $presonlog->fid) && (!in_array($presonlog->tanggal, $tanggallibur))) {
            $tanggalhadir[] = $presonlog->tanggal;
            // --- CHECK APEL DATE
            if (!in_array($presonlog->tanggal, $tanggalapel)) {
              $tglnew = explode('/', $presonlog->tanggal);
              $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
              // --- CHECK FRIDAY DATE ---
              if ((date('N', strtotime($tglformat)) != 5)) {
                // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---
                $lower_telatdtg = 80100;
                $upper_telatdtg = 90100;
                $lower_plgcepat = 150000;
                $upper_plgcepat = 160000;
                // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc): ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc): ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "telat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 70000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulangcepat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              } else {
                // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---
                $lower_telatdtg = 73100;
                $upper_telatdtg = 83100;
                $lower_plgcepat = 150000;
                $upper_plgcepat = 160000;
                // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if ($presonlog->jam_datang==null || $jamdtg < 63000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "telat-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 63000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulangcepat-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              }
              // --- END OF CHECK FRIDAY DATE ---
            } else {
              $tglnew = explode('/', $presonlog->tanggal);
              $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];

              // --- SET LOWER & UPPER BOUND APEL ---
              $maxjamdatang = 83100;
              $upper_telatdtg = 90100;
              $lower_plgcepat = 150000;
              $upper_plgcepat = 160000;
              // --- END OF SET LOWER & UPPER BOUND APEL ---

              // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
              $rawjamdtg = $presonlog->jam_datang;
              $jamdtg = str_replace(':', '', $rawjamdtg);
              $rawjamplg = $presonlog->jam_pulang;
              $jamplg = str_replace(':', '', $rawjamplg);
              // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

              if (in_array($presonlog->mach_id, $mesinapel)) {
                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg > $upper_plgcepat) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "tidak-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $tidakapel++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ((($jamdtg < $maxjamdatang && $jamdtg > 70000) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > 70000) && $jamplg==null)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              } else {
                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg <= $maxjamdatang || $jamdtg >= $maxjamdatang) && $jamplg > $upper_plgcepat) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "tidak-apel-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $tidakapel++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                }
              }
            }
            // --- END OF CHECK APEL DATE
          }
          // --- END OF MAKE SURE IS NOT HOLIDAY DATE
        }
        // -- END OF LOOP PRESON LOG

        // --- COUNT TOTAL BOLOS ---
        $arrharikerja = array();
        foreach ($harikerja as $hk) {
          $tglnew = explode('-', $hk);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $arrharikerja[] = $tglformat;
        }
        $tidakhadir = array_diff($arrharikerja, $tanggalhadir);
        $murnibolos = 0;
        foreach ($tidakhadir as $bolos) {
          if (!in_array($bolos, $tanggalintervensibebas)) {
            // echo "murnibolos: ".$pegawai->fid."---".$bolos."<br>";
            $murnibolos++;
          }
        }
        $totalbolos = $murnibolos+$dianggapbolos;
        // --- END OF COUNT TOTAL BOLOS ---

        $totalpotongantpp = 0;

        if (in_array($pegawai->nip_sapk, $arrpengecualian)) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
          $tidakapel=0;
        }

        $rowdata["telat"] = $telat;
        $potongtpptelat = ($pegawai->tpp_dibayarkan*60/100)*2/100*$telat;
        $rowdata["potongantelat"] = number_format($potongtpptelat, 0, '.', '.');
        $totalpotongantpp += $potongtpptelat;

        $rowdata["pulangcepat"] = $pulangcepat;
        $potongtpppulcep = ($pegawai->tpp_dibayarkan*60/100)*2/100*$pulangcepat;
        $rowdata["potonganpulangcepat"] = number_format($potongtpppulcep, 0, '.', '.');
        $totalpotongantpp += $potongtpppulcep;

        $rowdata["telatpulangcepat"] = $telatpulangcepat;
        $potongtppdtpc = ($pegawai->tpp_dibayarkan*60/100)*3/100*$telatpulangcepat;
        $rowdata["potongantelatpulangcepat"] = number_format($potongtppdtpc, 0, '.', '.');
        $totalpotongantpp += $potongtppdtpc;

        $rowdata["tidakhadir"] = $totalbolos;
        $potongantppbolos = ($pegawai->tpp_dibayarkan*100/100)*3/100*$totalbolos;
        $rowdata["potongantidakhadir"] = number_format($potongantppbolos, 0, '.', '.');
        $totalpotongantpp += $potongantppbolos;

        $jumlahtidakapelempatkali = 0;
        if ($tidakapel>=4) {
          $jumlahtidakapelempatkali = floor($tidakapel / 4);
          $tidakapel = $tidakapel % 4;
        }

        $rowdata["tidakapel"] = $tidakapel;
        $potongantppapel = ($pegawai->tpp_dibayarkan*60/100)*2.5/100*$tidakapel;
        $rowdata["potongantidakapel"] = number_format(floor($potongantppapel), 0, '.', '.');
        $totalpotongantpp += floor($potongantppapel);

        $rowdata["tidakapelempat"] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($pegawai->tpp_dibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $rowdata["potongantidakapelempat"] = floor($potongantppapelempatkali);
        $totalpotongantpp += floor($potongantppapelempatkali);

        $rowdata["totalpotongantpp"] = number_format($totalpotongantpp, 0, '.', '.');
        $rowdata["totalterimatpp"] = number_format(($pegawai->tpp_dibayarkan - $totalpotongantpp), 0, '.', '.');

        // return "--- MAINTENANCE ----";
        $rekaptpp[] = $rowdata;
        $grandtotalpotongantpp += $totalpotongantpp;
        $grandtotaltppdibayarkan += ($pegawai->tpp_dibayarkan - $totalpotongantpp);
      }
      // --- END OF LOOP GET PEGAWAI ---

      // echo "grand total potongan: ".$grandtotalpotongantpp."<br>";
      // echo "grand total tpp: ".$grandtotaltppdibayarkan."<br>";
      // return "--- MAINTENANCE ----";

      return view('pages.laporan.laporanAdmin')
        ->with('rekaptpp', $rekaptpp)
        ->with('bulan', $bulan)
        ->with('start_dateR', $tanggal_mulai)
        ->with('end_dateR', $tanggal_akhir)
        ->with('grandtotalpotongantpp', number_format($grandtotalpotongantpp, 0, '.', '.'))
        ->with('grandtotaltppdibayarkan', number_format($grandtotaltppdibayarkan, 0, '.', '.'))
        ->with('pengecualian', $arrpengecualian);
    }

    public function cetakAdmin(Request $request)
    {
      // --- GET REQUEST ---
      $bulan = $request->pilih_bulan;
      $bulanexplode = explode("/", $bulan);
      $bulanhitung = $bulanexplode[1]."-".$bulanexplode[0];
      // --- END OF GET REQUEST ---

      // --- GET TANGGAL MULAI & TANGGAL AKHIR ---
      $tanggal_mulai = $bulanhitung."-01";
      $tanggal_akhir = date("Y-m-t", strtotime($tanggal_mulai));
      // --- END OF GET TANGGAL MULAI & TANGGAL AKHIR ---

      // --- GET DATA PEGAWAI BASED ON SKPD ID ---
      $skpd_id = Auth::user()->skpd_id;
      $getpegawai = pegawai::
        select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')
        ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
        ->where('skpd_id', $skpd_id)
        ->orderby('preson_strukturals.nama', 'asc')
        ->orderby('preson_pegawais.nama', 'asc')
        ->get();

      $getidpegawaiperskpd = array();
      foreach ($getpegawai as $key) {
        $getidpegawaiperskpd[] = $key->pegawai_id;
      }
      // --- END OF GET DATA PEGAWAI BASED ON SKPD ID ---


      // --- GET DATA PRESON LOG ---
      $getpresonlog = PresonLog::
        select('preson_log.fid', 'mach_id', 'tanggal', 'jam_datang', 'jam_pulang')
        ->join('preson_pegawais', 'preson_log.fid', '=', 'preson_pegawais.fid')
        ->where('preson_pegawais.skpd_id', $skpd_id)
        ->where('tanggal', 'like', "%$bulan")
        ->orderby('fid')
        ->orderby('tanggal')
        ->get();
      // --- END OF GET DATA PRESON LOG ---

      // --- GET TANGGAL APEL ----
      $getapel = Apel::select('tanggal_apel')->get();
      $tanggalapel = array();
      foreach ($getapel as $key) {
        $tglnew = explode('-', $key->tanggal_apel);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $tanggalapel[] = $tglformat;
      }
      // --- END OF GET TANGGAL APEL ----

      // --- GET MESIN APEL ---
      $getmesinapel = MesinApel::select('mach_id')->where('flag_status', 1)->get();
      $mesinapel = array();
      foreach ($getmesinapel as $key) {
        $mesinapel[] = $key->mach_id;
      }

      // --- GET HARI LIBUR ---
      $getharilibur = HariLibur::select('libur')->where('libur', 'like', "$bulanhitung%")->get();
      $tanggallibur = array();
      foreach ($getharilibur as $key) {
        $tglnew = explode('-', $key->libur);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $tanggallibur[] = $tglformat;
      }
      $tanggalliburformatdash = array();
      foreach ($getharilibur as $key) {
        $tanggalliburformatdash[] = $key->libur;
      }
      // --- END OF GET HARI LIBUR ---

      // --- GET INTERVENSI SKPD ---
      $getintervensi = Intervensi::
        select('fid', 'tanggal_mulai', 'tanggal_akhir', 'preson_pegawais.id as id', 'preson_intervensis.id_intervensi as id_intervensi')
        ->join('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
        ->where('preson_pegawais.skpd_id', $skpd_id)
        ->where('preson_intervensis.flag_status', 1)
        ->whereIn('preson_pegawais.id', $getidpegawaiperskpd)
        ->orderby('fid')
        ->get();
      // ---  END OF GET INTERVENSI SKPD ---

      // --- GET HARI KERJA SEHARUSNYA ---
      $period = new DatePeriod(
           new DateTime("$tanggal_mulai"),
           new DateInterval('P1D'),
           new DateTime("$tanggal_akhir 23:59:59")
      );
      $daterange = array();
      foreach($period as $date) {$daterange[] = $date->format('Y-m-d'); }
      $harikerja = array();
      foreach ($daterange as $key) {
        if ((date('N', strtotime($key)) < 6) && (!in_array($key, $tanggalliburformatdash))) {
          $harikerja[] = $key;
        }
      }
      // --- GET HARI KERJA SEHARUSNYA ---

      // --- GET PENGECUALIAN TPP ---
      $getpengecualiantpp = DB::select("select nip_sapk from preson_pengecualian_tpp");
      $arrpengecualian = array();
      foreach ($getpengecualiantpp as $key) {
        $arrpengecualian[] = $key->nip_sapk;
      }
      // --- END OF GET PENGECUALIAN TPP ---

      // --- LOOP GET PEGAWAI ---
      $rekaptpp = array();
      $grandtotalpotongantpp=0;
      $grandtotaltppdibayarkan=0;
      foreach ($getpegawai as $pegawai) {
        $rowdata = array();
        $rowdata["nip"] = $pegawai->nip_sapk;
        $rowdata["nama"] = $pegawai->nama;
        $rowdata["tpp"] = number_format($pegawai->tpp_dibayarkan, 0, '.', '.');

        // --- INTERVENSI FOR SPECIFIC PEGAWAI
        $dateintervensibebas = array();
        $dateintervensitelat = array();
        $dateintervensipulcep = array();
        foreach ($getintervensi as $intervensi) {
          if ($pegawai->pegawai_id == $intervensi->id) {
            if ($intervensi->id_intervensi==2) {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensitelat[] = $date->format('Y-m-d'); }
            } else if ($intervensi->id_intervensi==3) {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensipulcep[] = $date->format('Y-m-d'); }
            } else {
              $period = new DatePeriod(
                   new DateTime("$intervensi->tanggal_mulai"),
                   new DateInterval('P1D'),
                   new DateTime("$intervensi->tanggal_akhir 23:59:59")
              );
              foreach($period as $date) {$dateintervensibebas[] = $date->format('Y-m-d'); }
            }
          }
        }
        $tanggalintervensitelat = array();
        $unique = array_unique($dateintervensitelat);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensitelat[] = $tglformat;
        }
        $tanggalintervensipulcep = array();
        $unique = array_unique($dateintervensipulcep);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensipulcep[] = $tglformat;
        }
        $tanggalintervensibebas = array();
        $unique = array_unique($dateintervensibebas);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensibebas[] = $tglformat;
        }
        // foreach ($tanggalintervensibebas as $key) {
        //   echo "intervensibebas:".$pegawai->pegawai_id."----".$key."<br>";
        // }
        // --- END OF INTERVENSI FOR SPECIFIC PEGAWAI

        // -- LOOP PRESON LOG
        $dianggapbolos = 0;
        $telat = 0;
        $pulangcepat = 0;
        $telatpulangcepat = 0;
        $tidakapel = 0;
        $tanggalhadir = array();
        foreach ($getpresonlog as $presonlog) {
          // --- MAKE SURE IS NOT HOLIDAY DATE
          if (($pegawai->fid == $presonlog->fid) && (!in_array($presonlog->tanggal, $tanggallibur))) {
            $tanggalhadir[] = $presonlog->tanggal;
            // --- CHECK APEL DATE
            if (!in_array($presonlog->tanggal, $tanggalapel)) {
              $tglnew = explode('/', $presonlog->tanggal);
              $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
              // --- CHECK FRIDAY DATE ---
              if ((date('N', strtotime($tglformat)) != 5)) {
                // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---
                $lower_telatdtg = 80100;
                $upper_telatdtg = 90100;
                $lower_plgcepat = 150000;
                $upper_plgcepat = 160000;
                // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc): ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc): ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "telat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 70000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulangcepat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              } else {
                // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---
                $lower_telatdtg = 73100;
                $upper_telatdtg = 83100;
                $lower_plgcepat = 150000;
                $upper_plgcepat = 160000;
                // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if ($presonlog->jam_datang==null || $jamdtg < 63000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "telat-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 63000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulangcepat-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              }
              // --- END OF CHECK FRIDAY DATE ---
            } else {
              $tglnew = explode('/', $presonlog->tanggal);
              $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];

              // --- SET LOWER & UPPER BOUND APEL ---
              $maxjamdatang = 83100;
              $upper_telatdtg = 90100;
              $lower_plgcepat = 150000;
              $upper_plgcepat = 160000;
              // --- END OF SET LOWER & UPPER BOUND APEL ---

              // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
              $rawjamdtg = $presonlog->jam_datang;
              $jamdtg = str_replace(':', '', $rawjamdtg);
              $rawjamplg = $presonlog->jam_pulang;
              $jamplg = str_replace(':', '', $rawjamplg);
              // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

              if (in_array($presonlog->mach_id, $mesinapel)) {
                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg > $upper_plgcepat) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "tidak-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $tidakapel++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                } else if ((($jamdtg < $maxjamdatang && $jamdtg > 70000) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > 70000) && $jamplg==null)) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              } else {
                if ($presonlog->jam_datang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "dianggapbolos-jamdtg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if ($presonlog->jam_pulang==null || $jamplg > 190000) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                    // echo "dianggapbolos-jamplg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg <= $maxjamdatang || $jamdtg >= $maxjamdatang) && $jamplg > $upper_plgcepat) {
                  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                    // echo "tidak-apel-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $tidakapel++;
                  }
                } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                  $intertelat = 0;
                  $interpulcep = 0;
                  $interbebas = 0;
                  if (in_array($presonlog->tanggal, $tanggalintervensibebas)) $interbebas++;
                  if (in_array($presonlog->tanggal, $tanggalintervensitelat)) $intertelat++;
                  if (in_array($presonlog->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                  if ($interbebas==0) {
                    if ($intertelat==0 && $interpulcep==0) {
                      // echo "telat-pulcep-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telatpulangcepat++;
                    } else if ($intertelat!=0 && $interpulcep==0) {
                      // echo "pulcep-(dtpc)-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    } else if ($intertelat==0 && $interpulcep!=0) {
                      // echo "telat-(dtpc)-bukanmesinapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $telat++;
                    }
                  }
                }
              }
            }
            // --- END OF CHECK APEL DATE
          }
          // --- END OF MAKE SURE IS NOT HOLIDAY DATE
        }
        // -- END OF LOOP PRESON LOG

        // --- COUNT TOTAL BOLOS ---
        $arrharikerja = array();
        foreach ($harikerja as $hk) {
          $tglnew = explode('-', $hk);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $arrharikerja[] = $tglformat;
        }
        $tidakhadir = array_diff($arrharikerja, $tanggalhadir);
        $murnibolos = 0;
        foreach ($tidakhadir as $bolos) {
          if (!in_array($bolos, $tanggalintervensibebas)) {
            // echo "murnibolos: ".$pegawai->fid."---".$bolos."<br>";
            $murnibolos++;
          }
        }
        $totalbolos = $murnibolos+$dianggapbolos;
        // --- END OF COUNT TOTAL BOLOS ---

        $totalpotongantpp = 0;

        if (in_array($pegawai->nip_sapk, $arrpengecualian)) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
          $tidakapel=0;
        }

        $rowdata["telat"] = $telat;
        $potongtpptelat = ($pegawai->tpp_dibayarkan*60/100)*2/100*$telat;
        $rowdata["potongantelat"] = number_format($potongtpptelat, 0, '.', '.');
        $totalpotongantpp += $potongtpptelat;

        $rowdata["pulangcepat"] = $pulangcepat;
        $potongtpppulcep = ($pegawai->tpp_dibayarkan*60/100)*2/100*$pulangcepat;
        $rowdata["potonganpulangcepat"] = number_format($potongtpppulcep, 0, '.', '.');
        $totalpotongantpp += $potongtpppulcep;

        $rowdata["telatpulangcepat"] = $telatpulangcepat;
        $potongtppdtpc = ($pegawai->tpp_dibayarkan*60/100)*3/100*$telatpulangcepat;
        $rowdata["potongantelatpulangcepat"] = number_format($potongtppdtpc, 0, '.', '.');
        $totalpotongantpp += $potongtppdtpc;

        $rowdata["tidakhadir"] = $totalbolos;
        $potongantppbolos = ($pegawai->tpp_dibayarkan*100/100)*3/100*$totalbolos;
        $rowdata["potongantidakhadir"] = number_format($potongantppbolos, 0, '.', '.');
        $totalpotongantpp += $potongantppbolos;

        $jumlahtidakapelempatkali = 0;
        if ($tidakapel>=4) {
          $jumlahtidakapelempatkali = floor($tidakapel / 4);
          $tidakapel = $tidakapel % 4;
        }

        $rowdata["tidakapel"] = $tidakapel;
        $potongantppapel = ($pegawai->tpp_dibayarkan*60/100)*2.5/100*$tidakapel;
        $rowdata["potongantidakapel"] = number_format(floor($potongantppapel), 0, '.', '.');
        $totalpotongantpp += floor($potongantppapel);

        $rowdata["tidakapelempat"] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($pegawai->tpp_dibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $rowdata["potongantidakapelempat"] = floor($potongantppapelempatkali);
        $totalpotongantpp += floor($potongantppapelempatkali);

        $rowdata["totalpotongantpp"] = number_format($totalpotongantpp, 0, '.', '.');
        $rowdata["totalterimatpp"] = number_format(($pegawai->tpp_dibayarkan - $totalpotongantpp), 0, '.', '.');

        // return "--- MAINTENANCE ----";
        $rekaptpp[] = $rowdata;
        $grandtotalpotongantpp += $totalpotongantpp;
        $grandtotaltppdibayarkan += ($pegawai->tpp_dibayarkan - $totalpotongantpp);
      }

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      // START = Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // END = Pejabat Dokumen Jika Login sebagai admin skpd

      view()->share('rekaptpp', $rekaptpp);
      view()->share('nama_skpd', $nama_skpd);
      view()->share('tanggalmulai', $tanggal_mulai);
      view()->share('tanggalakhir', $tanggal_akhir);
      view()->share('bulan', $bulan);
      view()->share('pejabatDokumen', $pejabatDokumen);


      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdmin')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$tanggal_mulai.' - '.$tanggal_akhir.'.pdf');
      }

      // return view('pages.laporan.cetakAdmin')
      //   ->with('rekaptpp', $rekaptpp)
      //   ->with('nama_skpd', $nama_skpd)
      //   ->with('tanggalmulai', $tanggal_mulai)
      //   ->with('tanggalakhir', $tanggal_akhir)
      //   ->with('bulan', $bulan)
      //   ->with('pejabatDokumen', $pejabatDokumen);
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
      $intervensi = DB::select("select a.tanggal_mulai, a.tanggal_akhir, a.jenis_intervensi, a.deskripsi
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
      }

      $list = DB::select("SELECT a.*
                          FROM preson_log a, preson_pegawais b, preson_skpd c
                          WHERE b.skpd_id = c.id
                          AND (STR_TO_DATE(a.tanggal,'%d/%m/%Y') between '$start_date' and '$end_date')
                          AND a.fid = b.fid
                          AND str_to_date(a.tanggal, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                          AND a.fid = '$fid->fid'");

      $absensi = collect($list);

      return view('pages.laporan.laporanPegawai', compact('start_dateR', 'end_dateR', 'intervensi', 'absensi', 'hariLibur', 'nip_sapk', 'tanggalBulan'));
    }

    public function cetakPegawai(Request $request)
    {
      $bulanhitung = $request->bulanhitung;
      $bulanhitungformatnormal = explode("/", $bulanhitung);
      // dd($bulanhitungformatnormal);
      $bulanhitung2 = $bulanhitungformatnormal[1]."-".$bulanhitungformatnormal[0];

      $nip_sapk = $request->nip_sapk;
      $fid = pegawai::select('fid', 'nama')->where('nip_sapk', $nip_sapk)->first();
      // $start_dateR = $request->start_date;
      // $start_date = explode('/', $start_dateR);
      // $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      // $end_dateR = $request->end_date;
      // $end_date = explode('/', $end_dateR);
      // $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      $start_date = $bulanhitung2."-01";
      $end_date = date("Y-m-t", strtotime($start_date));

      // Mencari jadwal intervensi pegawai dalam periode tertentu
      $intervensi = DB::select("select a.tanggal_mulai, a.tanggal_akhir, a.jenis_intervensi, a.deskripsi
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
      }

      $list = DB::select("SELECT a.*
                          FROM preson_log a, preson_pegawais b, preson_skpd c
                          WHERE b.skpd_id = c.id
                          AND (STR_TO_DATE(a.tanggal,'%d/%m/%Y') between '$start_date' and '$end_date')
                          AND a.fid = b.fid
                          AND str_to_date(a.tanggal, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                          AND a.fid = '$fid->fid'");

      $absensi = collect($list);

      $absensi = collect($list);

      view()->share('start_dateR', $start_date);
      view()->share('end_dateR', $end_date);
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
