<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Skpd;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\JadwalKerja;
use App\Models\JamKerja;
use App\Models\JamKerjaGroup;

use DB;
use Auth;
use Validator;

class JadwalKerjaController extends Controller
{

    public function __construct()
    {
      $this->middleware('auth');
    }

    public function index()
    {
      $getSKPD = JadwalKerja::join('preson_skpd', 'preson_skpd.id', '=', 'preson_jadwal_kerja.skpd_id')
                            ->join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_jadwal_kerja.actor')
                            ->select('preson_jadwal_kerja.*', 'preson_skpd.nama as skpd', 'preson_pegawais.nama as actor')
                            ->get();

      return view('pages.jadwalkerja.jadwal', compact('getSKPD'));
    }

    public function jadwalTambah()
    {
      $getSKPD = Skpd::get();
      $kerjaGroup = JamKerjaGroup::groupBy('group_id')->orderBy('nama_group', 'asc')->get();

      return view('pages.jadwalkerja.jadwalTambah', compact('getSKPD', 'kerjaGroup'));
    }

    public function jadwalPost(Request $request)
    {
      $message = [
        'skpd_id.required' => 'Wajib di isi',
        'periode_akhir.required' => 'Wajib di isi',
        'periode_awal.required' => 'Wajib di isi',
        'jam_kerja_group.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'skpd_id' => 'required',
        'periode_akhir' => 'required',
        'periode_awal' => 'required',
        'jam_kerja_group' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('jadwal-kerja.tambah')->withErrors($validator)->withInput();
      }

      $set = new JadwalKerja;
      $set->skpd_id = $request->skpd_id;
      $set->periode_awal = $request->periode_awal;
      $set->periode_akhir = $request->periode_akhir;
      $set->jam_kerja_group = $request->jam_kerja_group;
      $set->flag_status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('jadwal-kerja')->with('berhasil', 'Berhasil Menambahkan Jadwal Kerja');

    }

    public function jadwalUbah($id)
    {
      $getJadwal = JadwalKerja::find($id);
      $getSKPD = Skpd::get();
      $kerjaGroup = JamKerjaGroup::groupBy('group_id')->orderBy('nama_group', 'asc')->get();

      return view('pages.jadwalkerja.jadwalEdit', compact('getJadwal', 'getSKPD', 'kerjaGroup'));

    }

    public function jadwalEdit(Request $request)
    {
      $message = [
        'skpd_id.required' => 'Wajib di isi',
        'periode_akhir.required' => 'Wajib di isi',
        'periode_awal.required' => 'Wajib di isi',
        'jam_kerja_group.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'skpd_id' => 'required',
        'periode_akhir' => 'required',
        'periode_awal' => 'required',
        'jam_kerja_group' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('jadwal-kerja.tambah')->withErrors($validator)->withInput();
      }

      $set = JadwalKerja::find($request->id);
      $set->skpd_id = $request->skpd_id;
      $set->periode_awal = $request->periode_awal;
      $set->periode_akhir = $request->periode_akhir;
      $set->jam_kerja_group = $request->jam_kerja_group;
      $set->flag_status = $request->flag_status;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('jadwal-kerja')->with('berhasil', 'Berhasil Mengubah Jadwal Kerja');
    }

    public function jamKerja()
    {
      $getJamKerja = JamKerja::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_jam_kerja.actor')
                              ->select('preson_jam_kerja.*', 'preson_pegawais.nama as actor')
                              ->get();

      return view('pages.jadwalkerja.jamkerja', compact('getJamKerja'));
    }

    public function jamKerjaTambah()
    {
      return view('pages.jadwalkerja.jamkerjaAdd');
    }

    public function jamKerjaPost(Request $request)
    {
      $message = [
        'nama_jam_kerja.required' => 'Wajib di isi',
        'jam_masuk.required' => 'Wajib di isi',
        'jam_masuk_awal.required' => 'Wajib di isi',
        'jam_masuk_akhir.required' => 'Wajib di isi',
        'jam_pulang.required' => 'Wajib di isi',
        'jam_pulang_awal.required' => 'Wajib di isi',
        'jam_pulang_akhir.required' => 'Wajib di isi',
        'toleransi_pulcep.required' => 'Wajib di isi',
        'toleransi_terlambat.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_jam_kerja' => 'required',
        'jam_masuk' => 'required',
        'jam_masuk_awal' => 'required',
        'jam_masuk_akhir' => 'required',
        'jam_pulang' => 'required',
        'jam_pulang_awal' => 'required',
        'jam_pulang_akhir' => 'required',
        'toleransi_pulcep' => 'required',
        'toleransi_terlambat' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('jadwal-kerja.tambahjam')->withErrors($validator)->withInput();
      }

      $set = new JamKerja;
      $set->nama_jam_kerja = $request->nama_jam_kerja;
      $set->jam_masuk = $request->jam_masuk;
      $set->jam_masuk_awal = $request->jam_masuk_awal;
      $set->jam_masuk_akhir = $request->jam_masuk_akhir;
      $set->jam_pulang = $request->jam_pulang;
      $set->jam_pulang_awal = $request->jam_pulang_awal;
      $set->jam_pulang_akhir = $request->jam_pulang_akhir;
      $set->toleransi_pulcep = $request->toleransi_pulcep;
      $set->toleransi_terlambat = $request->toleransi_terlambat;
      $set->flag_status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('jadwal-kerja.jam')->with('berhasil', 'Berhasil Menambahkan Jam Kerja');
    }

    public function jamKerjaUbah($id)
    {
      $getJamKerja = JamKerja::find($id);

      return view('pages.jadwalkerja.jamkerjaEdit', compact('getJamKerja'));
    }

    public function jamKerjaEdit(Request $request)
    {
      $message = [
        'nama_jam_kerja.required' => 'Wajib di isi',
        'jam_masuk.required' => 'Wajib di isi',
        'jam_masuk_awal.required' => 'Wajib di isi',
        'jam_masuk_akhir.required' => 'Wajib di isi',
        'jam_pulang.required' => 'Wajib di isi',
        'jam_pulang_awal.required' => 'Wajib di isi',
        'jam_pulang_akhir.required' => 'Wajib di isi',
        'toleransi_pulcep.required' => 'Wajib di isi',
        'toleransi_terlambat.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_jam_kerja' => 'required',
        'jam_masuk' => 'required',
        'jam_masuk_awal' => 'required',
        'jam_masuk_akhir' => 'required',
        'jam_pulang' => 'required',
        'jam_pulang_awal' => 'required',
        'jam_pulang_akhir' => 'required',
        'toleransi_pulcep' => 'required',
        'toleransi_terlambat' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('jadwal-kerja.ubahjam', ['id' => $request->id])->withErrors($validator)->withInput();
      }

      $set = JamKerja::find($request->id);
      $set->nama_jam_kerja = $request->nama_jam_kerja;
      $set->jam_masuk = $request->jam_masuk;
      $set->jam_masuk_awal = $request->jam_masuk_awal;
      $set->jam_masuk_akhir = $request->jam_masuk_akhir;
      $set->jam_pulang = $request->jam_pulang;
      $set->jam_pulang_awal = $request->jam_pulang_awal;
      $set->jam_pulang_akhir = $request->jam_pulang_akhir;
      $set->toleransi_pulcep = $request->toleransi_pulcep;
      $set->toleransi_terlambat = $request->toleransi_terlambat;
      $set->flag_status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('jadwal-kerja.jam')->with('berhasil', 'Berhasil Mengubah Jam Kerja');
    }

    public function jamGroup()
    {
      $getJamGroup = JamKerjaGroup::join('preson_jam_kerja', 'preson_jam_kerja.id', '=', 'preson_jam_kerja_group.jam_kerja_id')
                                  ->join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_jam_kerja_group.actor')
                                  ->select('preson_jam_kerja_group.*', 'preson_jam_kerja.nama_jam_kerja as nama_jam', 'preson_jam_kerja.jam_masuk as jam_masuk', 'preson_jam_kerja.jam_pulang as jam_pulang', 'preson_pegawais.nama as actor')
                                  ->orderBy('preson_jam_kerja_group.nama_group')
                                  ->get();

      return view('pages.jadwalkerja.jamkerjagroup', compact('getJamGroup'));
    }

    public function jamGroupAdd()
    {
      $getJamKerja = JamKerja::get();

      return view('pages.jadwalkerja.jamkerjagroupAdd', compact('getJamKerja'));
    }

    public function jamGroupPost(Request $request)
    {
      $message  = [
        'nama_group.required'  => 'Wajib di isi',
        'jamKerja.*.jam_kerja_id.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'nama_group'  => 'required',
        'jamKerja.*.jam_kerja_id' => 'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('jadwal-kerja.tambahgroup')->withErrors($validator)->withInput();
      }

      if($request->group_id == null){
        $group = JamKerjaGroup::select('group_id')->max('group_id');
        $group += 1;
      }else{
        $group = $request->group_id;
      }

      DB::transaction(function() use($request, $group) {
        $jamKerja = $request->input('jamKerja');
        if($jamKerja != ""){
          foreach($jamKerja as $jam){
            $create = new JamKerjaGroup;
            $create->nama_group   = $request->nama_group;
            $create->group_id     = $group;
            $create->jam_kerja_id = $jam['jam_kerja_id'];
            $create->flag_status = 1;
            $set->actor = Auth::user()->pegawai_id;
            $create->save();
          }
        }
      });

      return redirect()->route('jadwal-kerja.group')->with('berhasil','Group Jam Kerja Berhasil Ditambah');
    }

    public function jamGroupLihat($group_id)
    {
      $getJamKerja = JamKerja::get();
      $lihat = JamKerjaGroup::where('group_id', '=', $group_id)->get();

      return view('pages.jadwalkerja.jamkerjagroupLihat', compact('lihat', 'getJamKerja'));
    }

    public function nonAktif($id)
    {
      $set = JamKerjaGroup::find($id);
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('jadwal-kerja.group')->with('berhasil','Group Jam Kerja Berhasil Dinonktif');
    }

    public function aktif($id)
    {
      $set = JamKerjaGroup::find($id);
      $set->flag_status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('jadwal-kerja.group')->with('berhasil','Group Jam Kerja Berhasil Diaktifkan');
    }
}
