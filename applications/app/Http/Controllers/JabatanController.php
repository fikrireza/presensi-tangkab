<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Jabatan;

use Validator;
use Auth;

class JabatanController extends Controller
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
      $jabatan = jabatan::get();

      return view('pages.jabatan.index', compact('jabatan'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama' => 'required|max:150',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('jabatan.index')->withErrors($validator)->withInput();
      }
      $set = new jabatan;
      $set->nama = $request->nama;
      $set->status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('jabatan.index')->with('berhasil', 'Berhasil Menambahkan Data Jabatan');
    }

    public function bind($id)
    {
      $get = jabatan::find($id);

      return $get;
    }

    public function edit(Request $request)
    {
      $set = jabatan::find($request->id_jabatan);
      $set->nama = $request->nama_jabatan;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('jabatan.index')->with('berhasil', 'Berhasil Mengubah Data Jabatan');
    }
}
