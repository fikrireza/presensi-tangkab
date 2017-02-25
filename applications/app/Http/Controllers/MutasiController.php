<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Mutasi;

use Validator;
use Auth;
use DB;
use Hash;

class MutasiController extends Controller
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
      $mutasi = Mutasi::get();

      $getmutasi = Mutasi::Select('preson_mutasi.id','preson_mutasi.pegawai_id', 'preson_mutasi.skpd_id_new', DB::raw('count(preson_mutasi.pegawai_id) as jumlahmutasi'))
                  ->where('preson_mutasi.skpd_id_new', Auth::user()->skpd_id)
                  ->whereNotIn('preson_mutasi.pegawai_id', [Auth::user()->id])
                  ->groupBy('preson_mutasi.pegawai_id')
                  ->orderby('jumlahmutasi', 'desc')
                  ->get();

      return view('pages.mutasi.index', compact('getmutasi','mutasi'));
    }

    public function create($id)
    {

      $getpegskpd = Pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                ->Select('preson_pegawais.id as pegawai_id', 'preson_pegawais.nama as pegawai_nama', 'preson_skpd.id as skpd_id', 'preson_skpd.nama as skpd_nama')->Where('preson_pegawais.id','=',$id)->first();


      $getskpd = Skpd::whereNotIn('id', [$getpegskpd->skpd_id])->get();

      return view('pages.mutasi.create', compact('getskpd', 'getpegskpd'));
    }


    public function createStore(Request $request)
    {
      // dd($request->file('upload_sk'));
      $message = [
        'skpd_id_new.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'tanggal_mutasi.required' => 'Wajib di isi',
        // 'tpp_dibayarkan.required' => 'Wajib di isi',
        'nomor_sk.required' => 'Wajib di isi',
        'tanggal_sk.required' => 'Wajib di isi',
        'upload_sk.*.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'skpd_id_new' => 'required',
        'keterangan' => 'required',
        'tanggal_mutasi' => 'required',
        // 'tpp_dibayarkan' => 'required',
        'nomor_sk' => 'required',
        'tanggal_sk' => 'required',
        'upload_sk.*' => 'required|mimes:jpeg,png,pdf,jpg',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('mutasi.create', ['id' => $request->pegawai_id])->withErrors($validator)->withInput();
      }
      $fileTemp = $request->file('upload_sk') ;
      if ($fileTemp != null) {
        $i = 1;
         $doc_name = '';
          foreach ($request->file('upload_sk') as $key) {
            $file = $request->upload_sk[$i];
            $file_name = $request->nama_pegawai.'-'.$request->nomor_sk.'-'.$request->tanggal_sk.'-'.$i.'.'. $file->getClientOriginalExtension();
            $doc_name .= $file_name.'//'; 
            
            $file->move('documents/', $file_name);
            $i++;
          }
      } else {
        $doc_name = '-';
      }
        
      $new = new Mutasi;
      $new->pegawai_id = $request->pegawai_id;
      $new->skpd_id_old = $request->skpd_id_old;
      $new->skpd_id_new = $request->skpd_id_new;
      $new->tanggal_mutasi = $request->tanggal_mutasi;
      $new->keterangan = $request->keterangan;
      $new->tpp_dibayarkan = $request->tpp_dibayarkan;
      $new->nomor_sk = $request->nomor_sk;
      $new->tanggal_sk = $request->tanggal_sk;
      $new->upload_sk = $doc_name;
      $new->actor = Auth::user()->id;
      $new->save();

      $set = pegawai::find($request->pegawai_id);
      $set->skpd_id = $request->skpd_id_new;
      $set->flag_mutasi = 1;
      $set->update();

      // $akses = User::where('pegawai_id', $request->pegawai_id)->get();
      // $akses->role_id = 3;
      // $akses->update();

      return redirect()->route('pegawai.index')->with('berhasil', 'Pegawai Berhasil Dimutasi');
    }


    public function view($id)
    {

      $getmutasi = Mutasi::Where('pegawai_id', $id)->orderBy('created_at','desc')->paginate(5);
      $empty = "";
      if ($getmutasi[0] != null) {
        $empty = "Tidak Kosong";
      } else {
        $empty = "Kosong";
      }
      // dd($getmutasi);
      return view('pages.mutasi.view', compact('getmutasi','empty'));
    }

    public function viewPegawai()
    {
      $getmutasi = Mutasi::Where('pegawai_id', Auth::user()->pegawai_id)->orderBy('created_at','desc')->paginate(5);
      $empty = "";
      if ($getmutasi[0] != null) {
        $empty = "Tidak Kosong";
      } else {
        $empty = "Kosong";
      }

      return view('pages.mutasi.view', compact('getmutasi','empty'));
    }
}
