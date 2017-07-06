<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pengecualian;
use App\Models\Pegawai;


use Validator;
use Auth;
use DB;

class PengecualianController extends Controller
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

      if(session('status') === 'admin')
      {
        $pegawai = pegawai::select('id', 'nip_sapk', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();
      }
      elseif(session('status') === 'administrator' || session('status') === 'superuser')
      {
        $pegawai = pegawai::select('id', 'nip_sapk', 'nama')->get();
      }

      $pengecualian = DB::table('preson_pengecualian_tpp')->select('preson_pengecualian_tpp.*',
                      'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
                  ->leftJoin('preson_pegawais', 'preson_pengecualian_tpp.nip_sapk', '=', 'preson_pegawais.nip_sapk')
                  ->orderby('preson_pengecualian_tpp.created_at', 'desc')
                  ->get();
      // dd($pengecualian);
      return view('pages.pengecualian.index', compact('pengecualian', 'pegawai'));
    }

    public function store(Request $request)
    {
      $message = [
        'nip_sapk.required' => 'Wajib di isi',
        'catatan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nip_sapk' => 'required|max:150',
        'catatan' => 'required|max:500',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pengecualian.index')->withErrors($validator)->withInput();
      }

      $check = Pengecualian::where('nip_sapk', $request->nip_sapk)->first();
      if($check=="") {
        $set = new Pengecualian;
        $set->nip_sapk = $request->nip_sapk;
        $set->catatan = $request->catatan;
        $set->status = 1;
        $set->actor = Auth::user()->pegawai_id;
        $set->save();
        return redirect()->route('pengecualian.index')->with('berhasil', 'Berhasil Menambahkan Data Pengecualian');
      } else {
        return redirect()->route('pengecualian.index')->with('gagal', 'Gagal melakukan simpan data. Data pegawai sudah ada.');
      }
    }

    public function bind($id)
    {
      $get = Pengecualian::where('preson_pengecualian_tpp.id', $id)
                          ->leftJoin('preson_pegawais', 'preson_pengecualian_tpp.nip_sapk', '=', 'preson_pegawais.nip_sapk')
                          ->select('preson_pengecualian_tpp.*', 'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
                          ->first();
         // $get = DB::table('preson_pengecualian_tpp')->select('preson_pengecualian_tpp.*',
         //              'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
         //          ->leftJoin('preson_pegawais', 'preson_pengecualian_tpp.nip_sapk', '=', 'preson_pegawais.nip_sapk')
         //          ->where('preson_pengecualian_tpp.id', $id)
         //          ->first();
      return $get;
    }

    public function edit(Request $request)
    {
      // dd($request);
      $message = [
        'catatan_edit.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'catatan_edit' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pengecualian.index')->withErrors($validator)->withInput();
      }
      // dd($request);
      $set = Pengecualian::find($request->id);
      $set->nip_sapk = $request->nip_sapk_edit;
      $set->catatan = $request->catatan_edit;
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('pengecualian.index')->with('berhasil', 'Berhasil Mengubah Data Pengecualian');
    }


    public function delete($id)
    {
      $set = Pengecualian::find($id);
      $set->delete();

      return redirect()->route('pengecualian.index')->with('berhasil', 'Berhasil menghapus pengecualian.');
    }
}
