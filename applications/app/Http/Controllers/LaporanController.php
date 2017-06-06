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
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function laporanAdministrator(){
      $getSkpd = skpd::select('id', 'nama')->get();

      return view('pages.laporan.laporanAdministrator', compact('getSkpd'));
    }

    public function laporanAdministratorStore(Request $request)
    {
      $getSkpd = skpd::select('id', 'nama')->get();

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
      $skpd_id = $request->skpd_id;
      $getpegawai = pegawai::
        select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama', 'preson_pegawais.status', 'preson_pegawais.tanggal_akhir_kerja')
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
        // --- END OF INTERVENSI FOR SPECIFIC PEGAWAI


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
			// -- LOOP PRESON LOG
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
				// echo "murnibolos: ".$pegawai->fid."---".$bolos."<br>";
				$murnibolos++;
			  }
			}
			$totalbolos = $murnibolos+$dianggapbolos;
			// --- END OF COUNT TOTAL BOLOS ---
		}
		// --- END OF CHECK STATUS PEGAWAI PENSIUN ---
        $totalpotongantpp = 0;

        if (in_array($pegawai->nip_sapk, $arrpengecualian)) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
          $tidakapel=0;
        }

        // --- CHECK STATUS PEGAWAI PENSIUN ---
        if ($pegawai->status==3) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
        }
        $rowdata["status"] = $pegawai->status;
        // --- END OF CHECK STATUS PEGAWAI PENSIUN ---

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
        if ($pegawai->status==3) {
          $rowdata["totalterimatpp"] = 0;
        } else {
          $rowdata["totalterimatpp"] = number_format(($pegawai->tpp_dibayarkan - $totalpotongantpp), 0, '.', '.');
        }

        // return "--- MAINTENANCE ----";
        $rekaptpp[] = $rowdata;
        $grandtotalpotongantpp += $totalpotongantpp;
        $grandtotaltppdibayarkan += ($pegawai->tpp_dibayarkan - $totalpotongantpp);
      }
      // --- END OF LOOP GET PEGAWAI ---

      return view('pages.laporan.laporanAdministrator')
        ->with('getSkpd', $getSkpd)
        ->with('skpd_id', $skpd_id)
        ->with('rekaptpp', $rekaptpp)
        ->with('bulan', $bulan)
        ->with('start_dateR', $tanggal_mulai)
        ->with('end_dateR', $tanggal_akhir)
        ->with('grandtotalpotongantpp', number_format($grandtotalpotongantpp, 0, '.', '.'))
        ->with('grandtotaltppdibayarkan', number_format($grandtotaltppdibayarkan, 0, '.', '.'))
        ->with('pengecualian', $arrpengecualian);
    }

    public function cetakAdministrator(Request $request)
    {
      $getSkpd = skpd::select('id', 'nama')->get();

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
      $skpd_id = $request->skpd_id;
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
        // --- END OF INTERVENSI FOR SPECIFIC PEGAWAI


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
			// -- LOOP PRESON LOG
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
						// echo "dianggapbolos-jamdtg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
						$dianggapbolos++;
					  }
					} else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
					} else if ((($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg==null)) {
					  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
						// echo "pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
						$pulangcepat++;
					  }
					}
				  } else {
					if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
					  if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
						// echo "dianggapbolos-jamdtg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
						$dianggapbolos++;
					  }
					} else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
		}
		// --- END OF CHECK STATUS PEGAWAI PENSIUN ---
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

      $nama_skpd = Skpd::select('nama')->where('id', $skpd_id)->first();

      // Pejabat Dokumen Jika Login sebagai admin skpd
      $pejabatDokumen = pejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                      ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nama', 'preson_pegawais.nip_sapk')
                                      ->where('preson_pegawais.skpd_id', $skpd_id)
                                      ->where('preson_pejabat_dokumen.flag_status', 1)
                                      ->get();
      // END = Pejabat Dokumen Jika Login sebagai admin skpd

      view()->share('getSkpd', $getSkpd);
      view()->share('skpd_id', $skpd_id);
      view()->share('rekaptpp', $rekaptpp);
      view()->share('bulan', $bulan);
      view()->share('grandtotalpotongantpp', number_format($grandtotalpotongantpp, 0, '.', '.'));
      view()->share('grandtotaltppdibayarkan', number_format($grandtotaltppdibayarkan, 0, '.', '.'));
      view()->share('pengecualian', $arrpengecualian);
      view()->share('pejabatDokumen', $pejabatDokumen);
      view()->share('nama_skpd', $nama_skpd);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakAdministrator')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online - '.$nama_skpd->nama.' Periode '.$bulan.'.pdf');
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
        select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama', 'preson_pegawais.status', 'preson_pegawais.tanggal_akhir_kerja')
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
      $harikerjaformatslash = array();
      foreach ($harikerja as $key) {
        $tglnew = explode('-', $key);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $harikerjaformatslash[] = $tglformat;
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
        $tppdibayarkan = $pegawai->tpp_dibayarkan;
        $rowdata = array();
        $rowdata["nip"] = $pegawai->nip_sapk;
        $rowdata["nama"] = $pegawai->nama;
        $rowdata["tpp"] = number_format($tppdibayarkan, 0, '.', '.');

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

        $dianggapbolos = 0;
        $telat = 0;
        $pulangcepat = 0;
        $telatpulangcepat = 0;
        $tidakapel = 0;
        $totalbolos = 0;
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
          // -- LOOP PRESON LOG
          foreach ($getpresonlog as $presonlog) {
            // --- MAKE SURE IS NOT HOLIDAY DATE
            if (($pegawai->fid == $presonlog->fid) && (!in_array($presonlog->tanggal, $tanggallibur)) && (in_array($presonlog->tanggal, $harikerjaformatslash))) {
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
                      // echo "dianggapbolos-jamdtg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
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
                      // echo "dianggapbolos-jamdtg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
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
                      // echo "dianggapbolos-jamdtg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if ((($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg==null)) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      // echo "pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    }
                  }
                } else {
                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      // echo "dianggapbolos-jamdtg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
        }
        // --- END OF CHECK STATUS PEGAWAI PENSIUN ---
        $totalpotongantpp = 0;

        if (in_array($pegawai->nip_sapk, $arrpengecualian)) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
          $tidakapel=0;
        }

        $rowdata["telat"] = $telat;
        $potongtpptelat = ($tppdibayarkan*60/100)*2/100*$telat;
        $rowdata["potongantelat"] = number_format(floor($potongtpptelat), '0', '.', '.');
        $totalpotongantpp += floor($potongtpptelat);

        $rowdata["pulangcepat"] = $pulangcepat;
        $potongtpppulcep = ($tppdibayarkan*60/100)*2/100*$pulangcepat;
        $rowdata["potonganpulangcepat"] = number_format(floor($potongtpppulcep), '0', '.', '.');
        $totalpotongantpp += floor($potongtpppulcep);

        $rowdata["telatpulangcepat"] = $telatpulangcepat;
        $potongtppdtpc = ($tppdibayarkan*60/100)*3/100*$telatpulangcepat;
        $rowdata["potongantelatpulangcepat"] = number_format(floor($potongtppdtpc), '0', '.', '.');
        $totalpotongantpp += floor($potongtppdtpc);

        $rowdata["tidakhadir"] = $totalbolos;
        $potongantppbolos = ($tppdibayarkan*100/100)*3/100*$totalbolos;
        $rowdata["potongantidakhadir"] = number_format(floor($potongantppbolos), '0', '.', '.');
        $totalpotongantpp += floor($potongantppbolos);

        $jumlahtidakapelempatkali = 0;
        if ($tidakapel>=4) {
          $jumlahtidakapelempatkali = floor($tidakapel / 4);
          $tidakapel = $tidakapel % 4;
        }

        $rowdata["tidakapel"] = $tidakapel;
        $potongantppapel = ($tppdibayarkan*60/100)*2.5/100*$tidakapel;
        $rowdata["potongantidakapel"] = number_format(floor($potongantppapel), '0', '.', '.');
        $totalpotongantpp += floor($potongantppapel);

        $rowdata["tidakapelempat"] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($tppdibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $rowdata["potongantidakapelempat"] = number_format(floor($potongantppapelempatkali), '0', '.', '.');
        $totalpotongantpp += floor($potongantppapelempatkali);

        $rowdata["totalpotongantpp"] = number_format(floor($totalpotongantpp), '0', '.', '.');
        $rowdata["totalterimatpp"] = number_format($tppdibayarkan - floor($totalpotongantpp), '0', '.', '.');

        // return "--- MAINTENANCE ----";
        $rekaptpp[] = $rowdata;
        $grandtotalpotongantpp += floor($totalpotongantpp);
        $grandtotaltppdibayarkan += ($tppdibayarkan - floor($totalpotongantpp));
      }
      // --- END OF LOOP GET PEGAWAI ---

      // SAVE TO PRESON_JURNAL
      $jumlah_bayarTPP = 0;
      foreach ($rekaptpp as $key) {
        $jumlah_bayarTPP += $key["totalterimatpp"];
      }

      $getJurnal = Jurnal::select('*')
				  ->where('skpd_id', $skpd_id)
				  ->where('bulan', $bulanexplode[0])
				  ->where('tahun', $bulanexplode[1])
				  ->first();

		if($getJurnal != null){
		if($getJurnal->flag_sesuai == 0){
		  $updateJurnal = Jurnal::find($getJurnal->id);
		  $updateJurnal->jumlah_tpp = $grandtotaltppdibayarkan;
		  $updateJurnal->update();
		}
		}else{
			$saveJurnal = new Jurnal;
			$saveJurnal->skpd_id  = $skpd_id;
			$saveJurnal->bulan = $bulanexplode[0];
			$saveJurnal->tahun = $bulanexplode[1];
			$saveJurnal->jumlah_tpp = $grandtotaltppdibayarkan;
			$saveJurnal->save();
		}
		// SAVE TO PRESON_JURNAL

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
        select('preson_pegawais.id as pegawai_id', 'nip_sapk', 'fid', 'tpp_dibayarkan', 'preson_pegawais.nama', 'preson_pegawais.status', 'preson_pegawais.tanggal_akhir_kerja')
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
      $harikerjaformatslash = array();
      foreach ($harikerja as $key) {
        $tglnew = explode('-', $key);
        $tglformat = $tglnew[2].'/'.$tglnew[1].'/'.$tglnew[0];
        $harikerjaformatslash[] = $tglformat;
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
        $tppdibayarkan = $pegawai->tpp_dibayarkan;
        $rowdata = array();
        $rowdata["nip"] = $pegawai->nip_sapk;
        $rowdata["nama"] = $pegawai->nama;
        $rowdata["tpp"] = number_format($tppdibayarkan, 0, '.', '.');

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

        $dianggapbolos = 0;
        $telat = 0;
        $pulangcepat = 0;
        $telatpulangcepat = 0;
        $tidakapel = 0;
        $totalbolos = 0;
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
          // -- LOOP PRESON LOG
          foreach ($getpresonlog as $presonlog) {
            // --- MAKE SURE IS NOT HOLIDAY DATE
            if (($pegawai->fid == $presonlog->fid) && (!in_array($presonlog->tanggal, $tanggallibur)) && (in_array($presonlog->tanggal, $harikerjaformatslash))) {
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
                      // echo "dianggapbolos-jamdtg: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
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
                      // echo "dianggapbolos-jamdtg-jumat: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
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
                      // echo "dianggapbolos-jamdtg-apel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
                  } else if ((($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg==null)) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensipulcep))) {
                      // echo "pulcep-apel: ".$presonlog->fid."--".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $pulangcepat++;
                    }
                  }
                } else {
                  if ($presonlog->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                    if ((!in_array($presonlog->tanggal, $tanggalintervensibebas)) && (!in_array($presonlog->tanggal, $tanggalintervensitelat))) {
                      // echo "dianggapbolos-jamdtg-bukanmesinapel: ".$presonlog->fid."--machid: ".$presonlog->mach_id."---".$presonlog->tanggal."--jamdatang:".$jamdtg."--jampulang:".$jamplg."<br>";
                      $dianggapbolos++;
                    }
                  } else if ($presonlog->jam_pulang==null || $jamplg > $batas_jamplg) {
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
        }
        // --- END OF CHECK STATUS PEGAWAI PENSIUN ---
        $totalpotongantpp = 0;

        if (in_array($pegawai->nip_sapk, $arrpengecualian)) {
          $telat=0;
          $pulangcepat=0;
          $telatpulangcepat=0;
          $totalbolos=0;
          $tidakapel=0;
        }

        $rowdata["telat"] = $telat;
        $potongtpptelat = ($tppdibayarkan*60/100)*2/100*$telat;
        $rowdata["potongantelat"] = number_format(floor($potongtpptelat), '0', '.', '.');
        $totalpotongantpp += floor($potongtpptelat);

        $rowdata["pulangcepat"] = $pulangcepat;
        $potongtpppulcep = ($tppdibayarkan*60/100)*2/100*$pulangcepat;
        $rowdata["potonganpulangcepat"] = number_format(floor($potongtpppulcep), '0', '.', '.');
        $totalpotongantpp += floor($potongtpppulcep);

        $rowdata["telatpulangcepat"] = $telatpulangcepat;
        $potongtppdtpc = ($tppdibayarkan*60/100)*3/100*$telatpulangcepat;
        $rowdata["potongantelatpulangcepat"] = number_format(floor($potongtppdtpc), '0', '.', '.');
        $totalpotongantpp += floor($potongtppdtpc);

        $rowdata["tidakhadir"] = $totalbolos;
        $potongantppbolos = ($tppdibayarkan*100/100)*3/100*$totalbolos;
        $rowdata["potongantidakhadir"] = number_format(floor($potongantppbolos), '0', '.', '.');
        $totalpotongantpp += floor($potongantppbolos);

        $jumlahtidakapelempatkali = 0;
        if ($tidakapel>=4) {
          $jumlahtidakapelempatkali = floor($tidakapel / 4);
          $tidakapel = $tidakapel % 4;
        }

        $rowdata["tidakapel"] = $tidakapel;
        $potongantppapel = ($tppdibayarkan*60/100)*2.5/100*$tidakapel;
        $rowdata["potongantidakapel"] = number_format(floor($potongantppapel), '0', '.', '.');
        $totalpotongantpp += floor($potongantppapel);

        $rowdata["tidakapelempat"] = $jumlahtidakapelempatkali;
        $potongantppapelempatkali = ($tppdibayarkan*60/100)*25/100*$jumlahtidakapelempatkali;
        $rowdata["potongantidakapelempat"] = number_format(floor($potongantppapelempatkali), '0', '.', '.');
        $totalpotongantpp += floor($potongantppapelempatkali);

        $rowdata["totalpotongantpp"] = number_format(floor($totalpotongantpp), '0', '.', '.');
        $rowdata["totalterimatpp"] = number_format($tppdibayarkan - floor($totalpotongantpp), '0', '.', '.');

        // return "--- MAINTENANCE ----";
        $rekaptpp[] = $rowdata;
        $grandtotalpotongantpp += floor($totalpotongantpp);
        $grandtotaltppdibayarkan += ($tppdibayarkan - floor($totalpotongantpp));
      }
      // --- END OF LOOP GET PEGAWAI ---

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
      view()->share('grandtotalpotongantpp', number_format($grandtotalpotongantpp, 0, '.', '.'));
      view()->share('grandtotaltppdibayarkan', number_format($grandtotaltppdibayarkan, 0, '.', '.'));
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
      $fid = pegawai::select('id','fid','skpd_id')->where('nip_sapk', $nip_sapk)->first();
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $bulan = $start_date[1].'/'.$start_date[2];
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

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

      // --- GET INTERVENSI SKPD ---
      $getintervensi = Intervensi::
        select('fid', 'tanggal_mulai', 'tanggal_akhir', 'preson_pegawais.id as id', 'preson_intervensis.id_intervensi as id_intervensi')
        ->join('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
        ->where('preson_pegawais.skpd_id', $fid->skpd_id)
        ->where('preson_intervensis.flag_status', 1)
        ->where('preson_pegawais.id', $fid->id)
        ->orderby('fid')
        ->get();

      // --- INTERVENSI FOR SPECIFIC PEGAWAI
      $dateintervensibebas = array();
      $dateintervensitelat = array();
      $dateintervensipulcep = array();
      foreach ($getintervensi as $intervensi) {
        if ($fid->id == $intervensi->id) {
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

      return view('pages.laporan.laporanPegawai', compact('start_dateR', 'end_dateR', 'intervensi', 'absensi', 'hariLibur', 'nip_sapk', 'bulan', 'tanggalBulan', 'tanggalapel', 'mesinapel', 'tanggalintervensitelat', 'tanggalintervensipulcep', 'tanggalintervensibebas', 'ramadhanformatslash'));
    }

    public function cetakPegawai(Request $request)
    {
      $bulanhitung = $request->bulanhitung;
      $bulanhitungformatnormal = explode("/", $bulanhitung);
      $bulanhitung2 = $bulanhitungformatnormal[1]."-".$bulanhitungformatnormal[0];

      $nip_sapk = $request->nip_sapk;
      $fid = pegawai::select('id', 'fid', 'nama', 'skpd_id')->where('nip_sapk', $nip_sapk)->first();

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

      // --- GET INTERVENSI SKPD ---
      $getintervensi = Intervensi::
        select('fid', 'tanggal_mulai', 'tanggal_akhir', 'preson_pegawais.id as id', 'preson_intervensis.id_intervensi as id_intervensi')
        ->join('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
        ->where('preson_pegawais.skpd_id', $fid->skpd_id)
        ->where('preson_intervensis.flag_status', 1)
        ->where('preson_pegawais.id', $fid->id)
        ->orderby('fid')
        ->get();

      // --- INTERVENSI FOR SPECIFIC PEGAWAI
      $dateintervensibebas = array();
      $dateintervensitelat = array();
      $dateintervensipulcep = array();
      foreach ($getintervensi as $intervensi) {
        if ($fid->id == $intervensi->id) {
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

      view()->share('start_dateR', $start_date);
      view()->share('end_dateR', $end_date);
      view()->share('absensi', $absensi);
      view()->share('tanggalBulan', $tanggalBulan);
      view()->share('intervensi', $intervensi);
      view()->share('hariLibur', $hariLibur);
      view()->share('nip_sapk', $nip_sapk);
      view()->share('fid', $fid);
      view()->share('tanggalapel', $tanggalapel);
      view()->share('mesinapel', $mesinapel);
      view()->share('ramadhanformatslash', $ramadhanformatslash);
      view()->share('tanggalintervensitelat', $tanggalintervensitelat);
      view()->share('tanggalintervensipulcep', $tanggalintervensipulcep);
      view()->share('tanggalintervensibebas', $tanggalintervensibebas);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.laporan.cetakPegawai')->setPaper('a4', 'potrait');
        return $pdf->download('Presensi Online - '.$nip_sapk.' Periode '.$start_date.' - '.$end_date.'.pdf');
      }

      return view('pages.laporan.cetakPegawai');
    }
}
