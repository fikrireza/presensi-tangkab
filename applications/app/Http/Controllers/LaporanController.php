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

      // --- END OF GET HARI LIBUR ---

      // --- GET INTERVENSI SKPD ---
      $getintervensi = Intervensi::
        select('fid', 'tanggal_mulai', 'tanggal_akhir', 'preson_pegawais.id as id')
        ->join('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
        ->where('preson_pegawais.skpd_id', $skpd_id)
        ->where('preson_intervensis.flag_status', 1)
        ->orderby('fid')
        ->get();
      // ---  END OF GET INTERVENSI SKPD ---

      // --- LOOP GET PEGAWAI ---
      $rekaptpp = array();
      foreach ($getpegawai as $pegawai) {
        $rowdata = array();
        $rowdata[] = $pegawai->nip_sapk;
        $rowdata[] = $pegawai->nama;
        $rowdata[] = $pegawai->tpp_dibayarkan;

        // --- INTERVENSI FOR SPECIFIC PEGAWAI
        $dateintervensi = array();
        foreach ($getintervensi as $intervensi) {
          if ($pegawai->pegawai_id == $intervensi->id) {
            $period = new DatePeriod(
                 new DateTime("$intervensi->tanggal_mulai"),
                 new DateInterval('P1D'),
                 new DateTime("$intervensi->tanggal_akhir 23:59:59")
            );
            foreach($period as $date) {$dateintervensi[] = $date->format('Y-m-d'); }
          }
        }
        $tanggalintervensi = array();
        $unique = array_unique($dateintervensi);
        foreach ($unique as $key) {
          $tglnew = explode('-', $key);
          $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
          $tanggalintervensi[] = $tglformat;
        }
        // --- END OF INTERVENSI FOR SPECIFIC PEGAWAI

        // -- LOOP PRESON LOG
        $dianggapbolos = 0;
        $telat = 0;
        $pulangcepat = 0;
        $telatpulangcepat = 0;
        $tidakapel = 0;
        foreach ($getpresonlog as $presonlog) {
          // --- MAKE SURE IS NOT HOLIDAY DATE
          if (($pegawai->fid == $presonlog->fid) && (!in_array($presonlog->tanggal, $tanggallibur))) {
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

                if ($presonlog->jam_datang==null || $presonlog->jam_pulang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg || $jamplg > 190000) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "dianggapbolos: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && ($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat)) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "telat-pulcep: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $telatpulangcepat++;
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "telat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 70000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "pulangcepat: ".$presonlog->fid."--".$presonlog->tanggal."--jampulang:".$jamplg."<br>";
                    $pulangcepat++;
                  }
                }
              } else {
                // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---
                $lower_telatdtg = 73100;
                $upper_telatdtg = 83100;
                $lower_plgcepat = 143000;
                $upper_plgcepat = 153000;
                // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if ($presonlog->jam_datang==null || $presonlog->jam_pulang==null || $jamdtg < 63000 || $jamdtg > $upper_telatdtg || $jamplg > 190000) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "dianggapbolosjumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $dianggapbolos++;
                  }
                } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && ($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat)) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "telat-pulcepjumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                    $telatpulangcepat++;
                  }
                } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "telatjumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."<br>";
                    $telat++;
                  }
                } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > 70000 && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                  if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                    echo "pulangcepatjumat: ".$presonlog->fid."--".$presonlog->tanggal."--jampulang:".$jamplg."<br>";
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

              if ($presonlog->jam_datang==null || $presonlog->jam_pulang==null || $jamdtg < 70000 || $jamdtg > $upper_telatdtg || $jamplg > 190000) {
                if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                  echo "dianggapboloshariapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                  $dianggapbolos++;
                }
              } else if (($jamdtg > $maxjamdatang) || (!in_array($presonlog->mach_id, $mesinapel))) {
                echo "tidakapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                $tidakapel++;
              }

              if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || ($jamplg < $upper_plgcepat)) {
                if (!in_array($presonlog->tanggal, $tanggalintervensi)) {
                  echo "pulangcepathariapel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                  $pulangcepat++;
                }
              }
            }
            // --- END OF CHECK APEL DATE
          }
          // --- END OF MAKE SURE IS NOT HOLIDAY DATE
        }
        // return $pulangcepat;
        // -- END OF LOOP PRESON LOG

        $rekaptpp[] = $rowdata;
      }
      dd($rekaptpp);
      // --- END OF LOOP GET PEGAWAI ---
    }

    public function cetakAdmin(Request $request)
    {
      // get request
      $bulanhitung = $request->bulanhitung;
      // dd($bulanhitung);
      $bulanhitungformatnormal = explode("/", $bulanhitung);
      $bulanhitung2 = $bulanhitungformatnormal[1]."-".$bulanhitungformatnormal[0];
      // return $bulanhitung2;

      $tanggalmulainya = $bulanhitung2."-01";
      $tanggalakhirnya = date("Y-m-t", strtotime($tanggalmulainya));
      $skpd_id = Auth::user()->skpd_id;
      // $start_dateR = $request->start_date;
      // $start_date = explode('/', $start_dateR);
      // $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      // $end_dateR = $request->end_date;
      // $end_date = explode('/', $end_dateR);
      // $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      // get hari libur
      $harilibur = DB::select("select libur from preson_harilibur
                                where libur like '$bulanhitung2%'");

      $arrharilibur = array();
      foreach ($harilibur as $hl) {
        $arrharilibur[] = $hl->libur;
      }

      // get tanggal kerja seharusnya (tanpa hari libur)

      $dateRange=array();
      $iDateFrom=mktime(1,0,0,substr($tanggalmulainya,5,2), substr($tanggalmulainya,8,2), substr($tanggalmulainya,0,4));
      $iDateTo=mktime(1,0,0,substr($tanggalakhirnya,5,2), substr($tanggalakhirnya,8,2), substr($tanggalakhirnya,0,4));

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
      $fridayDate = null;
      foreach ($dateRange as $key) {
        if ((date('N', strtotime($key)) < 6) && (!in_array($key, $arrharilibur))) {
          $weekdayDate[] = $key;
        }

        if ((date('N', strtotime($key)) == 5) && (!in_array($key, $arrharilibur))) {
          if ($fridayDate==null) {
            $fridayDate .= $key;
          } else {
            $fridayDate .= ", ".$key;
          }
        }
      }

      // Get Data Pegawai berdasarkan SKPD
      $pegawainya = pegawai::join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                            ->select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')->where('skpd_id', $skpd_id)
                            ->orderby('preson_strukturals.nama', 'asc')->get();

      // DFA LOGIC BARU
      $gettanggaldianggaptidakmasuk_jamdatang = DB::select("select fid, tanggal from preson_log
                                                            where (jam_datang is null or jam_datang > '09:00:00' or jam_datang < '07:00:00')
                                                            and !(jam_pulang is null or jam_pulang > '19:00:00')
                                                            and (tanggal like '%$bulanhitung')
                                                            and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))
                                                            and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel like '$bulanhitung2%'))
                                                            ");


      $gettanggaldianggaptidakmasuk_jampulang = DB::select("select fid, tanggal from preson_log
                                                            where !(jam_datang is null or jam_datang > '09:00:00' or jam_datang < '07:00:00')
                                                            and (jam_pulang is null or jam_pulang > '19:00:00')
                                                            and (tanggal like '%$bulanhitung')
                                                            and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))
                                                            and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel like '$bulanhitung2%'))
                                                            ");

                                                            //dd($gettanggaldianggaptidakmasuk_jampulang);


      $gettanggaldianggaptidakmasuk_jamdatangjampulang = DB::select("select fid, tanggal from preson_log
                                                            where (jam_datang is null or jam_datang > '09:00:00' or jam_datang < '07:00:00')
                                                            and (jam_pulang is null or jam_pulang > '19:00:00')
                                                            and (tanggal like '%$bulanhitung')
                                                            and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))
                                                            and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel like '$bulanhitung2%'))
                                                            ");

                                                              //dd($gettanggaldianggaptidakmasuk_jamdatangjampulang);


      $tanggalhadirperskpd = DB::select("select a.fid, tanggal from preson_log a join preson_pegawais b
                                          on a.fid = b.fid
                                          where tanggal like '%$bulanhitung'
                                          and skpd_id = $skpd_id");



      $getdatetelat = DB::select("select fid, tanggal from preson_log
                                  where (jam_datang > '08:01:00' and jam_datang < '09:00:00')
                                   and (jam_pulang > '16:00:00' and jam_pulang < '19:00:00')
                                   and (tanggal like '%$bulanhitung')
                                   and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))
                                   and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel like '$bulanhitung2%'))
                                   ");



      $getdatepulcep = DB::select("select fid, tanggal from preson_log
                                   where (jam_pulang > '12:01:00' and jam_pulang < '16:00:00')
                                    and (jam_datang < '08:00:00' and jam_datang > '07:00:00')
                                    and (tanggal like '%$bulanhitung')
                                    and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))

                                    ");

      $getdatedtpc = DB::select("select fid, tanggal from preson_log
                                  where (jam_datang > '08:01:00' and jam_datang < '09:00:00')
                                    and (jam_pulang > '15:00:00' and jam_pulang < '16:00:00')
                                    and (tanggal like '%$bulanhitung')
                                    and (tanggal not in (select DATE_FORMAT(libur,'%d/%m/%Y') from preson_harilibur where libur like '$bulanhitung2%'))
                                    and (tanggal not in (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel where tanggal_apel like '$bulanhitung2%'))

                                    ");

      $getdatehariapel = DB::select("select * from preson_log
                                      where tanggal in
                                      (select DATE_FORMAT(tanggal_apel,'%d/%m/%Y') from preson_apel
                                      where tanggal_apel like '$bulanhitung2%')
                                      and (jam_datang < '08:31:00' and jam_datang > '06:00:00')
                                      ");

      $gethariapel = DB::select("select tanggal_apel from preson_apel where tanggal_apel like '$bulanhitung2%'");

      $getpengecualiantpp = DB::select("select nip_sapk from preson_pengecualian_tpp");

      $intervensiperskpd = DB::select("select fid, tanggal_mulai, tanggal_akhir, id_intervensi
                                        from preson_intervensis a join preson_pegawais b
                                        on a.pegawai_id = b.id where skpd_id = $skpd_id and flag_status = 1");

                                        // return $intervensiperskpd;

      $arrpengecualian = array();
      foreach ($getpengecualiantpp as $key) {
        $arrpengecualian[] = $key->nip_sapk;
      }


      // masukin data ke array
      $dataabsensi = array();
      foreach ($pegawainya as $p) {
        // $intervensiperpegawai = array();
        // foreach ($intervensiperskpd as $key) {
        //   return "baa";
        // }

        $arrayrow = array();
        $arrayrow[] = $p->nip_sapk;
        $arrayrow[] = $p->nama;
        $arrayrow[] = $p->tpp_dibayarkan;


        // $arrfid = array_count_values(array_column($getdatetelat, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jmltelat = 0;
        // if ($statarr) {
        //    $jmltelat = $arrfid[$p->fid];
        // }

        $jmltelat = 0;
        foreach ($getdatetelat as $key) {
          if ($p->fid == $key->fid) {
            $jmltelat++;
          }
        }
        if ($jmltelat!=0) {
          foreach ($getdatetelat as $gdt) {
            if ($p->fid==$gdt->fid) {
              foreach ($intervensiperskpd as $key) {
                if (($key->fid == $p->fid) and $key->id_intervensi!=3) {
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

                  if (strpos($gdt->tanggal, '/') !== false) {
                    $tglnew = explode('/', $gdt->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                  } else {
                    $tglformat = $gdt->tanggal;
                  }


                  foreach ($dateRange as $dr) {
                    if ($tglformat==$dr) {
                      $jmltelat--;
                      break 2;
                    }
                  }
                }
              }
            }
            if ($jmltelat==0) break;
          }
        }
        $arrayrow[] = $jmltelat;
        $potongtpptelat = ($p->tpp_dibayarkan*60/100)*2/100*$jmltelat;
        $arrayrow[] = $potongtpptelat;


        // itung jumlah pulcep
        // $arrfid = array_count_values(array_column($getdatepulcep, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jmlpulcep = 0;
        // if ($statarr) {
        //    $jmlpulcep = $arrfid[$p->fid];
        // }
        $jmlpulcep = 0;
        foreach ($getdatepulcep as $key) {
          if ($p->fid == $key->fid) {
            $jmlpulcep++;
          }
        }

        if ($jmlpulcep!=0) {
          foreach ($getdatepulcep as $gdt) {
            if ($p->fid==$gdt->fid) {
              foreach ($intervensiperskpd as $key) {
                if (($key->fid == $p->fid) and $key->id_intervensi!=2) {
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

                  if (strpos($gdt->tanggal, '/') !== false) {
                    $tglnew = explode('/', $gdt->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                  } else {
                    $tglformat = $gdt->tanggal;
                  }

                  foreach ($dateRange as $dr) {
                    if ($tglformat==$dr) {
                      $jmlpulcep--;
                      break 2;
                    }
                  }
                }
              }
            }
            if ($jmlpulcep==0) break;
          }
        }

        $arrayrow[] = $jmlpulcep;
        $potongtpppulcep = ($p->tpp_dibayarkan*60/100)*2/100*$jmlpulcep;
        $arrayrow[] = $potongtpppulcep;


        //itung datang telat dan pulang cepat
        // $arrfid = array_count_values(array_column($getdatedtpc, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jmldtpc = 0;
        // if ($statarr) {
        //    $jmldtpc = $arrfid[$p->fid];
        // }
        $jmldtpc = 0;
        foreach ($getdatedtpc as $key) {
          if ($p->fid == $key->fid) {
            $jmldtpc++;
          }
        }
        if ($jmldtpc!=0) {
          foreach ($getdatedtpc as $gdt) {
            if ($p->fid==$gdt->fid) {
              foreach ($intervensiperskpd as $key) {
                if ($key->fid == $p->fid) {
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

                  if (strpos($gdt->tanggal, '/') !== false) {
                    $tglnew = explode('/', $gdt->tanggal);
                    $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                  } else {
                    $tglformat = $gdt->tanggal;
                  }

                  foreach ($dateRange as $dr) {
                    if ($tglformat==$dr) {
                      $jmldtpc--;
                      break 2;
                    }
                  }
                }
              }
            }
            if ($jmldtpc==0) break;
          }
        }
        $arrayrow[] = $jmldtpc;
        $potongtppdtpc = ($p->tpp_dibayarkan*60/100)*3/100*$jmldtpc;
        $arrayrow[] = $potongtppdtpc;


        // itung murni bolos
        $flagtanggal = 0;
        $tanggaltidakhadir = array(); //menampung tanggal yang tidak ada dalam database (berarti tidak hadir pada tgl tersebut)
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
        // dd($tanggaltidakhadir);
        $jmlmurnitidakhadir = count($tanggaltidakhadir);
        if ($jmlmurnitidakhadir!=0) {
          foreach ($tanggaltidakhadir as $gdt) {
            foreach ($intervensiperskpd as $key) {
              if ($key->fid == $p->fid) {
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

                if (strpos($gdt, '/') !== false) {
                  $tglnew = explode('/', $gdt);
                  $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                } else {
                  $tglformat = $gdt;
                }

                foreach ($dateRange as $dr) {
                  if ($tglformat==$dr) {
                    $jmlmurnitidakhadir--;
                    break 2;
                  }
                }
              }
            }
            if ($jmlmurnitidakhadir==0) break;
          }
        }


        $jumlahdianggapbolos_jamdatang = 0;
        foreach ($gettanggaldianggaptidakmasuk_jamdatang as $key) {
          if ($p->fid == $key->fid) {
            $jumlahdianggapbolos_jamdatang++;
          }
        }
        // $arrfid = array_count_values(array_column($gettanggaldianggaptidakmasuk_jamdatang, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jumlahdianggapbolos_jamdatang = 0;
        // if ($statarr) {
        //    $jumlahdianggapbolos_jamdatang = $arrfid[$p->fid];
        // }
        if ($jumlahdianggapbolos_jamdatang!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid) and $key->id_intervensi!=3) {
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
              foreach ($gettanggaldianggaptidakmasuk_jamdatang as $gdt) {
                if (strpos($gdt->tanggal, '/') !== false) {
                  $tglnew = explode('/', $gdt->tanggal);
                  $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                } else {
                  $tglformat = $gdt->tanggal;
                }
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gdt->fid) {
                    if ($tglformat==$dr) {
                      if ($jumlahdianggapbolos_jamdatang==0) { //mohon cek kembali logicnya.
                        break;
                      }
                      $jumlahdianggapbolos_jamdatang--;
                    }
                  }
                  if ($jumlahdianggapbolos_jamdatang==0) {
                    break;
                  }
                }
                if ($jumlahdianggapbolos_jamdatang==0) {
                  break;
                }
              }
            }
            if ($jumlahdianggapbolos_jamdatang==0) {
              break;
            }
          }
        }


        $jumlahdianggapbolos_jampulang = 0;
        foreach ($gettanggaldianggaptidakmasuk_jampulang as $key) {
          if ($p->fid == $key->fid) {
            $jumlahdianggapbolos_jampulang++;
          }
        }
        // $arrfid = array_count_values(array_column($gettanggaldianggaptidakmasuk_jampulang, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jumlahdianggapbolos_jampulang = 0;
        // if ($statarr) {
        //    $jumlahdianggapbolos_jampulang = $arrfid[$p->fid];
        // }
        if ($jumlahdianggapbolos_jampulang!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid) and $key->id_intervensi!=2) {
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
              foreach ($gettanggaldianggaptidakmasuk_jampulang as $gdt) {
                if (strpos($gdt->tanggal, '/') !== false) {
                  $tglnew = explode('/', $gdt->tanggal);
                  $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                } else {
                  $tglformat = $gdt->tanggal;
                }
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gdt->fid) {
                    if ($tglformat==$dr) {
                      if ($jumlahdianggapbolos_jampulang==0) { //mohon cek kembali logicnya.
                        break;
                      }
                      $jumlahdianggapbolos_jampulang--;
                    }
                  }
                }
              }
            }
          }
        }

        $jumlahdianggapbolos_jamdatangjampulang = 0;
        foreach ($gettanggaldianggaptidakmasuk_jamdatangjampulang as $key) {
          if ($p->fid == $key->fid) {
            $jumlahdianggapbolos_jamdatangjampulang++;
          }
        }
        // $arrfid = array_count_values(array_column($gettanggaldianggaptidakmasuk_jamdatangjampulang, 'fid'));
        // $statarr = array_key_exists($p->fid, $arrfid);
        // $jumlahdianggapbolos_jamdatangjampulang = 0;
        // if ($statarr) {
        //    $jumlahdianggapbolos_jamdatangjampulang = $arrfid[$p->fid];
        // }
        if ($jumlahdianggapbolos_jamdatangjampulang!=0) {
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid)) {
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
              foreach ($gettanggaldianggaptidakmasuk_jamdatangjampulang as $gdt) {
                if (strpos($gdt->tanggal, '/') !== false) {
                  $tglnew = explode('/', $gdt->tanggal);
                  $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                } else {
                  $tglformat = $gdt->tanggal;
                }
                foreach ($dateRange as $dr) {
                  if ($p->fid==$gdt->fid) {
                    if ($tglformat==$dr) {
                      if ($jumlahdianggapbolos_jamdatangjampulang==0) { //mohon cek kembali logicnya.
                        break;
                      }
                      $jumlahdianggapbolos_jamdatangjampulang--;
                    }
                  }
                }
              }
            }
          }
        }

        // echo $p->fid."---".$jmlmurnitidakhadir."---".$jumlahdianggapbolos_jamdatang."---".$jumlahdianggapbolos_jampulang."---".$jumlahdianggapbolos_jamdatangjampulang."<br>";

        $totaltidakhadir = $jmlmurnitidakhadir + $jumlahdianggapbolos_jamdatang + $jumlahdianggapbolos_jampulang + $jumlahdianggapbolos_jamdatangjampulang;
        // return $totaltidakhadir;


        //hitung apel
        $jumlahharushadirapel = count($gethariapel);

        $arrhadirapel = array();
        foreach ($getdatehariapel as $key) {
          $tglnew = explode('/', $key->tanggal);
          $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
          if ($p->fid == $key->fid) {
            $arrhadirapel[] = $tglformat;
          }
        }

        $arrharushadirapel = array();
        foreach ($gethariapel as $key) {
            $arrharushadirapel[] = $key->tanggal_apel;
        }

        $testya = array_diff($arrharushadirapel, $arrhadirapel);
        $jumlahtidakapeltapihadir = count($testya);


        $arrayrow[] = $totaltidakhadir;
        $potongantppbolos = ($p->tpp_dibayarkan*100/100)*3/100*$totaltidakhadir;
        $arrayrow[] = $potongantppbolos;

        foreach ($testya as $test) {
          foreach ($tanggalhadirperskpd as $key) {
            $tglnew = explode('/', $key->tanggal);
            $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
            if ($key->fid == $p->fid && $test == $tglformat) {
              $jumlahtidakapeltapihadir--;
            }
            if ($jumlahtidakapeltapihadir==0) {
              break 2;
            }
          }
        }

        $jumlahtidakhadirapel = (count($arrharushadirapel) - count($arrhadirapel)) - $jumlahtidakapeltapihadir;
        // dd($jumlahtidakhadirapel);
        if ($jumlahtidakhadirapel != 0) {
          $arrtidakhadirapel = array_diff($arrharushadirapel, $arrhadirapel);
          foreach ($intervensiperskpd as $key) {
            if (($key->fid == $p->fid)) {
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
              foreach ($arrtidakhadirapel as $tha) {
                foreach ($dateRange as $tgl) {
                  if ($tgl == $tha) {
                    $jumlahtidakhadirapel--;
                    break;
                  }
                }
                if ($jumlahtidakhadirapel==0) {
                  break;
                }
              }
            }
            if ($jumlahtidakhadirapel==0) {
              break;
            }
          }
        }

        $jumlahtidakapelempatkali = 0;
        if ($jumlahtidakhadirapel>=4) {
          $jumlahtidakapelempatkali = floor($jumlahtidakhadirapel / 4);
          $jumlahtidakhadirapel = $jumlahtidakhadirapel % 4;
        }

        $arrayrow[] = $jumlahtidakhadirapel;
        $potongantppapel = ($p->tpp_dibayarkan*60/100)*2.5/100*$jumlahtidakhadirapel;
        $arrayrow[] = $potongantppapel;

        $arrayrow[] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($p->tpp_dibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $arrayrow[] = $potongantppapelempatkali;

        // masukin ke array
        $dataabsensi[] = $arrayrow;
      }

      $nama_skpd = skpd::select('nama')->where('id', $skpd_id)->first();

      // START = Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // END = Pejabat Dokumen Jika Login sebagai admin skpd



      view()->share('dataabsensi', $dataabsensi);
      view()->share('nama_skpd', $nama_skpd);
      view()->share('tanggalmulai', $tanggalmulainya);
      view()->share('tanggalakhir', $tanggalakhirnya);
      // view()->share('start_date', $start_date);
      // view()->share('end_date', $end_date);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('pengecualian', $arrpengecualian);


      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdmin')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$tanggalmulainya.' - '.$tanggalakhirnya.'.pdf');
      }

      // return view('pages.laporan.cetakAdmin')
      //   ->with('dataabsensi', $dataabsensi)
      //   ->with('nama_skpd', $nama_skpd)
      //   ->with('start_dateR', $start_dateR)
      //   ->with('end_dateR', $end_dateR)
      //   ->with('pejabatDokumen', $pejabatDokumen)
      //   ->with('pengecualian', $arrpengecualian);
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
