<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;

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

        if(session('status') == 'administrator')
        {
          $jumlahPegawai = pegawai::count();
        }
        elseif(session('status') == 'admin')
        {
          $jumlahPegawai = pegawai::where('skpd_id', Auth::user()->skpd_id)->count();
        }


        $tpp = pegawai::where('id', $pegawai_id)->select('tpp_dibayarkan', 'fid')->first();

        $filterSKPD = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->get();

        $month = "08";
        $year = "2016";

        $start_date = "01-".$month."-".$year;
        $start_time = strtotime($start_date);

        $end_time = strtotime("+1 month", $start_time);

        if(session('status') == 'administrator')
        {
          for($i=$start_time; $i<$end_time; $i+=86400)
          {
            $tanggalini = date('d/m/Y', $i);

            foreach ($filterSKPD as $key) {
              $list[] = DB::select("SELECT c.nama AS skpd, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                      (select MIN(Jam_Log) from ta_log
                                    		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                    		and Fid = '$key->fid') as Jam_Datang,
                                    	(select MIN(Jam_Log) from ta_log
                                    		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                    		and Fid = '$key->fid') as Jam_Pulang
                                    FROM ta_log a, preson_pegawais b, preson_skpd c
                                    WHERE b.skpd_id = c.id
                                    AND a.Fid = b.fid
                                    AND a.Fid = '$key->fid'
                                    AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    LIMIT 1");
            }
          }
          $absensi = collect($list);
        }
        else if(session('status') == 'admin')
        {
          for($i=$start_time; $i<$end_time; $i+=86400)
          {
            $tanggalini = date('d/m/Y', $i);

            foreach ($filterSKPD as $key) {
              $list[] = DB::select("SELECT c.nama AS skpd, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                      (select MIN(Jam_Log) from ta_log
                                    		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                    		and Fid = '$key->fid') as Jam_Datang,
                                    	(select MIN(Jam_Log) from ta_log
                                    		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                    		and Fid = '$key->fid') as Jam_Pulang
                                    FROM ta_log a, preson_pegawais b, preson_skpd c
                                    WHERE b.skpd_id = c.id
                                    AND c.id = '".Auth::user()->skpd_id."'
                                    AND a.Fid = b.fid
                                    AND a.Fid = '$key->fid'
                                    AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                    LIMIT 1");
            }
          }
          $absensi = collect($list);
          // dd($absensi);

        }else{

          for($i=$start_time; $i<$end_time; $i+=86400)
          {
            $tanggalini = date('d/m/Y', $i);
            $list[] = DB::select("SELECT c.nama AS skpd, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                    (select MIN(Jam_Log) from ta_log
                                  		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                  		and Fid = '$tpp->fid') as Jam_Datang,
                                  	(select MIN(Jam_Log) from ta_log
                                  		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                  		and Fid = '$tpp->fid') as Jam_Pulang
                                  FROM ta_log a, preson_pegawais b, preson_skpd c
                                  WHERE b.skpd_id = c.id
                                  AND a.Fid = b.fid
                                  AND a.Fid = '$tpp->fid'
                                  AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  LIMIT 1");
          }
          $absensi = collect($list);
          // dd($absensi);
        }

        return view('home', compact('absensi', 'list', 'tpp', 'jumlahPegawai'));
    }

}
