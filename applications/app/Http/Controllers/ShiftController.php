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

    public function jadwalShiftBulan(Request $request)
    {
      $bulan = explode('-', $request->bulan_shift);

      $month = $bulan[0];
      $year = $bulan[1];

      $start_date = "01-".$month."-".$year;
      $start_time = strtotime($start_date);

      $end_time = strtotime("+1 month", $start_time);

      for($i=$start_time; $i<$end_time; $i+=86400)
      {
        $tanggalBulan[] = date('d-m-Y', $i);
      }

      $pilihBulan = $request->bulan_shift;

      return view('pages.shift.jadwalShift', compact('tanggalBulan', 'pilihBulan'));
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

      $getPegawai = DB::select("SELECT preson_pegawais.nip_sapk, preson_pegawais.nama as nama_pegawai, preson_pegawais.fid from preson_pegawais, preson_strukturals
                                WHERE preson_pegawais.fid NOT IN (SELECT fid FROM (preson_shift_log) WHERE DATE_FORMAT(STR_TO_DATE(preson_shift_log.tanggal,'%Y-%m-%d'), '%d-%m-%Y') = '$tanggal')
                                AND preson_pegawais.skpd_id = $skpd_id
                                AND preson_strukturals.id = preson_pegawais.struktural_id
                                AND preson_pegawais.status = 1
                                ORDER BY preson_strukturals.nama ASC");

      $getJamKerja = DB::select("SELECT preson_jam_kerja.*
                                FROM preson_jadwal_kerja, preson_jam_kerja_group, preson_jam_kerja
                                WHERE preson_jadwal_kerja.skpd_id = $skpd_id
                                AND preson_jadwal_kerja.jam_kerja_group = preson_jam_kerja_group.group_id
                                AND preson_jam_kerja_group.jam_kerja_id = preson_jam_kerja.id ");

      $getPegawaiKerja = DB::select("SELECT preson_shift_log.id, preson_pegawais.nama, preson_pegawais.nip_sapk, preson_jam_kerja.nama_jam_kerja, preson_jam_kerja.jam_masuk, preson_jam_kerja.jam_pulang FROM preson_shift_log, preson_pegawais, preson_jam_kerja
                                      WHERE preson_pegawais.fid = preson_shift_log.fid
                                      AND preson_shift_log.jam_kerja_id = preson_jam_kerja.id
                                      AND preson_pegawais.skpd_id = $skpd_id
                                      AND DATE_FORMAT(STR_TO_DATE(preson_shift_log.tanggal,'%Y-%m-%d'), '%d-%m-%Y') = '$tanggal'");

      return view('pages.shift.jadwalShiftTanggal', compact('tanggal','list','getPegawai','getJamKerja','getPegawaiKerja'));
    }

    public function jadwalShiftTanggalStore(Request $request)
    {
      $jumlah = 1;
      foreach ($request->pegawai_fid as $pegawai) {
        $save = new Shift;
        $save->fid = $pegawai;
        $save->jam_kerja_id = $request->jam_kerja_id;
        $save->tanggal  = $request->tanggal;
        $save->actor = Auth::user()->pegawai_id;
        $save->save();
        $jumlah++;
      }

      $tanggal = date("d-m-Y", strtotime($request->tanggal));

      return redirect()->route('shift.jadwaltanggal', ['tanggal' => $tanggal])->with('berhasil', 'Jadwal Pegawai Berhasil Diinput '.$jumlah);

    }

    public function jadwalShiftUbah($id)
    {
      $getShift = Shift::join('preson_pegawais', 'preson_pegawais.fid', '=', 'preson_shift_log.fid')
                        ->select('preson_shift_log.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.nip_sapk')
                        ->where('preson_shift_log.id', $id)
                        ->first();

      if($getShift == null){
        abort(404);
      }

      $skpd_id   = Auth::user()->skpd_id;
      $getJamKerja = DB::select("SELECT preson_jam_kerja.*
                                FROM preson_jadwal_kerja, preson_jam_kerja_group, preson_jam_kerja
                                WHERE preson_jadwal_kerja.skpd_id = $skpd_id
                                AND preson_jadwal_kerja.jam_kerja_group = preson_jam_kerja_group.group_id
                                AND preson_jam_kerja_group.jam_kerja_id = preson_jam_kerja.id ");

      return view('pages.shift.jadwalShiftUbah', compact('getShift', 'getJamKerja'));

    }

    public function jadwalShiftEdit(Request $request)
    {
      $set = Shift::find($request->id);
      $set->jam_kerja_id = $request->jam_kerja_id;
      $set->actor = Auth::user()->pegawai_id;
      $set->keterangan = $request->keterangan;
      $set->update();

      $tanggal = date("d-m-Y", strtotime($request->tanggal));

      return redirect()->route('shift.jadwaltanggal', ['tanggal' => $tanggal])->with('berhasil', 'Jadwal Pegawai '.$request->nama_pegawai.' Berhasil Dirubah');
    }
}
