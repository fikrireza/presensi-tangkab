<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Golongan;
use App\Models\Struktural;
use App\Models\User;
use App\Models\Intervensi;

use Validator;
use Auth;
use DB;
use Hash;
use Datatables;

class PegawaiController extends Controller
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
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.pegawai.index')->with('getunreadintervensi', $getunreadintervensi);
    }

    public function getPegawai(Request $request)
    {
      if($request->ajax()){
        DB::statement(DB::raw('set @rownum=0'));
        if(session('status') == 'administrator' || session('status') == 'superuser'){
          $pegawai = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->join('preson_golongans', 'preson_golongans.id', '=', 'preson_pegawais.golongan_id')
                            ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                            ->select(DB::raw('@rownum  := @rownum  + 1 AS no'), 'preson_pegawais.id', 'preson_pegawais.nip_sapk', 'preson_pegawais.fid', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.jabatan', 'preson_skpd.nama as nama_skpd', 'preson_golongans.nama as nama_golongan', 'preson_strukturals.nama as nama_struktural')
                            ->get();

          return Datatables::of($pegawai)
                            ->addColumn('action', function($pegawai){
                              return '<a class="btn btn-xs btn-warning" href="pegawai/edit/'.$pegawai->id.'"><i class="fa fa-edit"></i> Ubah</a></br><a class="btn btn-xs btn-success" href="mutasi/create/'.$pegawai->id.'"><i class="fa fa-code-fork"></i> Mutasi</a>';
                            })
                          ->make(true);

        }elseif(session('status') == 'admin'){
          $pegawai = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->join('preson_golongans', 'preson_golongans.id', '=', 'preson_pegawais.golongan_id')
                            ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                            ->select(DB::raw('@rownum  := @rownum  + 1 AS no'), 'preson_pegawais.id', 'preson_pegawais.nip_sapk', 'preson_pegawais.fid', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.jabatan', 'preson_skpd.nama as nama_skpd', 'preson_golongans.nama as nama_golongan', 'preson_strukturals.nama as nama_struktural')
                            ->where('preson_skpd.id', Auth::user()->skpd_id)
                            ->get();

          return Datatables::of($pegawai)
                            ->addColumn('action', function($pegawai){
                              return '<a class="btn btn-xs btn-warning" href="pegawai/edit/'.$pegawai->id.'"><i class="fa fa-edit"></i> Ubah</a>';
                            })
                          ->make(true);

        }
      } else {
         abort('403');
      }
    }

    public function create()
    {
      if(session('status') == 'admin')
      {
        $skpd = skpd::where('id', Auth::user()->skpd_id)->select('id', 'nama')->get();
      }
      else {
        $skpd = skpd::select('id', 'nama')->get();
      }
      $golongan = golongan::select('id', 'nama')->get();
      $struktural = struktural::select('id', 'nama')->get();
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.pegawai.create', compact('skpd', 'golongan', 'struktural', 'getunreadintervensi'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama_pegawai.required' => 'Wajib di isi',
        'nip_sapk.required' => 'Wajib di isi',
        'nip_sapk.unique' => 'NIP Sudah Dipakai',
        'fid.required' => 'Wajib di isi',
        'fid.unique'  => 'Finger ID Sudah Dipakai',
        'skpd_id.required' => 'Wajib di isi',
        'golongan_id.required' => 'Wajib di isi',
        'jabatan.required' => 'Wajib di isi',
        'struktural_id.required' => 'Wajib di isi',
        'tanggal_lahir.required' => 'Wajib di isi',
        'tempat_lahir.required' => 'Wajib di isi',
        'pendidikan_terakhir.required' => 'Wajib di isi',
        'alamat.required' => 'Wajib di isi',
        'tpp_dibayarkan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_pegawai' => 'required',
        'nip_sapk' => 'required|unique:preson_pegawais',
        'fid' => 'required|unique:preson_pegawais',
        'skpd_id' => 'required',
        'golongan_id' => 'required',
        'jabatan' => 'required',
        'struktural_id' => 'required',
        'tanggal_lahir' => 'required',
        'tempat_lahir' => 'required',
        'pendidikan_terakhir' => 'required',
        'alamat' => 'required',
        'tpp_dibayarkan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pegawai.create')->withErrors($validator)->withInput();
      }

      $set = new pegawai;
      $set->nama = $request->nama_pegawai;
      $set->nip_sapk = $request->nip_sapk;
      $set->nip_lm  = $request->nip_lm;
      $set->fid   = $request->fid;
      $set->skpd_id = $request->skpd_id;
      $set->golongan_id = $request->golongan_id;
      $set->jabatan = strtoupper($request->jabatan);
      $set->struktural_id = $request->struktural_id;
      $set->tanggal_lahir = $request->tanggal_lahir;
      $set->tempat_lahir = $request->tempat_lahir;
      $set->pendidikan_terakhir = $request->pendidikan_terakhir;
      $set->alamat  = $request->alamat;
      $set->tpp_dibayarkan  = $request->tpp_dibayarkan;
      $set->actor = Auth::user()->id;
      $set->status = 1;
      $set->upload_dokumen = "-";
      $set->save();

      $pegawai_id = pegawai::select('id')->where('nip_sapk', $request->nip_sapk)->first();

      $new = new user;
      $new->nip_sapk = $request->nip_sapk;
      $new->nama = $request->nama_pegawai;
      $new->password = Hash::make(12345678);
      $new->email = strtolower(str_replace(' ','', $request->nama_pegawai)).'@tangerangkab.go.id';
      $new->role_id = 3;
      $new->skpd_id = $request->skpd_id;
      $new->pegawai_id = $pegawai_id->id;
      $new->save();


      return redirect()->route('pegawai.index')->with('berhasil', 'Pegawai Baru Berhasil di Tambahkan');
    }

    public function edit($id)
    {
      $pegawai = pegawai::find($id);

      if($pegawai == null){
        abort(404);
      }

      if(session('status') == 'admin')
      {
        $skpd = skpd::where('id', Auth::user()->skpd_id)->select('id', 'nama')->get();
      }
      else {
        $skpd = skpd::select('id', 'nama')->get();
      }

      $golongan = golongan::select('id', 'nama')->get();
      $struktural = struktural::select('id', 'nama')->get();


      return view('pages.pegawai.edit', compact('pegawai', 'skpd', 'golongan', 'struktural'));
    }

    public function editStore(Request $request)
    {
      // dd($request);
      $message = [
        'nama_pegawai.required' => 'Wajib di isi',
        'nip_sapk.required' => 'Wajib di isi',
        'fid.required' => 'Wajib di isi',
        'fid.unique'  => 'Finger ID ini Sudah di Pakai',
        'skpd_id.required' => 'Wajib di isi',
        'golongan_id.required' => 'Wajib di isi',
        'jabatan.required' => 'Wajib di isi',
        'struktural_id.required' => 'Wajib di isi',
        'tanggal_lahir.required' => 'Wajib di isi',
        'tempat_lahir.required' => 'Wajib di isi',
        'pendidikan_terakhir.required' => 'Wajib di isi',
        'alamat.required' => 'Wajib di isi',
        'tpp_dibayarkan.required' => 'Wajib di isi',
        'status.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_pegawai' => 'required',
        'nip_sapk' => 'required',
        'fid' => 'required|unique:preson_pegawais,fid,'.$request->pegawai_id,
        'skpd_id' => 'required',
        'golongan_id' => 'required',
        'jabatan' => 'required',
        'struktural_id' => 'required',
        'tanggal_lahir' => 'required',
        'tempat_lahir' => 'required',
        'pendidikan_terakhir' => 'required',
        'alamat' => 'required',
        'tpp_dibayarkan' => 'required',
        'status' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pegawai.edit', ['id' => $request->pegawai_id])->withErrors($validator)->withInput();
      }

      $file = $request->file('upload_dokumen');
      if($file != null)
      {
        $photo_name = $request->nip_sapk.'-'.date('Y-m-d').'-'.$request->nama_pegawai.'.' . $file->getClientOriginalExtension();
        $file->move('documents/', $photo_name);
      }else{
        $photo_name = "-";

      }

      $set = pegawai::find($request->pegawai_id);
      $set->nama = $request->nama_pegawai;
      $set->nip_sapk = $request->nip_sapk;
      $set->nip_lm  = $request->nip_lm;
      $set->fid   = $request->fid;
      $set->skpd_id = $request->skpd_id;
      $set->golongan_id = $request->golongan_id;
      $set->jabatan = strtoupper($request->jabatan);
      $set->struktural_id = $request->struktural_id;
      $set->tanggal_lahir = $request->tanggal_lahir;
      $set->tempat_lahir = $request->tempat_lahir;
      $set->pendidikan_terakhir = $request->pendidikan_terakhir;
      $set->alamat  = $request->alamat;
      $set->tpp_dibayarkan  = $request->tpp_dibayarkan;
      $set->actor = Auth::user()->id;
      $set->status = $request->status;
      $set->upload_dokumen = $photo_name;

      if ($request->status==2 || $request->status==3 || $request->status==4) {
        $set->tanggal_akhir_kerja = date('Y-m-d');
      }

      $set->update();

      $update = user::where('pegawai_id', '=', $request->pegawai_id)->first();
      $update->nama = $request->nama_pegawai;
      $update->nip_sapk = $request->nip_sapk;
      $update->skpd_id = $request->skpd_id;
      $update->update();

      return redirect()->route('pegawai.index')->with('berhasil', 'Behasil Mengubah Data Pegawai');
    }
}
