<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\ManajemenIntervensi;
use App\Models\Intervensi;

use Validator;
use Auth;
use DB;
use Hash;

class RevisiIntervensiController extends Controller
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
       $getrevisiintervensi = DB::table('preson_intervensis')->select('preson_intervensis.*',
                      'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
                  ->leftJoin('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
                  ->orderby('preson_intervensis.created_at', 'desc')
                  ->where('preson_intervensis.id_intervensi', 9999)->get();
      // dd($getrevisiintervensi);
      return view('pages.revisiintervensi.index', compact('getrevisiintervensi'));
    }


    public function create()
    {
      $getskpd = Skpd::select('*')->get();
      $getcaripegawai = null;
      // dd($getcaripegawai);
      $skpd_id = "";
      return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai','skpd_id'));
    }


    public function caripegawai(Request $request)
      {
      // dd($request);
      $message = [
        'skpd.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'skpd' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

      $getskpd = Skpd::select('*')->get();
      $skpd_id = $request->skpd;
      $getcaripegawai = Pegawai::select('*')->where('skpd_id', $request->skpd)->get();
      // dd($getcaripegawai);

        return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai','skpd_id'));
    }

    public function createStore(Request $request)
    {
      // dd($request);
       $message = [
        // 'skpd.required' => 'Wajib di isi',
        'tanggal_awal.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'upload_revisi' => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        // 'skpd' => 'required',
        'tanggal_awal' => 'required',
        'tanggal_akhir' => 'required',
        'keterangan' => 'required',
        'upload_revisi'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

      // dd($request);
      $file = $request->file('upload_revisi');
          
        if($file != null)
        {
          $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_awal.'-'.$request->tanggal_akhir.'.' . $file->getClientOriginalExtension();
          $file->move('documents/', $photo_name);
        }else{
          $photo_name = "-";

        }
      // dd($request->idpegawai);
      if ($request->idpegawai != null) {
        foreach ($request->idpegawai as $key) {
            $set = new Intervensi;
            $set->pegawai_id = $key;
            $set->id_intervensi = 9999;
            $getnamaintervensi = ManajemenIntervensi::find(9999);
            $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
            $set->jumlah_hari = $request->jumlah_hari;
            $set->tanggal_mulai = $request->tanggal_awal;
            $set->tanggal_akhir = $request->tanggal_akhir;
            $set->deskripsi = $request->keterangan;
            $set->berkas = $photo_name;
            $set->flag_status = 0;
            $set->actor = Auth::user()->pegawai_id;
            $set->save(); 
        }
        return redirect()->route('revisiintervensi.index')->with('berhasil', 'Pegawai Berhasil Dimutasi');
        
      }else{

      }
    }

    
}
