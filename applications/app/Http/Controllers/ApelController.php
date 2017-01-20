<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Apel;
use App\Models\MesinApel;
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
}
