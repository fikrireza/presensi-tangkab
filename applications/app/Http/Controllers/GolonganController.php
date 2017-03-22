<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Golongan;
use App\Models\Pegawai;
use Validator;
use Auth;

class GolonganController extends Controller
{


    public function index()
    {
      $golongan = golongan::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_golongans.actor')
                          ->select('preson_golongans.*', 'preson_pegawais.nama as actor')
                          ->get();

      return view('pages.golongan.index', compact('golongan'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('golongan.index')->withErrors($validator)->withInput();
      }

      $set = new golongan;
      $set->nama = $request->nama;
      $set->status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('golongan.index')->with('berhasil', 'Berhasil Menambahkan Data Golongan');
    }

    public function nonAktif($id)
    {
      $set = golongan::find($id);
      $set->status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('golongan.index')->with('berhasil', 'Berhasil Non-Aktifkan Golongan');
    }

    public function aktif($id)
    {
      $set = golongan::find($id);
      $set->status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('golongan.index')->with('berhasil', 'Berhasil Aktifkan Golongan');
    }
}
