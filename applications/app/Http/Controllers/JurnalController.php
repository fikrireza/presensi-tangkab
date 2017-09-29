<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Skpd;
use App\Models\Jurnal;
use App\Models\Pegawai;
use App\Models\PresonLog;
use App\Models\Apel;
use App\Models\MesinApel;
use App\Models\HariLibur;
use App\Models\Intervensi;

use Auth;
use DB;
use DatePeriod;
use DateTime;
use DateInterval;

class JurnalController extends Controller
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
      $getJurnal = DB::select("SELECT skpd.id, skpd.nama, tpp_januari, flag_januari, tpp_februari, flag_februari, tpp_maret, flag_maret, tpp_april, flag_april, tpp_mei, flag_mei, tpp_juni, flag_juni, tpp_juli, flag_juli, tpp_agustus, flag_agustus, tpp_september, flag_september, tpp_oktober, flag_oktober, tpp_november, flag_november, tpp_desember, flag_desember
    	FROM (select id, nama from preson_skpd where preson_skpd.status = 1 AND preson_skpd.flag_shift = 0) as skpd

    	LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_januari, preson_jurnal.jumlah_tpp as tpp_januari, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '01'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Januari
    	ON skpd.id = Januari.id
    	LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_februari, preson_jurnal.jumlah_tpp as tpp_februari, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '02'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Februari
    	ON skpd.id = Februari.id
    	LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_maret, preson_jurnal.jumlah_tpp as tpp_maret, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '03'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Maret
    	ON skpd.id = Maret.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_april, preson_jurnal.jumlah_tpp as tpp_april, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '04'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as April
    	ON skpd.id = April.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_mei, preson_jurnal.jumlah_tpp as tpp_mei, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '05'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Mei
    	ON skpd.id = Mei.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_juni, preson_jurnal.jumlah_tpp as tpp_juni, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '06'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Juni
    	ON skpd.id = Juni.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_juli, preson_jurnal.jumlah_tpp as tpp_juli, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '07'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Juli
    	ON skpd.id = Juli.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_agustus, preson_jurnal.jumlah_tpp as tpp_agustus, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '08'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Agustus
    	ON skpd.id = Agustus.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_september, preson_jurnal.jumlah_tpp as tpp_september, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '09'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as September
    	ON skpd.id = September.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_oktober, preson_jurnal.jumlah_tpp as tpp_oktober, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '10'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Oktober
    	ON skpd.id = Oktober.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_november, preson_jurnal.jumlah_tpp as tpp_november, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '11'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as November
    	ON skpd.id = November.id
      LEFT OUTER JOIN (select preson_jurnal.flag_sesuai as flag_desember, preson_jurnal.jumlah_tpp as tpp_desember, preson_skpd.id as id from preson_jurnal, preson_skpd
    										where bulan = '12'
    										and tahun = '2017'
    										and preson_skpd.id = preson_jurnal.skpd_id) as Desember
    	ON skpd.id = Desember.id");

      $januari=0;$februari=0;$maret=0;$april=0;$mei=0;$juni=0;$juli=0;$agustus=0;$september=0; $oktober=0;$november=0;$desember=0;
      foreach ($getJurnal as $key) {
        $januari += $key->tpp_januari;
        $februari += $key->tpp_februari;
        $maret += $key->tpp_maret;
        $april += $key->tpp_april;
        $mei += $key->tpp_mei;
        $juni += $key->tpp_juni;
        $juli += $key->tpp_juli;
        $agustus += $key->tpp_agustus;
        $september += $key->tpp_september;
        $oktober += $key->tpp_oktober;
        $november += $key->tpp_november;
        $desember += $key->tpp_desember;
      }

      $grandTotal = $januari+$februari+$maret+$april+$mei+$juni+$juli+$agustus+$september+$oktober+$november+$desember;

      return view('pages.jurnal.index', compact('getJurnal', 'januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember', 'grandTotal'));
    }

    public function getJurnal($skpd_id, $bulan)
    {
        $bulan = $bulan;
        $bulanexplode = explode("-", $bulan);
        $bulanhitung = $bulanexplode[1].'-'.$bulanexplode[0];
        // --- END OF GET REQUEST ---

        // --- GET TANGGAL MULAI & TANGGAL AKHIR ---
        $tanggal_mulai = $bulanhitung."-01";
        $tanggal_akhir = date("Y-m-t", strtotime($tanggal_mulai));
        // --- END OF GET TANGGAL MULAI & TANGGAL AKHIR ---

        // --- GET DATA PEGAWAI BASED ON SKPD ID ---
        $getpegawai = pegawai::
          select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama')
          ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
          ->where('skpd_id', $skpd_id)
          ->orderby('preson_strukturals.nama', 'asc')
          ->orderby('preson_pegawais.nama', 'asc')
          ->get();

        if ($getpegawai->isEmpty()) {
          return redirect()->route('jurnal.index')->with('gagal', 'Data absen belum ada');
        }

        $getidpegawaiperskpd = array();
        foreach ($getpegawai as $key) {
          $getidpegawaiperskpd[] = $key->pegawai_id;
        }
        // --- END OF GET DATA PEGAWAI BASED ON SKPD ID ---

        $bulan = str_replace('-', '/', $bulan);

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

        if($getpresonlog->isEmpty()){
          return redirect()->route('jurnal.index')->with('gagal', 'Data absen belum ada');
        }

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

          // -- LOOP PRESON LOG
          $dianggapbolos = 0;
          $telat = 0;
          $pulangcepat = 0;
          $telatpulangcepat = 0;
          $tidakapel = 0;
          $tanggalhadir = array();


		  // --- RAMADHAN 2017 ---
		$periodramadhan = new DatePeriod(
			 new DateTime("2017-05-27"),
			 new DateInterval('P1D'),
			 new DateTime("2017-06-26 23:59:59")
		);
		$daterangeramadhan = array();
		foreach($periodramadhan as $date) {$daterangeramadhan[] = $date->format('Y-m-d'); }
		$ramadhan = array();
		foreach ($daterangeramadhan as $key) {
		  if (date('N', strtotime($key)) < 6) {
			$ramadhan[] = $key;
		  }
		}
		$ramadhanformatslash = array();
		foreach ($ramadhan as $key) {
		  $tglnew = explode('-', $key);
		  $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
		  $ramadhanformatslash[] = $tglformat;
		}
		// return $ramadhanformatslash;
		// --- END OF RAMADHAN 2017 ---

		// --- CHECK STATUS PEGAWAI PENSIUN OR MENINGGAL---
		$rowdata["status"] = $pegawai->status;
		$tanggalakhirkerja = $pegawai->tanggal_akhir_kerja;
		$monthyearakhirkerja=0;
		$monthyearcetaktpp=0;
		if ($tanggalakhirkerja!=null) {
		  $monthyearakhirkerja = date('Y-m', strtotime($tanggalakhirkerja));
		  $monthyearcetaktpp = date('Y-m', strtotime($tanggal_mulai));
		}

		if (($pegawai->status==3 || $pegawai->status==4) && ($monthyearakhirkerja<=$monthyearcetaktpp)) {
		  $tppdibayarkan = 0;
		} else {
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
          				$batas_jamdtg = 70000;
          				$batas_jamplg = 190000;

          				if (in_array($presonlog->tanggal, $ramadhanformatslash)) {
          				$upper_plgcepat = 150000;
          				}
                  // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT ---

                  // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                  $rawjamdtg = $presonlog->jam_datang;
                  $jamdtg = str_replace(':', '', $rawjamdtg);
                  $rawjamplg = $presonlog->jam_pulang;
                  $jamplg = str_replace(':', '', $rawjamplg);
                  // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
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
                        $telatpulangcepat++;
                      } else if ($intertelat!=0 && $interpulcep==0) {
                        $pulangcepat++;
                      } else if ($intertelat==0 && $interpulcep!=0) {
                        $telat++;
                      }
                    }
                  } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $telat++;
                    }
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      $pulangcepat++;
                    }
                  }
                } else {
                  // --- SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---
                  $lower_telatdtg = 73100;
                  $upper_telatdtg = 83100;
                  $lower_plgcepat = 150000;
                  $upper_plgcepat = 160000;
          				$batas_jamdtg = 63000;
          				$batas_jamplg = 190000;

          				if (in_array($presonlog->tanggal, $ramadhanformatslash)) {
                  $lower_telatdtg = 80100;
                  $upper_telatdtg = 90100;
          				$upper_plgcepat = 153000;
          				}
                  // --- END OF SET LOWER & UPPER BOUND JAM TELAT & PULANG CEPAT JUMAT ---

                  // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                  $rawjamdtg = $presonlog->jam_datang;
                  $jamdtg = str_replace(':', '', $rawjamdtg);
                  $rawjamplg = $presonlog->jam_pulang;
                  $jamplg = str_replace(':', '', $rawjamplg);
                  // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
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
                        $telatpulangcepat++;
                      } else if ($intertelat!=0 && $interpulcep==0) {
                        $pulangcepat++;
                      } else if ($intertelat==0 && $interpulcep!=0) {
                        $telat++;
                      }
                    }
                  } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $telat++;
                    }
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
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
        				$batas_jamdtg = 70000;
        			  $batas_jamplg = 190000;

        			  if (in_array($presonlog->tanggal, $ramadhanformatslash)) {
        				$upper_plgcepat = 150000;
        			  }
                // --- END OF SET LOWER & UPPER BOUND APEL ---

                // --- KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---
                $rawjamdtg = $presonlog->jam_datang;
                $jamdtg = str_replace(':', '', $rawjamdtg);
                $rawjamplg = $presonlog->jam_pulang;
                $jamplg = str_replace(':', '', $rawjamplg);
                // --- END OF KODE INI (((MUNGKIN))) PENYEBAB ERROR KALO JAM DATANG ATAU JAM PULANGNYA NULL ---

                if (in_array($presonlog->mach_id, $mesinapel)) {
                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      $dianggapbolos++;
                    }
                  } else if ($jamdtg > $maxjamdatang && $jamplg > $upper_plgcepat) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
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
                        $telatpulangcepat++;
                      } else if ($intertelat!=0 && $interpulcep==0) {
                        $pulangcepat++;
                      } else if ($intertelat==0 && $interpulcep!=0) {
                        $telat++;
                      }
                    }
                  } else if ((($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg==null)) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      $pulangcepat++;
                    }
                  }
                } else {
                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      $dianggapbolos++;
                    }
                  } else if (($jamdtg <= $maxjamdatang || $jamdtg >= $maxjamdatang) && $jamplg > $upper_plgcepat) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
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
                        $telatpulangcepat++;
                      } else if ($intertelat!=0 && $interpulcep==0) {
                        $pulangcepat++;
                      } else if ($intertelat==0 && $interpulcep!=0) {
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
              $murnibolos++;
            }
          }
          $totalbolos = $murnibolos+$dianggapbolos;
          // --- END OF COUNT TOTAL BOLOS ---
		}

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

        $getSKPDNama = SKPD::select('nama')->where('id', $skpd_id)->first();

        $getJurnalSesuai = Jurnal::where('bulan', $bulanexplode[0])
                                  ->where('tahun', $bulanexplode[1])
                                  ->where('skpd_id', $skpd_id)
                                  ->first();

        return view('pages.jurnal.detailJurnal')
          ->with('getSKPDNama', $getSKPDNama)
          ->with('getJurnalSesuai', $getJurnalSesuai)
          ->with('rekaptpp', $rekaptpp)
          ->with('bulan', $bulan)
          ->with('start_dateR', $tanggal_mulai)
          ->with('end_dateR', $tanggal_akhir)
          ->with('grandtotalpotongantpp', number_format($grandtotalpotongantpp, 0, '.', '.'))
          ->with('grandtotaltppdibayarkan', number_format($grandtotaltppdibayarkan, 0, '.', '.'))
          ->with('pengecualian', $arrpengecualian);

    }

    public function sesuai($id)
    {
        $sesuai = Jurnal::where('id', $id)->first();
        $sesuai->flag_sesuai = 1;
        $sesuai->update();

        return redirect()->route('jurnal.index')->with('berhasil', 'TPP Telah Diterbitkan');
    }

}
