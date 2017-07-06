<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PejabatDokumen;
use App\Models\Users;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Intervensi;

use Auth;
use Validator;
use DB;

class PejabatDokumenController extends Controller
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
      $pejabat = PejabatDokumen::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_pejabat_dokumen.pegawai_id')
                                ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                ->select('preson_pejabat_dokumen.*', 'preson_pegawais.nip_sapk', 'preson_pegawais.nama')
                                ->limit(2)
                                ->get();
      $limit = count($pejabat);
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      $pegawai = pegawai::select('id', 'nip_sapk', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();

      return view('pages.pejabatdokumen.index', compact('pejabat', 'pegawai', 'limit', 'getunreadintervensi'));
    }

    public function store(Request $request)
    {
      $message = [
        'pegawai_id.required' => 'Wajib di isi',
        'pangkat.required' => 'Wajib di isi',
        'jabatan.required' => 'Wajib di isi',
        'posisi_ttd.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'pegawai_id' => 'required',
        'pangkat' => 'required',
        'jabatan' => 'required',
        'posisi_ttd' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pejabatdokumen.index')->withErrors($validator)->withInput();
      }

      $set = new PejabatDokumen;
      $set->pegawai_id = $request->pegawai_id;
      $set->jabatan = $request->jabatan;
      $set->pangkat = $request->pangkat;
      $set->posisi_ttd = $request->posisi_ttd;
      $set->flag_status = 1;
      $set->save();

      return redirect()->route('pejabatdokumen.index')->with('berhasil', 'Berhasil Menambahkan Pejabat Dokumen');

    }

    public function bind($id)
    {
      $pejabatdokumen = pejabatdokumen::find($id);

      return $pejabatdokumen;
    }

    public function edit(Request $request)
    {
      $set = pejabatdokumen::find($request->pejabatdokumen_id);
      $set->pegawai_id = $request->pegawai_id;
      $set->jabatan = $request->jabatan;
      $set->pangkat = $request->pangkat;
      $set->posisi_ttd = $request->posisi_ttd;
      $set->update();

      return redirect()->route('pejabatdokumen.index')->with('berhasil', 'Berhasil Mengubah Data Pejabat Dokumen');
    }

    public function changeflag($id)
    {
      $get = pejabatdokumen::find($id);

      if($get->flag_status=="1") {
        $get->flag_status = "0";
      } elseif($get->flag_status=="0") {
        $get->flag_status = "1";
      }

      $get->save();

      return redirect()->route('pejabatdokumen.index')->with('message', 'Berhasil Mengubah Status Pejabat.');
    }
}
