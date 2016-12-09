<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Golongan;
use Validator;
use Auth;

class GolonganController extends Controller
{


    public function index()
    {
      $golongan = golongan::get();

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
}
