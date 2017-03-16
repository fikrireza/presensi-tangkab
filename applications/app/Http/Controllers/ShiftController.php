<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Shift;
use App\Models\JamKerja;
use App\Models\JadwalKerja;
use App\Models\JamKerjaGroup;

use Validator;
use Auth;
use DB;

class ShiftController extends Controller
{

    public function index()
    {
      $getSkpd = skpd::where('flag_shift', 0)->get();

      $skpdShift = skpd::where('flag_shift', 1)->get();

      return view('pages.shift.index', compact('getSkpd', 'skpdShift'));
    }

    public function skpdShift(Request $request)
    {

      $set = skpd::find($request->skpd_id);
      $set->flag_shift = 1;
      $set->update();

      return redirect()->route('shift.index')->with('berhasil', 'SKPD Terpilih Menjadi Shift');
    }

    public function jadwalShift()
    {
      $month = date('m');
      $year = date('Y');

      $start_date = "01-".$month."-".$year;
      $start_time = strtotime($start_date);

      $end_time = strtotime("+1 month", $start_time);

      for($i=$start_time; $i<$end_time; $i+=86400)
      {
        $tanggalBulan[] = date('d-m-Y', $i);

      }

      return view('pages.shift.jadwalShift', compact('tanggalBulan'));
    }

    public function jadwalShiftTanggal($tanggal)
    {
      $skpd_id   = Auth::user()->skpd_id;
      $list = DB::select("SELECT a.nama, a.fid, b.nama_group, c.nama_jam_kerja, d.tanggal, c.jam_masuk, c.jam_pulang
                          FROM preson_pegawais a, preson_jam_kerja_group b, preson_jam_kerja c, preson_shift_log d, preson_skpd e
                          WHERE b.jam_kerja_id = c.id
                          AND d.jam_kerja_id = c.id
                          AND d.fid = a.fid
                          AND DATE_FORMAT(STR_TO_DATE(d.tanggal,'%Y-%m-%d'), '%d-%m-%Y') = '$tanggal'
                          AND a.skpd_id = e.id
                          AND a.skpd_id = '$skpd_id'
                          group by a.nama
                          order by c.nama_jam_kerja asc");
      dd($list);
      return view('pages.shift.jadwalShiftTanggal', compact('list'));
    }
}
