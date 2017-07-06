<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManajemenIntervensi;

class ManajemenIntervensiController extends Controller
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
      $get = ManajemenIntervensi::where('flag_old', 0)->get();
      return view('pages/manajemen-intervensi/index')->with('getintervensi', $get);
    }

    public function store(Request $request)
    {
      $set = new ManajemenIntervensi;
      $set->nama_intervensi = $request->nama;
      $set->flag_old = 0;
      $set->save();

      return redirect()->route('manajemenintervensi.index')->with('berhasil', 'Berhasil Menambahkan Intervensi');
    }
}
