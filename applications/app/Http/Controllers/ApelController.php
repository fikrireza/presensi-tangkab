<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Apel;
use App\Models\MesinApel;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Golongan;
use App\Models\User;

use Auth;
use Validator;
use DB;

class ApelController extends Controller
{


    public function index()
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();

      return view('pages.apel.index', compact('getApel'));
    }

    public function store(Request $request)
    {
      $message = [
        'tanggal_apel.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'tanggal_apel' => 'required',
        'keterangan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('apel.index')->withErrors($validator)->withInput();
      }

      $set = new apel;
      $set->tanggal_apel = $request->tanggal_apel;
      $set->keterangan = $request->keterangan;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('apel.index')->with('berhasil', 'Berhasil Menambahkan Hari Apel');
    }

    public function bind($id)
    {

      $getApel = apel::find($id);

      return $getApel;
    }

    public function edit(Request $request)
    {
      $message = [
        'tanggal_apel_edit.required' => 'Wajib di isi',
        'keterangan_edit.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'tanggal_apel_edit' => 'required',
        'keterangan_edit' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('apel.index')->withErrors($validator)->withInput();
      }


      $set = harilibur::find($request->id);
      $set->libur = $request->tanggal_apel_edit;
      $set->keterangan = $request->keterangan_edit;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('apel.index')->with('berhasil', 'Berhasil Merubah Data Hari Apel');
    }

    public function mesin()
    {
      $getMesin = mesinapel::get();

      return view('pages.apel.mesin', compact('getMesin'));
    }

    public function mesinPost(Request $request)
    {
      $message = [
        'mach_id.required' => 'Wajib di isi',
      ];

      $validator = validator::make($request->all(), [
        'mach_id' => 'required|max:3',
      ], $message);

      if($validator->fails()){
        return redirect()->route('apel.mesin')->withErrors($message)->withInput();
      }

      $save = new mesinapel;
      $save->mach_id = $request->mach_id;
      $save->catatan = $request->catatan;
      $save->flag_status = 1;
      $save->actor = Auth::user()->pegawai_id;
      $save->save();

      return redirect()->route('apel.mesin')->with('berhasil', 'Berhasil Menambahkan Nomor Mesin Apel');

    }

    public function pegawaiapel()
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $getGolongan = golongan::get();

      return view('pages.apel.pegawaiapel', compact('getApel', 'getGolongan'));
    }

    public function pegawaiapelStore(Request $request)
    {
      // dd($request);
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $tanggalApel = apel::select('id', 'tanggal_apel')->where('id', '=', $request->apel_id)->first();
        $tanggalApelnya = date('d/m/Y', strtotime($tanggalApel->tanggal_apel));
      $getAbsenApel = DB::select("SELECT a.Mach_id, a.Fid, a.Tanggal_Log, a.Jam_Log, c.skpd_id as skpd, c.nama as pegawai,
                                        d.id as golongan
                                  FROM ta_log a, preson_mesinapel b, preson_pegawais c, preson_golongans d
                                  WHERE a.Mach_id = b.mach_id
                                  AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApelnya'
                                  AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                  AND a.Fid = c.fid
                                  AND c.golongan_id = d.id
                                  GROUP BY c.nama");
// dd($getAbsenApel);
      $getSkpd = skpd::join('preson_pegawais', 'preson_pegawais.skpd_id', '=', 'preson_skpd.id')
                      ->select('preson_skpd.*')
                      ->groupby('preson_skpd.id')
                      ->get();

      $getGolongan = golongan::orderby('id', 'desc')->get();

      $jumlahPegawaiSKPD = DB::select("select b.nama as skpd, a.skpd_id, count(a.skpd_id) as jumlah_pegawai
                                      from preson_pegawais a, preson_skpd b
                                      where a.skpd_id = b.id
                                      group by skpd_id");

      return view('pages.apel.pegawaiapel', compact('getApel', 'tanggalApel', 'getAbsenApel', 'getSkpd', 'getGolongan', 'jumlahPegawaiSKPD'));
    }
}
