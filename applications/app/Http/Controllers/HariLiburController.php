<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\HariLibur;

use Validator;
use Auth;

class HariLiburController extends Controller
{


    public function index()
    {
      $harilibur = harilibur::get();

      return view('pages.harilibur.index', compact('harilibur'));
    }

    public function store(Request $request)
    {
      $message = [
        'libur.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'libur' => 'required',
        'keterangan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('harilibur.index')->withErrors($validator)->withInput();
      }

      $set = new harilibur;
      $set->libur = $request->libur;
      $set->keterangan = $request->keterangan;
      $set->actor = Auth::user()->id;
      $set->save();

      return redirect()->route('harilibur.index')->with('berhasil', 'Berhasil Menambahkan Hari Libur');

    }

    public function bind($id)
    {
      $find = harilibur::find($id);

      return $find;
    }

    public function edit(Request $request)
    {
      $message = [
        'libur_edit.required' => 'Wajib di isi',
        'keterangan_edit.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'libur_edit' => 'required',
        'keterangan_edit' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('harilibur.index')->withErrors($validator)->withInput();
      }


      $set = harilibur::find($request->id);
      $set->libur = $request->libur_edit;
      $set->keterangan = $request->keterangan_edit;
      $set->actor = Auth::user()->id;
      $set->save();

      return redirect()->route('harilibur.index')->with('berhasil', 'Berhasil Menambahkan Hari Libur');

    }
}
