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
use DB;
use Carbon;

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
        $fid = Auth::user()->fid;
        $skpd_id   = Auth::user()->skpd_id;

        if(session('status') == 'administrator' || session('status') == 'superuser' || session('status') == 'sekretaris' || session('status') == 'bpkad')
        {
          $jumlahPegawai = pegawai::count();
          $jumlahTPP = DB::select("select sum(preson_pegawais.tpp_dibayarkan) as jumlah_tpp from preson_pegawais");
        }
        elseif(session('status') == 'admin')
        {
          $jumlahPegawai = pegawai::where('skpd_id', Auth::user()->skpd_id)->count();
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
          for($i=$start_time; $i<$end_time; $i+=86400)
          {
            $tanggalBulan[] = date('d/m/Y', $i);
          }

          $list = DB::select("SELECT a.*
                              FROM preson_log a, preson_pegawais b, preson_skpd c
                              WHERE b.skpd_id = c.id
                              AND a.tanggal like '%/.$month./.$year.%'
                              AND a.fid = b.fid
                              AND str_to_date(a.tanggal, '%d/%m/%Y') NOT IN (SELECT libur FROM preson_harilibur)
                              AND a.fid = '$tpp->fid'");

          $absensi = collect($list);

          $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                  ->select('preson_pegawais.id as pegawai_id', 'preson_intervensis.tanggal_mulai', 'preson_intervensis.jumlah_hari', 'preson_intervensis.tanggal_akhir', 'preson_intervensis.deskripsi', 'preson_intervensis.jenis_intervensi')
                                  ->where('preson_pegawais.id', $pegawai_id)
                                  ->where('preson_intervensis.tanggal_mulai', 'LIKE', '%'.$month.'%')
                                  ->where('preson_intervensis.flag_status', 1)
                                  ->get();

          $hariLibur = hariLibur::where('libur', 'LIKE', '____-'.$month.'-__')->get();

          return view('home', compact('absensi', 'pegawai', 'tanggalBulan', 'intervensi', 'hariLibur', 'tpp', 'jumlahPegawai', 'jumlahTPP'));
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
