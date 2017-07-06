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
          $jumlahPegawai = pegawai::count();
          $jumlahTPP = DB::select("SELECT sum(preson_pegawais.tpp_dibayarkan) as jumlah_tpp from preson_pegawais");
          $jumlahTPPDibayarkan = DB::select("SELECT sum(jumlah_tpp) as jumlah_tpp FROM preson_jurnal WHERE bulan = $month AND tahun = $year");

          $tanggalini = date('d/m/Y');
          $tanggalinter = date('Y-m-d');

          $totalBaru = DB::select("SELECT skpd.id, skpd.nama as nama_skpd,
                                    IFNULL(tabel_jumlah_pegawai.jumlah_pegawai, 0) as jumlah_pegawai,
                                    IFNULL(tabel_jumlah_hadir.jumlah_hadir, 0) as jumlah_hadir,
                                    IFNULL(jumlah_pegawai - jumlah_hadir, 0) as jumlah_bolos,
                                    IFNULL(tabel_jumlah_intervensi.jumlah_intervensi, 0) as jumlah_intervensi
                                    -- , last_update

                                	FROM (select id, nama from preson_skpd where status = 1) as skpd

                                	LEFT OUTER JOIN (select b.nama as skpd, a.skpd_id, b.id, count(a.skpd_id) as jumlah_pegawai
                                										from preson_pegawais a, preson_skpd b
                                										where a.skpd_id = b.id
                                										and b.status = 1
                                										group by skpd_id) as tabel_jumlah_pegawai
                                	ON skpd.id = tabel_jumlah_pegawai.id

                                	LEFT OUTER JOIN (SELECT id, skpd, count(*) as jumlah_hadir
                                										FROM
                                											(select c.id, c.nama as skpd, count(*) as kk
                                												from preson_log a join preson_pegawais b
                                												on a.fid = b.fid
                                												join preson_skpd c on b.skpd_id = c.id
                                												where a.tanggal = '$tanggalini'
                                												group by c.nama, a.fid) as ab
                                										GROUP BY skpd) as tabel_jumlah_hadir
                                	ON skpd.id = tabel_jumlah_hadir.id

                                	LEFT OUTER JOIN (select c.id, c.nama, count(*) as 'jumlah_intervensi'
                                										from preson_intervensis a
                                										join preson_pegawais b on a.pegawai_id = b.id
                                										join preson_skpd c on b.skpd_id = c.id
                                										where a.tanggal_mulai <= '$tanggalinter'
                                										and a.tanggal_akhir >= '$tanggalinter'
                                										group by c.nama) as tabel_jumlah_intervensi
                                	ON skpd.id = tabel_jumlah_intervensi.id

                                	-- LEFT OUTER JOIN (select c.id, c.nama, max(str_to_date(a.datetime, '%d/%m/%Y %H:%i:%s')) as last_update
                                	-- 									from preson_log a, preson_pegawais b, preson_skpd c
                                	-- 									where c.id = b.skpd_id
                                	-- 									and b.fid = a.fid
                                	-- 									and a.tanggal = '$tanggalini'
                                	-- 									GROUP BY c.id) as tabel_last_update
                                	-- ON skpd.id = tabel_last_update.id
                                order by skpd.nama ASC");

          $totalHadir = collect($totalBaru)->sum('jumlah_hadir');

          return view('home', compact('jumlahPegawai', 'tpp', 'jumlahTPP', 'totalHadir', 'jumlahPegawaiSKPD', 'totalBaru', 'jumlahTPPDibayarkan'));

        }
        elseif(session('status') == 'admin')
        {
          $jumlahPegawai = pegawai::where('skpd_id', Auth::user()->skpd_id)->count();
          $jumlahTPP = DB::select("select sum(preson_pegawais.tpp_dibayarkan) as jumlah_tpp from preson_pegawais where preson_pegawais.skpd_id = '$skpd_id'");
          $jumlahTPPDibayarkan = DB::select("SELECT sum(jumlah_tpp) as jumlah_tpp FROM preson_jurnal WHERE bulan = $month AND tahun = $year AND skpd_id = '$skpd_id'");


          $tanggalini = date('d/m/Y');
          $absensi = DB::select("SELECT a.fid, a.nama as nama_pegawai, b.tanggal, b.jam_datang, b.jam_pulang
                                  FROM preson_pegawais a, preson_log b
                                  WHERE a.fid = b.fid
                                  AND b.tanggal = '$tanggalini'
                                  AND a.skpd_id = '$skpd_id'
                                  ORDER BY jam_datang ASC");
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

          return view('home', compact('getunreadintervensi', 'absensi', 'pegawai', 'list', 'tpp', 'jumlahPegawai', 'jumlahTPP', 'totalHadir', 'jumlahTPPDibayarkan'));
        }
        else
        {
          $jumlahTPPDibayarkan = DB::select("SELECT sum(jumlah_tpp) as jumlah_tpp FROM preson_jurnal WHERE bulan = $month AND tahun = $year AND skpd_id = '$skpd_id'");

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

          return view('home', compact('absensi', 'pegawai', 'tanggalBulan', 'intervensi', 'hariLibur', 'tpp', 'jumlahPegawai', 'jumlahTPP', 'bulan', 'tanggalBulan', 'tanggalapel', 'mesinapel', 'tanggalintervensitelat', 'tanggalintervensipulcep', 'tanggalintervensibebas', 'ramadhanformatslash', 'jumlahTPPDibayarkan'));
        }

    }

    public function detailabsensi($id)
    {
      $getskpd = skpd::find($id);

      if($getskpd == null){
        abort(404);
      }

      $tanggalini = date('d/m/Y');

      $logBaru = DB::select("SELECT a.fid, a.nama, b.tanggal, b.jam_datang, b.jam_pulang
                              from (select fid, nama from preson_pegawais where skpd_id = '$getskpd->id') as a
                              left join preson_log b on a.fid = b.fid
                              where b.tanggal = '$tanggalini'
                              ORDER BY b.jam_datang ASC");

      return view('pages.absensi.detailabsen')
        ->with('logBaru', $logBaru)
        ->with('getskpd', $getskpd);
    }

}
