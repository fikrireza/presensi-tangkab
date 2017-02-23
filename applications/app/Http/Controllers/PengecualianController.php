<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pengecualian;
use App\Models\Pegawai;


use Validator;
use Auth;

class PengecualianController extends Controller
{
    //

    public function index()
    {
      $pengecualian = Pengecualian::get();

      return view('pages.pengecualian.index', compact('pengecualian'));
    }

    public function store(Request $request)
    {
      $message = [
        'nip_sapk.required' => 'Wajib di isi',
        'catatan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nip_sapk' => 'required|max:150',
        'catatan' => 'required|max:150',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pengecualian.index')->withErrors($validator)->withInput();
      }
      $set = new Pengecualian;
      $set->nip_sapk = $request->nip_sapk;
      $set->catatan = $request->catatan;
      $set->status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('pengecualian.index')->with('berhasil', 'Berhasil Menambahkan Data Pengecualian');
    }

    public function bind($id)
    {
      $get = Pengecualian::find($id);

      return $get;
    }

    public function edit(Request $request)
    {
      $set = Pengecualian::find($request->id_pengecualian);
      $set->nip_sapk = $request->nip_sapk;
      $set->catatan = $request->catatan;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('pengecualian.index')->with('berhasil', 'Berhasil Mengubah Data Pengecualian');
    }
}
