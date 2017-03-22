<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Skpd;
use App\Models\Pegawai;

use Validator;
use Auth;

class SkpdController extends Controller
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
      $skpd = skpd::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_skpd.actor')
                    ->select('preson_skpd.*', 'preson_pegawais.nama as actor')
                    ->get();

      return view('pages.skpd.index', compact('skpd'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama.required' => 'Wajib di isi',
        'singkatan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'singkatan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('skpd.index')->withErrors($validator)->withInput();
      }

      $set = new skpd;
      $set->nama = $request->nama;
      $set->singkatan = $request->singkatan;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('skpd.index')->with('berhasil', 'Berhasil Menambahkan Data SKPD');
    }

    public function bind($id)
    {
      $get = skpd::find($id);

      return $get;
    }

    public function edit(Request $request)
    {
      $set = skpd::find($request->id_skpd);
      $set->nama = $request->nama_skpd;
      $set->singkatan = $request->singkatan_skpd;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('skpd.index')->with('berhasil', 'Berhasil Mengubah Data SKPD');
    }

    public function nonAktif($id)
    {
      $set = skpd::find($id);

      $set->status = 0;
      $set->update();

      return redirect()->route('skpd.index')->with('berhasil', 'Berhasil NonAktif SKPD');
    }

    public function aktif($id)
    {
      $set = skpd::find($id);

      $set->status = 1;
      $set->update();

      return redirect()->route('skpd.index')->with('berhasil', 'Berhasil Aktifkan SKPD');
    }
}
