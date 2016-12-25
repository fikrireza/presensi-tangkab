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
use DateTime;
use DateInterval;
use DatePeriod;

class AbsensiController extends Controller
{



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
      foreach ($potongHariLibur as $liburs) {
        $hariLibur[] = $liburs->libur;
      }

      //--------- Menghitung Berapa Jumlah Tanpa Keterangan/Absen Pegawai dalam Periode Tertentu, Sudah termasuk Intervensi dan Hari Libur
      $getPegawaiSKPD = pegawai::where('skpd_id', $skpd_id)->get();
      foreach ($getPegawaiSKPD as $pegawai) {
        //Mencari Apakah Pegawai Ada Intervensi Atau Tidak
        $potongIntervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                    ->select('preson_pegawais.id as pegawai_id', 'preson_intervensis.tanggal_mulai', 'preson_intervensis.jumlah_hari', 'preson_intervensis.tanggal_akhir')
                                    ->where('preson_pegawais.id', $pegawai->id)
                                    ->where('preson_intervensis.tanggal_mulai', '>=', $start_date)
                                    ->where('preson_intervensis.tanggal_akhir', '<=', $end_date)
                                    ->where('preson_intervensis.flag_status', 1)
                                    ->get();

        // menghitung Jumlah Hadir Dengan Periode Tertentu
        $jumlahMasuk = DB::select("SELECT b.id as pegawai_id, b.fid, count(DISTINCT a.Tanggal_Log) as Jumlah_Masuk
                                    FROM ta_log a, preson_pegawais b
                                    WHERE a.Fid = b.fid
                                    AND b.id = $pegawai->id
                                    AND str_to_date(a.Tanggal_Log, '%d/%m/%Y') BETWEEN '$start_date' AND '$end_date'");
        $jumlahMasuk = $jumlahMasuk[0]->Jumlah_Masuk;


        $hasilTanpaKeterangan = array();
        if($potongIntervensi->isEmpty()){
          $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
          $holidayDays = $hariLibur;

          $from = new DateTime($start_date);
          $to = new DateTime($end_date);
          // $to->modify('+1 day'); H+1 Hari
          $interval = new DateInterval('P1D');
          $periods = new DatePeriod($from, $interval, $to);

          $days = 0;
          foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) continue;
            if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($period->format('*-m-d'), $holidayDays)) continue;
            $days++;
          }
          $hasilTanpaKeterangan['nip_sapk'] = $pegawai->nip_sapk;
          $hasilTanpaKeterangan['hasil'] = $days - $jumlahMasuk;

        }else{
          foreach ($potongIntervensi as $intervensi) {
            $pegawai_id = $intervensi->pegawai_id;
            $tanggal_mulai = $intervensi->tanggal_mulai;
            $tanggal_akhir = $intervensi->tanggal_akhir;
            $mulai = new DateTime($tanggal_mulai);
            $akhir   = new DateTime($tanggal_akhir);

            for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
            {
              $intervensiHasil[] =  $i->format("Y-m-d");
            }

            $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
            $holidayDays = array_merge($hariLibur, $intervensiHasil);

            $from = new DateTime($start_date);
            $to = new DateTime($end_date);
            // $to->modify('+1 day');
            $interval = new DateInterval('P1D');
            $periods = new DatePeriod($from, $interval, $to);

            $days = 0;
            foreach ($periods as $period) {
              if (!in_array($period->format('N'), $workingDays)) continue;
              if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
              if (in_array($period->format('*-m-d'), $holidayDays)) continue;
              $days++;
            }
            $hasilTanpaKeterangan['nip_sapk'] = $pegawai->nip_sapk;
            $hasilTanpaKeterangan['hasil'] = $days - $jumlahMasuk;

          }
        }
        $hasilTanpaKeterangan;
      }
      // Menghitung Berapa Jumlah Ketidakhadiran Pegawai dalam Periode Tertentu, Sudah termasuk Intervensi dan Hari Libur -------------//


      // Menghitung Jumlah Terlambat dan Pulang Cepat
      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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

      return view('pages.absensi.index', compact('getSkpd', 'skpd_id', 'start_dateR', 'end_dateR', 'rekapAbsenPeriode', 'hasilTanpaKeterangan'));
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

    public function filterAdmin(Request $request)
    {
      $skpd_id = Auth::user()->skpd_id;
      $start_dateR = $request->start_date;
      $start_date = explode('/', $start_dateR);
      $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
      $end_dateR = $request->end_date;
      $end_date = explode('/', $end_dateR);
      $end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

      $rekapAbsenPeriode = DB::select("SELECT pegawai.nip_sapk, pegawai.fid, pegawai.nama as nama_pegawai,
                                    				IFNULL(tabel_Jam_Datang_Terlambat.Jumlah_Terlambat, 0) as Jumlah_Terlambat,
                                    				IFNULL(tabel_Jam_Pulang_Cepat.Jumlah_Pulcep,0) as Jumlah_Pulcep
                                    	FROM (select nip_sapk, nama, fid from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id') as pegawai

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

      return view('pages.absensi.absensiSKPD', compact('start_dateR', 'end_dateR', 'rekapAbsenPeriode'));
    }
}
