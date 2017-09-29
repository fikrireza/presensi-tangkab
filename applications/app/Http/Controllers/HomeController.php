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
use App\Models\MesinApel;

use Auth;
use DB;
use Carbon;
use DatePeriod;
use DateTime;
use DateInterval;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pegawai_id = Auth::user()->pegawai_id;
        $nip_sapk = Auth::user()->nip_sapk;
        $fid = Auth::user()->fid;
        $skpd_id   = Auth::user()->skpd_id;

        if(session('status') == 'administrator' || session('status') == 'superuser' || session('status') == 'sekretaris')
        {
          $jumlahPegawai = pegawai::where('status', 1)->count();
          $jumlahTPP = DB::select("select sum(preson_pegawais.tpp_dibayarkan) as jumlah_tpp from preson_pegawais");
        }
        elseif(session('status') == 'admin')
        {
          $jumlahPegawai = pegawai::where('skpd_id', Auth::user()->skpd_id)->where('status', 1)->count();
          $jumlahTPP = DB::select("select sum(preson_pegawais.tpp_dibayarkan) as jumlah_tpp from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id'");
        }

        $tpp = pegawai::where('id', $pegawai_id)->select('tpp_dibayarkan', 'fid')->first();

        $filterSKPD = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->select('preson_skpd.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.fid', 'preson_pegawais.tpp_dibayarkan')
                            ->get();

        $month = date('m');
        $year = date('Y');

        $start_date = "01-".$month."-".$year;
        $start_time = strtotime($start_date);

        $end_time = strtotime("+1 month", $start_time);
        $pegawai = pegawai::select('preson_skpd.nama as nama_skpd')->join('preson_skpd', 'preson_pegawais.skpd_id', '=', 'preson_skpd.id')->get();


        if(session('status') == 'administrator' || session('status') == 'superuser' || session('status') == 'sekretaris')
        {
          $tanggalini = date('d/m/Y');
          $tanggalinter = date('Y-m-d');

          $jumlahintervensi = DB::select("select c.nama, count(*) as 'jumlah_intervensi'
                                          from preson_intervensis a
                                          join preson_pegawais b on a.pegawai_id = b.id
                                          join preson_skpd c on b.skpd_id = c.id
                                          where a.tanggal_mulai <= '$tanggalinter'
                                          and a.tanggal_akhir >= '$tanggalinter'
                                          group by c.nama");

          $jumlahPegawaiSKPD = DB::select("select b.nama as skpd, a.skpd_id, count(a.skpd_id) as jumlah_pegawai
                                          from preson_pegawais a, preson_skpd b
                                          where a.skpd_id = b.id
                                          and b.status = 1
                                          group by skpd_id");

          $absensi = DB::select("select id, skpd, count(*) as 'jumlah_hadir'
                                  from
                                  (select c.id, c.nama as skpd, count(*) as kk
                                  from ta_log a join preson_pegawais b
                                  on a.fid = b.fid
                                  join preson_skpd c on b.skpd_id = c.id
                                  where tanggal_log='$tanggalini'
                                  group by c.nama, a.fid) as ab
                                  group by skpd");

          $skpdall = DB::select("select a.id as 'id_skpd', a.nama as 'nama_skpd', b.nama as 'nama_pegawai', count(*) as 'jumlah_pegawai'
                                  from preson_skpd a
                                  left join preson_pegawais b
                                  on a.id = b.skpd_id
                                  where a.status = 1
                                  group by a.nama");

          $totalHadir = collect($absensi)->sum('jumlah_hadir');

          $lastUpdate = DB::select("select c.id, c.nama, max(str_to_date(a.DateTime, '%d/%m/%Y %H:%i:%s')) as last_update
                                    from ta_log a, preson_pegawais b, preson_skpd c
                                    where c.id = b.skpd_id
                                    and b.fid = a.Fid
                                    GROUP BY c.id");

          return view('home', compact('skpdall', 'absensi', 'pegawai', 'jumlahintervensi', 'tpp', 'jumlahPegawai', 'jumlahTPP', 'jumlahPegawaiSKPD', 'lastUpdate', 'totalHadir'));
        }
        else if(session('status') == 'admin')
        {
          $tanggalini = date('d/m/Y');
          $absensi = DB::select("SELECT pegawai.fid, pegawai.nama as nama_pegawai, Tanggal_Log,
                                				tabel_Jam_Datang.Jam_Datang, tabel_Jam_Pulang.Jam_Pulang
                                	FROM (select nama, fid from preson_pegawais where preson_pegawais.skpd_id = $skpd_id) as pegawai
                                	LEFT OUTER JOIN (select Tanggal_Log, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                 									  and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = $skpd_id) as tabel_Tanggal_Log
                                	ON pegawai.fid = tabel_Tanggal_Log.Fid
                                	LEFT OUTER JOIN (select Jam_Log as Jam_Datang, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = $skpd_id) as tabel_Jam_Datang
                                	ON pegawai.fid = tabel_Jam_Datang.Fid
                                	LEFT OUTER JOIN (select Jam_Log as Jam_Pulang, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '14:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = $skpd_id) as tabel_Jam_Pulang
                                	ON pegawai.fid = tabel_Jam_Pulang.Fid
                                	GROUP BY nama_pegawai");
          $absensi = collect($absensi);

          $totalHadir = DB::select("select count(*) as 'jumlah_hadir'
                                    from
                                    (select c.id, c.nama as skpd, count(*) as kk
                                    from ta_log a join preson_pegawais b
                                    on a.fid = b.fid
                                    join preson_skpd c on b.skpd_id = c.id
                                    where tanggal_log='$tanggalini'
                                    and b.skpd_id = $skpd_id
                                    group by c.nama, a.fid) as ab");
          $totalHadir = $totalHadir[0]->jumlah_hadir;
          $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                             ->where('preson_intervensis.flag_view', 0)
                                             ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                             ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                             ->count();

          return view('home', compact('getunreadintervensi', 'absensi', 'pegawai', 'list', 'tpp', 'jumlahPegawai', 'jumlahTPP', 'totalHadir'));
        }else{

          $fid = pegawai::select('id','fid','skpd_id')->where('nip_sapk', $nip_sapk)->first();
          $bulan = $month."/".$year;
          $bulanexplode = explode("/", $bulan);
          $bulanhitung = $bulanexplode[1]."-".$bulanexplode[0];
          // --- END OF GET REQUEST ---

          // --- GET TANGGAL MULAI & TANGGAL AKHIR ---
          $tanggal_mulai = $bulanhitung."-01";
          $tanggal_akhir = date("Y-m-t", strtotime($tanggal_mulai));

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
          $hariLibur = harilibur::select('libur', 'keterangan')->whereBetween('libur', array($tanggal_mulai, $tanggal_akhir))->get();

          // Mengambil data Absen Pegawai per Periode
          $date_from = strtotime($tanggal_mulai); // Convert date to a UNIX timestamp
          $date_to = strtotime($tanggal_akhir); // Convert date to a UNIX timestamp

          for ($i=$date_from; $i<=$date_to; $i+=86400) {
            $tanggalBulan[] = date('d/m/Y', $i);
          }

          $list = DB::select("SELECT a.*
                              FROM preson_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND (STR_TO_DATE(a.tanggal,'%d/%m/%Y') between '$tanggal_mulai' and '$tanggal_akhir')
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

          return view('home', compact('absensi', 'pegawai', 'tanggalBulan', 'intervensi', 'hariLibur', 'tpp', 'jumlahPegawai', 'jumlahTPP', 'bulan', 'tanggalBulan', 'tanggalapel', 'mesinapel', 'tanggalintervensitelat', 'tanggalintervensipulcep', 'tanggalintervensibebas', 'ramadhanformatslash'));
        }
    }

    public function detailabsensi($id)
    {
      $getskpd = skpd::find($id);

      if($getskpd == null){
        abort(404);
      }

      $tanggalini = date('d/m/Y');
      $pegawai = pegawai::where('skpd_id', $id)->get();
      $absensi = DB::select("select a.fid, nama, tanggal_log, jam_log
                              from (select fid, nama from preson_pegawais where skpd_id = $id) as a
                              left join ta_log b on a.fid = b.fid
                              where b.tanggal_log = '$tanggalini'");

      return view('pages.absensi.detailabsen')
        ->with('absensi', $absensi)
        ->with('pegawai', $pegawai)
        ->with('getskpd', $getskpd);
    }

}
