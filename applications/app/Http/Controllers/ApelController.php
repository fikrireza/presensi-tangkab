<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Apel;
use App\Models\Pegawai;
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
}
